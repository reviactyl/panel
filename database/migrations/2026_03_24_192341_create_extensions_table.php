<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('name');
            $table->string('version');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('website')->nullable();

            $table->string('api_version')->nullable();
            $table->string('target_version')->nullable();

            $table->boolean('enabled')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('extension_updated_at')->nullable();

            $table->json('manifest')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extensions');
    }
};
