<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'code' => 'PROD-001',
                'name' => 'Laptop HP 15',
                'description' => 'Laptop HP 15 inch dengan processor Intel i5 dan RAM 8GB',
                'price' => 7500000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-002',
                'name' => 'Mouse Wireless Logitech',
                'description' => 'Mouse wireless 2.4GHz dengan akurasi presisi tinggi',
                'price' => 250000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-003',
                'name' => 'Keyboard Mekanik RGB',
                'description' => 'Keyboard mekanik dengan LED RGB dan switch mechanical',
                'price' => 1200000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-004',
                'name' => 'Monitor LED 24 inch',
                'description' => 'Monitor LED 24 inch Full HD 1920x1080 dengan refresh rate 60Hz',
                'price' => 2000000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-005',
                'name' => 'Webcam HD 1080p',
                'description' => 'Webcam HD dengan resolusi 1080p dan microphone built-in',
                'price' => 400000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-006',
                'name' => 'Headset Gaming Razer',
                'description' => 'Headset gaming dengan noise cancellation dan surround sound 7.1',
                'price' => 1500000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-007',
                'name' => 'USB Cable 3.0 Type-C',
                'description' => 'Kabel USB 3.0 Type-C dengan panjang 2 meter',
                'price' => 150000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-008',
                'name' => 'External SSD 1TB',
                'description' => 'SSD eksternal 1TB dengan kecepatan baca hingga 550MB/s',
                'price' => 1800000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-009',
                'name' => 'Router WiFi 6 ASUS',
                'description' => 'Router WiFi 6 dengan dual band dan MU-MIMO technology',
                'price' => 2500000,
                'image' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-010',
                'name' => 'Tablet Samsung Tab S7',
                'description' => 'Tablet 11 inch dengan processor Snapdragon dan RAM 6GB',
                'price' => 5000000,
                'image' => null,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
