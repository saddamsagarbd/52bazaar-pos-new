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
        Schema::table('orders', function (Blueprint $table) {
            if(!Schema::hasColumn('orders', 'outlet_id')){
                $table->foreignId('outlet_id')->nullable()->after('customer_id')->constrained('outlets')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if(Schema::hasColumn('orders', 'outlet_id')){
                $table->dropForeign(['outlet_id']);
                $table->dropColumn('outlet_id');
            }
        });
    }
};
