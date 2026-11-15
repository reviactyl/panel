<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Nests\Eggs\Pages\CreateEgg;
use App\Filament\Resources\Nests\Eggs\Pages\EditEgg;
use App\Filament\Resources\Nests\Pages\EditNest;
use App\Filament\Resources\Nests\RelationManagers\EggsRelationManager;
use App\Models\Egg;
use App\Models\Nest;
use App\Models\User;
use App\Services\Eggs\EggConfigurationService;
use App\Services\Eggs\EggCreationService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class CreateEggTest extends IntegrationTestCase
{
    public function test_uploaded_image_is_stored_as_base64_and_editor_state_stays_small(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $egg = $this->createServerModel()->egg;
        $contents = str_pad(file_get_contents(public_path('reviactyl/icon.png')), 10 * 1024 * 1024, "\0");
        $file = UploadedFile::fake()->createWithContent('custom.png', $contents);

        $page = Livewire::test(EditEgg::class, ['record' => $egg->id])
            ->set('eggImageUpload', $file)
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame('data:image/png;base64,'.base64_encode($contents), $egg->fresh()->image);
        $page = Livewire::test(EditEgg::class, ['record' => $egg->id]);
        $this->assertStringNotContainsString('data:image/png;base64,', json_encode($page->get('data')));
        $page->call('save')->assertHasNoFormErrors();
        $this->assertSame('data:image/png;base64,'.base64_encode($contents), $egg->fresh()->image);

        $url = $egg->fresh()->image_url;
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame($contents, $response->getContent());
        $this->get($url, ['If-None-Match' => $response->headers->get('ETag')])->assertStatus(304);
        $this->get(preg_replace('/[a-f0-9]{32}\/image$/', str_repeat('0', 32).'/image', $url))->assertNotFound();

        $page->fillForm(['image' => null])->call('save')->assertHasNoFormErrors();
        $this->assertNull($egg->fresh()->image);
        $this->get($url)->assertNotFound();
    }

    public function test_existing_image_urls_can_be_kept_or_replaced(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $egg = $this->createServerModel()->egg;
        $egg->update(['image' => 'https://example.com/old.png']);
        Livewire::test(EditEgg::class, ['record' => $egg->id])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('https://example.com/old.png', $egg->fresh()->image);
        Livewire::test(EditEgg::class, ['record' => $egg->id])
            ->set('data.image', 'https://example.com/new.png')
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('https://example.com/new.png', $egg->fresh()->image);
    }

    public function test_invalid_upload_keeps_the_previous_choice_and_a_url_can_replace_an_upload(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $egg = $this->createServerModel()->egg;
        $egg->update(['image' => 'https://example.com/old.png']);
        $contents = file_get_contents(public_path('reviactyl/icon.png'));
        $page = Livewire::test(EditEgg::class, ['record' => $egg->id])
            ->set('eggImageUpload', UploadedFile::fake()->createWithContent('custom.png', $contents));
        $preview = $page->get('data')['image'];
        $this->assertStringContainsString('preview-file', $preview);
        $this->assertSame('https://example.com/old.png', $egg->fresh()->image);

        $page->set('eggImageUpload', UploadedFile::fake()->createWithContent('invalid.txt', 'not an image'))
            ->assertHasErrors('eggImageUpload')
            ->assertSet('data.image', $preview);
        $page->call('save')->assertHasNoFormErrors();
        $this->assertSame('data:image/png;base64,'.base64_encode($contents), $egg->fresh()->image);

        $page->set('eggImageUpload', UploadedFile::fake()->createWithContent('another.png', $contents))
            ->set('data.image', 'https://example.com/new.png')
            ->assertSet('eggImageFile', null)
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('https://example.com/new.png', $egg->fresh()->image);
    }

    public function test_an_image_can_be_uploaded_when_creating_an_egg(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $contents = '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10H0z"/></svg>';
        Livewire::test(CreateEgg::class)
            ->fillForm([
                'nest_id' => Nest::factory()->create()->id,
                'name' => 'Uploaded image',
                'docker_images' => ['Demo' => 'alpine:3.20'],
                'startup' => 'echo demo',
                'config_stop' => 'stop',
                'config_startup' => '{"done":"ready"}',
                'config_logs' => '{}',
                'config_files' => '{}',
                'script_container' => 'alpine:3.20',
                'script_entry' => 'sh',
                'script_install' => '#!/bin/sh',
            ])
            ->set('eggImageUpload', UploadedFile::fake()->createWithContent('image.svg', $contents))
            ->call('create')->assertHasNoFormErrors()->assertRedirect();
        $this->assertSame('data:image/svg+xml;base64,'.base64_encode($contents), Egg::query()->where('name', 'Uploaded image')->sole()->image);
    }

    public function test_nest_from_create_link_is_preselected_and_can_be_changed(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $originalNest = Nest::factory()->create();
        $selectedNest = Nest::factory()->create();

        foreach ([$originalNest, $selectedNest] as $nest) {
            $name = 'Egg for nest '.$nest->id;

            Livewire::withQueryParams(['nest_id' => $originalNest->id])
                ->test(CreateEgg::class)
                ->assertFormSet(['nest_id' => $originalNest->id])
                ->fillForm([
                    'nest_id' => $nest->id,
                    'name' => $name,
                    'docker_images' => ['Demo' => 'alpine:3.20'],
                    'startup' => 'echo demo',
                    'config_stop' => 'stop',
                    'config_startup' => '{"done":"ready"}',
                    'config_logs' => '{}',
                    'config_files' => '{}',
                    'script_container' => 'alpine:3.20',
                    'script_entry' => 'sh',
                    'script_install' => '#!/bin/sh',
                ])
                ->call('create')
                ->assertHasNoFormErrors()
                ->assertRedirect();

            $egg = Egg::query()->where('name', $name)->sole();
            $this->assertSame($nest->id, $egg->nest_id);
            $this->assertSame('Created', $egg->creation_source_label);
        }
    }

    public function test_secret_variable_can_be_configured_in_the_editor(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $server = $this->createServerModel();
        Livewire::test(EditEgg::class, ['record' => $server->egg_id])
            ->fillForm(['variables' => ['secret' => [
                'name' => 'Git Access Token', 'env_variable' => 'GIT_TOKEN',
                'default_value' => '', 'rules' => ['nullable', 'string', 'max:100'],
                'user_viewable' => true, 'user_editable' => true, 'is_secret' => true,
            ]]])
            ->call('save')->assertHasNoFormErrors();
        $variable = $server->egg->variables()->where('env_variable', 'GIT_TOKEN')->sole();
        $this->assertTrue($variable->is_secret);
        $this->assertSame('nullable|string|max:100', $variable->rules);
    }

    public function test_editor_rejects_duplicate_environment_names(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $server = $this->createServerModel();
        $variable = [
            'name' => 'Test option', 'env_variable' => 'TEST_OPTION',
            'default_value' => 'value', 'rules' => ['required', 'string'],
            'user_viewable' => true, 'user_editable' => true,
        ];

        Livewire::test(EditEgg::class, ['record' => $server->egg_id])
            ->fillForm(['variables' => ['first' => $variable, 'second' => $variable]])
            ->call('save')
            ->assertHasFormErrors(['variables.first.env_variable', 'variables.second.env_variable']);

        $this->assertDatabaseMissing('egg_variables', ['egg_id' => $server->egg_id, 'env_variable' => 'TEST_OPTION']);
    }

    #[DataProvider('deletionActions')]
    public function test_deletion_preserves_parent_eggs_and_dependent_server_configuration(string $action): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $server = $this->createServerModel();
        $parent = $server->egg;
        $parent->forceFill(['config_files' => '{}'])->save();
        $child = app(EggCreationService::class)->handle([
            'nest_id' => $parent->nest_id,
            'name' => 'Dependent egg',
            'docker_images' => $parent->docker_images,
            'startup' => $parent->startup,
            'config_from' => $parent->id,
        ]);
        $server->forceFill(['egg_id' => $child->id])->save();
        $configuration = app(EggConfigurationService::class)->handle($server->fresh());
        $this->assertSame(0, $parent->servers()->count());
        $this->assertNull($child->config_files);

        if ($action === 'edit') {
            $page = Livewire::test(EditEgg::class, ['record' => $parent->id])->callAction('delete');
        } else {
            $page = Livewire::test(EggsRelationManager::class, [
                'ownerRecord' => $parent->nest,
                'pageClass' => EditNest::class,
            ]);
            if ($action === 'row') {
                $page->callTableAction('delete', $parent);
            } else {
                $unused = $this->cloneEggAndVariables($parent);
                $unused->name = 'Unused egg';
                $unused->save();
                $page->callTableBulkAction('delete', [$parent, $child, $unused]);
                $this->assertModelMissing($unused);
            }
        }

        $page->assertNotified(Notification::make()
            ->title(trans('admin/eggs.notices.cannot_delete'))
            ->body(trans('exceptions.nest.egg.has_children'))
            ->status($action === 'bulk' ? 'warning' : 'danger'));
        $this->assertModelExists($parent);
        $this->assertModelExists($child);
        $this->assertSame($parent->id, $child->fresh()->config_from);
        $this->assertSame($configuration, app(EggConfigurationService::class)->handle($server->fresh()));
    }

    #[DataProvider('deletionActions')]
    public function test_deletion_allows_eggs_without_servers_or_dependents(string $action): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $egg = $this->cloneEggAndVariables($this->createServerModel()->egg);

        if ($action === 'edit') {
            Livewire::test(EditEgg::class, ['record' => $egg->id])->callAction('delete');
        } else {
            $page = Livewire::test(EggsRelationManager::class, [
                'ownerRecord' => $egg->nest,
                'pageClass' => EditNest::class,
            ]);
            if ($action === 'row') {
                $page->callTableAction('delete', $egg);
            } else {
                $page->callTableBulkAction('delete', [$egg]);
            }
        }

        $this->assertModelMissing($egg);
    }

    public static function deletionActions(): array
    {
        return [['edit'], ['row'], ['bulk']];
    }

    public function test_nest_is_required_without_a_create_link_default(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateEgg::class)
            ->assertFormSet(['nest_id' => null])
            ->call('create')
            ->assertHasFormErrors(['nest_id' => 'required']);

        $this->assertDatabaseCount('eggs', 0);
    }
}
