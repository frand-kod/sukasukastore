<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ShoeSize;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoeSize>
 */
class ShoeSizeFactory extends Factory
{
    protected $model = ShoeSize::class;

    public function definition(): array
    {
        // Biasanya ukuran sepatu standar, misal 36 - 45
        $sizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];

        return [
            'size' => $this->faker->randomElement($sizes),
            // 'shoe_id' akan di-assign otomatis lewat relasi di seeder
        ];
    }
}