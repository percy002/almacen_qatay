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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image_path');
        });

        DB::table('product_variants')
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->chunkById(100, function ($variants): void {
                foreach ($variants as $variant) {
                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update([
                            'image_paths' => json_encode([$variant->image_path]),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
