<?php

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeServicesToUseAMoreUniqueIdentifier extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['file']);

            $table->string('author')->change();
            $table->char('uuid', 36)->after('id');
            $table->dropColumn('folder');
            $table->dropColumn('startup');
            $table->dropColumn('index_file');
        });

        DB::table('services')->get(['id', 'author', 'uuid'])->each(function ($service) {
            DB::table('services')->where('id', $service->id)->update([
                'author' => ($service->author === 'ptrdctyl-v040-11e6-8b77-86f30ca893d3') ? 'support@pterodactyl.io' : 'unknown@unknown-author.com',
                'uuid' => Uuid::uuid4()->toString(),
            ]);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->dropUnique('services_uuid_unique');
            });
        } catch (\Throwable) {
            // Ignore when missing due to partial rollback state.
        }

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'uuid')) {
                $table->dropColumn('uuid');
            }

            if (!Schema::hasColumn('services', 'folder')) {
                $table->string('folder')->nullable();
            }

            if (!Schema::hasColumn('services', 'startup')) {
                $table->text('startup')->nullable();
            }

            if (!Schema::hasColumn('services', 'index_file')) {
                if (DB::getDriverName() === 'sqlite') {
                    $table->text('index_file')->nullable();
                } else {
                    $table->text('index_file');
                }
            }

            $table->string('author', 36)->change();

            $table->unique('name');
            $table->unique('folder', 'services_file_unique');
        });
    }
}
