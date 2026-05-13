<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('password');
            $table->string('industry')->nullable()->after('company_name');
            $table->string('company_size')->nullable()->after('industry');
            $table->unsignedInteger('company_size_employees')->nullable()->after('company_size');
            $table->string('location')->nullable()->after('company_size_employees');
            $table->string('contact_number')->nullable()->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'industry',
                'company_size',
                'company_size_employees',
                'location',
                'contact_number',
            ]);
        });
    }
};
