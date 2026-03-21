<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFileDenylistToJson extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('eggs', 'file_denylist')) {
            Schema::table('eggs', function (Blueprint $table) {
                $table->dropColumn('file_denylist');
            });
        }

        if (!Schema::hasColumn('eggs', 'file_denylist')) {
            Schema::table('eggs', function (Blueprint $table) {
                $table->json('file_denylist')->nullable()->after('docker_images');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('eggs', 'file_denylist')) {
            Schema::table('eggs', function (Blueprint $table) {
                $table->dropColumn('file_denylist');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            if (!Schema::hasColumn('eggs', 'file_denylist')) {
                Schema::table('eggs', function (Blueprint $table) {
                    $table->text('file_denylist')->nullable()->after('docker_images');
                });
            }

            return;
        }

        if (!Schema::hasColumn('eggs', 'file_denylist')) {
            Schema::table('eggs', function (Blueprint $table) {
                $table->text('file_denylist')->after('docker_images');
            });
        }
    }
}
