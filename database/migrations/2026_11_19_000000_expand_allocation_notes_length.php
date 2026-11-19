<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->string('notes', 256)->nullable()->change();
        });
    }

    public function down(): void
    {
        $length = DB::getDriverName() === 'sqlite' ? 'length(notes)' : 'char_length(notes)';
        if (DB::table('allocations')->whereRaw("$length > ?", [191])->exists()) {
            throw new RuntimeException('Shorten allocation notes to 191 characters before rolling back this migration.');
        }

        Schema::table('allocations', function (Blueprint $table) {
            $table->string('notes', 191)->nullable()->change();
        });
    }
};
