<?php

namespace Tests\Integration\Api\Client\Server\Files;

use App\Models\Permission;
use App\Repositories\Agent\DaemonFileRepository;
use App\Services\Servers\RecycleBinService;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class RecycleBinTest extends ClientApiIntegrationTestCase
{
    private array $disk = [];

    private array $directoryReads = [];

    private ?string $failedDeletePath = null;

    private ?int $writeLockServerId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = ['/folder' => null, '/folder/file.txt' => 'original'];
        $this->mock(DaemonFileRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('setServer')->andReturnSelf();
            $mock->shouldReceive('getDirectory')->andReturnUsing(function ($path) {
                $this->directoryReads[] = $path;
                $items = [];
                foreach ($this->disk as $name => $content) {
                    if (dirname($name) === $path) {
                        $items[] = ['name' => basename($name), 'file' => $content !== null, 'symlink' => false, 'size' => $content === null ? 0 : strlen($content)];
                    }
                }

                return $items;
            });
            $mock->shouldReceive('getContent')->andReturnUsing(fn ($path) => $this->disk[$path]);
            $mock->shouldReceive('putContent')->andReturnUsing(function ($path, $content) {
                if ($this->writeLockServerId !== null) {
                    $this->assertFalse(Cache::lock('recycle-bin:'.$this->writeLockServerId, 300)->get());
                }
                $this->disk[$path] = $content;

                return new Response(204);
            });
            $mock->shouldReceive('createDirectory')->andReturnUsing(function ($name, $root) {
                $path = rtrim($root, '/').'/'.$name;
                while ($path !== '/' && $path !== '.') {
                    $this->disk[$path] = null;
                    $path = dirname($path);
                }

                return new Response(204);
            });
            $mock->shouldReceive('renameFiles')->andReturnUsing(function ($root, $files) {
                foreach ($files as $file) {
                    $from = '/'.$file['from'];
                    $to = '/'.$file['to'];
                    $this->assertArrayNotHasKey($to, $this->disk, 'A move must never overwrite directly.');
                    foreach ($this->disk as $path => $content) {
                        if ($path === $from || str_starts_with($path, $from.'/')) {
                            $this->disk[$to.substr($path, strlen($from))] = $content;
                            unset($this->disk[$path]);
                        }
                    }
                }

                return new Response(204);
            });
            $mock->shouldReceive('deleteFiles')->andReturnUsing(function ($root, $files) {
                foreach ($files as $file) {
                    $target = rtrim($root, '/').'/'.$file;
                    if ($target === $this->failedDeletePath) {
                        throw new \RuntimeException('Agent unavailable');
                    }
                    foreach (array_keys($this->disk) as $path) {
                        if ($path === $target || str_starts_with($path, $target.'/')) {
                            unset($this->disk[$path]);
                        }
                    }
                }

                return new Response(204);
            });
        });
    }

    public function test_recycle_preserves_bytes_and_restores_missing_parents(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/folder', 'files' => ['file.txt']])->assertNoContent();
        $entry = DB::table('recycled_files')->first();
        $this->assertSame('/folder/file.txt', $entry->original_path);
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$entry->id.'/file']);
        $this->assertArrayNotHasKey('/folder/file.txt', $this->disk);
        unset($this->disk['/folder']);
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $entry->id])->assertNoContent();
        $this->assertArrayHasKey('/folder', $this->disk);
        $this->assertSame('original', $this->disk['/folder/file.txt']);
        $this->assertArrayNotHasKey('/.recycle-bin/'.$entry->id, $this->disk);
        $this->assertDatabaseCount('recycled_files', 0);
    }

    public function test_restore_requires_confirmation_and_keeps_displaced_file_recoverable(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/folder', 'files' => ['file.txt']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->disk['/folder/file.txt'] = 'replacement';
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id])->assertStatus(409)->assertJson(['conflict' => '/folder/file.txt']);
        $this->assertSame('replacement', $this->disk['/folder/file.txt']);
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'replace' => true])->assertNoContent();
        $this->assertSame('original', $this->disk['/folder/file.txt']);
        $replacement = DB::table('recycled_files')->first();
        $this->assertNotSame($id, $replacement->id);
        $this->assertSame('replacement', $this->disk['/.recycle-bin/'.$replacement->id.'/file']);
    }

    public function test_directory_recycling_and_purge(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file/file.txt']);
        $this->postJson($this->link($server, '/files/recycle-bin/delete'), ['id' => $id])->assertNoContent();
        $this->assertArrayNotHasKey('/.recycle-bin/'.$id.'/file/file.txt', $this->disk);
        $this->assertDatabaseCount('recycled_files', 0);
    }

    public function test_listing_reports_real_recycled_bytes_and_original_path(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $this->getJson($this->link($server, '/files/recycle-bin'))->assertOk()
            ->assertJsonPath('size', strlen('original'))
            ->assertJsonPath('data.0.size', strlen('original'))
            ->assertJsonPath('data.0.original_path', '/folder');
    }

    public function test_unusual_filenames_round_trip_without_normalizing_the_name(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $name = '日本語 % back\\slash.txt';
        $this->disk['/'.$name] = 'contents';
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => [$name]])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id])->assertNoContent();
        $this->assertSame('contents', $this->disk['/'.$name]);
    }

    public function test_restore_preview_reports_collisions_without_moving_either_file(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/folder', 'files' => ['file.txt']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'preview' => true])->assertOk()->assertJson(['conflict' => null]);
        $this->assertArrayNotHasKey('/folder/file.txt', $this->disk);
        $this->disk['/folder/file.txt'] = 'existing';
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'preview' => true, 'replace' => true])->assertOk()->assertJson(['conflict' => '/folder/file.txt']);
        $this->assertSame('existing', $this->disk['/folder/file.txt']);
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file']);
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_missing_payload_does_not_replace_existing_file(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/folder', 'files' => ['file.txt']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        unset($this->disk['/.recycle-bin/'.$id.'/file']);
        $this->disk['/folder/file.txt'] = 'replacement';
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'replace' => true])->assertStatus(409);
        $this->assertSame('replacement', $this->disk['/folder/file.txt']);
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_parent_file_conflict_preserves_recycled_data(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/folder', 'files' => ['file.txt']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->disk['/folder'] = 'parent is now a file';
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'replace' => true])->assertStatus(409);
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file']);
        $this->assertSame('parent is now a file', $this->disk['/folder']);
    }

    public function test_permanent_deletion_does_not_create_recovery_entry(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/folder', 'files' => ['file.txt'], 'permanent' => true])->assertNoContent();
        $this->assertArrayNotHasKey('/folder/file.txt', $this->disk);
        $this->assertDatabaseCount('recycled_files', 0);
    }

    public function test_reserved_and_traversal_paths_are_rejected_before_any_move(): void
    {
        [$user, $server] = $this->generateTestAccount();
        foreach (['../folder', '.recycle-bin', '.', 'folder/../../'] as $path) {
            $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => [$path]])->assertStatus(400);
        }
        $this->assertDatabaseCount('recycled_files', 0);
        $this->assertSame('original', $this->disk['/folder/file.txt']);
    }

    public function test_restore_and_purge_cannot_access_another_servers_entry(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        [$other, $otherServer] = $this->generateTestAccount();
        foreach (['restore', 'delete'] as $action) {
            $this->actingAs($other)->postJson($this->link($otherServer, '/files/recycle-bin/'.$action), ['id' => $id])->assertNotFound();
        }
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_restore_requires_create_update_delete_and_read_permissions(): void
    {
        foreach ([Permission::ACTION_FILE_READ, Permission::ACTION_FILE_DELETE, Permission::ACTION_FILE_CREATE, Permission::ACTION_FILE_UPDATE] as $missing) {
            $permissions = array_diff([Permission::ACTION_FILE_READ, Permission::ACTION_FILE_DELETE, Permission::ACTION_FILE_CREATE, Permission::ACTION_FILE_UPDATE], [$missing]);
            [$user, $server] = $this->generateTestAccount($permissions);
            $this->actingAs($user)->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => '00000000-0000-4000-8000-000000000000'])->assertForbidden();
        }
    }

    public function test_api_can_browse_read_edit_and_restore_recycled_contents(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $entry = $this->getJson($this->link($server, '/files/recycle-bin'))->assertOk()->json('data.0');
        $path = $entry['storage_path'].'/file.txt';
        $this->getJson($this->link($server, '/files/list').'?'.http_build_query(['directory' => $entry['storage_path']]))->assertOk()->assertJsonPath('data.0.attributes.name', 'file.txt');
        $this->get($this->link($server, '/files/contents').'?'.http_build_query(['file' => $path]))->assertOk()->assertContent('original');
        $this->call('PUT', $this->link($server, '/files/write').'?'.http_build_query(['file' => $path]), [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'edited in bin')->assertNoContent();
        $this->getJson($this->link($server, '/files/recycle-bin'))->assertJsonPath('size', strlen('edited in bin'));
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $entry['id']])->assertNoContent();
        $this->assertSame('edited in bin', $this->disk['/folder/file.txt']);
    }

    public function test_empty_is_server_scoped_and_repeatable(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $entry = DB::table('recycled_files')->first();
        [$other, $otherServer] = $this->generateTestAccount();
        $this->actingAs($other)->postJson($this->link($otherServer, '/files/recycle-bin/empty'))->assertNoContent();
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$entry->id.'/file/file.txt']);
        $this->assertDatabaseCount('recycled_files', 1);
        $this->actingAs($user)->postJson($this->link($server, '/files/recycle-bin/empty'))->assertNoContent();
        $this->assertArrayNotHasKey('/.recycle-bin/'.$entry->id.'/file/file.txt', $this->disk);
        $this->assertDatabaseCount('recycled_files', 0);
        $this->postJson($this->link($server, '/files/recycle-bin/empty'))->assertNoContent();
    }

    public function test_empty_requires_read_and_delete_permissions(): void
    {
        foreach ([[Permission::ACTION_FILE_READ], [Permission::ACTION_FILE_DELETE]] as $permissions) {
            [$user, $server] = $this->generateTestAccount($permissions);
            $this->actingAs($user)->postJson($this->link($server, '/files/recycle-bin/empty'))->assertForbidden();
        }
    }

    public function test_recycled_file_contents_retain_read_and_update_permission_guards(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ, Permission::ACTION_FILE_DELETE]);
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $entry = $this->getJson($this->link($server, '/files/recycle-bin'))->assertOk()->json('data.0');
        $path = $entry['storage_path'].'/file.txt';
        $this->getJson($this->link($server, '/files/contents').'?'.http_build_query(['file' => $path]))->assertForbidden();
        $this->call('PUT', $this->link($server, '/files/write').'?'.http_build_query(['file' => $path]), [], [], [], ['CONTENT_TYPE' => 'text/plain', 'HTTP_ACCEPT' => 'application/json'], 'changed')->assertForbidden();
        $this->assertSame('original', $this->disk[$path]);
    }

    public function test_nested_restore_recreates_parents_and_preserves_recycled_siblings(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->disk['/folder/deep'] = null;
        $this->disk['/folder/deep/nested.txt'] = 'nested original';
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'path' => 'deep/nested.txt'])->assertNoContent();
        $this->assertSame('nested original', $this->disk['/folder/deep/nested.txt']);
        $this->assertArrayHasKey('/folder/deep', $this->disk);
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file/file.txt']);
        $this->assertArrayNotHasKey('/.recycle-bin/'.$id.'/file/deep/nested.txt', $this->disk);
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_nested_folder_restore_warns_before_replacing_and_retains_displaced_contents(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->disk['/folder/deep'] = null;
        $this->disk['/folder/deep/nested.txt'] = 'nested original';
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->disk['/folder'] = null;
        $this->disk['/folder/deep'] = null;
        $this->disk['/folder/deep/existing.txt'] = 'existing';
        $body = ['id' => $id, 'path' => 'deep'];
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), $body + ['preview' => true])->assertOk()->assertJson(['conflict' => '/folder/deep']);
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), $body)->assertStatus(409);
        $this->assertSame('existing', $this->disk['/folder/deep/existing.txt']);
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), $body + ['replace' => true])->assertNoContent();
        $this->assertSame('nested original', $this->disk['/folder/deep/nested.txt']);
        $displaced = DB::table('recycled_files')->where('id', '!=', $id)->first();
        $this->assertSame('/folder/deep', $displaced->original_path);
        $this->assertSame('existing', $this->disk['/.recycle-bin/'.$displaced->id.'/file/existing.txt']);
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file/file.txt']);
    }

    public function test_nested_deletion_removes_only_the_selected_subtree(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->disk['/folder/deep'] = null;
        $this->disk['/folder/deep/nested.txt'] = 'nested';
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->postJson($this->link($server, '/files/recycle-bin/delete'), ['id' => $id, 'path' => 'deep'])->assertNoContent();
        $this->assertArrayNotHasKey('/.recycle-bin/'.$id.'/file/deep/nested.txt', $this->disk);
        $this->assertArrayNotHasKey('/.recycle-bin/'.$id.'/file/deep', $this->disk);
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file/file.txt']);
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_nested_operations_reject_paths_outside_the_entry(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        foreach (['../file', '/file', 'deep/../../file', './file.txt', 'deep//file', "file\0.txt"] as $path) {
            foreach (['restore', 'delete'] as $action) {
                $this->postJson($this->link($server, '/files/recycle-bin/'.$action), ['id' => $id, 'path' => $path])->assertStatus(400);
            }
        }
        $this->assertSame('original', $this->disk['/.recycle-bin/'.$id.'/file/file.txt']);
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_automatic_cleanup_defaults_to_31_days_and_retains_newer_files(): void
    {
        $this->travelTo(now()->startOfSecond());
        [$user, $server] = $this->generateTestAccount();
        $this->disk['/recent.txt'] = 'recent';
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder', 'recent.txt']])->assertNoContent();
        $expired = DB::table('recycled_files')->where('original_path', '/folder')->first();
        $recent = DB::table('recycled_files')->where('original_path', '/recent.txt')->first();
        DB::table('recycled_files')->where('id', $expired->id)->update(['created_at' => now()->subDays(31)]);
        DB::table('recycled_files')->where('id', $recent->id)->update(['created_at' => now()->subDays(31)->addSecond()]);
        $this->getJson($this->link($server, '/files/recycle-bin'))->assertJsonPath('retention_days', 31);
        $this->artisan('p:maintenance:prune-recycle-bin')->assertSuccessful();
        $this->assertArrayNotHasKey('/.recycle-bin/'.$expired->id.'/file', $this->disk);
        $this->assertSame('recent', $this->disk['/.recycle-bin/'.$recent->id.'/file']);
        $this->assertDatabaseCount('recycled_files', 1);
        $this->travelBack();
    }

    public function test_cleanup_honors_configured_retention_and_retries_failed_deletions(): void
    {
        config()->set('panel.files.recycle_bin_retention_days', 7);
        [$user, $server] = $this->generateTestAccount();
        $this->disk['/second.txt'] = 'second';
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder', 'second.txt']])->assertNoContent();
        DB::table('recycled_files')->update(['created_at' => now()->subDays(8)]);
        $failed = DB::table('recycled_files')->where('original_path', '/folder')->first();
        $this->failedDeletePath = '/.recycle-bin/'.$failed->id;
        $this->getJson($this->link($server, '/files/recycle-bin'))->assertJsonPath('retention_days', 7);
        $this->artisan('p:maintenance:prune-recycle-bin')->assertFailed();
        $this->assertDatabaseCount('recycled_files', 1);
        $this->assertSame('original', $this->disk[$this->failedDeletePath.'/file/file.txt']);
        $this->failedDeletePath = null;
        $this->artisan('p:maintenance:prune-recycle-bin')->assertSuccessful();
        $this->assertDatabaseCount('recycled_files', 0);
    }

    public function test_large_bin_listing_reuses_directory_results_and_cached_scans_expire(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->disk['/.recycle-bin'] = null;
        $entries = [];
        for ($index = 0; $index < 1000; $index++) {
            $id = (string) Str::uuid();
            $entries[] = ['id' => $id, 'server_id' => $server->id, 'original_path' => '/'.$index.'.txt', 'created_at' => now()];
            $this->disk['/.recycle-bin/'.$id] = null;
            $this->disk['/.recycle-bin/'.$id.'/file'] = 'x';
        }
        DB::table('recycled_files')->insert($entries);
        $url = $this->link($server, '/files/recycle-bin');
        $this->actingAs($user)->getJson($url)->assertOk()->assertJsonPath('size', 1000);
        $this->assertCount(1002, $this->directoryReads);
        $this->assertCount(1002, array_unique($this->directoryReads));
        $this->directoryReads = [];
        config()->set('panel.files.recycle_bin_retention_days', 7);
        $this->getJson($url)->assertOk()->assertJsonPath('size', 1000)->assertJsonPath('retention_days', 7);
        $this->assertSame([], $this->directoryReads);
        $this->disk['/.recycle-bin/'.$id.'/file'] = 'changed externally';
        $this->travel(61)->seconds();
        $this->getJson($url)->assertOk()->assertJsonPath('size', 999 + strlen('changed externally'));
        $this->assertCount(1002, $this->directoryReads);
        $this->travelBack();
    }

    public function test_cached_listing_updates_after_recycling_restoring_and_purging(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $url = $this->link($server, '/files/recycle-bin');
        $this->actingAs($user)->getJson($url)->assertJsonPath('data', []);
        $this->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = $this->getJson($url)->assertJsonPath('size', strlen('original'))->json('data.0.id');
        $this->postJson($url.'/restore', ['id' => $id, 'path' => 'file.txt'])->assertNoContent();
        $this->getJson($url)->assertJsonPath('size', 0)->assertJsonCount(1, 'data');
        $this->postJson($url.'/delete', ['id' => $id])->assertNoContent();
        $this->getJson($url)->assertJsonPath('data', []);
    }

    public function test_full_restore_does_not_delete_unexpected_wrapper_contents(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->disk['/.recycle-bin/'.$id.'/extra.txt'] = 'preserve me';
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id])->assertStatus(409);
        $this->assertSame('original', $this->disk['/folder/file.txt']);
        $this->assertSame('preserve me', $this->disk['/.recycle-bin/'.$id.'/extra.txt']);
        $this->assertDatabaseCount('recycled_files', 1);
    }

    public function test_failed_wrapper_cleanup_keeps_metadata_for_a_later_purge(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $this->failedDeletePath = '/.recycle-bin/'.$id;
        try {
            app(RecycleBinService::class)->restore($server, $id, false);
            $this->fail('The cleanup failure must be reported.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Agent unavailable', $exception->getMessage());
        }
        $this->assertSame('original', $this->disk['/folder/file.txt']);
        $this->assertDatabaseCount('recycled_files', 1);
        $this->failedDeletePath = null;
        $this->postJson($this->link($server, '/files/recycle-bin/delete'), ['id' => $id])->assertNoContent();
        $this->assertArrayNotHasKey('/.recycle-bin/'.$id, $this->disk);
        $this->assertSame('original', $this->disk['/folder/file.txt']);
        $this->assertDatabaseCount('recycled_files', 0);
    }

    public function test_recycled_saves_hold_restore_lock_and_stale_saves_are_rejected(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user)->postJson($this->link($server, '/files/delete'), ['root' => '/', 'files' => ['folder']])->assertNoContent();
        $id = DB::table('recycled_files')->value('id');
        $path = '/.recycle-bin/'.$id.'/file/file.txt';
        $this->writeLockServerId = $server->id;
        $this->call('PUT', $this->link($server, '/files/write').'?'.http_build_query(['file' => $path]), [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'saved before restore')->assertNoContent();
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'path' => 'file.txt'])->assertNoContent();
        foreach (['PUT', 'POST'] as $method) {
            $this->call($method, $this->link($server, '/files/write').'?'.http_build_query(['file' => $path]), [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'stale edit')->assertStatus(409);
        }
        $this->assertArrayNotHasKey($path, $this->disk);
        $this->assertSame('saved before restore', $this->disk['/folder/file.txt']);
        $this->postJson($this->link($server, '/files/recycle-bin/restore'), ['id' => $id, 'replace' => true])->assertNoContent();
        foreach (['/files/write', '/files/create'] as $endpoint) {
            $this->call('POST', $this->link($server, $endpoint).'?'.http_build_query(['file' => '/./.recycle-bin//'.$id.'/file/file.txt']), [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'stale edit')->assertStatus(409);
        }
        $this->assertArrayNotHasKey('/.recycle-bin/'.$id, $this->disk);
        $lock = Cache::lock('recycle-bin:'.$server->id, 300);
        $this->assertTrue($lock->get());
        $lock->release();
    }
}
