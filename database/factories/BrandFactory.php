<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company;
        return [
            //
            'name'=>$name,
            'slug'=>Str::slug($name),
            'icon'=> $this->faker->randomElement(['fa-nike', 'fa-puma', 'fa-adidas']),
            'logo'=> $this->faker->imageUrl(200, 100, 'brands', true, $name),
        ];
    }
}
