<?php

namespace Tests\Unit;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    public function test_returns_401_when_no_authenticated_user(): void
    {
        $middleware = new RoleMiddleware;
        $request = Request::create('/api/users', 'POST');

        $response = $middleware->handle(
            $request,
            static fn (): Response => response()->json(['ok' => true]),
            'admin',
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_returns_403_when_no_roles_are_configured(): void
    {
        $user = $this->makeUserWithRole('admin');
        $middleware = new RoleMiddleware;
        $request = Request::create('/api/users', 'POST');
        $request->setUserResolver(static fn (): User => $user);

        $response = $middleware->handle(
            $request,
            static fn (): Response => response()->json(['ok' => true]),
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_returns_403_when_user_has_no_role(): void
    {
        $user = new User(['name' => 'Sin Rol', 'email' => 'norole@smai.com']);
        $user->setRelation('role', null);

        $middleware = new RoleMiddleware;
        $request = Request::create('/api/users', 'POST');
        $request->setUserResolver(static fn (): User => $user);

        $response = $middleware->handle(
            $request,
            static fn (): Response => response()->json(['ok' => true]),
            'admin',
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_returns_403_when_user_role_is_not_allowed(): void
    {
        $user = $this->makeUserWithRole('cliente');
        $middleware = new RoleMiddleware;
        $request = Request::create('/api/users/1', 'DELETE');
        $request->setUserResolver(static fn (): User => $user);

        $response = $middleware->handle(
            $request,
            static fn (): Response => response()->json(['ok' => true]),
            'admin',
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_allows_request_when_user_has_required_role_case_insensitively(): void
    {
        $user = $this->makeUserWithRole('Admin');
        $middleware = new RoleMiddleware;
        $request = Request::create('/api/users', 'POST');
        $request->setUserResolver(static fn (): User => $user);

        $response = $middleware->handle(
            $request,
            static fn (): Response => response()->json(['ok' => true]),
            'admin',
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_allows_request_when_user_matches_any_of_multiple_roles(): void
    {
        $supportUser = $this->makeUserWithRole('soporte');
        $clientUser = $this->makeUserWithRole('cliente');
        $middleware = new RoleMiddleware;

        $supportRequest = Request::create('/api/users/1', 'PUT');
        $supportRequest->setUserResolver(static fn (): User => $supportUser);

        $clientRequest = Request::create('/api/users/1', 'PUT');
        $clientRequest->setUserResolver(static fn (): User => $clientUser);

        $supportResponse = $middleware->handle(
            $supportRequest,
            static fn (): Response => response()->json(['ok' => true]),
            'admin',
            'soporte',
        );

        $clientResponse = $middleware->handle(
            $clientRequest,
            static fn (): Response => response()->json(['ok' => true]),
            'admin',
            'soporte',
        );

        $this->assertSame(200, $supportResponse->getStatusCode());
        $this->assertSame(403, $clientResponse->getStatusCode());
    }

    private function makeUserWithRole(string $roleName): User
    {
        $role = new Role(['name' => $roleName]);
        $user = new User(['name' => 'Test User', 'email' => 'test@smai.com']);
        $user->setRelation('role', $role);

        return $user;
    }
}
