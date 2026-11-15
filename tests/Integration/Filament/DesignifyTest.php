<?php

namespace Tests\Integration\Filament;

use App\Filament\Pages\Designify;
use App\Models\Setting;
use App\Models\User;
use App\Services\Designify\SiteImageService;
use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class DesignifyTest extends IntegrationTestCase
{
    public function test_background_scope_saves_and_rejects_unknown_options(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Designify::class)
            ->assertSet('data.designify:backgroundScope', 'page')
            ->set('data.designify:backgroundScope', 'all')
            ->call('save')->assertHasNoErrors();
        Livewire::test(Designify::class)
            ->assertSet('data.designify:backgroundScope', 'all')
            ->set('data.designify:backgroundScope', 'invalid')
            ->call('save')->assertHasErrors(['data.designify:backgroundScope']);
    }

    public function test_background_overlay_can_be_saved_disabled_and_restored_without_losing_options(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Designify::class)
            ->assertSet('data.designify:backgroundOverlay', true)
            ->assertSet('data.designify:backgroundOverlayOpacity', 85)
            ->set('data.designify:backgroundOverlayOpacity', 60)
            ->set('data.designify:backgroundOverlayColor', '#123456')
            ->call('save')->assertHasNoErrors();

        Livewire::test(Designify::class)
            ->set('data.designify:backgroundOverlay', false)
            ->call('save')->assertHasNoErrors();

        Livewire::test(Designify::class)
            ->assertSet('data.designify:backgroundOverlay', false)
            ->assertSet('data.designify:backgroundOverlayOpacity', '60')
            ->assertSet('data.designify:backgroundOverlayColor', '#123456')
            ->set('data.designify:backgroundOverlay', true)
            ->set('data.designify:backgroundOverlayColor', '')
            ->call('save')->assertHasNoErrors();
        Livewire::test(Designify::class)
            ->assertSet('data.designify:backgroundOverlayColor', '');
    }

    public function test_background_overlay_rejects_invalid_opacity_and_color(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        foreach ([-1, 101, 2.5, 'invalid'] as $opacity) {
            Livewire::test(Designify::class)
                ->set('data.designify:backgroundOverlayOpacity', $opacity)
                ->call('save')->assertHasErrors(['data.designify:backgroundOverlayOpacity']);
        }
        Livewire::test(Designify::class)
            ->set('data.designify:backgroundOverlayColor', 'red; color: blue')
            ->call('save')->assertHasErrors(['data.designify:backgroundOverlayColor']);
    }

    public function test_classic_is_default_and_the_first_background_selection_is_centered(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $page = Livewire::test(Designify::class)
            ->assertSet('data.designify:cardIconStyle', 'classic')
            ->set('data.designify:cardIconSize', 'enlarged')
            ->set('data.designify:cardIconStyle', 'background')
            ->assertSet('data.designify:cardIconSize', 'standard')
            ->set('data.designify:cardIconSize', 'enlarged')
            ->set('data.designify:cardIconStyle', 'classic')
            ->set('data.designify:cardIconStyle', 'background')
            ->assertSet('data.designify:cardIconSize', 'enlarged')
            ->call('save')->assertHasNoErrors();

        Livewire::test(Designify::class)
            ->assertSet('data.designify:cardIconStyle', 'background')
            ->assertSet('data.designify:cardIconSize', 'enlarged');
    }

    public function test_uploaded_images_preview_before_saving_and_persist_only_when_saved(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $page = Livewire::test(Designify::class);
        foreach (['site_image', 'site_favicon', 'background'] as $key) {
            $page->set('siteImageFiles.'.$key, UploadedFile::fake()->createWithContent('image.png', file_get_contents(public_path('reviactyl/icon.png'))))
                ->assertHasNoErrors();
            $url = $page->get('data')['designify:'.$key];
            $this->assertStringStartsWith('/storage/designify/', $url);
            Storage::disk('public')->assertExists(substr($url, strlen('/storage/')));
            $this->assertDatabaseMissing('settings', ['key' => 'settings::designify:'.$key]);
            $page->assertDispatched('designify-preview-updated', fn ($name, $params) => $params['settings'][$key] === $url);
        }

        $page->call('save')->assertHasNoErrors()
            ->assertDispatched('designify-saved', fn ($name, $params) => $params['formData'] === $page->get('data'));
        foreach (['site_image', 'site_favicon', 'background'] as $key) {
            $url = $page->get('data')['designify:'.$key];
            $this->assertDatabaseHas('settings', ['key' => 'settings::designify:'.$key, 'value' => $url]);
            Livewire::test(Designify::class)->assertSet('data.designify:'.$key, $url);
        }
        $this->assertDatabaseMissing('settings', ['key' => 'settings::site_image_upload']);
    }

    public function test_regular_users_cannot_access_designify(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => false]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Designify::class)->assertForbidden();
    }

    public function test_background_upload_errors_preserve_the_saved_image_and_cleanup_keeps_referenced_uploads(): void
    {
        $disk = Storage::fake('public');
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $page = Livewire::test(Designify::class)
            ->set('data.designify:background', '/previous.png')
            ->set('siteImageFiles.background', UploadedFile::fake()->create('document.pdf', 1, 'application/pdf'))
            ->assertHasErrors(['siteImageFiles.background'])
            ->assertSet('data.designify:background', '/previous.png')
            ->set('siteImageFiles.background', UploadedFile::fake()->createWithContent('wallpaper.png', file_get_contents(public_path('reviactyl/icon.png'))))
            ->assertHasNoErrors()
            ->call('save')->assertHasNoErrors();
        $url = $page->get('data')['designify:background'];
        $path = substr($url, strlen('/storage/'));
        touch($disk->path($path), now()->subHours(25)->timestamp);
        $images = app(SiteImageService::class);
        $images->pruneUnusedUploads();
        $disk->assertExists($path);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');

        $page->set('data.designify:background', 'none')->call('save')->assertHasNoErrors();
        $images->pruneUnusedUploads();
        $disk->assertMissing($path);
    }

    public function test_manual_image_paths_remain_editable_and_upload_errors_preserve_them(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Designify::class)
            ->set('data.designify:site_image', '/reviactyl/previous.png')
            ->set('siteImageFiles.site_image', UploadedFile::fake()->create('document.pdf', 1, 'application/pdf'))
            ->assertHasErrors(['siteImageFiles.site_image'])
            ->assertSee('The Site Image must be a file of type:')
            ->assertSet('data.designify:site_image', '/reviactyl/previous.png')
            ->set('siteImageFiles.site_image', UploadedFile::fake()->createWithContent('image.png', file_get_contents(public_path('reviactyl/icon.png'))))
            ->assertHasNoErrors()
            ->set('data.designify:site_image', '/reviactyl/manual.png')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['key' => 'settings::designify:site_image', 'value' => '/reviactyl/manual.png']);
    }

    public function test_environment_only_configuration_cannot_upload_images(): void
    {
        config()->set('panel.load_environment_only', true);
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Designify::class)
            ->set('siteImageFiles.site_image', UploadedFile::fake()->createWithContent('image.png', file_get_contents(public_path('reviactyl/icon.png'))))
            ->assertForbidden();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_uploaded_images_are_public_without_a_storage_symlink(): void
    {
        Storage::fake('public');
        $url = app(SiteImageService::class)->store(UploadedFile::fake()->createWithContent('image.png', file_get_contents(public_path('reviactyl/icon.png'))));
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/storage/designify/'.str_repeat('a', 40).'.png')->assertNotFound();
    }

    public function test_cleanup_preserves_saved_images_and_recent_drafts_and_only_removes_managed_uploads(): void
    {
        $disk = Storage::fake('public');
        $paths = [];
        foreach (range('a', 'h') as $name) {
            $path = 'designify/'.str_repeat($name, 40).'.png';
            $disk->put($path, 'image');
            touch($disk->path($path), now()->subHours(25)->timestamp);
            $paths[$name] = $path;
        }
        Setting::query()->create(['key' => 'settings::designify:site_image', 'value' => '/storage/'.$paths['a']]);
        Setting::query()->create(['key' => 'settings::designify:site_favicon', 'value' => '/public/storage/'.$paths['b']]);
        Setting::query()->create(['key' => 'settings::email-template:custom', 'value' => json_encode(['html' => '<img src="https://panel.test/storage/'.$paths['c'].'">'])]);
        config()->set('designify.errors.404.image', '/storage/'.$paths['d']);
        config()->set('app.logo', '/storage/'.$paths['e']);
        touch($disk->path($paths['f']), now()->subHours(23)->timestamp);
        $disk->put('designify/manually-managed.png', 'image');
        touch($disk->path('designify/manually-managed.png'), now()->subDays(2)->timestamp);
        $disk->put('other/'.basename($paths['g']), 'image');
        touch($disk->path('other/'.basename($paths['g'])), now()->subDays(2)->timestamp);

        $this->artisan('p:maintenance:prune-designify-images')->assertSuccessful();

        foreach (range('a', 'f') as $name) {
            $disk->assertExists($paths[$name]);
        }
        $disk->assertMissing($paths['g']);
        $disk->assertMissing($paths['h']);
        $disk->assertExists('designify/manually-managed.png');
        $disk->assertExists('other/'.basename($paths['g']));

        $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains($event->command ?? '', 'p:maintenance:prune-designify-images'));
        $this->assertNotNull($event);
        $this->assertSame('0 * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    public function test_replaced_and_abandoned_uploads_are_eventually_cleaned_up(): void
    {
        $disk = Storage::fake('public');
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $page = Livewire::test(Designify::class);
        $paths = [];
        for ($index = 0; $index < 3; $index++) {
            $page->set('siteImageFiles.site_image', UploadedFile::fake()->createWithContent('image.png', file_get_contents(public_path('reviactyl/icon.png'))))
                ->assertHasNoErrors();
            $paths[] = substr($page->get('data')['designify:site_image'], strlen('/storage/'));
            if ($index !== 2) {
                $page->call('save')->assertHasNoErrors();
            }
        }

        $this->artisan('p:maintenance:prune-designify-images')->assertSuccessful();
        foreach ($paths as $path) {
            $disk->assertExists($path);
            touch($disk->path($path), now()->subHours(25)->timestamp);
        }
        $this->artisan('p:maintenance:prune-designify-images')->assertSuccessful();
        $disk->assertMissing($paths[0]);
        $disk->assertExists($paths[1]);
        $disk->assertMissing($paths[2]);
    }
}
