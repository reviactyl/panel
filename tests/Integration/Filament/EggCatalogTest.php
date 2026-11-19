<?php

namespace Tests\Integration\Filament;

use App\Exceptions\Model\DataValidationException;
use App\Filament\Resources\Nests\EggResource;
use App\Filament\Resources\Nests\Eggs\Pages\EggCatalog;
use App\Filament\Resources\Nests\NestResource;
use App\Filament\Resources\Nests\Pages\CreateNest;
use App\Models\Egg;
use App\Models\Nest;
use App\Models\Role;
use App\Models\User;
use App\Services\Eggs\Catalog\CatalogDefinition;
use App\Services\Eggs\Catalog\CatalogException;
use App\Services\Eggs\Catalog\CatalogImportService;
use App\Services\Eggs\Catalog\EggCatalogEntry;
use App\Services\Eggs\Catalog\EggCatalogService;
use App\Services\Eggs\Catalog\PterodactylProvider;
use App\Services\Eggs\Catalog\RextstoreProvider;
use App\Services\Eggs\Sharing\EggExporterService;
use App\Services\Eggs\Sharing\EggImporterService;
use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Integration\IntegrationTestCase;
use Tests\Unit\Services\Eggs\Catalog\EggCatalogServiceTest;

class EggCatalogTest extends IntegrationTestCase
{
    private string $bytes;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        $this->bytes = file_get_contents(base_path('tests/Fixtures/egg-catalog/egg.json'));
        Http::preventStrayRequests();
        $this->fakeDefinition($this->bytes);
    }

    private function fakeDefinition(string $bytes): void
    {
        Http::swap(new Factory());
        Http::preventStrayRequests();
        Http::fake([
            PterodactylProvider::INDEX_URL => Http::response([EggCatalogServiceTest::record()]),
            RextstoreProvider::TREE_URL => Http::response(['sha' => str_repeat('a', 40), 'truncated' => false,
                'tree' => [['type' => 'blob', 'path' => 'Minecraft/egg-paper.json']]]),
            'https://raw.githubusercontent.com/*' => Http::response($bytes),
        ]);
    }

    public function test_search_matches_title_description_and_words_combined_in_any_order(): void
    {
        Livewire::test(EggCatalog::class)->call('loadCatalog')
            ->set('search', 'Paper')->assertSee('Review Paper from Rextstore')->assertSee('Review Paper from Pterodactyl')
            ->set('search', 'deterministic')->assertSee('Review Paper from Rextstore')->assertDontSee('Review Paper from Pterodactyl')
            ->set('search', 'deterministic PaPeR')->assertSee('Review Paper from Rextstore')->assertDontSee('Review Paper from Pterodactyl')
            ->set('search', 'Paper missing')->assertSee(trans('admin/catalog.no_matches'));
    }

    public function test_rextstore_categories_are_readable_without_changing_their_filter_identity(): void
    {
        $component = Livewire::test(EggCatalog::class)->call('loadCatalog');
        $entry = app(EggCatalogService::class)->resolve('Rextstore', 'Minecraft/egg-paper.json');
        $this->assertSame('Minecraft', $component->instance()->categoryLabel($entry));
        foreach (['source-engine' => 'Source Engine', 'voice-servers' => 'Voice Servers', 'rust' => 'Rust'] as $category => $label) {
            $entry = new EggCatalogEntry('Rextstore', 'id', 'Egg', '', $category,
                'reviactyl/eggs', $category.'/egg-test.json', 'https://rextstore.app/eggs/',
                'https://raw.githubusercontent.com/reviactyl/eggs/main/'.$category.'/egg-test.json', 'main');
            $this->assertSame($label, $component->instance()->categoryLabel($entry));
        }
    }

    public function test_native_pagination_moves_between_pages_and_filters_reset_it(): void
    {
        $records = [];
        for ($index = 1; $index <= 30; $index++) {
            $record = EggCatalogServiceTest::record();
            $record['id'] = 'paper-'.$index;
            $record['name'] = sprintf('Paper %02d', $index);
            $records[] = $record;
        }
        Http::swap(new Factory());
        Http::fake([
            PterodactylProvider::INDEX_URL => Http::response($records),
            RextstoreProvider::TREE_URL => Http::response(['sha' => str_repeat('a', 40), 'truncated' => false, 'tree' => []]),
        ]);
        Livewire::test(EggCatalog::class)->call('loadCatalog')
            ->assertSee('Paper 01')->assertDontSee('Paper 25')
            ->call('nextPage', 'page')->assertSet('page', 2)->assertSee('Paper 25')->assertDontSee('Paper 01')
            ->call('previousPage', 'page')->assertSet('page', 1)
            ->call('gotoPage', 2, 'page')->assertSet('page', 2)
            ->set('perPage', 48)->assertSet('page', 1)->assertSee('Paper 30')
            ->set('perPage', 1000000)->assertSet('perPage', 24)
            ->set('search', 'Paper 01')->assertSet('page', 1)->assertSee('Paper 01');
    }

    public function test_source_labels_distinguish_catalog_file_manual_and_existing_eggs(): void
    {
        $nest = Nest::factory()->create();
        $existing = Egg::factory()->make(['nest_id' => $nest->id]);
        $this->assertSame('Unknown', $existing->creation_source_label);
        $file = app(EggImporterService::class)->handle(UploadedFile::fake()->createWithContent('egg.json', $this->bytes), $nest->id);
        $this->assertSame('Imported', $file->creation_source_label);
        $this->assertNull($file->catalog_origin);
        foreach (EggCatalogService::PROVIDERS as $provider) {
            $catalog = app(CatalogImportService::class);
            $id = $provider === 'Pterodactyl' ? 'paper' : 'Minecraft/egg-paper.json';
            $egg = $catalog->import($catalog->stage($provider, $id), $nest->id);
            $this->assertSame($provider, $egg->creation_source_label);
        }
    }

    public function test_scheduled_command_refreshes_both_sources_and_retains_good_data_on_failure(): void
    {
        $this->artisan('p:eggs:refresh-catalog')->assertSuccessful();
        $this->artisan('p:eggs:refresh-catalog')->assertSuccessful();
        Http::assertSentCount(3);
        $catalog = app(EggCatalogService::class);
        $old = $catalog->cached('Pterodactyl');
        $this->travel(30)->minutes();
        Http::swap(new Factory());
        Http::fake([
            PterodactylProvider::INDEX_URL => Http::response('', 503),
            RextstoreProvider::TREE_URL => Http::response(['sha' => str_repeat('a', 40), 'truncated' => false, 'tree' => []]),
        ]);
        $this->artisan('p:eggs:refresh-catalog')->assertFailed();
        $this->assertSame($old['refreshed_at'], $catalog->cached('Pterodactyl')['refreshed_at']);
        $this->assertTrue($catalog->cached('Pterodactyl')['stale']);
        $this->assertSame([], $catalog->cached('Rextstore')['entries']);
        $schedule = app(Schedule::class);
        $event = collect($schedule->events())->first(fn ($event) => str_contains($event->command ?? '', 'p:eggs:refresh-catalog'));
        $this->assertSame('*/30 * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    public function test_header_refresh_notifies_only_after_a_successful_refresh(): void
    {
        $component = Livewire::test(EggCatalog::class)->call('refreshCatalog')
            ->assertNotified(trans('admin/catalog.refresh_success'));
        $this->assertCount(1, app(EggCatalogService::class)->cached('Pterodactyl')['entries']);
        $component->call('refreshCatalog')->assertDontSee(trans('admin/catalog.errors.cooldown'));
        Http::assertSentCount(5);
    }

    public function test_reviewed_bytes_import_into_selected_nest_with_variables_and_historical_provenance(): void
    {
        $nest = Nest::factory()->create();
        $other = Nest::factory()->create();
        $service = app(CatalogImportService::class);
        $token = $service->stage('Pterodactyl', 'paper');
        $changed = json_decode($this->bytes, true);
        $changed['startup'] = 'changed upstream';
        $this->fakeDefinition(json_encode($changed));
        $egg = $service->import($token, $nest->id);
        $this->assertSame($nest->id, $egg->nest_id);
        $this->assertSame('java -jar {{SERVER_JARFILE}}', $egg->startup);
        $this->assertSame('ghcr.io/pterodactyl/yolks:java_21', $egg->docker_images['Java']);
        $this->assertSame("#!/bin/sh\necho fixture", $egg->script_install);
        $this->assertSame('SERVER_JARFILE', $egg->variables()->sole()->env_variable);
        $this->assertSame(hash('sha256', $this->bytes), $egg->catalog_origin['content_sha256']);
        $this->assertSame('pterodactyl/game-eggs', $egg->catalog_origin['repository']);
        $this->assertSame($egg->id, $service->import($token, $nest->id)->id);
        $this->assertNotSame($egg->id, $service->import($token, $other->id)->id);
        $egg->update(['description' => 'Local edit']);
        $this->assertSame(hash('sha256', $this->bytes), $egg->fresh()->catalog_origin['content_sha256']);
        $this->assertStringNotContainsString('catalog_origin', app(EggExporterService::class)->handle($egg->id));
    }

    public function test_matching_names_are_separate_across_providers_and_untracked_eggs(): void
    {
        $nest = Nest::factory()->create();
        $local = app(EggImporterService::class)->handle(UploadedFile::fake()->createWithContent('egg.json', $this->bytes), $nest->id);
        $service = app(CatalogImportService::class);
        $a = $service->import($service->stage('Pterodactyl', 'paper'), $nest->id);
        $b = $service->import($service->stage('Rextstore', 'Minecraft/egg-paper.json'), $nest->id);
        $this->assertNotSame($a->catalog_key, $b->catalog_key);
        $this->assertNull($local->fresh()->catalog_origin);
        $this->assertSame(3, Egg::query()->where('nest_id', $nest->id)->count());
        $this->assertSame('Paper', $local->fresh()->name);
    }

    public function test_remote_variable_ids_and_foreign_keys_are_never_imported(): void
    {
        $data = json_decode($this->bytes, true);
        $data['variables'][0] += ['id' => 123456789, 'egg_id' => 999, 'created_at' => '2000-01-01'];
        $data['catalog_origin'] = ['provider' => 'forged'];
        $nest = Nest::factory()->create();
        $egg = app(EggImporterService::class)->handle(UploadedFile::fake()->createWithContent('egg.json', json_encode($data)), $nest->id);
        $variable = $egg->variables()->sole();
        $this->assertNotSame(123456789, $variable->id);
        $this->assertSame($egg->id, $variable->egg_id);
        $this->assertNull($egg->catalog_origin);
    }

    public function test_invalid_variable_insertion_rolls_back_file_import_without_an_orphan(): void
    {
        $data = json_decode($this->bytes, true);
        $data['variables'][0]['env_variable'] = 'invalid-name!';
        try {
            app(EggImporterService::class)->handle(UploadedFile::fake()->createWithContent('egg.json', json_encode($data)), Nest::factory()->create()->id);
            $this->fail('Invalid variable imported');
        } catch (DataValidationException $exception) {
            $this->assertSame(0, Egg::query()->count());
        }
    }

    public function test_staging_expiry_and_cross_user_tokens_are_rejected(): void
    {
        $service = app(CatalogImportService::class);
        $nest = Nest::factory()->create();
        $token = $service->stage('Pterodactyl', 'paper');
        $original = auth()->user();
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        try {
            $service->import($token, $nest->id);
            $this->fail('Cross-user token accepted');
        } catch (CatalogException $exception) {
            $this->assertSame('expired', $exception->reason);
        }
        $this->actingAs($original);
        $this->travel(11)->minutes();
        $this->expectException(CatalogException::class);
        $service->import($token, $nest->id);
    }

    public function test_provenance_failure_rolls_back_egg_and_variables(): void
    {
        $service = app(CatalogImportService::class);
        $token = $service->stage('Pterodactyl', 'paper');
        $dispatcher = Egg::getEventDispatcher();
        Egg::setEventDispatcher(clone $dispatcher);
        Egg::updating(function (Egg $egg): void {
            if ($egg->isDirty('catalog_origin')) {
                throw new QueryException(DB::connection()->getName(), 'UPDATE eggs SET catalog_origin = ?', [], new \RuntimeException('Fixture provenance failure'));
            }
        });
        try {
            $service->import($token, Nest::factory()->create()->id);
            $this->fail('Failure ignored');
        } catch (QueryException $exception) {
            $this->assertSame(0, Egg::query()->count());
            $this->assertDatabaseCount('egg_variables', 0);
        } finally {
            Egg::setEventDispatcher($dispatcher);
        }
    }

    public function test_move_conflict_is_validation_and_deletion_permits_reimport(): void
    {
        $service = app(CatalogImportService::class);
        $token = $service->stage('Pterodactyl', 'paper');
        $first = Nest::factory()->create();
        $second = Nest::factory()->create();
        $a = $service->import($token, $first->id);
        $b = $service->import($token, $second->id);
        try {
            $a->forceFill(['nest_id' => $second->id])->save();
            $this->fail('Conflicting move accepted');
        } catch (DataValidationException $exception) {
            $this->assertArrayHasKey('nest_id', $exception->getMessageBag()->messages());
        }
        $b->delete();
        $this->assertNotSame($b->id, $service->import($token, $second->id)->id);
        $third = Nest::factory()->create();
        $a->forceFill(['nest_id' => $third->id])->save();
        $this->assertSame('Pterodactyl', $a->fresh()->catalog_origin['provider']);
    }

    public function test_deleted_nest_and_revoked_permissions_are_checked_before_submit(): void
    {
        $service = app(CatalogImportService::class);
        $token = $service->stage('Pterodactyl', 'paper');
        $nest = Nest::factory()->create();
        $id = $nest->id;
        $nest->delete();
        try {
            $service->import($token, $id);
            $this->fail('Deleted nest accepted');
        } catch (CatalogException $exception) {
            $this->assertSame('nest', $exception->reason);
        }
        auth()->user()->update(['root_admin' => false]);
        $this->expectException(HttpException::class);
        $service->import($token, Nest::factory()->create()->id);
    }

    public function test_livewire_catalog_preserves_filters_and_preselects_only_explicit_nest_context(): void
    {
        $nest = Nest::factory()->create();
        Livewire::withQueryParams(['nest_id' => $nest->id])->test(EggCatalog::class)
            ->call('loadCatalog')->set('search', 'Paper')->set('source', 'Pterodactyl')
            ->set('page', 100)->assertSee('Paper')
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper'])
            ->assertSchemaStateSet(['nest_id' => $nest->id])
            ->assertSet('reviewError', null)
            ->assertSet('reviewToken', fn ($token) => is_string($token))
            ->callMountedAction()->assertRedirect();
        $this->assertSame($nest->id, Egg::query()->sole()->nest_id);
        Livewire::withQueryParams([])->test(EggCatalog::class)->call('loadCatalog')
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper'])
            ->assertSchemaStateSet(['nest_id' => null]);
    }

    public function test_catalog_refresh_cannot_change_an_active_staged_review(): void
    {
        Nest::factory()->create();
        $component = Livewire::withQueryParams([])->test(EggCatalog::class)->call('loadCatalog')
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper']);
        Cache::put('egg-catalog:provider:Pterodactyl', ['entries' => [], 'invalid' => 0, 'refreshed_at' => now()->timestamp], 86400);
        $html = $component->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('pterodactyl/game-eggs', $html);
        $this->assertStringNotContainsString(trans('admin/catalog.errors.unknown'), $html);
        Http::assertSentCount(4);
    }

    public function test_repeated_livewire_import_opens_existing_egg_without_announcing_a_new_import(): void
    {
        $nest = Nest::factory()->create();
        $service = app(CatalogImportService::class);
        $egg = $service->import($service->stage('Pterodactyl', 'paper'), $nest->id);
        Livewire::withQueryParams(['nest_id' => $nest->id])->test(EggCatalog::class)
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper'])
            ->callMountedAction()->assertNotified(trans('admin/catalog.existing_in', ['nest' => $nest->name]))
            ->assertRedirect(EggResource::getUrl('edit', ['record' => $egg]));
        $this->assertDatabaseCount('eggs', 1);
    }

    public function test_manual_refresh_can_run_immediately_without_cooldown_notices(): void
    {
        Livewire::withQueryParams([])->test(EggCatalog::class)->call('loadCatalog')
            ->call('refreshCatalog')->assertDontSee(trans('admin/catalog.errors.cooldown'));
        Http::assertSentCount(5);
    }

    public function test_no_nests_review_explains_requirement_and_offers_authorized_creation(): void
    {
        $component = Livewire::withQueryParams([])->test(EggCatalog::class)->call('loadCatalog')
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper']);
        $html = $component->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString(trans('admin/catalog.nest_required'), $html);
        $this->assertStringContainsString(trans('admin/catalog.create_nest'), $html);
        $this->assertDatabaseCount('nests', 0);
    }

    public function test_create_nest_return_url_preserves_originating_nest_and_catalog_parameters(): void
    {
        $nest = Nest::factory()->create();
        $entry = app(EggCatalogService::class)->provider('Rextstore')['entries'][0];
        $entries = [$entry, ...array_map(fn ($index) => new EggCatalogEntry(
            $entry->provider, 'paper-'.$index, 'Paper '.$index, $entry->description, $entry->category,
            $entry->repository, $entry->path, $entry->sourceUrl, $entry->downloadUrl(),
        ), range(1, 99))];
        Cache::put('egg-catalog:provider:Rextstore', ['entries' => $entries, 'invalid' => 0, 'refreshed_at' => now()->timestamp], 86400);
        $query = [
            'nest_id' => (string) $nest->id, 'search' => 'Paper', 'source' => 'Rextstore', 'category' => 'Rextstore|Minecraft',
            'page' => '3', 'per_page' => '48', 'review_provider' => 'Rextstore', 'review_id' => 'Minecraft/egg-paper.json',
        ];
        $component = Livewire::withQueryParams($query)->test(EggCatalog::class)->call('loadCatalog');
        $url = $component->instance()->resumeUrl();
        parse_str(parse_url($url, PHP_URL_QUERY), $returned);
        $this->assertEquals($query, $returned);
        Livewire::withQueryParams(['catalog_return' => $url])->test(CreateNest::class)->assertSet('catalogReturn', $url);
        $resumed = Livewire::withQueryParams($returned)->test(EggCatalog::class)
            ->assertSet('nestContext', $nest->id)->assertSet('search', 'Paper')->assertSet('source', 'Rextstore')
            ->assertSet('category', 'Rextstore|Minecraft')->assertSet('page', 3)->assertSet('perPage', 48)
            ->call('loadCatalog')->assertSchemaStateSet(['nest_id' => $nest->id]);
        $this->assertSame(NestResource::getUrl('edit', ['record' => $nest]), $resumed->instance()->backUrl());
    }

    public function test_create_nest_return_url_without_nest_context_does_not_preselect_a_nest(): void
    {
        $component = Livewire::withQueryParams([])->test(EggCatalog::class);
        parse_str(parse_url($component->instance()->resumeUrl(), PHP_URL_QUERY), $query);
        $this->assertArrayNotHasKey('nest_id', $query);
        Livewire::withQueryParams($query)->test(EggCatalog::class)->assertSet('nestContext', null);
    }

    public function test_create_nest_resumes_catalog_context_with_the_new_destination(): void
    {
        $old = Nest::factory()->create();
        $query = ['search' => 'Paper', 'source' => 'Rextstore', 'review_provider' => 'Rextstore', 'review_id' => 'Minecraft/egg-paper.json'];
        $url = EggResource::getUrl('catalog', $query + ['nest_id' => $old->id]);
        $component = Livewire::withQueryParams(['catalog_return' => $url])
            ->test(CreateNest::class)
            ->fillForm(['name' => 'Catalog destination'])->call('create')->assertHasNoFormErrors();
        $new = Nest::query()->where('name', 'Catalog destination')->sole();
        $component->assertRedirect(EggResource::getUrl('catalog', $query + ['nest_id' => $new->id]));
        Livewire::withQueryParams($query + ['nest_id' => $new->id])->test(EggCatalog::class)
            ->call('loadCatalog')->assertSet('search', 'Paper')->assertSet('source', 'Rextstore')
            ->assertSchemaStateSet(['nest_id' => $new->id]);
    }

    public function test_malformed_resume_parameters_do_not_break_the_catalog_page(): void
    {
        Livewire::withQueryParams(['review_provider' => ['unexpected'], 'review_id' => ['unexpected']])
            ->test(EggCatalog::class)->assertOk()->assertSet('resumeProvider', null)->assertSet('resumeId', null);
        Http::assertNothingSent();
    }

    public function test_live_destination_context_cannot_bypass_nest_read_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Egg reader', 'permissions' => ['eggs.read']]);
        $user->roles()->attach($role);
        $this->actingAs($user);
        $nest = Nest::factory()->create();
        Livewire::withQueryParams([])->test(EggCatalog::class)->set('nestContext', $nest->id)->assertForbidden();
    }

    public function test_direct_livewire_actions_reauthorize_when_role_is_revoked(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Catalog reader', 'permissions' => ['eggs.read', 'nests.read']]);
        $user->roles()->attach($role);
        $this->actingAs($user);
        $component = Livewire::test(EggCatalog::class)->call('loadCatalog');
        $role->update(['permissions' => ['nests.read']]);
        $component->call('refreshCatalog')->assertForbidden();
        $this->assertDatabaseCount('eggs', 0);
    }

    public function test_expired_livewire_review_preserves_destination_until_fresh_confirmation(): void
    {
        $nest = Nest::factory()->create();
        $component = Livewire::withQueryParams([])->test(EggCatalog::class)->call('loadCatalog')
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper'])
            ->fillForm(['nest_id' => $nest->id]);
        $this->travel(11)->minutes();
        $component->callMountedAction()->assertSet('reviewError', 'expired')
            ->assertSchemaStateSet(['nest_id' => $nest->id]);
        $this->assertDatabaseCount('eggs', 0);
        $component->callMountedAction()->assertRedirect();
        $this->assertDatabaseCount('eggs', 1);
    }

    public function test_file_import_retains_supported_legacy_conversion(): void
    {
        $data = json_decode($this->bytes, true);
        $data['meta']['version'] = 'PTDL_v1';
        $data['image'] = 'alpine:3.20';
        unset($data['docker_images']);
        $egg = app(EggImporterService::class)->handle(UploadedFile::fake()->createWithContent('egg.json', json_encode($data)), Nest::factory()->create()->id);
        $this->assertSame(['alpine:3.20' => 'alpine:3.20'], $egg->docker_images);
        $this->assertDatabaseCount('egg_variables', 1);
        $this->assertNull($egg->catalog_origin);
    }

    public function test_review_rejects_invalid_shapes_duplicate_variables_and_unsupported_formats(): void
    {
        $data = json_decode($this->bytes, true);
        $invalid = [$data, $data, $data, $data, $data, $data];
        $invalid[0]['meta']['version'] = 'PTDL_v99';
        $invalid[1]['variables'][] = $data['variables'][0];
        $invalid[2]['scripts']['installation'] = 'invalid';
        $invalid[3]['variables'] = 'invalid';
        $invalid[4]['variables'][0]['env_variable'] = 'INVALID!';
        $invalid[5]['meta']['version'] = 'PTDL_v1';
        $invalid[5]['images'] = [['not' => 'an image']];
        foreach ($invalid as $index => $definition) {
            $this->fakeDefinition(json_encode($definition));
            try {
                app(CatalogImportService::class)->stage('Pterodactyl', 'paper');
                $this->fail('Invalid definition staged');
            } catch (CatalogException $exception) {
                $this->assertSame($index === 1 ? 'duplicate_variables' : 'definition', $exception->reason);
            }
        }
        $this->assertDatabaseCount('eggs', 0);
    }

    public function test_database_identity_race_recovers_only_the_catalog_unique_constraint(): void
    {
        $nest = Nest::factory()->create();
        $token = app(CatalogImportService::class)->stage('Pterodactyl', 'paper');
        $entry = app(EggCatalogService::class)->resolve('Pterodactyl', 'paper');
        $real = app(CatalogDefinition::class);
        $winner = null;
        $definition = \Mockery::mock(CatalogDefinition::class)->makePartial();
        $definition->shouldReceive('withFile')->once()->andReturnUsing(function ($bytes, $callback) use ($real, $nest, $entry, &$winner) {
            $winner = $real->withFile($bytes, fn ($file) => app(EggImporterService::class)->handle($file, $nest->id));
            $winner->forceFill(['catalog_key' => $entry->key(), 'catalog_origin' => $entry->origin($bytes)])->save();

            return $real->withFile($bytes, $callback);
        });
        $this->app->instance(CatalogDefinition::class, $definition);
        $egg = app(CatalogImportService::class)->import($token, $nest->id);
        $this->assertSame($winner->id, $egg->id);
        $this->assertDatabaseCount('eggs', 1);
        $this->assertDatabaseCount('egg_variables', 1);
    }

    public function test_unrelated_unique_failure_is_not_hidden_even_if_a_catalog_winner_exists(): void
    {
        $nest = Nest::factory()->create();
        $token = app(CatalogImportService::class)->stage('Pterodactyl', 'paper');
        $entry = app(EggCatalogService::class)->resolve('Pterodactyl', 'paper');
        $realDefinition = app(CatalogDefinition::class);
        $realImporter = app(EggImporterService::class);
        $definition = \Mockery::mock(CatalogDefinition::class)->makePartial();
        $definition->shouldReceive('withFile')->once()->andReturnUsing(function ($bytes, $callback) use ($realDefinition, $realImporter, $nest, $entry) {
            $winner = $realDefinition->withFile($bytes, fn ($file) => $realImporter->handle($file, $nest->id));
            $winner->forceFill(['catalog_key' => $entry->key(), 'catalog_origin' => $entry->origin($bytes)])->save();

            return $realDefinition->withFile($bytes, $callback);
        });
        $this->app->instance(CatalogDefinition::class, $definition);
        $failure = new UniqueConstraintViolationException('sqlite', 'insert into eggs', [], new \PDOException('UNIQUE constraint failed: eggs.uuid'));
        $importer = \Mockery::mock(EggImporterService::class);
        $importer->shouldReceive('handle')->once()->andThrow($failure);
        $this->app->instance(EggImporterService::class, $importer);
        $this->expectException(UniqueConstraintViolationException::class);
        app(CatalogImportService::class)->import($token, $nest->id);
    }

    public function test_revoked_session_cannot_submit_the_staged_livewire_import(): void
    {
        $nest = Nest::factory()->create();
        $response = $this->get(EggResource::getUrl('catalog'))->assertOk();
        preg_match_all('/wire:snapshot="([^\"]+)"/', $response->getContent(), $matches);
        $snapshot = null;
        foreach ($matches[1] as $encoded) {
            $decoded = html_entity_decode($encoded, ENT_QUOTES);
            if (str_contains($decoded, 'EggCatalog') || str_contains($decoded, 'egg-catalog')) {
                $snapshot = $decoded;
                break;
            }
        }
        $this->assertNotNull($snapshot);
        $this->withHeader('X-Livewire', 'true');
        $response = $this->postJson(Livewire::getUpdateUri(), ['components' => [[
            'snapshot' => $snapshot, 'updates' => [],
            'calls' => [['path' => '', 'method' => 'mountAction', 'params' => ['review', ['provider' => 'Pterodactyl', 'id' => 'paper']]]],
        ]]])->assertOk();
        $snapshot = $response->json('components.0.snapshot');
        $this->assertNotNull(json_decode($snapshot, true)['data']['reviewToken']);
        \Livewire\trigger('flush-state');
        $user = auth()->user();
        $this->withSession(['password_hash_web' => $user->password]);
        $user->forceFill(['password' => Hash::make('changed-password')])->save();
        $this->postJson(Livewire::getUpdateUri(), ['components' => [[
            'snapshot' => $snapshot, 'updates' => ['mountedActions.0.data.nest_id' => $nest->id],
            'calls' => [['path' => '', 'method' => 'callMountedAction', 'params' => []]],
        ]]])->assertUnauthorized();
        $this->assertDatabaseCount('eggs', 0);
    }

    public function test_reader_cannot_submit_a_livewire_import_even_when_calling_the_action_directly(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Read only', 'permissions' => ['eggs.read', 'nests.read']]);
        $user->roles()->attach($role);
        $this->actingAs($user);
        $nest = Nest::factory()->create();
        Livewire::withQueryParams([])->test(EggCatalog::class)->call('loadCatalog')
            ->mountAction('review', ['provider' => 'Pterodactyl', 'id' => 'paper'])
            ->fillForm(['nest_id' => $nest->id])->callMountedAction()->assertForbidden();
        $this->assertDatabaseCount('eggs', 0);
    }

    public function test_reader_cannot_invoke_import_action_directly(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'Reader', 'permissions' => ['eggs.read', 'nests.read']]);
        $user->roles()->attach($role);
        $this->actingAs($user);
        $nest = Nest::factory()->create();
        $token = app(CatalogImportService::class)->stage('Pterodactyl', 'paper');
        $this->expectException(HttpException::class);
        app(CatalogImportService::class)->import($token, $nest->id);
    }
}
