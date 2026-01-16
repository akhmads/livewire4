<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Enums\ActiveStatus;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use Toast, WithFileUploads;

    public User $user;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $avatar = '';
    // public $role = '';
    public $is_active = '';
    public $storedAvatar = '';

    public function mount(): void
    {
        Gate::authorize('update users');

        $this->fill($this->user);
        // $this->role = $this->user->getRoleNames()->first();

        $this->user->password = '';
        $this->storedAvatar = $this->avatar;
        $this->avatar = '';
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user)],
            'password' => 'nullable|confirmed',
            'password_confirmation' => 'nullable',
            'avatar' => 'nullable|image|max:1024',
            // 'role' => 'required',
            'is_active' => 'required',
        ]);

        unset($data['avatar']);
        unset($data['password_confirmation']);
        // unset($data['role']);

        if ($this->password) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($this->avatar) {
            $url = $this->avatar->store('avatar', 'public');
            $data['avatar'] =  "/storage/".$url;
        }

        $this->user->update($data);

        // $this->user->syncRoles([$this->role]);

        $this->success('Success', 'User successfully saved.', redirectTo: route('users.index'));
    }
}; ?>

<div>
    <x-header title="Update User" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('users.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <div class="xl:w-[70%]">
        <x-form wire:submit="save">
            <x-card class="border border-base-300">
                <div class="space-y-4">
                    <x-file label="Avatar" wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
                        <img src="{{ $storedAvatar ?? asset('assets/img/default-avatar.png') }}" class="h-40 rounded-lg" />
                    </x-file>
                    <div class="space-y-4 xl:space-y-0 xl:grid grid-cols-2 gap-4">
                        <x-input label="Name" wire:model="name" />
                        <x-input label="Email" wire:model="email" />
                    </div>
                    <div class="space-y-4 xl:space-y-0 xl:grid grid-cols-2 gap-4">
                        <x-input label="Password" wire:model="password" type="password" hint="Password changes are optional" />
                        <x-input label="Confirm Password" wire:model="password_confirmation" type="password" />
                    </div>
                    <div class="space-y-4 xl:space-y-0 xl:grid grid-cols-2 gap-4">
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
