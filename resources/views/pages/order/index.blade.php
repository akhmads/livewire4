<?php

use Mary\Traits\Toast;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'order_per_page')]
    public int $perPage = 10;

    #[Session(key: 'order_code')]
    public string $code = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function mount(): void
    {
        Gate::authorize('orders.view');
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'contact.name', 'label' => 'Contact'],
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'total', 'label' => 'Total'],
            ['key' => 'created_at', 'label' => 'Created At', 'class' => 'lg:w-[200px]'],
        ];
    }

    public function orders(): LengthAwarePaginator
    {
        return Order::query()
        ->with('contact')
        ->orderBy(...array_values($this->sortBy))
        ->when($this->code, fn($query) => $query->where('code', 'like', '%'.$this->code.'%'))
        ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'orders' => $this->orders(),
        ];
    }

    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
            $this->updateFilterCount();
        }
    }

    public function search(): void
    {
        $this->validate([
            'code' => 'nullable',
        ]);
    }

    public function clear(): void
    {
        $this->success('Filters cleared.');
        $this->reset(['code']);
        $this->resetPage();
        $this->updateFilterCount();
        $this->drawer = false;
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->code)) $count++;
        $this->filterCount = $count;
    }

    public function delete(Order $order): void
    {
        $this->authorize('delete orders');
        $order->details()->delete();
        $order->delete();
        $this->success('Order successfully deleted.');
    }
}; ?>

<div>
    {{-- HEADER --}}
    <x-header title="Order" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" icon="o-funnel" badge="{{ $filterCount }}" />
            @can('orders.create')
            <x-button label="Create" link="{{ route('order.create') }}" icon="o-plus" class="btn-primary" />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$orders"
            :sort-by="$sortBy"
            with-pagination
            show-empty-text
            per-page="perPage"
            :link="auth()->user()->can('orders.edit') ? route('order.edit', ['order' => '[id]']) : null"
        >
            @scope('actions', $order)
            <div class="flex gap-0">
                @can('orders.delete')
                <x-button
                    wire:click="delete({{ $order->id }})"
                    spinner="delete({{ $order->id }})"
                    wire:confirm="Are you sure you want to delete this order and all its details?"
                    icon="o-trash"
                    class="btn-ghost btn-sm"
                />
                @endcan
                @can('orders.edit')
                <x-button
                    link="{{ route('order.edit', $order->id) }}"
                    icon="o-pencil-square"
                    class="btn-ghost btn-sm"
                />
                @endcan
            </div>
            @endscope
            @scope('cell_date', $order)
                {{ $order->date ? \Carbon\Carbon::parse($order->date)->translatedFormat('d-M-Y') : '' }}
            @endscope
            @scope('cell_total', $order)
                Rp {{ number_format($order->total, 0, ',', '.') }}
            @endscope
            @scope('cell_created_at', $order)
                {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->setTimezone(auth()->user()->timezone ?? 'UTC')->translatedFormat('d-M-y, H:i') : '' }}
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-filter-drawer>
        <x-input label="Code" wire:model="code" />
    </x-filter-drawer>
</div>
