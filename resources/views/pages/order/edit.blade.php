<?php

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Order;
use App\Models\Product;
use App\Models\Contact;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use Toast;

    public Order $order;

    public $code;
    public $date;
    public $note;
    public $contact_id;
    public $status;
    public $total = 0;

    public $details = [];
    public $products = [];
    public $contacts = [];

    public function mount(): void
    {
        Gate::authorize('orders.edit');
        $this->searchProduct();
        $this->searchContact();

        $this->code = $this->order->code;
        $this->date = $this->order->date?->format('Y-m-d');
        $this->contact_id = $this->order->contact_id;
        $this->status = $this->order->status->value;
        $this->note = $this->order->note;

        // Load existing details
        $this->details = $this->order->details->map(function($detail) {
            return [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'price' => $detail->price,
                'qty' => $detail->qty,
                'subtotal' => $detail->subtotal,
            ];
        })->toArray();

        if (empty($this->details)) {
            $this->addDetail();
        }

        $this->calculateTotal();
    }

    public function addDetail(): void
    {
        $this->details[] = [
            'id' => null,
            'product_id' => '',
            'price' => 0,
            'qty' => 1,
            'subtotal' => 0,
        ];
    }

    public function removeDetail($index): void
    {
        if (count($this->details) > 1) {
            unset($this->details[$index]);
            $this->details = array_values($this->details);
            $this->calculateTotal();
        }
    }

    public function updatedDetails($value, $key): void
    {
        // Parse key like "0.product_id" or "0.qty"
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1] ?? null;

        if ($field === 'product_id' && !empty($value)) {
            $product = Product::find($value);
            if ($product) {
                $this->details[$index]['price'] = $product->price;
            }
        }

        // Recalculate subtotal for this detail
        if (isset($this->details[$index]['price']) && isset($this->details[$index]['qty'])) {
            $price = is_numeric($this->details[$index]['price']) ? (float) $this->details[$index]['price'] : 0;
            $qty = is_numeric($this->details[$index]['qty']) ? (int) $this->details[$index]['qty'] : 0;
            $this->details[$index]['subtotal'] = $price * $qty;
        }

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->total = collect($this->details)->sum('subtotal');
    }

    public function searchContact($value = ''): void
    {
        $this->contacts = Contact::query()
            ->where('name', 'like', '%' . $value . '%')
            ->orWhere('email', 'like', '%' . $value . '%')
            ->orWhere('phone', 'like', '%' . $value . '%')
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function searchProduct($value = ''): void
    {
        $this->products = Product::query()
            ->where('is_active', true)
            ->where('name', 'like', '%' . $value . '%')
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function save(): void
    {
        // Check for duplicate products
        $productIds = collect($this->details)
            ->where('product_id', '!==', '')
            ->pluck('product_id')
            ->toArray();

        if (count($productIds) !== count(array_unique($productIds))) {
            $this->error('Error', 'Duplicate products are not allowed. Each product can only be ordered once.');
            return;
        }

        $this->validate([
            'code' => 'required|unique:orders,code,' . $this->order->id,
            'date' => 'required|date',
            'contact_id' => 'required|exists:contacts,id',
            'status' => 'required|in:new,processing,shipped,delivered,cancelled',
            'note' => 'nullable',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.price' => 'required|numeric|min:0',
            'details.*.qty' => 'required|integer|min:1',
        ], [
            'details.required' => 'At least one detail is required.',
            'details.*.product_id.required' => 'Product is required.',
            'details.*.product_id.exists' => 'Invalid product selected.',
            'details.*.qty.min' => 'Quantity must be at least 1.',
        ]);

        DB::transaction(function () {
            $this->order->update([
                'code' => $this->code,
                'date' => $this->date,
                'contact_id' => $this->contact_id,
                'status' => $this->status,
                'note' => $this->note,
                'total' => $this->total,
            ]);

            // Get existing detail IDs
            $existingIds = collect($this->details)->pluck('id')->filter()->toArray();

            // Delete removed details
            $this->order->details()->whereNotIn('id', $existingIds)->delete();

            // Update or create details
            foreach ($this->details as $detail) {
                if (!empty($detail['id'])) {
                    // Update existing
                    $this->order->details()->where('id', $detail['id'])->update([
                        'product_id' => $detail['product_id'],
                        'price' => $detail['price'],
                        'qty' => $detail['qty'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                } else {
                    // Create new
                    $this->order->details()->create([
                        'product_id' => $detail['product_id'],
                        'price' => $detail['price'],
                        'qty' => $detail['qty'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                }
            }
        });

        $this->success('Success', 'Order successfully updated.', redirectTo: route('order.index'));
    }
}; ?>

<div>
    <x-header title="Update Order" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('order.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">

        <div class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <x-card class="col-span-2 border border-base-300">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <x-input label="Code" wire:model="code" />
                        <x-input label="Date" wire:model="date" type="date" />
                        <x-choices
                            label="Contact"
                            wire:model="contact_id"
                            :options="$contacts"
                            search-function="searchContact"
                            option-label="name"
                            option-value="id"
                            searchable
                            single
                        />
                        <x-select
                            label="Status"
                            wire:model="status"
                            :options="\App\Enums\OrderStatus::toSelect()"
                        />
                        <x-textarea label="Note" wire:model="note" rows="3" class="lg:col-span-2" />
                    </div>
                </x-card>
                <div>
                    <x-card class="border border-base-300">
                        <div>
                            <div class="font-semibold text-xl text-gray-500 dark:text-gray-300">TOTAL :</div>
                            <div class="text-4xl font-semibold text-blue-800 dark:text-blue-400">{{ number_format($total, 2, ',', '.') }}</div>
                        </div>
                    </x-card>
                </div>
            </div>

            {{-- Order Details --}}
            <x-card class="border border-base-300">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Order Details</h3>
                        <x-button
                            label="Add Item"
                            icon="o-plus"
                            wire:click="addDetail"
                            spinner="addDetail"
                            class="btn-sm btn-primary"
                        />
                    </div>

                    <div class="border border-base-300 rounded-lg divide-y divide-base-300">
                        @foreach($details as $index => $detail)
                        <div class="px-4 pt-2 pb-4 bg-gray-50 dark:bg-base-200 first:rounded-t-lg last:rounded-b-lg">
                            <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-2">
                                <div class="lg:col-span-5">
                                    <x-choices
                                        label="Product"
                                        wire:model.live="details.{{ $index }}.product_id"
                                        :options="$products"
                                        search-function="searchProduct"
                                        option-label="name"
                                        option-value="id"
                                        placeholder="Select product"
                                        searchable
                                        single
                                    />
                                </div>
                                <div class="lg:col-span-2">
                                    <x-input
                                        label="Price"
                                        wire:model.live.debounce.500ms="details.{{ $index }}.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                                <div class="lg:col-span-2">
                                    <x-input
                                        label="Qty"
                                        wire:model.live.debounce.500ms="details.{{ $index }}.qty"
                                        type="number"
                                        min="1"
                                    />
                                </div>
                                <div class="lg:col-span-2">
                                    <x-input
                                        label="Subtotal"
                                        wire:model.live="details.{{ $index }}.subtotal"
                                        type="number"
                                        min="0"
                                        readonly
                                    />
                                </div>
                                <div class="flex flex-col gap-2 pt-7">
                                    @if(count($details) > 1)
                                    <x-button
                                        icon="o-trash"
                                        wire:click="removeDetail({{ $index }})"
                                        class="btn-ghost btn-sm text-error"
                                        wire:confirm="Remove this item?"
                                    />
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @error('details')
                    <div class="text-sm text-error">{{ $message }}</div>
                    @enderror
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('order.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
