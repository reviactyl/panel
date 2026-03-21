<?php

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeToABetterUniqueServiceConfiguration extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_options', function (Blueprint $table) {
            $table->char('uuid', 36)->after('id');
            $table->string('author')->after('service_id');
            $table->dropColumn('tag');
        });

        DB::transaction(function () {
            DB::table('service_options')->select([
                'service_options.id',
                'service_options.uuid',
                'services.author AS service_author',
            ])->join('services', 'services.id', '=', 'service_options.service_id')->get()->each(function ($option) {
                DB::table('service_options')->where('id', $option->id)->update([
                    'author' => $option->service_author,
                    'uuid' => Uuid::uuid4()->toString(),
                ]);
            });
        });

        Schema::table('service_options', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('service_options', function (Blueprint $table) {
                $table->dropUnique('service_options_uuid_unique');
            });
        } catch (\Throwable) {
            // Ignore if the unique index does not exist.
        }

        Schema::table('service_options', function (Blueprint $table) {
            if (Schema::hasColumn('service_options', 'uuid')) {
                $table->dropColumn('uuid');
            }

            if (Schema::hasColumn('service_options', 'author')) {
                $table->dropColumn('author');
            }

            if (DB::getDriverName() === 'sqlite') {
                $table->string('tag')->nullable();

                return;
            }

            $table->string('tag');
        });

        DB::transaction(function () {
            DB::table('service_options')->select(['id', 'tag'])->get()->each(function ($option) {
                DB::table('service_options')->where('id', $option->id)->update([
                    'tag' => str_random(10),
                ]);
            });
        });
    }
}
