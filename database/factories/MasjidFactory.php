<?php

namespace Database\Factories;

use App\Models\Masjid;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Masjid>
 */
class MasjidFactory extends Factory
{
    protected $model = Masjid::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Masjid ' . fake()->city(),
            'address' => fake()->address(),
            'pic_name' => fake()->name(),
            'pic_phone' => fake()->numerify('08##########'),
            'last_service_date' => fake()->optional()->date(),
        ];
    }
}
