<?php

namespace Tests\Integration\Filament;

use App\Filament\Pages\Designify;
use App\Models\User;
use App\Services\Designify\SiteImageService;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class DesignifyTest extends IntegrationTestCase
{
    public function test_uploaded_images_preview_before_saving_and_persist_only_when_saved(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $page = Livewire::test(Designify::class);
        foreach (['site_image', 'site_favicon'] as $key) {
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
        foreach (['site_image', 'site_favicon'] as $key) {
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
}
