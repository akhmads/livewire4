<?php

use Livewire\Component;
use App\Models\Order;
use App\Models\Contact;
use App\Models\OrderDetail;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component {

    public function mount(): void
    {
        $this->loadGrossProfitChart();
        $this->loadOrdersByStatusChart();
    }

    public function stats(): array
    {
        $grossProfit = Order::whereIn('status', [OrderStatus::Delivered->value])->sum('total');
        $totalOrders = Order::count();
        $totalCustomers = Contact::count();
        $totalDelivered = Order::where('status', OrderStatus::Delivered->value)->count();

        return [
            'gross_profit' => $grossProfit,
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'total_delivered' => $totalDelivered,
        ];
    }

    public array $grossProfitChart = [];

    public function loadGrossProfitChart(): void
    {
        $months = [];
        $profits = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M y');

            $profit = Order::where('status', OrderStatus::Delivered->value)
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('total');

            $months[] = $monthName;
            $profits[] = (float) $profit;
        }

        $this->grossProfitChart = [
            'type' => 'line',
            'data' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Gross Profit (Rp)',
                        'data' => $profits,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'aspectRatio' => 2,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'elements' => [
                    'line' => [
                        'tension' => 0.1,
                        'borderWidth' => 2,
                    ],
                    'point' => [
                        'radius' => 4,
                        'hoverRadius' => 6,
                    ],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ]
                ]
            ]
        ];
    }

    public array $ordersByStatusChart = [];

    public function loadOrdersByStatusChart(): void
    {
        $statusData = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusData as $item) {
            // $item->status is already cast to OrderStatus enum
            $status = $item->status;
            $labels[] = $status->label();
            $data[] = $item->total;

            // Color mapping
            $colorMap = [
                'New' => '#3b82f6',
                'Processing' => '#f59e0b',
                'Delivered' => '#22c55e',
                'Cancelled' => '#ef4444',
            ];
            $colors[] = $colorMap[$status->label()] ?? '#6b7280';
        }

        $this->ordersByStatusChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff'
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'aspectRatio' => 1,
                'plugins' => [
                    'legend' => [
                        'position' => 'left',
                        'labels' => [
                            'padding' => 15,
                            'usePointStyle' => true
                        ]
                    ]
                ]
            ]
        ];
    }

    public function topCustomers(): array
    {
        return Contact::select('contacts.id', 'contacts.name', 'contacts.email', DB::raw('COUNT(orders.id) as total_orders'), DB::raw('SUM(orders.total) as total_spent'))
            ->join('orders', 'contacts.id', '=', 'orders.contact_id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->groupBy('contacts.id', 'contacts.name', 'contacts.email')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function bestSellerProducts(): array
    {
        return OrderDetail::select('products.id', 'products.code', 'products.name', DB::raw('SUM(order_details.qty) as total_sold'), DB::raw('SUM(order_details.subtotal) as total_revenue'))
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->groupBy('products.id', 'products.code', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function with(): array
    {
        return [
            'stats' => $this->stats(),
            'topCustomers' => $this->topCustomers(),
            'bestSellers' => $this->bestSellerProducts(),
        ];
    }
}; ?>
<div>
    <x-header title="Dashboard" separator progress-indicator />

    {{-- STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-stat
            title="Gross Profit"
            description="From delivered orders"
            :value="Illuminate\Support\Number::abbreviate($stats['gross_profit'], precision: 2)"
            icon="o-currency-dollar"
            color="text-lime-600 bg-lime-50 dark:bg-lime-950 p-2 rounded-full"
            class="rounded-xl shadow-sm"
        />
        <x-stat
            title="Total Orders"
            description="All time"
            :value="Illuminate\Support\Number::abbreviate($stats['total_orders'])"
            icon="o-shopping-cart"
            color="text-sky-600 bg-sky-50 dark:bg-sky-950 p-2 rounded-full"
            class="rounded-xl shadow-sm"
        />
        <x-stat
            title="Total Customers"
            description="Registered contacts"
            :value="Illuminate\Support\Number::abbreviate($stats['total_customers'])"
            icon="o-users"
            color="text-orange-600 bg-orange-50 dark:bg-orange-950 p-2 rounded-full"
            class="rounded-xl shadow-sm"
        />
        <x-stat
            title="Total Delivered"
            description="Completed orders"
            :value="Illuminate\Support\Number::abbreviate($stats['total_delivered'])"
            icon="o-check-circle"
            color="text-green-600 bg-green-50 dark:bg-green-950 p-2 rounded-full"
            class="rounded-xl shadow-sm"
        />
    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Line Chart --}}
        <x-card title="Gross Profit (Last 12 Months)" class="border border-base-300 col-span-2">
            <x-chart wire:model="grossProfitChart" style="height: 250px;" />
        </x-card>

        {{-- Donut Chart --}}
        <x-card title="Orders By Status" class="border border-base-300">
            <x-chart wire:model="ordersByStatusChart" style="height: 250px;" />
        </x-card>
    </div>

    {{-- TABLES --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Customers --}}
        <x-card title="Top 5 Customers" class="border border-base-300">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th class="text-center">Orders</th>
                        <th class="text-right">Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $customer)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $customer['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $customer['email'] }}</div>
                        </td>
                        <td class="text-center">{{ $customer['total_orders'] }}</td>
                        <td class="text-right font-semibold">Rp {{ number_format($customer['total_spent'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        {{-- Best Sellers --}}
        <x-card title="Best Seller Products" class="border border-base-300">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Sold</th>
                        <th class="text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bestSellers as $product)
                    <tr>
                        <td>
                            <div class="font-medium">{{ \Illuminate\Support\Str::limit($product['name'], 30) }}</div>
                            <div class="text-xs text-gray-500">{{ $product['code'] }}</div>
                        </td>
                        <td class="text-center">{{ $product['total_sold'] }} pcs</td>
                        <td class="text-right font-semibold">Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    <x-grid cols="2" gap="10" class="mt-6">
        <x-card class="border border-base-300">
            <div class="flex items-center justify-between gap-4">
                <x-avatar
                    :title="auth()->user()->name"
                    :subtitle="auth()->user()->email"
                    image="{{ auth()->user()->avatar ?? asset('assets/img/default-avatar.png') }}"
                    class="w-14 h-14"
                />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button label="Sign Out" type="submit" icon="o-power" class="btn btn-error btn-soft" />
                </form>
            </div>
        </x-card>
        <x-card class="border border-base-300">
            <div>
                <table class="table table-xs text-sm">
                    <tbody>
                        <tr>
                            <td class="text-end w-30">PHP Version</td>
                            <td>{{ phpversion() }}</td>
                        </tr>
                        <tr>
                            <td class="text-end">Laravel Version</td>
                            <td>{{ app()->version() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </x-grid>
</div>
