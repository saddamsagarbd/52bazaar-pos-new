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
        Schema::create('product_price_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->nullOnDelete();
            $table->decimal('purchase_rate', 15, 2)->default(0);
            $table->decimal('selling_rate', 15, 2)->default(0);
            $table->date('effective_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_timelines');
    }
};
