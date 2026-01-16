<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Password;

new #[Layout('layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>
<div>
    <x-auth-card>
        <x-auth-header
            :title="__('Forgot password')"
            :description="__('Enter your email to receive a password reset link.')"
        />
        <x-auth-alert
            :status="session('status')"
            class="alert-success"
        />

        <x-form wire:submit="sendPasswordResetLink">
            <x-input
                label="Email Address"
                wire:model="email"
                icon="o-envelope"
                placeholder="email@example.com"
                wire:loading.attr="disabled"
                wire:target="sendPasswordResetLink"
            />

            <div class="space-y-6 my-4">
                <x-button
                    label="Email password reset link"
                    type="submit"
                    class="btn-primary w-full"
                    spinner="sendPasswordResetLink"
                />

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
