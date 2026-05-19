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
        Schema::table('employees', function (Blueprint $table) {
            // No schema changes. Data normalization only.
        });

        DB::table('employees')
            ->whereNotNull('employee_photo')
            ->orderBy('id')
            ->chunkById(100, function ($employees) {
                foreach ($employees as $employee) {
                    $photo = $employee->employee_photo;
                    if (!$photo) {
                        continue;
                    }

                    if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                        continue;
                    }

                    if (str_starts_with($photo, '/storage/')) {
                        continue;
                    }

                    if (str_starts_with($photo, 'storage/')) {
                        $photo = '/' . $photo;
                    } else {
                        $photo = '/storage/' . ltrim($photo, '/');
                    }

                    DB::table('employees')
                        ->where('id', $employee->id)
                        ->update(['employee_photo' => $photo]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('employees')
            ->whereNotNull('employee_photo')
            ->orderBy('id')
            ->chunkById(100, function ($employees) {
                foreach ($employees as $employee) {
                    $photo = $employee->employee_photo;
                    if (!$photo) {
                        continue;
                    }

                    if (str_starts_with($photo, '/storage/')) {
                        $photo = ltrim(substr($photo, strlen('/storage/')), '/');
                    }

                    DB::table('employees')
                        ->where('id', $employee->id)
                        ->update(['employee_photo' => $photo]);
                }
            });
    }
};
