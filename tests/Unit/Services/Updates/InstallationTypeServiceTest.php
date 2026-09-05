<?php

namespace Tests\Unit\Services\Updates;

use App\Filament\Pages\SoftwareUpdates;
use App\Services\Updates\InstallationTypeService;
use Tests\TestCase;

class InstallationTypeServiceTest extends TestCase
{
    public function test_panel_only_supports_automatic_updates_for_explicit_native_installations(): void
    {
        $service = new InstallationTypeService();
        config()->set('app.version', '26.09.0');
        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.driver', 'mysql');

        config()->set('panel.installation_type', 'native');
        $this->assertSame(InstallationTypeService::NATIVE, $service->panel());
        $this->assertTrue($service->panelSupportsAutomaticUpdates());

        config()->set('panel.installation_type', 'docker');
        $this->assertSame(InstallationTypeService::DOCKER, $service->panel());
        $this->assertFalse($service->panelSupportsAutomaticUpdates());

        config()->set('panel.installation_type', 'unexpected');
        $this->assertSame(InstallationTypeService::UNKNOWN, $service->panel());
        $this->assertFalse($service->panelSupportsAutomaticUpdates());
    }

    public function test_panel_automatic_updates_require_a_release_and_supported_database(): void
    {
        $service = new InstallationTypeService();
        config()->set('panel.installation_type', 'native');
        config()->set('database.default', 'database');

        config()->set('app.version', 'canary');
        config()->set('database.connections.database.driver', 'mysql');
        $this->assertFalse($service->panelSupportsAutomaticUpdates());
        $this->assertTrue($service->panelSupportsSoftwareUpdatesPage());

        config()->set('app.version', '26.09.0');
        config()->set('database.connections.database.driver', 'pgsql');
        $this->assertTrue($service->panelSupportsAutomaticUpdates());

        config()->set('database.connections.database.driver', 'sqlite');
        $this->assertFalse($service->panelSupportsAutomaticUpdates());
    }

    public function test_missing_agent_metadata_is_treated_as_unknown(): void
    {
        $this->assertSame(InstallationTypeService::UNKNOWN, InstallationTypeService::normalize(null));
    }

    public function test_software_update_page_is_only_visible_and_accessible_on_native_installations(): void
    {
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', 'canary');
        $this->assertTrue(SoftwareUpdates::shouldRegisterNavigation());
        $this->assertTrue(SoftwareUpdates::canAccess());

        config()->set('panel.installation_type', 'docker');
        $this->assertFalse(SoftwareUpdates::shouldRegisterNavigation());
        $this->assertFalse(SoftwareUpdates::canAccess());
    }
}
