<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        try {
            DB::table('servers')
                ->where('image', 'like', '%pterodactyl%')
                ->orderBy('id')
                ->chunkById(100, function ($servers) {
                    foreach ($servers as $server) {
                        $newImage = str_replace(
                            'ghcr.io/pterodactyl/',
                            'ghcr.io/reviactyl/',
                            $server->image
                        );

                        if ($newImage !== $server->image) {
                            DB::table('servers')
                                ->where('id', $server->id)
                                ->update(['image' => $newImage]);
                        }
                    }
                });

            DB::table('eggs')
                ->where('docker_images', 'like', '%pterodactyl%')
                ->orderBy('id')
                ->chunkById(100, function ($eggs) {
                    foreach ($eggs as $egg) {
                        $images = json_decode($egg->docker_images, true);

                        if (!is_array($images)) {
                            continue; // skip invalid JSON
                        }

                        $updated = false;

                        foreach ($images as $key => $value) {
                            if (is_string($value) && str_contains($value, 'pterodactyl')) {
                                $images[$key] = str_replace(
                                    'pterodactyl',
                                    'reviactyl',
                                    $value
                                );
                                $updated = true;
                            }
                        }

                        if ($updated) {
                            DB::table('eggs')
                                ->where('id', $egg->id)
                                ->update([
                                    'docker_images' => json_encode($images),
                                ]);
                        }
                    }
                });

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
