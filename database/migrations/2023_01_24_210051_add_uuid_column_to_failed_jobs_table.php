<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->string('uuid')->after('id')->nullable()->unique();
        });

        DB::table('failed_jobs')->whereNull('uuid')->cursor()->each(function ($job) {
            DB::table('failed_jobs')
                ->where('id', $job->id)
                ->update(['uuid' => (string) Illuminate\Support\Str::uuid()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('failed_jobs') || !Schema::hasColumn('failed_jobs', 'uuid')) {
            return;
        }

        try {
            Schema::table('failed_jobs', function (Blueprint $table) {
                $table->dropUnique('failed_jobs_uuid_unique');
            });
        } catch (\Throwable) {

        }

        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
