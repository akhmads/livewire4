<?php

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Contact;

new class extends Component {
    use Toast;

    public Contact $contact;

    public $name;
    public $email;
    public $phone;
    public $mobile;
    public $address;

    public function mount(): void
    {
        $this->fill($this->contact);
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'mobile' => 'nullable',
            'address' => 'nullable',
        ]);

        $this->contact->update($data);

        $this->success('Success','Contact successfully updated.', redirectTo: route('contact.index'));
    }
}; ?>

<div>
    <x-header title="Update Contact" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('contact.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <x-grid cols="5">
            <div class="col-span-3">
                <x-card class="border border-base-300">
                    <div class="space-y-4">
                        <x-input label="Name" wire:model="name" />
                        <x-input label="Email" wire:model="email" />
                        <x-input label="Phone" wire:model="phone" />
                        <x-input label="Mobile" wire:model="mobile" />
                        <x-textarea label="Address" wire:model="address" class="field-sizing-content" />
                    </div>
                </x-card>
            </div>
        </x-grid>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('contact.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
