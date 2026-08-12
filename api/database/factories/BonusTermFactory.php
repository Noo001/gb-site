<?php

namespace Database\Factories;

use App\Models\BonusTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusTermFactory extends Factory
{
    protected $model = BonusTerm::class;

    public function definition(): array
    {
        return [
            'version' => $this->faker->unique()->numberBetween(1, 9999),
            'content' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
