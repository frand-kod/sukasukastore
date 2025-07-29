<?php

namespace Database\Seeders;

use App\Models\Shoe;
use Database\Factories\ShoesFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //panggil shoe
        Shoe::factory()->count(5000)->create();
    }
}
