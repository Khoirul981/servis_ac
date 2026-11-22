<?php

namespace App\Services;

class AcPricingService
{
    /**
     * Dapatkan seluruh pilihan kapasitas PK yang tersedia.
     *
     * @return array<string, string>
     */
    public static function getPkOptions(): array
    {
        return config('ac_pricing.pk_options', [
            '0.5 PK' => '0.5 PK (1/2 PK)',
            '0.75 PK' => '0.75 PK (3/4 PK)',
            '1 PK' => '1 PK',
            '1.5 PK' => '1.5 PK',
            '2 PK' => '2 PK',
            '2.5 PK' => '2.5 PK',
            '3 PK' => '3 PK',
            '5 PK' => '5 PK (Standing / Cassette)',
        ]);
    }

    /**
     * Dapatkan seluruh kategori keluhan / jenis servis.
     *
     * @return array<string, array{name: string, desc: string, rates: array<string, int>, default_rate: int}>
     */
    public static function getCategories(): array
    {
        return config('ac_pricing.categories', []);
    }

    /**
     * Standarisasi string PK agar cocok dengan matriks tarif.
     */
    public static function normalizePk(?string $pk): string
    {
        if (empty($pk)) {
            return '1 PK';
        }

        $clean = strtoupper(trim($pk));

        if (str_contains($clean, '0.5') || str_contains($clean, '1/2') || str_contains($clean, 'SETENGAH')) {
            return '0.5 PK';
        }
        if (str_contains($clean, '0.75') || str_contains($clean, '3/4')) {
            return '0.75 PK';
        }
        if (str_contains($clean, '1.5') || str_contains($clean, '1 1/2')) {
            return '1.5 PK';
        }
        if (str_contains($clean, '2.5') || str_contains($clean, '2 1/2')) {
            return '2.5 PK';
        }
        if (str_contains($clean, '2') && !str_contains($clean, '2.5')) {
            return '2 PK';
        }
        if (str_contains($clean, '3')) {
            return '3 PK';
        }
        if (str_contains($clean, '5')) {
            return '5 PK';
        }

        return '1 PK';
    }

    /**
     * Hitung tarif per unit berdasarkan Kategori Servis dan Kapasitas PK.
     */
    public static function getRate(string $category, ?string $pk = null): float
    {
        $categories = self::getCategories();
        $normalizedPk = self::normalizePk($pk);

        if (! isset($categories[$category])) {
            // Coba cari kecocokan sebagian
            foreach ($categories as $catKey => $catData) {
                if (str_contains(strtolower($category), strtolower($catKey)) || str_contains(strtolower($catKey), strtolower($category))) {
                    $category = $catKey;
                    break;
                }
            }
        }

        $catData = $categories[$category] ?? ($categories['Cuci Rutin'] ?? null);

        if (! $catData) {
            return 75000.0;
        }

        $rates = $catData['rates'] ?? [];

        return (float) ($rates[$normalizedPk] ?? $catData['default_rate'] ?? 75000);
    }
}
