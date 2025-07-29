<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PromoCode;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        # generate seeder based by factories
   Category::factory(15)->create();
    dump('Categories seeded');
    
    Brand::factory(15)->create();
    dump('Brands seeded');
    
    PromoCode::factory(20)->create();
    dump('Promo codes seeded');



    }
}
