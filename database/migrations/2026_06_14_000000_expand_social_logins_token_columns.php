<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_logins', function (Blueprint $table) {
            $table->text('provider_token')->nullable()->change();
            $table->text('provider_refresh_token')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('social_logins', function (Blueprint $table) {
            $table->string('provider_token')->nullable()->change();
            $table->string('provider_refresh_token')->nullable()->change();
        });
    }
};
