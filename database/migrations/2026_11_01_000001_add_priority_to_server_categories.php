<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_categories', function (Blueprint $table) {
            $table->integer('priority')->default(0);
        });
        DB::table('server_categories')->update(['priority' => DB::raw('position')]);

    }

    public function down(): void
    {
        Schema::table('server_categories', fn (Blueprint $table) => $table->dropColumn('priority'));
    }
};
