<?php

namespace Tests\Integration\Api\Application\Users;

use App\Models\User;
use App\Services\Acl\Api\AdminAcl;
use App\Transformers\Api\Application\ServerTransformer;
use App\Transformers\Api\Application\UserTransformer;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class UserControllerTest extends ApplicationApiIntegrationTestCase
{
    public function test_social_accounts_can_be_linked_on_create_and_update(): void
    {
        $data = [
            'email' => 'social@example.com', 'username' => 'social',
            'first_name' => 'Social', 'last_name' => 'User',
            'password' => 'StrongPassword123!',
            'social_accounts' => [['provider' => 'discord', 'provider_user_id' => '900719925474099312345']],
        ];
        $response = $this->postJson('/api/application/users', $data)->assertCreated();
        $id = $response->json('attributes.id');
        $this->assertDatabaseHas('social_logins', [
            'user_id' => $id, 'provider' => 'discord', 'provider_user_id' => '900719925474099312345',
        ]);

        $data['social_accounts'][] = ['provider' => 'github', 'provider_user_id' => '12345'];
        $this->patchJson('/api/application/users/'.$id, $data)->assertOk();
        $this->patchJson('/api/application/users/'.$id, $data)->assertOk();
        unset($data['social_accounts']);
        $this->patchJson('/api/application/users/'.$id, $data)->assertOk();
        $this->assertSame(2, User::findOrFail($id)->socialLogins()->count());
    }

    public function test_social_account_conflict_rolls_back_user_and_links(): void
    {
        $owner = User::factory()->create();
        $owner->socialLogins()->create(['provider' => 'discord', 'provider_user_id' => 'taken']);
        $user = User::factory()->create();
        $data = [
            'email' => 'conflict@example.com', 'username' => 'conflict',
            'first_name' => 'Test', 'last_name' => 'User', 'password' => 'StrongPassword123!',
            'social_accounts' => [
                ['provider' => 'github', 'provider_user_id' => 'new'],
                ['provider' => 'discord', 'provider_user_id' => 'taken'],
            ],
        ];
        $this->postJson('/api/application/users', $data)->assertUnprocessable();
        $this->assertDatabaseMissing('users', ['email' => 'conflict@example.com']);
        $this->patchJson('/api/application/users/'.$user->id, $data)->assertUnprocessable();
        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertDatabaseMissing('social_logins', ['provider' => 'github', 'provider_user_id' => 'new']);
        $this->assertDatabaseHas('social_logins', ['user_id' => $owner->id, 'provider_user_id' => 'taken']);
    }

    public function test_social_account_linking_requires_write_permission(): void
    {
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_users' => AdminAcl::READ]);
        $user = User::factory()->create();
        $data = [
            'email' => 'forbidden@example.com', 'username' => 'forbidden',
            'first_name' => 'Test', 'last_name' => 'User',
            'social_accounts' => [['provider' => 'google', 'provider_user_id' => '123']],
        ];
        $this->postJson('/api/application/users', $data)->assertForbidden();
        $this->patchJson('/api/application/users/'.$user->id, $data)->assertForbidden();
        $this->assertDatabaseCount('social_logins', 0);
    }

    public function test_social_account_input_requires_supported_provider_and_string_id(): void
    {
        $user = User::factory()->create();
        $data = [
            'email' => $user->email, 'username' => $user->username,
            'first_name' => 'Test', 'last_name' => 'User',
        ];
        foreach ([
            ['provider' => 'unsupported', 'provider_user_id' => '123'],
            ['provider' => 'discord', 'provider_user_id' => 123],
            ['provider' => 'discord', 'provider_user_id' => '123', 'user_id' => $user->id],
        ] as $account) {
            $this->patchJson('/api/application/users/'.$user->id, $data + ['social_accounts' => [$account]])
                ->assertUnprocessable();
        }
        $this->assertSame(0, $user->socialLogins()->count());
    }

    /**
     * Test the response when requesting all users on the panel.
     */
    public function test_get_users()
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/application/users?per_page=60');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'object',
            'data' => [
                ['object', 'attributes' => ['id', 'external_id', 'uuid', 'username', 'email', 'first_name', 'last_name', 'language', 'root_admin', '2fa', 'created_at', 'updated_at']],
                ['object', 'attributes' => ['id', 'external_id', 'uuid', 'username', 'email', 'first_name', 'last_name', 'language', 'root_admin', '2fa', 'created_at', 'updated_at']],
            ],
            'meta' => ['pagination' => ['total', 'count', 'per_page', 'current_page', 'total_pages']],
        ]);

        $response
            ->assertJson([
                'object' => 'list',
                'data' => [[], []],
                'meta' => [
                    'pagination' => [
                        'total' => 2,
                        'count' => 2,
                        'per_page' => 60,
                        'current_page' => 1,
                        'total_pages' => 1,
                    ],
                ],
            ])
            ->assertJsonFragment([
                'object' => 'user',
                'attributes' => [
                    'id' => $this->getApiUser()->id,
                    'external_id' => $this->getApiUser()->external_id,
                    'uuid' => $this->getApiUser()->uuid,
                    'username' => $this->getApiUser()->username,
                    'email' => $this->getApiUser()->email,
                    'first_name' => $this->getApiUser()->name_first,
                    'last_name' => $this->getApiUser()->name_last,
                    'language' => $this->getApiUser()->language,
                    'root_admin' => $this->getApiUser()->root_admin,
                    '2fa' => (bool) $this->getApiUser()->totp_enabled,
                    'created_at' => $this->formatTimestamp($this->getApiUser()->created_at),
                    'updated_at' => $this->formatTimestamp($this->getApiUser()->updated_at),
                ],
            ])
            ->assertJsonFragment([
                'object' => 'user',
                'attributes' => [
                    'id' => $user->id,
                    'external_id' => $user->external_id,
                    'uuid' => $user->uuid,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->name_first,
                    'last_name' => $user->name_last,
                    'language' => $user->language,
                    'root_admin' => (bool) $user->root_admin,
                    '2fa' => (bool) $user->totp_enabled,
                    'created_at' => $this->formatTimestamp($user->created_at),
                    'updated_at' => $this->formatTimestamp($user->updated_at),
                ],
            ]);
    }

    /**
     * Test getting a single user.
     */
    public function test_get_single_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/application/users/'.$user->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'object',
            'attributes' => ['id', 'external_id', 'uuid', 'username', 'email', 'first_name', 'last_name', 'language', 'root_admin', '2fa', 'created_at', 'updated_at'],
        ]);

        $response->assertJson([
            'object' => 'user',
            'attributes' => [
                'id' => $user->id,
                'external_id' => $user->external_id,
                'uuid' => $user->uuid,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->name_first,
                'last_name' => $user->name_last,
                'language' => $user->language,
                'root_admin' => (bool) $user->root_admin,
                '2fa' => (bool) $user->totp_enabled,
                'created_at' => $this->formatTimestamp($user->created_at),
                'updated_at' => $this->formatTimestamp($user->updated_at),
            ],
        ]);
    }

    /**
     * Test that the correct relationships can be loaded.
     */
    public function test_relationships_can_be_loaded()
    {
        $user = User::factory()->create();
        $server = $this->createServerModel(['user_id' => $user->id]);

        $response = $this->getJson('/api/application/users/'.$user->id.'?include=servers');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'object',
            'attributes' => [
                'id', 'external_id', 'uuid', 'username', 'email', 'first_name', 'last_name', 'language', 'root_admin', '2fa', 'created_at', 'updated_at',
                'relationships' => ['servers' => ['object', 'data' => [['object', 'attributes' => []]]]],
            ],
        ]);

        $response->assertJsonFragment([
            'object' => 'list',
            'data' => [
                [
                    'object' => 'server',
                    'attributes' => $this->getTransformer(ServerTransformer::class)->transform($server),
                ],
            ],
        ]);
    }

    /**
     * Test that attempting to load a relationship that the key does not have permission
     * for returns a null object.
     */
    public function test_key_without_permission_cannot_load_relationship()
    {
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_servers' => 0]);

        $user = User::factory()->create();
        $this->createServerModel(['user_id' => $user->id]);

        $response = $this->getJson('/api/application/users/'.$user->id.'?include=servers');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2)->assertJsonCount(1, 'attributes.relationships');
        $response->assertJsonStructure([
            'attributes' => [
                'relationships' => [
                    'servers' => ['object', 'attributes'],
                ],
            ],
        ]);

        // Just assert that we see the expected relationship IDs in the response.
        $response->assertJson([
            'attributes' => [
                'relationships' => [
                    'servers' => [
                        'object' => 'null_resource',
                        'attributes' => null,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test that an invalid external ID returns a 404 error.
     */
    public function test_get_missing_user()
    {
        $response = $this->getJson('/api/application/users/nil');
        $this->assertNotFoundJson($response);
    }

    /**
     * Test that an authentication error occurs if a key does not have permission
     * to access a resource.
     */
    public function test_error_returned_if_no_permission()
    {
        $user = User::factory()->create();
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_users' => 0]);

        $response = $this->getJson('/api/application/users/'.$user->id);
        $this->assertAccessDeniedJson($response);
    }

    /**
     * Test that a user can be created.
     */
    public function test_create_user()
    {
        config()->set('designify.avatarType', 'critters');

        $response = $this->postJson('/api/application/users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'object',
            'attributes' => ['id', 'external_id', 'uuid', 'username', 'email', 'first_name', 'last_name', 'language', 'root_admin', '2fa', 'created_at', 'updated_at'],
            'meta' => ['resource'],
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'avatar_style' => 'critters',
        ]);

        $user = User::where('username', 'testuser')->first();
        $response->assertJson([
            'object' => 'user',
            'attributes' => $this->getTransformer(UserTransformer::class)->transform($user),
            'meta' => [
                'resource' => route('api.application.users.view', $user->id),
            ],
        ], true);
    }

    public function test_create_user_falls_back_to_gravatar_for_an_invalid_configured_default()
    {
        config()->set('designify.avatarType', 'rings');

        $this->postJson('/api/application/users', [
            'username' => 'fallback-user',
            'email' => 'fallback@example.com',
            'first_name' => 'Fallback',
            'last_name' => 'User',
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'username' => 'fallback-user',
            'avatar_style' => 'gravatar',
        ]);
    }

    /**
     * Test that a user can be updated.
     */
    public function test_update_user()
    {
        $user = User::factory()->create();

        $response = $this->patchJson('/api/application/users/'.$user->id, [
            'username' => 'new.test.name',
            'email' => 'new@emailtest.com',
            'first_name' => $user->name_first,
            'last_name' => $user->name_last,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'object',
            'attributes' => ['id', 'external_id', 'uuid', 'username', 'email', 'first_name', 'last_name', 'language', 'root_admin', '2fa', 'created_at', 'updated_at'],
        ]);

        $this->assertDatabaseHas('users', ['username' => 'new.test.name', 'email' => 'new@emailtest.com']);
        $user = $user->fresh();

        $response->assertJson([
            'object' => 'user',
            'attributes' => $this->getTransformer(UserTransformer::class)->transform($user),
        ]);
    }

    /**
     * Test that a user can be deleted from the database.
     */
    public function test_delete_user()
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $response = $this->delete('/api/application/users/'.$user->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test that an API key without write permissions cannot create, update, or
     * delete a user model.
     */
    #[DataProvider('userWriteEndpointsDataProvider')]
    public function test_api_key_without_write_permissions(string $method, string $url)
    {
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_users' => AdminAcl::READ]);

        if (Str::contains($url, '{id}')) {
            $user = User::factory()->create();
            $url = Str::replace('{id}', $user->id, $url);
        }

        $response = $this->$method($url);
        $this->assertAccessDeniedJson($response);
    }

    /**
     * Endpoints that should return a 403 error when the key does not have write
     * permissions for user management.
     */
    public static function userWriteEndpointsDataProvider(): array
    {
        return [
            ['postJson', '/api/application/users'],
            ['patchJson', '/api/application/users/{id}'],
            ['delete', '/api/application/users/{id}'],
        ];
    }
}
