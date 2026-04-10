<?php

namespace Tests\Integration\Api\Remote;

use App\Models\Node;
use App\Models\Permission;
use App\Models\Server;
use App\Models\User;
use App\Models\UserSSHKey;
use phpseclib3\Crypt\EC;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class SftpAuthenticationControllerTest extends IntegrationTestCase
{
    protected User $user;

    protected Server $server;

    /**
     * Sets up the tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        [$user, $server] = $this->generateTestAccount();

        $user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->user = $user;
        $this->server = $server;

        $this->setAuthorization();
    }

    /**
     * Test that a public key is validated correctly.
     */
    public function test_public_key_is_validated_correctly()
    {
        $key = UserSSHKey::factory()->for($this->user)->create();

        $this->postJson('/api/remote/sftp/auth', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'username')
            ->assertJsonPath('errors.0.meta.rule', 'required')
            ->assertJsonPath('errors.1.meta.source_field', 'password')
            ->assertJsonPath('errors.1.meta.rule', 'required');

        $data = [
            'type' => 'public_key',
            'username' => $this->getUsername(),
            'password' => $key->public_key,
        ];

        $this->postJson('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('server', $this->server->uuid)
            ->assertJsonPath('permissions', ['*']);

        $key->delete();
        $this->postJson('/api/remote/sftp/auth', $data)->assertForbidden();
        $this->postJson('/api/remote/sftp/auth', array_merge($data, ['type' => null]))->assertForbidden();
    }

    /**
     * Test that an account password is validated correctly.
     */
    public function test_password_is_validated_correctly()
    {
        $this->postJson('/api/remote/sftp/auth', [
            'username' => $this->getUsername(),
            'password' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'password')
            ->assertJsonPath('errors.0.meta.rule', 'required');

        $this->postJson('/api/remote/sftp/auth', [
            'username' => $this->getUsername(),
            'password' => 'wrong password',
        ])
            ->assertForbidden();

        $this->user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->postJson('/api/remote/sftp/auth', [
            'username' => $this->getUsername(),
            'password' => 'foobar',
        ])
            ->assertOk();
    }

    /**
     * Test that providing an invalid key and/or invalid username triggers the throttle on
     * the endpoint.
     */
    #[DataProvider('authorizationTypeDataProvider')]
    public function test_user_is_throttled_if_invalid_credentials_are_provided(string $type)
    {
        $statuses = [];

        for ($i = 0; $i <= 10; $i++) {
            $statuses[] = $this->postJson('/api/remote/sftp/auth', [
                'type' => $type,
                'username' => $i % 2 === 0 ? $this->user->username : $this->getUsername(),
                'password' => 'invalid key',
            ])
                ->getStatusCode();
        }

        $this->assertSame(403, $statuses[0]);
        $this->assertContains(429, $statuses);

        $firstLockout = array_search(429, $statuses, true);
        $this->assertNotFalse($firstLockout);

        for ($i = $firstLockout; $i < count($statuses); $i++) {
            $this->assertSame(429, $statuses[$i]);
        }
    }

    /**
     * Test that the user is not throttled so long as a valid public key is provided, even
     * if it doesn't actually exist in the database for the user.
     */
    public function test_user_is_not_throttled_if_no_public_key_matches()
    {
        for ($i = 0; $i <= 10; $i++) {
            $this->postJson('/api/remote/sftp/auth', [
                'type' => 'public_key',
                'username' => $this->getUsername(),
                'password' => EC::createKey('Ed25519')->getPublicKey()->toString('OpenSSH'),
            ])
                ->assertForbidden();
        }
    }

    /**
     * Test that a request is rejected if the credentials are valid but the username indicates
     * a server on a different node.
     */
    #[DataProvider('authorizationTypeDataProvider')]
    public function test_request_is_rejected_if_server_belongs_to_different_node(string $type)
    {
        $node2 = $this->createServerModel()->node;

        $this->setAuthorization($node2);

        $password = $type === 'public_key'
            ? UserSSHKey::factory()->for($this->user)->create()->public_key
            : 'foobar';

        $this->postJson('/api/remote/sftp/auth', [
            'type' => 'public_key',
            'username' => $this->getUsername(),
            'password' => $password,
        ])
            ->assertForbidden();
    }

    public function test_request_is_denied_if_user_lacks_sftp_permission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ]);

        $user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->setAuthorization($server->node);

        $this->postJson('/api/remote/sftp/auth', [
            'username' => $user->username.'.'.$server->uuidShort,
            'password' => 'foobar',
        ])
            ->assertForbidden()
            ->assertJsonPath('errors.0.detail', 'Authorization credentials were not correct, please try again.');
    }

    #[DataProvider('serverStateDataProvider')]
    public function test_invalid_server_state_returns_conflict_error(string $status)
    {
        $this->server->update(['status' => $status]);

        $this->postJson('/api/remote/sftp/auth', ['username' => $this->getUsername(), 'password' => 'foobar'])
            ->assertStatus(409);
    }

    /**
     * Test that permissions are returned for the user account correctly.
     */
    public function test_user_permissions_are_returned_correctly()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ, Permission::ACTION_FILE_SFTP]);

        $user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->setAuthorization($server->node);

        $data = [
            'username' => $user->username.'.'.$server->uuidShort,
            'password' => 'foobar',
        ];

        $this->postJson('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('permissions', [Permission::ACTION_FILE_READ, Permission::ACTION_FILE_SFTP]);

        $user->update(['root_admin' => true]);

        $this->postJson('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('permissions.0', '*');

        $this->setAuthorization();
        $data['username'] = $user->username.'.'.$this->server->uuidShort;

        $this->post('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('permissions.0', '*');

        $user->update(['root_admin' => false]);
        $this->post('/api/remote/sftp/auth', $data)->assertForbidden();
    }

    public static function authorizationTypeDataProvider(): array
    {
        return [
            'password auth' => ['password'],
            'public key auth' => ['public_key'],
        ];
    }

    public static function serverStateDataProvider(): array
    {
        return [
            'installing' => [Server::STATUS_INSTALLING],
            'suspended' => [Server::STATUS_SUSPENDED],
            'restoring a backup' => [Server::STATUS_RESTORING_BACKUP],
        ];
    }

    /**
     * Returns the username for connecting to SFTP.
     */
    protected function getUsername(bool $long = false): string
    {
        return $this->user->username.'.'.($long ? $this->server->uuid : $this->server->uuidShort);
    }

    /**
     * Sets the authorization header for the rest of the test.
     */
    protected function setAuthorization(?Node $node = null): void
    {
        $node = $node ?? $this->server->node;

        $this->withHeader('Authorization', 'Bearer '.$node->daemon_token_id.'.'.decrypt($node->daemon_token));
    }
}
