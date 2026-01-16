<?php

use App\Enums\Role;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use Toast, WithFileUploads;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $avatar = '';
    // public $role = '';
    public $is_active = '';

    public function mount(): void
    {
        Gate::authorize('create users');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')],
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'avatar' => 'nullable|image|max:1024',
            // 'role' => 'required',
            'is_active' => 'required',
        ]);

        unset($data['avatar']);
        unset($data['password_confirmation']);
        // unset($data['role']);

        if ($this->avatar) {
            $url = $this->avatar->store('avatar', 'public');
            $data['avatar'] =  "/storage/".$url;
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // $user->syncRoles([$this->role]);

        $this->success('Success', 'User successfully created.', redirectTo: route('users.index'));
    }
}; ?>

<div>
    <x-header title="Create User" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('users.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <div class="lg:w-[70%]">
        <x-form wire:submit="save">
            <x-card>
                <div class="space-y-4">
                    <x-file label="Avatar" wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
                        <img src="{{ $user->avatar ?? asset('assets/img/default-avatar.png') }}" class="h-40 rounded-lg" />
                    </x-file>
                    <div class="space-y-4 lg:space-y-0 lg:grid grid-cols-2 gap-4">
                        <x-input label="Name" wire:model="name" />
                        <x-input label="Email" wire:model="email" />
                    </div>
                    <div class="space-y-4 lg:space-y-0 lg:grid grid-cols-2 gap-4">
                        <x-input label="Password" wire:model="password" type="password" />
                        <x-input label="Confirm Password" wire:model="password_confirmation" type="password" />
                    </div>
                    <div class="space-y-4 lg:space-y-0 lg:grid grid-cols-2 gap-4">
                        {{-- <x-select label="Role" :options="\Spatie\Permission\Models\Role::get()" wire:model="role" option-value="name" option-label="name" placeholder="-- Select --" /> --}}
                        <x-select label="Is Active" :options="\App\Enums\ActiveStatus::toSelect()" wire:model="is_active" placeholder="-- Select --" />
                    </div>
                </div>
            </x-card>
            <x-slot:actions>
                <x-button label="Cancel" link="{{ route('users.index') }}" />
                <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </div>
</div>
