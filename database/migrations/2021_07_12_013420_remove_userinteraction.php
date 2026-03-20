<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class RemoveUserInteraction extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove User Interaction from startup config
        switch (DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                DB::table('eggs')->update([
                    'config_startup' => DB::raw('JSON_REMOVE(config_startup, \'$.userInteraction\')'),
                ]);
                break;
            case 'pgsql':
                DB::table('eggs')->update([
                    'config_startup' => DB::raw('config_startup::jsonb - \'userInteraction\''),
                ]);
                break;
            case 'sqlite':
                DB::table('eggs')->select(['id', 'config_startup'])->orderBy('id')->cursor()->each(function ($egg) {
                    $startup = json_decode($egg->config_startup ?? '{}', true);
                    if (!is_array($startup)) {
                        $startup = [];
                    }

                    unset($startup['userInteraction']);

                    DB::table('eggs')->where('id', $egg->id)->update([
                        'config_startup' => json_encode($startup),
                    ]);
                });
                break;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add blank User Interaction array back to startup config
        switch (DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                DB::table('eggs')->update([
                    'config_startup' => DB::raw('JSON_SET(config_startup, \'$.userInteraction\', JSON_ARRAY())'),
                ]);
                break;
            case 'pgsql':
                DB::table('eggs')->update([
                    'config_startup' => DB::raw('jsonb_set(config_startup::jsonb, \'$.userInteraction\', jsonb_build_array())'),
                ]);
                break;
            case 'sqlite':
                DB::table('eggs')->select(['id', 'config_startup'])->orderBy('id')->cursor()->each(function ($egg) {
                    $startup = json_decode($egg->config_startup ?? '{}', true);
                    if (!is_array($startup)) {
                        $startup = [];
                    }

                    if (!array_key_exists('userInteraction', $startup)) {
                        $startup['userInteraction'] = [];
                    }

                    DB::table('eggs')->where('id', $egg->id)->update([
                        'config_startup' => json_encode($startup),
                    ]);
                });
                break;
        }
    }
}
