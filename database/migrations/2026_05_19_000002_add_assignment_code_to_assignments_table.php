<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('assignment_code', 20)->nullable()->unique()->after('id');
        });

        DB::table('assignments')
            ->orderBy('id')
            ->chunkById(100, function ($assignments) {
                foreach ($assignments as $assignment) {
                    $code = sprintf('ASSIGN-%03d', $assignment->id);
                    DB::table('assignments')
                        ->where('id', $assignment->id)
                        ->update(['assignment_code' => $code]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropUnique(['assignment_code']);
            $table->dropColumn('assignment_code');
        });
    }
};
