<?php

namespace Tests\Integration\Api\Client\Server\Backup;

use App\Models\Backup;
use App\Models\Permission;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class IndexBackupTest extends ClientApiIntegrationTestCase
{
    #[DataProvider('invalidPageSizes')]
    public function test_invalid_page_sizes_return_validation_errors(string $query): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_READ]);
        Backup::factory()->create(['server_id' => $server->id]);

        $this->actingAs($user)->getJson($this->link($server, '/backups?'.$query))
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'per_page');
    }

    public static function invalidPageSizes(): array
    {
        return [
            'zero' => ['per_page=0'],
            'negative' => ['per_page=-5'],
            'nonnumeric' => ['per_page=invalid'],
            'over maximum' => ['per_page=51'],
            'decimal' => ['per_page=1.5'],
            'scientific notation' => ['per_page=1e1'],
            'overflow' => ['per_page=99999999999999999999'],
            'empty' => ['per_page='],
            'array' => ['per_page[]=5'],
        ];
    }

    #[DataProvider('validPageSizes')]
    public function test_backup_listing_paginates_with_valid_page_sizes(string $query, int $perPage, int $page): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_READ]);
        Backup::factory()->count(55)->create(['server_id' => $server->id]);

        $this->actingAs($user)->getJson($this->link($server, '/backups'.$query))
            ->assertOk()
            ->assertJsonCount($perPage, 'data')
            ->assertJsonPath('meta.pagination.per_page', $perPage)
            ->assertJsonPath('meta.pagination.current_page', $page)
            ->assertJsonPath('meta.pagination.total', 55);
    }

    public static function validPageSizes(): array
    {
        return [
            'default' => ['', 20, 1],
            'minimum' => ['?per_page=1', 1, 1],
            'custom' => ['?per_page=7', 7, 1],
            'explicit default' => ['?per_page=20', 20, 1],
            'maximum' => ['?per_page=50', 50, 1],
            'second page' => ['?per_page=7&page=2', 7, 2],
        ];
    }

    public function test_empty_backup_listing_uses_default_page_size(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_READ]);

        $this->actingAs($user)->getJson($this->link($server, '/backups'))
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.pagination.per_page', 20);
    }

    public function test_empty_page_size_is_rejected_without_empty_string_normalization(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_READ]);

        $this->withoutMiddleware(ConvertEmptyStringsToNull::class)->actingAs($user)
            ->getJson($this->link($server, '/backups?per_page='))
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'per_page');
    }

    public function test_user_without_read_permission_cannot_list_backups(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_CREATE]);

        $this->actingAs($user)->getJson($this->link($server, '/backups?per_page=-5'))->assertForbidden();
    }
}
