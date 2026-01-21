<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Enums\ActiveStatus;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'users_per_page')]
    public int $perPage = 10;

    #[Session(key: 'users_name')]
    public string $name = '';

    #[Session(key: 'users_email')]
    public string $email = '';

    #[Session(key: 'users_is_active')]
    public string $is_active = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function mount(): void
    {
        Gate::authorize('view users');
    }

    public function headers(): array
    {
        return [
            ['key' => 'avatar', 'label' => 'Avatar', 'sortable' => false],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'is_active', 'label' => 'Is Active'],
        ];
    }

    public function users(): LengthAwarePaginator
    {
        $user = User::query()
        ->orderBy(...array_values($this->sortBy))
        ->filterLike('name', $this->name)
        ->filterLike('email', $this->email)
        ->filterWhere('is_active', $this->is_active);

        return $user->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
        ];
    }

    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
            $this->updateFilterCount();
        }
    }

    public function search(): void
    {
        $data = $this->validate([
            'name' => 'nullable',
            'email' => 'nullable',
            'is_active' => 'nullable',
        ]);
    }

    public function clear(): void
    {
        $this->success('Filters cleared.');
        $this->reset(['name','email','is_active']);
        $this->resetPage();
        $this->updateFilterCount();
        $this->drawer = false;
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->name)) $count++;
        if (!empty($this->email)) $count++;
        if (!empty($this->is_active)) $count++;
        $this->filterCount = $count;
    }

    public function delete(User $user): void
    {
        Gate::authorize('delete users');
        $user->delete();
        $this->success("User successfully deleted.");
    }
}; ?>

<div>
    {{-- HEADER --}}
    <x-header title="Users" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" badge="{{ $filterCount }}" />
            @can('create users')
            <x-button label="Create" link="{{ route('users.create') }}" responsive icon="o-plus" class="btn-primary" />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$users"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            show-empty-text
            :link="auth()->user()->can('update users') ? route('users.edit', ['user' => '[id]']) : null"
        >
            @scope('cell_avatar', $user)
            <x-avatar image="{{ $user->avatar ?? asset('assets/img/default-avatar.png') }}" class="w-6" />
            @endscope
            @scope('cell_is_active', $user)
                <x-badge :value="$user->is_active->name" class="{{ $user->is_active->color() }}" />
            @endscope
            @scope('actions', $user)
            <div class="flex gap-0">
                @can('delete users')
                <x-button
                    wire:click="delete({{ $user->id }})"
                    spinner="delete({{ $user->id }})"
                    wire:confirm="Are you sure you want to delete this row?"
                    icon="o-trash"
                    class="btn-ghost btn-sm"
                />
                @endcan
                @can('update users')
                <x-button
                    link="{{ route('users.edit', $user->id) }}"
                    icon="o-pencil-square"
                    class="btn-ghost btn-sm"
                />
                @endcan
            </div>
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-filter-drawer>
        <x-input label="Name" wire:model="name" />
        <x-input label="Email" wire:model="email" />
        <x-select label="Is Active" wire:model="is_active" :options="\App\Enums\ActiveStatus::toSelect()" placeholder="-- All --" />
    </x-filter-drawer>
</div>
