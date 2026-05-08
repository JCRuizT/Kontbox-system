<?php

namespace Database\Factories;

use App\Models\User;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100000, 5000000);
        $tax = $subtotal * 0.19;
        $total = $subtotal + $tax;

        return [
            'quote_number' => 'COT-' . fake()->unique()->numerify('######'),
            'prospect_id' => Prospect::factory(),
            'plan_id' => null,
            'created_by' => User::factory(),
            'status' => 'draft',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'valid_until' => now()->addDays(15),
            'version' => 1,
            'parent_id' => null,
            'rejection_reason' => null,
        ];
    }
}
