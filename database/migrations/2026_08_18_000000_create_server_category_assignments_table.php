<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_category_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            $table->unique(['server_id', 'user_id']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('server_categories')->cascadeOnDelete();
        });

        $timestamp = now();
        DB::table('servers')
            ->join('server_categories', 'server_categories.id', '=', 'servers.category_id')
            ->whereColumn('server_categories.user_id', 'servers.owner_id')
            ->select(['servers.id as server_id', 'servers.owner_id as user_id', 'server_categories.id as category_id'])
            ->orderBy('servers.id')
            ->chunk(500, function ($servers) use ($timestamp): void {
                DB::table('server_category_assignments')->insert(
                    $servers->map(fn ($server) => [
                        'server_id' => $server->server_id,
                        'user_id' => $server->user_id,
                        'category_id' => $server->category_id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])->all()
                );
            });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('owner_id')->constrained('server_categories')->nullOnDelete();
        });

        DB::table('server_category_assignments')
            ->join('servers', 'servers.id', '=', 'server_category_assignments.server_id')
            ->whereColumn('server_category_assignments.user_id', 'servers.owner_id')
            ->select(['server_category_assignments.server_id', 'server_category_assignments.category_id'])
            ->orderBy('server_category_assignments.server_id')
            ->chunk(500, function ($assignments): void {
                foreach ($assignments as $assignment) {
                    DB::table('servers')
                        ->where('id', $assignment->server_id)
                        ->update(['category_id' => $assignment->category_id]);
                }
            });

        Schema::dropIfExists('server_category_assignments');
    }
};
