<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationPreferenceService
{
    private const DEFINITIONS = [
        'server-access' => [
            'events' => ['added', 'removed'],
            'database' => true,
            'email' => true,
            'emailAvailable' => true,
        ],
        'server-setup' => [
            'events' => ['installed', 'reinstalled'],
            'database' => true,
            'email' => true,
            'emailAvailable' => true,
        ],
        'server-failures' => [
            'events' => ['install-failed', 'reinstall-failed'],
            'database' => true,
            'email' => false,
            'emailAvailable' => false,
        ],
        'backups' => [
            'events' => ['backup-completed'],
            'database' => true,
            'email' => false,
            'emailAvailable' => true,
        ],
        'backup-failures' => [
            'events' => ['backup-failed'],
            'database' => true,
            'email' => false,
            'emailAvailable' => true,
        ],
        'extension-alerts' => [
            'events' => ['extension-failed'],
            'database' => true,
            'email' => false,
            'emailAvailable' => false,
        ],
    ];

    public function definitions(User $user): array
    {
        $saved = DB::table('notification_preferences')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('category');

        return collect(self::DEFINITIONS)->map(function (array $definition, string $category) use ($saved) {
            $row = $saved->get($category);

            return [
                'category' => $category,
                'database' => (bool) ($row->database ?? $definition['database']),
                'email' => (bool) ($row->email ?? $definition['email']),
                'emailAvailable' => $definition['emailAvailable'],
            ];
        })->values()->all();
    }

    public function update(User $user, string $category, bool $database, bool $email): array
    {
        abort_unless(isset(self::DEFINITIONS[$category]), 422, 'Unknown notification category.');

        $email = $email && self::DEFINITIONS[$category]['emailAvailable'];

        DB::table('notification_preferences')->updateOrInsert(
            ['user_id' => $user->id, 'category' => $category],
            ['database' => $database, 'email' => $email],
        );

        return ['category' => $category, 'database' => $database, 'email' => $email];
    }

    public function isEnabled(User|int $user, string $event, string $channel = 'database'): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        $category = $this->categoryFor($event);
        $row = $this->preferenceRow($userId, $category);

        return (bool) ($row->{$channel} ?? self::DEFINITIONS[$category][$channel]);
    }

    public function categoryFor(string $event): string
    {
        foreach (self::DEFINITIONS as $category => $definition) {
            if (in_array($event, $definition['events'], true)) {
                return $category;
            }
        }

        return 'server-access';
    }

    private function preferenceRow(int $userId, string $category): ?object
    {
        $row = DB::table('notification_preferences')
            ->where('user_id', $userId)
            ->where('category', $category)
            ->first();

        return $row;
    }

    public function categories(): array
    {
        return array_keys(self::DEFINITIONS);
    }
}
