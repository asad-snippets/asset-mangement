<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email_address');
            $table->string('department_name');
            $table->string('job_title');
            $table->string('employee_photo')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'email_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
