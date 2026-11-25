<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\UserServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function __construct(protected UserServices $userService)
    {
    }

    /**
     * GET /api/users - Listar todos los usuarios.
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Usuarios obtenidos correctamente',
                'data' => $this->userService->getAllUsers(),
            ]);
        } catch (Throwable $e) {
            return $this->serverErrorResponse($e, 'Error al listar usuarios en UserController@index');
        }
    }

    /**
     * GET /api/users/{id} - Ver detalle de un usuario.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario obtenido correctamente',
                'data' => $user,
            ]);
        } catch (Throwable $e) {
            return $this->serverErrorResponse($e, 'Error al consultar usuario en UserController@show');
        }
    }

    /**
     * POST /api/users - Crear un nuevo usuario.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'min:8'],
                'role_id' => ['required_without:role', 'nullable', 'integer', Rule::exists('US_roles', 'id')],
                'role' => ['required_without:role_id', 'nullable'],
            ]);

            $payload = $this->prepareUserPayload($validated);

            if ($payload === null) {
                return $this->invalidRoleResponse();
            }

            $user = $this->userService->createUser($payload);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Throwable $e) {
            return $this->serverErrorResponse($e, 'Error al crear usuario en UserController@store');
        }
    }

    /**
     * PUT|PATCH /api/users/{id} - Actualizar datos o rol de un usuario.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'password' => ['sometimes', 'nullable', 'string', 'min:8'],
                'role_id' => ['sometimes', 'nullable', 'integer', Rule::exists('US_roles', 'id')],
                'role' => ['sometimes', 'nullable'],
            ]);

            $payload = $this->prepareUserPayload($validated);

            if ($payload === null) {
                return $this->invalidRoleResponse();
            }

            $user = $this->userService->updateUser($id, $payload);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $user,
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Throwable $e) {
            return $this->serverErrorResponse($e, 'Error al actualizar usuario en UserController@update');
        }
    }

    /**
     * DELETE /api/users/{id} - Eliminar un usuario.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->userService->deleteUser($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente',
                'data' => null,
            ]);
        } catch (Throwable $e) {
            return $this->serverErrorResponse($e, 'Error al eliminar usuario en UserController@destroy');
        }
    }

    /**
     * Normaliza el payload validado resolviendo role_id desde id, nombre u objeto role cuando corresponda.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>|null
     */
    private function prepareUserPayload(array $validated): ?array
    {
        if (array_key_exists('role_id', $validated) && $validated['role_id'] !== null) {
            $validated['role_id'] = (int) $validated['role_id'];
            unset($validated['role']);

            return $validated;
        }

        if (array_key_exists('role', $validated) && $validated['role'] !== null) {
            $roleValue = $validated['role'];
            $role = null;

            if (is_array($roleValue) && isset($roleValue['id']) && is_numeric($roleValue['id'])) {
                $role = Role::find((int) $roleValue['id']);
            } elseif (is_numeric($roleValue)) {
                $role = Role::find((int) $roleValue);
            } elseif (is_string($roleValue) && trim($roleValue) !== '') {
                $role = Role::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower(trim($roleValue))])
                    ->first();
            } else {
                return null;
            }

            if (! $role) {
                return null;
            }

            $validated['role_id'] = $role->id;
        }

        unset($validated['role']);

        return $validated;
    }

    private function invalidRoleResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'El rol especificado no existe en US_roles.',
            'data' => null,
            'errors' => [
                'role' => ['El rol especificado no existe en US_roles.'],
            ],
        ], 422);
    }

    private function validationErrorResponse(ValidationException $e): JsonResponse
    {
        $messages = $e->validator->errors()->all();

        return response()->json([
            'success' => false,
            'message' => $messages !== [] ? implode(' ', $messages) : $e->getMessage(),
            'data' => null,
            'errors' => $e->errors(),
        ], 422);
    }

    private function serverErrorResponse(Throwable $e, string $context): JsonResponse
    {
        $realMessage = $e->getPrevious()?->getMessage() ?: $e->getMessage();

        Log::error($context, [
            'message' => $realMessage,
            'exception' => $e,
        ]);

        return response()->json([
            'success' => false,
            'message' => $realMessage,
            'data' => null,
        ], 500);
    }
}
