<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Indonesian locale

        for ($i = 1; $i <= 20; $i++) {
            Contact::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'mobile' => $faker->optional(0.8)->mobileNumber(), // 80% chance to have mobile
                'address' => $faker->optional(0.7)->address(), // 70% chance to have address
            ]);
        }
    }
}
