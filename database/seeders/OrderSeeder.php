<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Contact;
use App\Enums\OrderStatus;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::where('is_active', true)->get();
        $contacts = Contact::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please run ProductSeeder first.');
            return;
        }

        if ($contacts->isEmpty()) {
            $this->command->error('No contacts found. Please run ContactSeeder first.');
            return;
        }

        $statuses = [
            OrderStatus::New->value => 20,        // 20 orders with New status
            OrderStatus::Processing->value => 30, // 30 orders with Processing status
            OrderStatus::Delivered->value => 35,  // 35 orders with Delivered status
            OrderStatus::Cancelled->value => 5,   // 5 orders with Cancelled status
        ];

        $orderNumber = 1;

        foreach ($statuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                // Random date in the last 90 days
                $daysAgo = rand(0, 89);
                $orderDate = Carbon::now()->subDays($daysAgo);

                // Create order
                $order = Order::create([
                    'code' => 'ORD-' . Carbon::now()->format('Y') . '-' . str_pad($orderNumber, 4, '0', STR_PAD_LEFT),
                    'contact_id' => $contacts->random()->id,
                    'date' => $orderDate->format('Y-m-d'),
                    'status' => $status,
                    'note' => rand(0, 100) > 70 ? 'Please handle with care and deliver on time.' : null,
                    'total' => 0, // Will be calculated from details
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Random number of items per order (1-5 items)
                $itemCount = rand(1, min(5, $products->count()));
                $orderTotal = 0;

                // Get random unique products for this order
                $selectedProducts = $products->random($itemCount);

                // Ensure it's always a collection
                if (!is_iterable($selectedProducts)) {
                    $selectedProducts = collect([$selectedProducts]);
                }

                foreach ($selectedProducts as $product) {
                    // Verify product exists and has valid data
                    if (!$product || !$product->id) {
                        continue;
                    }

                    $qty = rand(1, 3);
                    $price = $product->price;
                    $subtotal = $price * $qty;
                    $orderTotal += $subtotal;

                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'price' => $price,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                }

                // Update order total
                $order->update(['total' => $orderTotal]);

                $orderNumber++;
            }
        }

        $this->command->info('Successfully created 90 orders with details.');
    }
}
