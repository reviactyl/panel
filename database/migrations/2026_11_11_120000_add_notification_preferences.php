<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('category', 64);
            $table->boolean('database')->default(true);
            $table->boolean('email')->default(false);
            $table->primary(['user_id', 'category']);
        });

        $preferences = DB::table('backup_notification_preferences')
            ->select('user_id')
            ->selectRaw('MAX(CASE WHEN manual OR scheduled THEN 1 ELSE 0 END) AS database_enabled')
            ->selectRaw('MAX(CASE WHEN (manual OR scheduled) AND email THEN 1 ELSE 0 END) AS email_enabled')
            ->groupBy('user_id')
            ->orderBy('user_id');

        // Enable an account channel if any active server subscription enabled it.
        foreach ($preferences->lazy() as $preference) {
            foreach (['backups', 'backup-failures'] as $category) {
                DB::table('notification_preferences')->insert([
                    'user_id' => $preference->user_id,
                    'category' => $category,
                    'database' => (bool) $preference->database_enabled,
                    'email' => (bool) $preference->email_enabled,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
