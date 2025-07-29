<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Shoe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shoes>
 */
class ShoeFactory extends Factory
{
    protected $model = Shoe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        # deklarasi variabel faker
        $name = $this->faker->unique()->words(3,true);
        $category = Category::inRandomOrder()->first();
        $brand = Brand::inRandomOrder()->first();
        return [
            // seed ke db
                        'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'shoes', true),
            'about' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100_000, 3_000_000),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_popular' => $this->faker->boolean(15), // 15% chance true
            'category_id' => $category ? $category->id : null,
            'brand_id' => $brand ? $brand->id : null,
        ];
    }
}
