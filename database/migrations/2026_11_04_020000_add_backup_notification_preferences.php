<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->boolean('is_scheduled')->default(false);
        });
        Schema::create('backup_notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedInteger('server_id');
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->boolean('manual')->default(false);
            $table->boolean('scheduled')->default(false);
            $table->boolean('email')->default(false);
            $table->primary(['user_id', 'server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_notification_preferences');
        Schema::table('backups', fn (Blueprint $table) => $table->dropColumn('is_scheduled'));
    }
};
