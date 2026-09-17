<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the subuser preview sessions table and its relationships.
     */
    public function up(): void
    {
        Schema::create('subuser_preview_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('owner_id')->unique();
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('subuser_id');
            $table->string('token_hash', 64);
            $table->json('state')->nullable();
            $table->timestamp('last_seen_at');
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->foreign('subuser_id')->references('id')->on('subusers')->cascadeOnDelete();
        });
    }

    /**
     * Removes the subuser preview sessions table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('subuser_preview_sessions');
    }
};
