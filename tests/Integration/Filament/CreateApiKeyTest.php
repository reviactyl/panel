<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Api\Pages\CreateApiKey;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\Acl\Api\AdminAcl;
use App\Services\Api\KeyCreationService;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class CreateApiKeyTest extends IntegrationTestCase
{
    public function test_root_can_create_a_key_with_default_permissions(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create(['root_admin' => true]);
        $this->actingAs($admin);

        $page = Livewire::test(CreateApiKey::class)
            ->fillForm(['memo' => 'Default application key'])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = $page->instance()->getRecord();
        $this->assertSame(ApiKey::query()->sole()->id, $record->id);
        $this->assertSame($admin->id, $record->user_id);
        $this->assertSame(ApiKey::TYPE_APPLICATION, $record->key_type);
        foreach (AdminAcl::getResourceList() as $resource) {
            $this->assertSame(AdminAcl::NONE, $record->getAttribute(AdminAcl::COLUMN_IDENTIFIER.$resource));
        }
    }

    public function test_regular_users_cannot_create_application_keys(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateApiKey::class)->assertForbidden();

        $this->assertDatabaseCount('api_keys', 0);
    }

    #[DataProvider('competingKeys')]
    public function test_creation_keeps_the_service_record_when_another_key_is_inserted(int $keyType, bool $sameTimestamp): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->freezeTime();
        $admin = User::factory()->create(['root_admin' => true]);
        $otherUser = User::factory()->create();
        $this->actingAs($admin);

        ApiKey::factory()->for($otherUser)->create(['key_type' => $keyType]);
        $service = app(KeyCreationService::class);
        $created = null;
        $competing = null;
        $mock = Mockery::mock(KeyCreationService::class);
        $mock->shouldReceive('setKeyType')->once()->with(ApiKey::TYPE_APPLICATION)->andReturnSelf();
        $mock->shouldReceive('handle')->once()->andReturnUsing(function (array $data, array $permissions) use ($service, $otherUser, $keyType, $sameTimestamp, &$created, &$competing): ApiKey {
            $created = $service->setKeyType(ApiKey::TYPE_APPLICATION)->handle($data, $permissions);
            $competing = ApiKey::factory()->for($otherUser)->create([
                'key_type' => $keyType,
                'created_at' => $sameTimestamp ? now() : now()->addSecond(),
            ]);

            return $created;
        });
        $this->app->instance(KeyCreationService::class, $mock);

        $permissions = collect(AdminAcl::getResourceList())->mapWithKeys(fn (string $resource) => [
            AdminAcl::COLUMN_IDENTIFIER.$resource => (string) match ($resource) {
                'servers' => AdminAcl::READ | AdminAcl::WRITE,
                'users' => AdminAcl::READ,
                default => AdminAcl::NONE,
            },
        ])->all();

        $page = Livewire::test(CreateApiKey::class)
            ->fillForm(['memo' => 'Application integration key', ...$permissions])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = $page->instance()->getRecord();
        $this->assertTrue($record === $created, 'Filament must retain the exact model returned by the creation service.');
        $this->assertFalse($record->is($competing));
        $this->assertSame($admin->id, $record->user_id);
        $this->assertSame(ApiKey::TYPE_APPLICATION, $record->key_type);
        $this->assertSame('Application integration key', $record->memo);
        $record->refresh();
        foreach ($permissions as $column => $value) {
            $this->assertSame((int) $value, $record->getAttribute($column));
        }
        $this->assertTrue(strlen(decrypt($record->token)) === ApiKey::KEY_LENGTH);
        $this->assertTrue($record->token !== decrypt($record->token));
        $this->assertDatabaseCount('api_keys', 3);
    }

    public static function competingKeys(): array
    {
        return [
            'newer account key' => [ApiKey::TYPE_ACCOUNT, false],
            'newer application key' => [ApiKey::TYPE_APPLICATION, false],
            'same timestamp account key' => [ApiKey::TYPE_ACCOUNT, true],
            'same timestamp application key' => [ApiKey::TYPE_APPLICATION, true],
        ];
    }
}
