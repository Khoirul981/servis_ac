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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('masjid_id')->constrained('masjids')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pesanan_baru'); // pesanan_baru, dijadwalkan, dikerjakan, pekerjaan_selesai, menunggu_verifikasi, menunggu_bayar, selesai, batal
            $table->date('service_date')->nullable();
            $table->string('service_time')->nullable();
            $table->integer('ac_count')->default(1);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('additional_amount', 12, 2)->default(0);
            $table->text('additional_notes')->nullable();
            $table->string('payment_method')->nullable(); // transfer, qris, cash
            $table->string('payment_proof')->nullable(); // path foto bukti transfer/pembayaran dari teknisi
            $table->timestamp('paid_at')->nullable();
            $table->text('dkm_complaint')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
