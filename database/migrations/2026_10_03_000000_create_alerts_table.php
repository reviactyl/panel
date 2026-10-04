<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const WELCOME_MESSAGE = '**Welcome to Reviactyl!** You can modify Theme Look & Feel using [Designify](/admin/designify) at the administration area.';

    private const TYPES = ['info', 'announcement', 'success', 'warning', 'danger'];

    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->boolean('enabled')->default(true);
            $table->string('type', 32)->default('info');
            $table->string('title')->nullable();
            $table->text('message');
            $table->string('color', 9)->nullable();
            $table->json('buttons')->nullable();
            $table->boolean('dismissible')->default(true);
            $table->boolean('dismiss_on_action')->default(false);
            $table->unsignedInteger('redisplay_after_days')->nullable();
            $table->json('placements');
            $table->string('audience', 32)->default('everyone');
            $table->json('targeting')->nullable();
            $table->integer('priority')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('dismissals_reset_at')->nullable();
            $table->timestamps();
        });

        Schema::create('alert_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('alerts')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->dateTime('dismissed_at')->nullable();
            $table->dateTime('clicked_at')->nullable();
            $table->timestamps();

            $table->unique(['alert_id', 'user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $this->importLegacyAlerts();
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_interactions');
        Schema::dropIfExists('alerts');
    }

    private function importLegacyAlerts(): void
    {
        $settings = DB::table('settings')
            ->whereIn('key', [
                'settings::designify:alerts',
                'settings::designify:alertType',
                'settings::designify:alertMessage',
            ])
            ->pluck('value', 'key');

        $legacy = json_decode((string) $settings->get('settings::designify:alerts', ''), true);

        if (! is_array($legacy) || empty($legacy)) {
            $message = $settings->get('settings::designify:alertMessage');

            if ($message === null && ! $settings->has('settings::designify:alertType')) {
                $this->insert('Welcome', 'info', self::WELCOME_MESSAGE, 0, [
                    'audience' => 'admins',
                    'dismissible' => true,
                ]);

                return;
            }

            $legacy = [[
                'type' => $settings->get('settings::designify:alertType', 'info'),
                'message' => $message ?? self::WELCOME_MESSAGE,
            ]];
        }

        $legacy = array_values(array_filter($legacy, 'is_array'));
        $total = count($legacy);

        foreach ($legacy as $index => $alert) {
            $type = (string) ($alert['type'] ?? 'info');
            $message = trim((string) ($alert['message'] ?? ''));

            if ($message === '') {
                continue;
            }

            $name = Str::limit(trim(str_replace('*', '', preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $message))), 60);

            $this->insert($name !== '' ? $name : 'Alert '.($index + 1), $type, $message, $total - $index, [
                'enabled' => $type !== 'disabled',
            ]);
        }
    }

    private function insert(string $name, string $type, string $message, int $priority, array $overrides = []): void
    {
        $now = Carbon::now();

        DB::table('alerts')->insert(array_merge([
            'uuid' => Str::uuid()->toString(),
            'name' => $name,
            'enabled' => true,
            'type' => in_array($type, self::TYPES, true) ? $type : 'info',
            'message' => $message,
            'dismissible' => false,
            'dismiss_on_action' => false,
            'placements' => json_encode(['dashboard', 'server']),
            'audience' => 'everyone',
            'priority' => $priority,
            'created_at' => $now,
            'updated_at' => $now,
        ], $overrides));
    }
};
