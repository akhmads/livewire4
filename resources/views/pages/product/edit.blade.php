<?php

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use Toast;

    public Product $product;

    public $code;
    public $name;
    public $description;
    public $price;
    public $is_active;

    public function mount(): void
    {
        Gate::authorize('products.edit');
        $this->fill($this->product);
        $this->is_active = (bool) $this->product->is_active;
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => 'required|unique:products,code,'.$this->product->id,
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = (bool) $data['is_active'];

        $this->product->update($data);

        $this->success('Success','Product successfully updated.', redirectTo: route('product.index'));
    }
}; ?>

<div>
    <x-header title="Update Product" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('product.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-grid cols="2">
        <x-form wire:submit="save">
            <x-card class="border border-base-300">
                <div class="space-y-4">
                    <x-input label="Code" wire:model="code" />
                    <x-input label="Name" wire:model="name" />
                    <x-textarea label="Description" wire:model="description" class="field-sizing-content" />
                    <x-input label="Price" wire:model="price" type="number" step="0.01" min="0" prefix="Rp" />
                    <x-checkbox label="Active" wire:model="is_active" />
                </div>
            </x-card>
            <x-slot:actions>
                <x-button label="Cancel" link="{{ route('product.index') }}" />
                <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-grid>
</div>
