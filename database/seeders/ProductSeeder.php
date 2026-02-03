<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Indonesian locale

        $categories = ['Laptop', 'Mouse', 'Keyboard', 'Monitor', 'Webcam', 'Headset', 'Cable', 'Storage', 'Router', 'Tablet', 'Printer', 'Speaker'];
        $brands = ['HP', 'Logitech', 'Razer', 'ASUS', 'Samsung', 'Dell', 'Lenovo', 'Acer', 'Canon', 'Epson'];

        for ($i = 1; $i <= 50; $i++) {
            $category = $faker->randomElement($categories);
            $brand = $faker->randomElement($brands);

            Product::create([
                'code' => 'PROD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => $brand . ' ' . $category . ' ' . $faker->randomElement(['Pro', 'Plus', 'Max', 'Elite', 'Standard']),
                'description' => $faker->optional(0.8)->sentence(10), // 80% chance to have description
                'price' => $faker->randomFloat(2, 50000, 10000000), // Random price between 50k - 10M
                'image' => null,
                'is_active' => $faker->boolean(90), // 90% chance to be active
            ]);
        }
    }
}
