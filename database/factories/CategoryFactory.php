<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model=Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            // kirim ke database
            'name'=>ucfirst($name),
            'slug'=>Str::slug($name),
            'icon'=>$this->faker->randomElement(['fa-shoe-prints', 'fa-running', 'fa-boot'])
        ];
    }
}
