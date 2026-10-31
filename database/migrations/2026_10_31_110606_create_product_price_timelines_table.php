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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('batch_no')->unique();
            $table->decimal('purchase_rate', 15, 2)->default(0);
            $table->decimal('selling_rate', 15, 2)->default(0);
            $table->decimal('initial_qty', 15, 2)->default(0);
            $table->decimal('remaining_qty', 15, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
