<?php

namespace Database\Seeders;

use App\Models\Product;
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
        User::factory(10)->create();
        Product::factory(500)->create();
        /*  User::factory()->create([
              'name' => 'Test User',
              'email' => 'admin@gmail.com',
              'password' => bcrypt('123456'),
          ]);*/
    }
}
