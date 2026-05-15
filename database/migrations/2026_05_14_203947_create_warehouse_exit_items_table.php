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
        Schema::create('warehouse_exit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_exit_id')->constrained('warehouse_exits');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_exit_items');
    }
};
