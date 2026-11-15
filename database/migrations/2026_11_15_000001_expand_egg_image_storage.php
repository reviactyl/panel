<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->longText('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('eggs')->whereRaw('LENGTH(image) > 255')->exists()) {
            throw new RuntimeException('Remove uploaded egg images before rolling back image storage.');
        }

        Schema::table('eggs', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
        });
    }
};
