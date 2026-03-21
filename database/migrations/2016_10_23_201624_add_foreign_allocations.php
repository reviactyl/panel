<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignAllocations extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->integer('assigned_to', false, true)->nullable()->change();
            $table->integer('node', false, true)->nullable(false)->change();
            $table->foreign('assigned_to')->references('id')->on('servers');
            $table->foreign('node')->references('id')->on('nodes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('allocations', function (Blueprint $table) {
                $table->integer('assigned_to')->nullable()->change();
                $table->integer('node')->nullable(false)->change();
            });

            return;
        }

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);

            $table->dropForeign(['node']);

            $table->mediumInteger('assigned_to', false, true)->nullable()->change();
            $table->mediumInteger('node', false, true)->nullable(false)->change();
        });
    }
}
