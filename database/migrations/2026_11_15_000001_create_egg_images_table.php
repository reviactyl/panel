<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egg_images', function (Blueprint $table) {
            $table->unsignedInteger('egg_id')->primary();
            $table->foreign('egg_id')->references('id')->on('eggs')->cascadeOnDelete();
            $table->longText('data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_images');
    }
};
