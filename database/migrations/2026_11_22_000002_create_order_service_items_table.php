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
        Schema::create('order_service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('ac_unit_id')->nullable()->constrained('ac_units')->nullOnDelete();
            $table->string('room_name')->nullable();
            $table->string('ac_type')->default('1 PK');
            $table->string('service_category')->default('Cuci Rutin');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_per_unit', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_service_items');
    }
};
