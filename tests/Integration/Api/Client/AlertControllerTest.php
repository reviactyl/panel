<?php

namespace Tests\Integration\Api\Client;

use App\Exceptions\Model\DataValidationException;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use App\Models\Alert;
use App\Models\AlertInteraction;
use App\Models\Permission;
use App\Models\User;
use App\Services\Alerts\AlertVisibilityService;
use Carbon\CarbonImmutable;

class AlertControllerTest extends ClientApiIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Alert::query()->delete();
    }

    protected function tearDown(): void
    {
        Alert::query()->delete();

        parent::tearDown();
    }

    public function test_only_active_alerts_for_the_placement_are_returned_in_priority_order(): void
    {
        $user = User::factory()->create();

        $low = Alert::factory()->create(['priority' => 1]);
        $high = Alert::factory()->create([
            'priority' => 5,
            'title' => 'Offer',
            'buttons' => [
                ['label' => 'Claim', 'url' => '/account'],
            ],
        ]);
        Alert::factory()->create(['enabled' => false]);
        Alert::factory()->create(['starts_at' => CarbonImmutable::now()->addDay()]);
        Alert::factory()->create(['ends_at' => CarbonImmutable::now()->subMinute()]);
        Alert::factory()->create(['placements' => [Alert::PLACEMENT_ACCOUNT]]);

        $this->actingAs($user)->getJson('/api/client/alerts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.object', Alert::RESOURCE_NAME)
            ->assertJsonPath('data.0.attributes.uuid', $high->uuid)
            ->assertJsonPath('data.0.attributes.title', 'Offer')
            ->assertJsonPath('data.0.attributes.buttons', [
                ['label' => 'Claim', 'url' => '/account', 'style' => 'primary', 'new_tab' => false],
            ])
            ->assertJsonPath('data.1.attributes.uuid', $low->uuid)
            ->assertJsonMissingPath('data.0.attributes.targeting');

        $this->getJson('/api/client/alerts?placement=account')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/client/alerts?placement=auth')->assertUnprocessable();
    }

    public function test_a_button_cannot_point_at_an_unsafe_link(): void
    {
        $this->expectException(DataValidationException::class);

        Alert::factory()->create(['buttons' => [['label' => 'Bad', 'url' => 'javascript:alert(1)']]]);
    }

    public function test_alerts_are_limited_to_their_audience(): void
    {
        $admin = User::factory()->create(['root_admin' => true, 'language' => 'en']);
        $user = User::factory()->create(['language' => 'de', 'use_totp' => true]);
        $newcomer = User::factory()->create(['language' => 'en']);
        $user->forceFill(['created_at' => CarbonImmutable::now()->subDays(40)])->saveQuietly();

        $admins = Alert::factory()->create(['audience' => 'admins']);
        $users = Alert::factory()->create(['audience' => 'users']);
        $specific = Alert::factory()->create(['targeting' => ['users' => [$newcomer->id]]]);
        $german = Alert::factory()->create(['targeting' => ['languages' => ['de']]]);
        $noTwoFactor = Alert::factory()->create(['targeting' => ['two_factor' => 'disabled']]);
        $established = Alert::factory()->create(['targeting' => ['account_age_min' => 30]]);
        $recent = Alert::factory()->create(['audience' => 'users', 'targeting' => ['account_age_max' => 7]]);

        $this->assertEqualsCanonicalizing(
            [$admins->uuid, $noTwoFactor->uuid],
            $this->visibleAlerts($admin),
        );
        $this->assertEqualsCanonicalizing(
            [$users->uuid, $german->uuid, $established->uuid],
            $this->visibleAlerts($user),
        );
        $this->assertEqualsCanonicalizing(
            [$users->uuid, $specific->uuid, $noTwoFactor->uuid, $recent->uuid],
            $this->visibleAlerts($newcomer),
        );
    }

    public function test_alerts_can_target_people_by_their_servers(): void
    {
        [$owner, $server] = $this->generateTestAccount();
        [$subuser, $shared] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);
        $stranger = User::factory()->create();
        $admin = User::factory()->create(['root_admin' => true]);

        $onNode = Alert::factory()->create(['targeting' => ['nodes' => [$server->node_id]]]);
        $onLocation = Alert::factory()->create(['targeting' => ['locations' => [$server->node->location_id]]]);
        $onEgg = Alert::factory()->create(['targeting' => ['eggs' => [$server->egg_id], 'nests' => [$server->nest_id]]]);
        $owners = Alert::factory()->create(['targeting' => ['servers' => 'owner']]);
        $subusers = Alert::factory()->create(['targeting' => ['servers' => 'subuser']]);
        $serverless = Alert::factory()->create(['targeting' => ['servers' => 'none']]);
        Alert::factory()->create(['targeting' => ['nodes' => [$server->node_id + $shared->node_id + 1]]]);

        $this->assertEqualsCanonicalizing(
            [$onNode->uuid, $onLocation->uuid, $onEgg->uuid, $owners->uuid],
            $this->visibleAlerts($owner),
        );
        $this->assertContains($subusers->uuid, $this->visibleAlerts($subuser));
        $this->assertNotContains($owners->uuid, $this->visibleAlerts($subuser));
        $this->assertNotContains($onNode->uuid, $this->visibleAlerts($subuser));
        $this->assertSame([$serverless->uuid], $this->visibleAlerts($stranger));

        $this->assertEqualsCanonicalizing(
            [$onNode->uuid, $onLocation->uuid, $onEgg->uuid, $owners->uuid],
            $this->visibleAlerts($owner, "?placement=server&server=$server->uuid"),
        );
        $this->assertEqualsCanonicalizing(
            [$onEgg->uuid, $subusers->uuid],
            $this->visibleAlerts($subuser, "?placement=server&server=$shared->uuidShort"),
        );
        $this->assertEqualsCanonicalizing(
            [$onNode->uuid, $onLocation->uuid, $onEgg->uuid, $serverless->uuid],
            $this->visibleAlerts($admin, "?placement=server&server=$server->uuid"),
        );

        $this->actingAs($stranger)->getJson("/api/client/alerts?placement=server&server=$server->uuid")->assertNotFound();
        $this->getJson('/api/client/alerts?placement=server')->assertUnprocessable();
    }

    public function test_an_alert_can_be_dismissed_and_comes_back_when_configured(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $alert = Alert::factory()->create(['redisplay_after_days' => 7]);
        $permanent = Alert::factory()->create(['dismissible' => false]);

        $this->actingAs($user)->postJson("/api/client/alerts/$alert->uuid/dismiss")->assertNoContent();
        $this->postJson("/api/client/alerts/$permanent->uuid/dismiss")->assertStatus(400);
        $this->postJson('/api/client/alerts/not-an-alert/dismiss')->assertNotFound();

        $this->assertSame([$permanent->uuid], $this->visibleAlerts($user));
        $this->assertCount(2, $this->visibleAlerts($other));

        $this->travelTo(CarbonImmutable::now()->addDays(8));
        $this->assertCount(2, $this->visibleAlerts($user));
    }

    public function test_button_clicks_are_recorded_and_can_dismiss_the_alert(): void
    {
        $user = User::factory()->create();
        $button = [['label' => 'Claim', 'url' => 'https://example.com']];

        $tracked = Alert::factory()->create(['buttons' => $button]);
        $once = Alert::factory()->create(['buttons' => $button, 'dismissible' => false, 'dismiss_on_action' => true]);
        $plain = Alert::factory()->create();

        $this->actingAs($user)->postJson("/api/client/alerts/$tracked->uuid/click")->assertNoContent();
        $this->postJson("/api/client/alerts/$once->uuid/click")->assertNoContent();
        $this->postJson("/api/client/alerts/$plain->uuid/click")->assertStatus(400);

        $this->assertEqualsCanonicalizing([$tracked->uuid, $plain->uuid], $this->visibleAlerts($user));
        $this->assertSame(2, AlertInteraction::query()->where('user_id', $user->id)->whereNotNull('clicked_at')->count());
    }

    public function test_changing_how_an_alert_is_closed_applies_to_people_who_already_closed_it(): void
    {
        $user = User::factory()->create();
        $button = [['label' => 'Claim', 'url' => '/account']];

        $closed = Alert::factory()->create();
        $followed = Alert::factory()->create(['buttons' => $button, 'dismiss_on_action' => true]);

        $this->actingAs($user)->postJson("/api/client/alerts/$closed->uuid/dismiss")->assertNoContent();
        $this->postJson("/api/client/alerts/$followed->uuid/click")->assertNoContent();
        $this->assertSame([], $this->visibleAlerts($user));

        $closed->update(['dismissible' => false]);
        $followed->update(['dismiss_on_action' => false]);
        $this->assertEqualsCanonicalizing([$closed->uuid, $followed->uuid], $this->visibleAlerts($user));

        $closed->update(['dismissible' => true]);
        $followed->update(['dismiss_on_action' => true]);
        $this->assertSame([], $this->visibleAlerts($user));

        $followed->update(['buttons' => []]);
        $this->assertSame([$followed->uuid], $this->visibleAlerts($user));
    }

    public function test_resetting_dismissals_shows_the_alert_to_everyone_again(): void
    {
        $user = User::factory()->create();
        $button = [['label' => 'Claim', 'url' => '/account']];

        $closed = Alert::factory()->create();
        $followed = Alert::factory()->create(['buttons' => $button, 'dismiss_on_action' => true]);

        $this->actingAs($user)->postJson("/api/client/alerts/$closed->uuid/dismiss")->assertNoContent();
        $this->postJson("/api/client/alerts/$followed->uuid/click")->assertNoContent();

        $this->travelTo(CarbonImmutable::now()->addMinute());
        $service = $this->app->make(AlertVisibilityService::class);
        $service->resetDismissals($closed);
        $service->resetDismissals($followed);

        $this->assertEqualsCanonicalizing([$closed->uuid, $followed->uuid], $this->visibleAlerts($user));
        $this->assertSame(1, $followed->interactions()->whereNotNull('clicked_at')->count());

        $this->travelTo(CarbonImmutable::now()->addMinute());
        $this->postJson("/api/client/alerts/$closed->uuid/dismiss")->assertNoContent();
        $this->assertSame([$followed->uuid], $this->visibleAlerts($user));
    }

    public function test_alerts_that_are_not_shown_to_someone_cannot_be_interacted_with(): void
    {
        $user = User::factory()->create();
        $button = [['label' => 'Claim', 'url' => '/account']];

        $unavailable = [
            Alert::factory()->create(['enabled' => false, 'buttons' => $button]),
            Alert::factory()->create(['ends_at' => CarbonImmutable::now()->subMinute(), 'buttons' => $button]),
            Alert::factory()->create(['starts_at' => CarbonImmutable::now()->addDay(), 'buttons' => $button]),
            Alert::factory()->create(['audience' => 'admins', 'buttons' => $button]),
        ];

        $this->actingAs($user);
        foreach ($unavailable as $alert) {
            $this->postJson("/api/client/alerts/$alert->uuid/dismiss")->assertNotFound();
            $this->postJson("/api/client/alerts/$alert->uuid/click")->assertNotFound();
        }

        $this->assertSame(0, AlertInteraction::query()->where('user_id', $user->id)->count());
    }

    public function test_interactions_follow_the_placement_and_server_the_alert_is_shown_on(): void
    {
        [$owner, $server] = $this->generateTestAccount();
        $admin = User::factory()->create(['root_admin' => true]);
        $button = [['label' => 'Claim', 'url' => '/account']];

        $onNode = Alert::factory()->create(['targeting' => ['nodes' => [$server->node_id]], 'buttons' => $button]);
        $accountOnly = Alert::factory()->create(['placements' => [Alert::PLACEMENT_ACCOUNT], 'buttons' => $button]);
        $context = ['placement' => Alert::PLACEMENT_SERVER, 'server' => $server->uuid];

        $this->actingAs($admin)->postJson("/api/client/alerts/$onNode->uuid/dismiss")->assertNotFound();
        $this->postJson("/api/client/alerts/$onNode->uuid/click", $context)->assertNoContent();
        $this->postJson("/api/client/alerts/$accountOnly->uuid/dismiss")->assertNotFound();
        $this->postJson("/api/client/alerts/$accountOnly->uuid/dismiss", ['placement' => Alert::PLACEMENT_ACCOUNT])->assertNoContent();
        $this->postJson("/api/client/alerts/$accountOnly->uuid/click", ['placement' => Alert::PLACEMENT_AUTH])->assertUnprocessable();

        $this->actingAs($owner)->postJson("/api/client/alerts/$onNode->uuid/dismiss")->assertNoContent();
        $this->assertNotContains($onNode->uuid, $this->visibleAlerts($owner));

        $server->update(['owner_id' => $admin->id]);
        $this->actingAs($owner)->postJson("/api/client/alerts/$onNode->uuid/click")->assertNotFound();
        $this->postJson("/api/client/alerts/$onNode->uuid/click", $context)->assertNotFound();
    }

    public function test_an_interaction_right_after_a_reset_still_hides_the_alert(): void
    {
        $this->travelTo(CarbonImmutable::now()->startOfSecond());

        $user = User::factory()->create();
        $closed = Alert::factory()->create();
        $followed = Alert::factory()->create([
            'buttons' => [['label' => 'Claim', 'url' => '/account']],
            'dismissible' => false,
            'dismiss_on_action' => true,
        ]);

        $service = $this->app->make(AlertVisibilityService::class);
        $service->resetDismissals($closed);
        $service->resetDismissals($followed);

        $this->actingAs($user)->postJson("/api/client/alerts/$closed->uuid/dismiss")->assertNoContent();
        $this->postJson("/api/client/alerts/$followed->uuid/click")->assertNoContent();

        $this->assertSame([], $this->visibleAlerts($user));
    }

    public function test_alerts_are_unavailable_until_required_two_factor_is_enabled(): void
    {
        config()->set('panel.auth.2fa_required', RequireTwoFactorAuthentication::LEVEL_ALL);

        $user = User::factory()->create(['use_totp' => false]);
        $alert = Alert::factory()->create(['buttons' => [['label' => 'Claim', 'url' => '/account']]]);

        $this->actingAs($user)->getJson('/api/client/alerts')->assertStatus(400);
        $this->postJson("/api/client/alerts/$alert->uuid/dismiss")->assertStatus(400);
        $this->postJson("/api/client/alerts/$alert->uuid/click")->assertStatus(400);
        $this->assertSame(0, AlertInteraction::query()->count());

        $user->update(['use_totp' => true]);
        $this->assertSame([$alert->uuid], $this->visibleAlerts($user));
    }

    public function test_repeated_interactions_keep_a_single_record(): void
    {
        $user = User::factory()->create();
        $alert = Alert::factory()->create(['buttons' => [['label' => 'Claim', 'url' => '/account']]]);

        $this->actingAs($user);
        $this->postJson("/api/client/alerts/$alert->uuid/click")->assertNoContent();
        $this->postJson("/api/client/alerts/$alert->uuid/dismiss")->assertNoContent();
        $this->postJson("/api/client/alerts/$alert->uuid/dismiss")->assertNoContent();
        $this->postJson("/api/client/alerts/$alert->uuid/click")->assertNoContent();

        $interaction = AlertInteraction::query()->where('user_id', $user->id)->sole();
        $this->assertNotNull($interaction->dismissed_at);
        $this->assertNotNull($interaction->clicked_at);

        $alert->delete();
        $this->assertSame(0, AlertInteraction::query()->count());
    }

    public function test_guests_only_receive_untargeted_alerts_for_the_authentication_pages(): void
    {
        $alert = Alert::factory()->create(['placements' => [Alert::PLACEMENT_AUTH, Alert::PLACEMENT_DASHBOARD]]);
        Alert::factory()->create();

        Alert::factory()->create(['placements' => [Alert::PLACEMENT_AUTH], 'audience' => 'admins']);
        Alert::factory()->create(['placements' => [Alert::PLACEMENT_AUTH], 'targeting' => ['two_factor' => 'disabled']]);
        Alert::factory()->create(['placements' => [Alert::PLACEMENT_AUTH], 'targeting' => ['nodes' => [1]]]);

        $this->getJson('/auth/alerts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.uuid', $alert->uuid);

        $this->getJson('/api/client/alerts')->assertUnauthorized();
    }

    /**
     * @return string[]
     */
    private function visibleAlerts(User $user, string $query = ''): array
    {
        return $this->actingAs($user)
            ->getJson('/api/client/alerts'.$query)
            ->assertOk()
            ->json('data.*.attributes.uuid');
    }
}
