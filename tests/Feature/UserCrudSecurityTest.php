<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\PresenceVerifierInterface;
use Mockery\MockInterface;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserCrudSecurityTest extends TestCase
{
    /** @var array<int, User> */
    private array $inMemoryUsers = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.secret' => 'test_jwt_secret_key_for_user_service_123456',
            'auth.defaults.guard' => 'api',
            'auth.guards.api.driver' => 'jwt',
            'auth.guards.api.provider' => 'users',
            'auth.providers.users.driver' => 'in_memory_jwt_users',
        ]);

        $admin = $this->buildUser(1, 'Admin', 'admin@smai.com', 1, 'admin');
        $soporte = $this->buildUser(2, 'Soporte', 'soporte@smai.com', 2, 'soporte');
        $cliente = $this->buildUser(3, 'Cliente', 'cliente@smai.com', 3, 'cliente');

        $this->inMemoryUsers = [
            1 => $admin,
            2 => $soporte,
            3 => $cliente,
        ];

        $users = &$this->inMemoryUsers;

        Auth::provider('in_memory_jwt_users', static fn () => new class($users) implements UserProvider
        {
            /** @param array<int, User> $users */
            public function __construct(private array $users)
            {
            }

            public function retrieveById($identifier): ?Authenticatable
            {
                return $this->users[(int) $identifier] ?? null;
            }

            public function retrieveByToken($identifier, $token): ?Authenticatable
            {
                return null;
            }

            public function updateRememberToken(Authenticatable $user, $token): void
            {
            }

            public function retrieveByCredentials(array $credentials): ?Authenticatable
            {
                return null;
            }

            public function validateCredentials(Authenticatable $user, array $credentials): bool
            {
                return false;
            }

            public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
            {
            }
        });

        app()->instance('validation.presence', new class implements PresenceVerifierInterface
        {
            public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = []): int
            {
                if ($collection === 'US_roles' && $column === 'id') {
                    return in_array((int) $value, [1, 2, 3], true) ? 1 : 0;
                }

                if ($collection === 'users' && $column === 'email') {
                    return $value === 'duplicate@smai.com' ? 1 : 0;
                }

                return 0;
            }

            public function getMultiCount($collection, $column, array $values, array $extra = []): int
            {
                return 0;
            }
        });
    }

    public function test_unauthenticated_requests_return_401_json_even_without_accept_header(): void
    {
        $this->get('/api/users')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
        $this->get('/api/users/1')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
        $this->post('/api/users')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
        $this->put('/api/users/1')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
        $this->delete('/api/users/1')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_invalid_jwt_token_is_rejected_with_401(): void
    {
        $this->withHeader('Authorization', 'Bearer invalid.jwt.token')
            ->getJson('/api/users')
            ->assertStatus(401);
    }

    public function test_jwt_custom_claims_include_user_role(): void
    {
        $claims = $this->inMemoryUsers[1]->getJWTCustomClaims();

        $this->assertSame(['role' => 'admin'], $claims);
    }

    public function test_non_admin_user_with_valid_jwt_can_read_users_but_cannot_modify_or_delete(): void
    {
        $cliente = $this->inMemoryUsers[3];
        $token = JWTAuth::fromUser($cliente);

        $this->mock(UserServices::class, function (MockInterface $mock) use ($cliente): void {
            $mock->shouldReceive('getAllUsers')
                ->once()
                ->andReturn(new EloquentCollection([$cliente]));

            $mock->shouldReceive('getUserById')
                ->once()
                ->with(3)
                ->andReturn($cliente);
        });

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/users')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/users/3')
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'cliente@smai.com');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/users', [
                'name' => 'Nuevo',
                'email' => 'nuevo@smai.com',
                'password' => 'secret1234',
                'role_id' => 3,
            ])
            ->assertStatus(403);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/users/3', ['name' => 'Hack', 'role_id' => 1])
            ->assertStatus(403);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/users/3', ['role_id' => 1])
            ->assertStatus(403);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/users/3')
            ->assertStatus(403);
    }

    public function test_support_user_is_denied_on_user_mutation_routes(): void
    {
        $soporte = $this->inMemoryUsers[2];
        $token = JWTAuth::fromUser($soporte);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/users/3', ['role_id' => 1])
            ->assertStatus(403);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/users/3')
            ->assertStatus(403);
    }

    public function test_admin_can_execute_full_user_crud(): void
    {
        $admin = $this->inMemoryUsers[1];
        $token = JWTAuth::fromUser($admin);

        $createdUser = $this->buildUser(10, 'Nuevo Usuario', 'nuevo@smai.com', 2, 'soporte');
        $updatedUser = $this->buildUser(10, 'Usuario Editado', 'nuevo@smai.com', 1, 'admin');

        $this->mock(UserServices::class, function (MockInterface $mock) use ($createdUser, $updatedUser): void {
            $mock->shouldReceive('createUser')
                ->once()
                ->andReturn($createdUser);

            $mock->shouldReceive('updateUser')
                ->once()
                ->with(10, ['name' => 'Usuario Editado', 'role_id' => 1])
                ->andReturn($updatedUser);

            $mock->shouldReceive('deleteUser')
                ->once()
                ->with(10)
                ->andReturn(true);

            $mock->shouldReceive('getUserById')
                ->once()
                ->with(999)
                ->andReturn(null);
        });

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/users', [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@smai.com',
                'password' => 'password123',
                'role_id' => 2,
            ])
            ->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.role.name', 'soporte');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/users/10', [
                'name' => 'Usuario Editado',
                'role_id' => 1,
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.role.name', 'admin');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/users/10')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/users/999')
            ->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_admin_receives_400_for_invalid_optional_id_usage_and_404_for_non_numeric_id(): void
    {
        $admin = $this->inMemoryUsers[1];
        $token = JWTAuth::fromUser($admin);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/users/10', [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@smai.com',
                'password' => 'password123',
                'role_id' => 2,
            ])
            ->assertStatus(400);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/users', ['name' => 'Sin ID'])
            ->assertStatus(400);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/users')
            ->assertStatus(400);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/users/abc')
            ->assertStatus(404);
    }

    private function buildUser(int $id, string $name, string $email, int $roleId, string $roleName): User
    {
        $role = new Role(['name' => $roleName]);
        $role->id = $roleId;

        $user = new User([
            'name' => $name,
            'email' => $email,
            'role_id' => $roleId,
        ]);
        $user->id = $id;
        $user->setRelation('role', $role);

        return $user;
    }
}
