<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackupLimitToServers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Same as in the backups migration, we need to handle that plugin messing with the data structure
        // here. If the column already exists we'll keep it and normalize its definition.
        if (Schema::hasColumn('servers', 'backup_limit')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->unsignedInteger('backup_limit')->default(0)->change();
            });
        } else {
            Schema::table('servers', function (Blueprint $table) {
                $table->unsignedInteger('backup_limit')->default(0)->after('database_limit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('backup_limit');
        });
    }
}
