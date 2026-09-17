<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('database_hosts')
            ->select(['id', 'password'])
            ->orderBy('id')
            ->each(function (object $host): void {
                if ($host->password === null || $host->password === '') {
                    return;
                }

                try {
                    Crypt::decrypt($host->password);

                    return;
                } catch (DecryptException) {

                }

                DB::table('database_hosts')
                    ->where('id', $host->id)
                    ->update(['password' => Crypt::encrypt($host->password)]);
            });
    }

    public function down(): void
    {
        // no-op
    }
};
