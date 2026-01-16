<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Rules\CurrentPassword;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use Toast, WithFileUploads;

    public $name = '';
    public $email = '';
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    public $avatar = '';
    public $storedAvatar = '';
    public $role = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        $this->storedAvatar = auth()->user()->avatar;
        // $this->role = auth()->user()->getRoleNames()->first();
        $this->avatar = '';
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->user()->id)],
            'avatar' => 'nullable|image|mimes:jpg,png|max:1024',
        ]);

        unset($data['avatar']);

        if ($this->avatar) {
            $url = $this->avatar->store('avatar', 'public');
            $data['avatar'] =  "/storage/".$url;
        }

        auth()->user()->update($data);

        $this->success('Success','Profile successfully updated.', redirectTo: route('users.profile'));
    }

    public function changePassword(): void
    {
        $data = $this->validate([
            'current_password' => ['required', new CurrentPassword],
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        unset($data['current_password']);
        unset($data['password_confirmation']);

        $data['password'] = Hash::make($data['password']);

        auth()->user()->update($data);

        $this->success('Password has been updated.');
    }
}; ?>

<div>
    <x-header title="User Profile" separator />
    <div class="xl:w-[60%]">
        <div class="space-y-6 ">
            <x-form wire:submit="save">
                <x-card separator class="border border-base-300">
                    <div class="space-y-4">
                        <x-file label="Avatar" wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
                            <img src="{{ $storedAvatar ?? asset('assets/img/default-avatar.png') }}" class="h-40 rounded-lg" />
                        </x-file>
                        <x-input label="Name" wire:model="name" />
                        <x-input label="Email" wire:model="email" />
                        {{-- <x-input label="Role" wire:model="role" readonly /> --}}
                    </div>
                    <x-slot:actions>
                        <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
                    </x-slot:actions>
                </x-card>
            </x-form>

            <x-form wire:submit="changePassword">
                <x-card title="Change Password" separator class="border border-base-300">
                    <div class="space-y-4">
                        <x-input label="Current Password" wire:model="current_password" type="password" />
                        <x-input label="Password" wire:model="password" type="password" />
                        <x-input label="Confirm Password" wire:model="password_confirmation" type="password" />
                    </div>
                    <x-slot:actions>
                        <x-button label="Save" icon="o-paper-airplane" spinner="changePassword" type="submit" class="btn-primary" />
                    </x-slot:actions>
                </x-card>
            </x-form>
        </div>
    </div>
</div>
