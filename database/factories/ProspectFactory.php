<?php

namespace Database\Factories;

use App\Models\User;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProspectFactory extends Factory
{
    protected $model = Prospect::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'status' => 'new',
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
