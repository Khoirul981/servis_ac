<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number',
    'masjid_id',
    'technician_id',
    'status',
    'service_date',
    'service_time',
    'ac_count',
    'base_amount',
    'additional_amount',
    'additional_notes',
    'payment_method',
    'payment_proof',
    'paid_at',
    'dkm_complaint',
])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_PESANAN_BARU = 'pesanan_baru';
    public const STATUS_DIJADWALKAN = 'dijadwalkan';
    public const STATUS_DIKERJAKAN = 'dikerjakan';
    public const STATUS_PEKERJAAN_SELESAI = 'pekerjaan_selesai';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_MENUNGGU_BAYAR = 'menunggu_bayar';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL = 'batal';

    /**
     * Get the masjid for this order.
     *
     * @return BelongsTo<Masjid, $this>
     */
    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class);
    }

    /**
     * Get the assigned technician.
     *
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the photos attached to this order.
     *
     * @return HasMany<OrderPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(OrderPhoto::class);
    }

    /**
     * Get the spare parts / additional items attached to this order.
     *
     * @return HasMany<OrderSparepart, $this>
     */
    public function spareparts(): HasMany
    {
        return $this->hasMany(OrderSparepart::class);
    }

    /**
     * Get the multi-item AC service categories attached to this order.
     *
     * @return HasMany<OrderServiceItem, $this>
     */
    public function serviceItems(): HasMany
    {
        return $this->hasMany(OrderServiceItem::class);
    }

    /**
     * Hitung ulang total biaya tambahan dan ringkasan catatan dari seluruh suku cadang.
     */
    public function recalculateSpareparts(): void
    {
        $this->load('spareparts');
        $total = (float) $this->spareparts->sum('subtotal');
        $notes = $this->spareparts->map(function ($item) {
            $qtyText = $item->quantity > 1 ? "{$item->quantity}x " : '';
            return "{$item->item_name} ({$qtyText}Rp " . number_format($item->price, 0, ',', '.') . ")";
        })->implode(', ');

        $this->update([
            'additional_amount' => $total,
            'additional_notes' => $notes ?: null,
        ]);
    }

    /**
     * Cek apakah pesanan telah lunas / diverifikasi dibayar oleh admin.
     */
    public function isPaid(): bool
    {
        return ! is_null($this->paid_at) || $this->status === self::STATUS_SELESAI;
    }

    /**
     * Label status pembayaran (Belum Dibayar vs Sudah Dibayar).
     */
    public function paymentStatusLabel(): string
    {
        return $this->isPaid() ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    /**
     * Label metode pembayaran dalam Bahasa Indonesia.
     */
    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'cash' => 'Tunai (Cash)',
            default => $this->payment_method ? ucfirst($this->payment_method) : '-',
        };
    }

    /**
     * URL foto bukti pembayaran/transfer jika ada.
     */
    public function paymentProofUrl(): ?string
    {
        if (! $this->payment_proof) {
            return null;
        }

        return str_starts_with($this->payment_proof, 'http')
            ? $this->payment_proof
            : asset('storage/' . $this->payment_proof);
    }

    /**
     * Calculate grand total (base + additional).
     */
    public function grandTotal(): float
    {
        return (float) ($this->base_amount + $this->additional_amount);
    }

    /**
     * Cek apakah pekerjaan fisik servis di lokasi telah dikonfirmasi selesai.
     */
    public function isWorkCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_PEKERJAAN_SELESAI,
            self::STATUS_MENUNGGU_VERIFIKASI,
            self::STATUS_MENUNGGU_BAYAR,
            self::STATUS_SELESAI,
        ]);
    }

    /**
     * Get human-friendly Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PESANAN_BARU => 'Pesanan Masuk',
            self::STATUS_DIJADWALKAN => 'Dijadwalkan',
            self::STATUS_DIKERJAKAN => 'Sedang Dikerjakan',
            self::STATUS_PEKERJAAN_SELESAI => 'Pekerjaan Selesai (Nunggu Bukti)',
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi Admin',
            self::STATUS_MENUNGGU_BAYAR => 'Menunggu Pembayaran',
            self::STATUS_SELESAI => 'Selesai & Lunas',
            self::STATUS_BATAL => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Attributes cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'paid_at' => 'datetime',
            'base_amount' => 'decimal:2',
            'additional_amount' => 'decimal:2',
            'ac_count' => 'integer',
        ];
    }
}
