<?php

namespace Tests\Integration\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\Integration\IntegrationTestCase;

class DatabaseHostPasswordMigrationTest extends IntegrationTestCase
{
    use RefreshDatabase;

    private function runMigration(): void
    {
        $migration = require base_path('database/migrations/2026_06_06_000000_encrypt_plaintext_database_host_passwords.php');
        $migration->up();
    }

    private function insertHost(string $name, string $password): int
    {
        return DB::table('database_hosts')->insertGetId([
            'name' => $name,
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'user',
            'password' => $password,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_migration_encrypts_plaintext_and_leaves_encrypted_untouched(): void
    {
        $plainId = $this->insertHost('plain', 'PlainPass1!');

        $encryptedValue = Crypt::encrypt('EncryptedPass2!');
        $encId = $this->insertHost('encrypted', $encryptedValue);

        $this->runMigration();

        $healed = DB::table('database_hosts')->where('id', $plainId)->value('password');
        $this->assertNotSame('PlainPass1!', $healed);
        $this->assertSame('PlainPass1!', Crypt::decrypt($healed));

        $untouched = DB::table('database_hosts')->where('id', $encId)->value('password');
        $this->assertSame($encryptedValue, $untouched);
        $this->assertSame('EncryptedPass2!', Crypt::decrypt($untouched));
    }

    public function test_migration_is_idempotent(): void
    {
        $plainId = $this->insertHost('plain', 'PlainPass1!');

        $this->runMigration();
        $afterFirst = DB::table('database_hosts')->where('id', $plainId)->value('password');

        $this->runMigration();
        $afterSecond = DB::table('database_hosts')->where('id', $plainId)->value('password');

        $this->assertSame($afterFirst, $afterSecond);
        $this->assertSame('PlainPass1!', Crypt::decrypt($afterSecond));
    }
}
