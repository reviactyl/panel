<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('archive_format', 6)->default('tar.gz');
        });

        Schema::table('backups', function (Blueprint $table) {
            $table->string('format', 6)->default('tar.gz');
        });
    }

    public function down(): void
    {
        Schema::table('backups', fn (Blueprint $table) => $table->dropColumn('format'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('archive_format'));
    }
};
