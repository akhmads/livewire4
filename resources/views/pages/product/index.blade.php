<?php

use Mary\Traits\Toast;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'product_per_page')]
    public int $perPage = 10;

    #[Session(key: 'product_name')]
    public string $name = '';

    #[Session(key: 'product_code')]
    public string $code = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function mount(): void
    {
        Gate::authorize('products.view');
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'price', 'label' => 'Price'],
            ['key' => 'is_active', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Created At', 'class' => 'lg:w-[200px]'],
            ['key' => 'updated_at', 'label' => 'Updated At', 'class' => 'lg:w-[200px]'],
        ];
    }

    public function products(): LengthAwarePaginator
    {
        return Product::query()
        ->orderBy(...array_values($this->sortBy))
        ->when($this->name, fn($query) => $query->where('name', 'like', '%'.$this->name.'%'))
        ->when($this->code, fn($query) => $query->where('code', 'like', '%'.$this->code.'%'))
        ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'products' => $this->products(),
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
            'name' => 'nullable',
            'code' => 'nullable',
        ]);
    }

    public function clear(): void
    {
        $this->success('Filters cleared.');
        $this->reset(['name', 'code']);
        $this->resetPage();
        $this->updateFilterCount();
        $this->drawer = false;
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->name)) $count++;
        if (!empty($this->code)) $count++;
        $this->filterCount = $count;
    }

    public function delete(Product $product): void
    {
        $this->authorize('delete products');
        $product->delete();
        $this->success('Product successfully deleted.');
    }
}; ?>

<div>
    {{-- HEADER --}}
    <x-header title="Product" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" icon="o-funnel" badge="{{ $filterCount }}" />
            @can('products.create')
            <x-button label="Create" link="{{ route('product.create') }}" icon="o-plus" class="btn-primary" />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$products"
            :sort-by="$sortBy"
            with-pagination
            show-empty-text
            per-page="perPage"
            :link="auth()->user()->can('products.edit') ? route('product.edit', ['product' => '[id]']) : null"
        >
            @scope('actions', $product)
            <div class="flex gap-0">
                @can('products.delete')
                <x-button
                    wire:click="delete({{ $product->id }})"
                    spinner="delete({{ $product->id }})"
                    wire:confirm="Are you sure you want to delete this row?"
                    icon="o-trash"
                    class="btn-ghost btn-sm"
                />
                @endcan
                @can('products.edit')
                <x-button
                    link="{{ route('product.edit', $product->id) }}"
                    icon="o-pencil-square"
                    class="btn-ghost btn-sm"
                />
                @endcan
            </div>
            @endscope
            @scope('cell_price', $product)
                Rp {{ number_format($product->price, 0, ',', '.') }}
            @endscope
            @scope('cell_is_active', $product)
                @if($product->is_active)
                    <x-badge value="Active" class="badge-success" />
                @else
                    <x-badge value="Inactive" class="badge-error" />
                @endif
            @endscope
            @scope('cell_created_at', $product)
                {{ $product->created_at ? \Carbon\Carbon::parse($product->created_at)->setTimezone(auth()->user()->timezone ?? 'UTC')->translatedFormat('d-M-y, H:i') : '' }}
            @endscope
            @scope('cell_updated_at', $product)
                {{ $product->updated_at ? \Carbon\Carbon::parse($product->updated_at)->setTimezone(auth()->user()->timezone ?? 'UTC')->translatedFormat('d-M-y, H:i') : '' }}
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-filter-drawer>
        <x-input label="Name" wire:model="name" />
        <x-input label="Code" wire:model="code" />
    </x-filter-drawer>
</div>
