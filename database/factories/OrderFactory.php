<?php

namespace Database\Factories;

use App\Models\Masjid;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'SO-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('###'),
            'masjid_id' => Masjid::factory(),
            'technician_id' => null,
            'status' => Order::STATUS_PESANAN_BARU,
            'service_date' => now()->addDay()->toDateString(),
            'service_time' => 'Pagi (08:30 - 11:30 WIB)',
            'ac_count' => fake()->numberBetween(1, 5),
            'base_amount' => 150000,
            'additional_amount' => 0,
            'dkm_complaint' => 'Cuci Rutin',
        ];
    }
}
