<?php

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Order;
use App\Models\Product;
use App\Models\Contact;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use Toast;

    public $code;
    public $date;
    public $note;
    public $contact_id;
    public $total = 0;

    public $details = [];
    public $products = [];
    public $contacts = [];

    public function mount(): void
    {
        Gate::authorize('orders.create');
        $this->date = now()->format('Y-m-d');
        $this->products = Product::where('is_active', true)->orderBy('name')->get();
        $this->contacts = Contact::orderBy('name')->get();
        $this->addDetail();
    }

    public function addDetail(): void
    {
        $this->details[] = [
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
            $this->details[$index]['subtotal'] = $this->details[$index]['price'] * $this->details[$index]['qty'];
        }

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->total = collect($this->details)->sum('subtotal');
    }

    public function save(): void
    {
        $this->validate([
            'code' => 'required|unique:orders,code',
            'date' => 'required|date',
            'contact_id' => 'required|exists:contacts,id',
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
            $order = Order::create([
                'code' => $this->code,
                'date' => $this->date,
                'contact_id' => $this->contact_id,
                'note' => $this->note,
                'total' => $this->total,
            ]);

            foreach ($this->details as $detail) {
                $order->details()->create([
                    'product_id' => $detail['product_id'],
                    'price' => $detail['price'],
                    'qty' => $detail['qty'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        $this->success('Success', 'Order successfully created.', redirectTo: route('order.index'));
    }
}; ?>

<div>
    <x-header title="Create Order" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('order.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Order Header --}}
            <div class="lg:col-span-1">
                <x-card class="border border-base-300">
                    <div class="space-y-4">
                        <x-input label="Code" wire:model="code" />
                        <x-input label="Date" wire:model="date" type="date" />
                        <x-choices
                            label="Contact"
                            wire:model="contact_id"
                            :options="$contacts"
                            option-label="name"
                            option-value="id"
                            searchable
                            single
                        />
                        <x-textarea label="Note" wire:model="note" rows="3" />
                        <div class="pt-4 border-t">
                            <div class="text-lg font-semibold">
                                Total: Rp {{ number_format($total, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Order Details --}}
            <div class="lg:col-span-2">
                <x-card class="border border-base-300">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold">Order Details</h3>
                            <x-button label="Add Item" icon="o-plus" wire:click="addDetail" class="btn-sm btn-primary" />
                        </div>

                        <div class="space-y-3">
                            @foreach($details as $index => $detail)
                            <div class="p-4 border border-base-200 rounded-lg bg-base-50 dark:bg-base-200">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                                        <div class="md:col-span-2">
                                            <x-select
                                                label="Product"
                                                wire:model.live="details.{{ $index }}.product_id"
                                                :options="$products"
                                                option-label="name"
                                                option-value="id"
                                                placeholder="Select product"
                                            />
                                        </div>
                                        <div>
                                            <x-input
                                                label="Price"
                                                wire:model.live="details.{{ $index }}.price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                prefix="Rp"
                                            />
                                        </div>
                                        <div>
                                            <x-input
                                                label="Qty"
                                                wire:model.live="details.{{ $index }}.qty"
                                                type="number"
                                                min="1"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 pt-7">
                                        <div class="text-sm font-medium whitespace-nowrap">
                                            Rp {{ number_format($detail['subtotal'], 0, ',', '.') }}
                                        </div>
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
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('order.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
