<?php

namespace Tests\Integration\Api\Application\Users;

use App\Models\User;
use App\Services\Acl\Api\AdminAcl;
use App\Services\Notifications\NotificationService;
use Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class NotificationControllerTest extends ApplicationApiIntegrationTestCase
{
    public function test_custom_notification_lifecycle_and_extension_filter(): void
    {
        $user = User::factory()->create();
        $url = '/api/application/users/'.$user->id.'/notifications';
        $response = $this->postJson($url, ['title' => 'Invoice ready', 'body' => 'Open Billing', 'url' => '/extensions/billing', 'extension' => 'billing', 'source' => 'override', 'event' => 'installed']);
        $response->assertCreated()->assertJsonPath('object', 'notification')->assertJsonPath('attributes.data.event', 'custom')->assertJsonPath('attributes.data.source', 'reviactyl');
        $id = $response->json('attributes.id');
        $this->getJson($url.'?extension=billing&unread=1')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson($url.'?extension=other')->assertOk()->assertJsonCount(0, 'data');
        $this->patchJson($url.'/'.$id, ['title' => 'Invoice paid', 'status' => 'success', 'read' => true])->assertOk()->assertJsonPath('attributes.data.title', 'Invoice paid');
        $this->getJson($url.'/'.$id)->assertOk()->assertJsonPath('attributes.data.status', 'success');
        $this->getJson($url.'?unread=1')->assertJsonCount(0, 'data');
        $this->deleteJson($url.'/'.$id)->assertNoContent();
        $this->getJson($url.'/'.$id)->assertNotFound();
    }

    public function test_user_permissions_and_notification_ownership_are_enforced(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notice = app(NotificationService::class)->create($other, ['title' => 'Private']);
        $url = '/api/application/users/'.$user->id.'/notifications';
        $this->getJson($url.'/'.$notice->id)->assertNotFound();
        $this->patchJson($url.'/'.$notice->id, ['read' => true])->assertNotFound();
        $this->deleteJson($url.'/'.$notice->id)->assertNotFound();
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_users' => AdminAcl::READ]);
        $this->app['auth']->forgetGuards();
        $this->getJson($url)->assertOk();
        $this->postJson($url, ['title' => 'Blocked'])->assertForbidden();
        $this->patchJson('/api/application/users/'.$other->id.'/notifications/'.$notice->id, ['read' => true])->assertForbidden();
        $this->deleteJson('/api/application/users/'.$other->id.'/notifications/'.$notice->id)->assertForbidden();
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_users' => AdminAcl::NONE]);
        $this->app['auth']->forgetGuards();
        $this->getJson($url)->assertForbidden();
    }

    public function test_unsafe_links_invalid_content_and_system_content_edits_are_rejected(): void
    {
        $user = User::factory()->create();
        $url = '/api/application/users/'.$user->id.'/notifications';
        foreach (['https://example.com', '//example.com', '/\\example.com', 'javascript:alert(1)'] as $link) {
            $this->postJson($url, ['title' => 'Unsafe', 'url' => $link])->assertUnprocessable();
        }
        $this->postJson($url, ['body' => 'Missing title'])->assertUnprocessable();
        $this->postJson($url, ['title' => 'Invalid status', 'status' => 'other'])->assertUnprocessable();
        $notice = app(NotificationService::class)->create($user, ['title' => 'System']);
        $notice->update(['data' => ['source' => 'reviactyl', 'event' => 'installed', 'server' => 'Example', 'url' => '/']]);
        $this->patchJson($url.'/'.$notice->id, ['title' => 'Rewritten'])->assertUnprocessable();
        $this->patchJson($url.'/'.$notice->id, ['read' => true])->assertOk();
    }
}
