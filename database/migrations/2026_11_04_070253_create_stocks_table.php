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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('booking_qty', 15, 2)->default(0);
            $table->decimal('threshold_qty', 15, 2)->default(5);
            $table->decimal('purchase_rate', 15, 2)->default(0);
            $table->decimal('selling_rate', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('remain_qty', 15, 2)->default(0);
            $table->decimal('purchase_rate', 15, 2)->default(0);
            $table->decimal('selling_rate', 15, 2)->default(0);
            $table->date('expire_date')->nullable();
            $table->date('purchase_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('stocks');
    }
};
