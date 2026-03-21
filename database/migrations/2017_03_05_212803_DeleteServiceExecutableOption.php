<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteServiceExecutableOption extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('file', 'folder');
            $table->dropColumn('executable');
            $table->text('description')->nullable()->change();
            $table->text('startup')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->string('executable')->nullable()->after('folder');
            } else {
                $table->string('executable')->after('folder');
            }

            $table->renameColumn('folder', 'file');

            if (DB::getDriverName() !== 'sqlite') {
                $table->text('description')->nullable(false)->change();
                $table->text('startup')->nullable(false)->change();
            }
        });
    }
}
