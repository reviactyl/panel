<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeServiceVariablesValidationRules extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_variables', function (Blueprint $table) {
            $table->renameColumn('regex', 'rules');
        });

        DB::transaction(function () {
            foreach (DB::table('service_variables')->get() as $variable) {
                DB::table('service_variables')->where('id', $variable->id)->update([
                    'rules' => $variable->required ? 'required|regex:' . $variable->rules : 'regex:' . $variable->rules,
                ]);
            }
        });

        Schema::table('service_variables', function (Blueprint $table) {
            $table->dropColumn('required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('service_variables', 'rules')) {
            Schema::table('service_variables', function (Blueprint $table) {
                $table->renameColumn('rules', 'regex');
            });
        }

        if (!Schema::hasColumn('service_variables', 'required')) {
            Schema::table('service_variables', function (Blueprint $table) {
                $table->boolean('required')->default(true);
            });
        }

        DB::transaction(function () {
            if (!Schema::hasColumn('service_variables', 'regex')) {
                return;
            }

            foreach (DB::table('service_variables')->get() as $variable) {
                DB::table('service_variables')->where('id', $variable->id)->update([
                    'regex' => str_replace(['required|regex:', 'regex:'], '', $variable->regex),
                ]);
            }
        });
    }
}
