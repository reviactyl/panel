<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupDatabasesDatabase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->dropForeign(['db_server']);

            $table->renameColumn('db_server', 'database_host_id');

            $table->foreign('database_host_id')->references('id')->on('database_hosts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('databases', function (Blueprint $table) {
                $table->dropForeign('databases_database_host_id_foreign');
            });
        } catch (\Throwable) {
            try {
                Schema::table('databases', function (Blueprint $table) {
                    $table->dropForeign('databases_db_server_foreign');
                });
            } catch (\Throwable) {
                // Foreign key already removed or named differently.
            }
        }

        Schema::table('databases', function (Blueprint $table) {
            $table->renameColumn('database_host_id', 'db_server');

            $table->foreign('db_server')->references('id')->on('database_hosts');
        });
    }
}
