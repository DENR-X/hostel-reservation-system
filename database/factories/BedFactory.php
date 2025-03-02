<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bed>
 */
class BedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomNumber(1, 50),
            'price' => $this->faker->randomFloat(2, 50, 5000),
            'status' => 'available',
            'room_id' => Room::inRandomOrder()->first()->id
        ];
    }
}
