<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recycled_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('server_id');
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->text('original_path');
            $table->timestamp('created_at');
            $table->index(['server_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recycled_files');
    }
};
