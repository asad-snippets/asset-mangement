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
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('asset_name')
                ->constrained('categories')
                ->nullOnDelete();
        });

        DB::table('assets')
            ->orderBy('id')
            ->chunkById(100, function ($assets) {
                foreach ($assets as $asset) {
                    if ($asset->category === null) {
                        continue;
                    }

                    $categoryId = DB::table('categories')
                        ->where('name', $asset->category)
                        ->value('id');

                    if ($categoryId) {
                        DB::table('assets')
                            ->where('id', $asset->id)
                            ->update(['category_id' => $categoryId]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
