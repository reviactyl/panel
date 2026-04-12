<?php

namespace Tests\Integration\Repositories;

use App\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Tests\Integration\IntegrationTestCase;

class SettingsRepositoryTest extends IntegrationTestCase
{
    public function test_setting_repository_updates_existing_setting_by_key(): void
    {
        $settings = $this->app->make(SettingsRepositoryInterface::class);

        $settings->set('settings::app:locale', 'en');
        $settings->set('settings::app:locale', 'fr');

        $this->assertSame('fr', DB::table('settings')->where('key', 'settings::app:locale')->value('value'));
        $this->assertSame('fr', $settings->get('settings::app:locale'));
        $this->assertSame(1, DB::table('settings')->where('key', 'settings::app:locale')->count());
    }
}
