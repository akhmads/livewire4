<?php

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new #[Layout('layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('home', absolute: false), navigate: true);
    }
}; ?>
<div>
    <x-auth-card>

        <x-auth-header
            :title="__('Create an account')"
            :description="__('Enter your details below to create your account')"
        />

        <x-form wire:submit="register">
            <x-input
                label="Name"
                wire:model="name"
                icon="o-envelope"
                placeholder="Full name"
                wire:loading.attr="disabled"
                wire:target="register"
            />
            <x-input
                label="E-mail address"
                wire:model="email"
                icon="o-envelope"
                placeholder="email@example.com"
                wire:loading.attr="disabled"
                wire:target="register"
            />
            <x-input
                label="Password"
                wire:model="password"
                type="password"
                icon="o-key"
                placeholder="Password"
                wire:loading.attr="disabled"
                wire:target="register"
            />
            <x-input
                label="Confirm password"
                wire:model="password_confirmation"
                type="password"
                icon="o-key"
                placeholder="Confirm password"
                wire:loading.attr="disabled"
                wire:target="register"
            />

            <div class="space-y-4 mt-4">
                <x-button
                    label="Create account"
                    type="submit"
                    class="btn-primary w-full"
                    spinner="register"
                />

                <div class="space-x-1 text-center text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Already have an account?') }}</span>
                    <a href="{{ route('login') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ __('Log in') }}
                    </a>
                </div>

                <div class="space-x-1 text-center text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Back to') }}</span>
                    <a href="{{ route('home') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ __('home page') }}
                    </a>
                </div>
            </div>
        </x-form>
    </x-auth-card>
</div>
