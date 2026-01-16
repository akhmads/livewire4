<?php

use Livewire\Component;

new class extends Component {

    public function mount(): void
    {

    }

    public function with(): array
    {
        return [];
    }
}; ?>
<div>
    <x-header title="Dashboard" separator />

    <x-grid cols="2" gap="10">
        <x-card class="border border-base-300">
            <div class="flex items-center justify-between gap-4">
                <x-avatar
                    :title="auth()->user()->name"
                    :subtitle="auth()->user()->email"
                    image="{{ auth()->user()->avatar ?? asset('assets/img/default-avatar.png') }}"
                    class="w-14 h-14"
                />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button label="Sign Out" type="submit" icon="o-power" class="btn btn-error btn-soft" />
                </form>
            </div>
        </x-card>
        <x-card class="border border-base-300">
            <div>
                <table class="table table-xs text-sm">
                    <tbody>
                        <tr>
                            <td class="text-end w-30">PHP Version</td>
                            <td>{{ phpversion() }}</td>
                        </tr>
                        <tr>
                            <td class="text-end">Laravel Version</td>
                            <td>{{ app()->version() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </x-grid>
</div>
