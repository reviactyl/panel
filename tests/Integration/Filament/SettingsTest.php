<?php

namespace Tests\Integration\Filament;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Filament\Pages\Settings;
use App\Models\User;
use App\Providers\SettingsServiceProvider;
use App\Services\Users\UserCreationService;
use App\Support\InstallationState;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class SettingsTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('panel.load_environment_only', false);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Notification::fake();
    }

    public function test_monaco_is_selected_and_assigned_to_new_users_by_default(): void
    {
        Livewire::test(Settings::class)->assertSet('data.app:fileEditor', 'mo');

        $this->assertSame('mo', $this->createUser()->fresh()->editor);
    }

    public function test_saved_editor_applies_to_new_users_and_preserves_existing_preferences(): void
    {
        $existingUser = User::factory()->create(['editor' => 'cm']);
        $monacoUser = $this->createUser();

        Livewire::test(Settings::class)
            ->set('data.app:fileEditor', 'cm')
            ->call('save')
            ->assertHasNoErrors();

        $settings = $this->app->make(SettingsRepositoryInterface::class);
        $this->assertSame('cm', $settings->get('settings::app:fileEditor'));
        Livewire::test(Settings::class)->assertSet('data.app:fileEditor', 'cm');

        (new SettingsServiceProvider($this->app))->boot(config(), $this->app->make(InstallationState::class), $settings);
        $codeMirrorUser = $this->createUser();
        $this->assertSame('cm', $codeMirrorUser->fresh()->editor);
        $this->assertSame('mo', $this->createUser(['editor' => 'mo'])->fresh()->editor);

        Livewire::test(Settings::class)
            ->set('data.app:fileEditor', 'mo')
            ->call('save')
            ->assertHasNoErrors();
        (new SettingsServiceProvider($this->app))->boot(config(), $this->app->make(InstallationState::class), $settings);

        $this->assertSame('mo', $this->createUser()->fresh()->editor);
        $this->assertSame('cm', $existingUser->fresh()->editor);
        $this->assertSame('cm', $codeMirrorUser->fresh()->editor);
        $this->assertSame('mo', $monacoUser->fresh()->editor);
    }

    public function test_unknown_or_empty_default_editors_cannot_be_saved(): void
    {
        foreach (['unknown', ''] as $editor) {
            Livewire::test(Settings::class)
                ->set('data.app:fileEditor', $editor)
                ->call('save')
                ->assertHasErrors(['data.app:fileEditor']);
        }

        $this->assertDatabaseMissing('settings', ['key' => 'settings::app:fileEditor']);
    }

    public function test_database_padding_is_removed_from_editor_preferences(): void
    {
        foreach (['mo', 'cm'] as $editor) {
            $user = (new User())->newFromBuilder(['editor' => str_pad($editor, 5)]);

            $this->assertSame($editor, $user->editor);
            $this->assertSame($editor, $user->toArray()['editor']);
        }
    }

    public function test_regular_users_cannot_change_admin_settings(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => false]));
        Livewire::test(Settings::class)->assertForbidden();
    }

    private function createUser(array $attributes = []): User
    {
        return $this->app->make(UserCreationService::class)->handle(array_merge(
            User::factory()->raw(),
            ['password' => 'test-password'],
            $attributes,
        ));
    }
}
