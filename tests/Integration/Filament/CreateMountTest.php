<?php

namespace Tests\Integration\Filament;

use App\Events\ActivityLogged;
use App\Filament\Resources\Mounts\Pages\CreateMount;
use App\Models\ActivityLogSubject;
use App\Models\Mount;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class CreateMountTest extends IntegrationTestCase
{
    public function test_admin_can_create_mounts_with_unique_uuids(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        foreach ([false, true] as $enabled) {
            $name = $enabled ? 'Shared mount' : 'Private mount';

            Livewire::test(CreateMount::class)
                ->fillForm([
                    'name' => $name,
                    'description' => 'Mount creation regression',
                    'source' => '/tmp/mount-source',
                    'target' => '/mnt/data',
                    'read_only' => $enabled,
                    'user_mountable' => $enabled,
                ])
                ->call('create')
                ->assertHasNoFormErrors()
                ->assertRedirect();

            $mount = Mount::query()->where('name', $name)->sole();
            $this->assertTrue(Str::isUuid($mount->uuid));
            $this->assertSame('/tmp/mount-source', $mount->source);
            $this->assertSame('/mnt/data', $mount->target);
            $this->assertSame('Mount creation regression', $mount->description);
            $this->assertSame($enabled, $mount->read_only);
            $this->assertSame($enabled, $mount->user_mountable);
            $this->assertCount(1, Event::dispatched(ActivityLogged::class, fn (ActivityLogged $event) => $event->is('mount:create')
                && $event->model->subjects->contains(fn (ActivityLogSubject $subject) => $subject->subject_type === $mount->getMorphClass()
                    && $subject->subject_id === $mount->getKey())));
        }

        $this->assertDatabaseCount('mounts', 2);
        $this->assertCount(2, Mount::query()->pluck('uuid')->unique());
        $this->assertFalse((new Mount())->isFillable('uuid'));
        $this->assertFalse((new Mount())->isFillable('id'));
    }
}
