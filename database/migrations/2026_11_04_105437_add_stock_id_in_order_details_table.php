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
        Schema::table('order_details', function (Blueprint $table) {
            if(!Schema::hasColumn('order_details', 'stock_id')){
                $table->foreignId('stock_id')->nullable()->after('product_id')->constrained('stocks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            if(Schema::hasColumn('order_details', 'stock_id')){
                $table->dropForeign(['stock_id']);
                $table->dropColumn('stock_id');
            }
        });
    }
};
