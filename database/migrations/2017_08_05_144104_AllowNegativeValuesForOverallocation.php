<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNegativeValuesForOverallocation extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->integer('disk_overallocate')->default(0)->nullable(false)->change();
            $table->integer('memory_overallocate')->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->integer('disk_overallocate')->default(0)->nullable(false)->change();
                $table->integer('memory_overallocate')->default(0)->nullable(false)->change();

                return;
            }

            $table->mediumInteger('disk_overallocate', false, true)->nullable()->change();
            $table->mediumInteger('memory_overallocate', false, true)->nullable()->change();
        });
    }
}
