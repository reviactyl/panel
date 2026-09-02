<?php

namespace Tests\Integration\Admin;

use App\Filament\Resources\DatabaseHost\Pages\CreateDatabaseHost;
use App\Filament\Resources\DatabaseHost\Pages\EditDatabaseHost;
use App\Models\DatabaseHost;
use App\Models\User;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class DatabaseHostEncryptionTest extends IntegrationTestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => 1]));
    }

    public function test_creating_host_via_admin_stores_encrypted_password(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateDatabaseHost::class)
            ->fillForm([
                'name' => 'test-host',
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'test_user',
                'password' => 'TestPassword123!',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $host = DatabaseHost::query()->where('name', 'test-host')->firstOrFail();

        $this->assertNotSame('TestPassword123!', $host->getRawOriginal('password'));
        $this->assertSame('TestPassword123!', app(Encrypter::class)->decrypt($host->password));
    }

    public function test_editing_host_with_new_password_stores_encrypted(): void
    {
        $this->actingAsAdmin();

        $host = DatabaseHost::factory()->create([
            'password' => app(Encrypter::class)->encrypt('OldPassword1!'),
        ]);

        Livewire::test(EditDatabaseHost::class, ['record' => $host->getKey()])
            ->fillForm(['password' => 'NewPassword2!'])
            ->call('save')
            ->assertHasNoFormErrors();

        $host->refresh();

        $this->assertSame('NewPassword2!', app(Encrypter::class)->decrypt($host->password));
    }

    public function test_editing_host_without_password_keeps_existing(): void
    {
        $this->actingAsAdmin();

        $host = DatabaseHost::factory()->create([
            'password' => app(Encrypter::class)->encrypt('KeepThisPass1!'),
        ]);

        Livewire::test(EditDatabaseHost::class, ['record' => $host->getKey()])
            ->fillForm(['name' => 'renamed-host'])
            ->call('save')
            ->assertHasNoFormErrors();

        $host->refresh();

        $this->assertSame('renamed-host', $host->name);
        $this->assertSame('KeepThisPass1!', app(Encrypter::class)->decrypt($host->password));
    }
}
