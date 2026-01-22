<div>
    {{-- Header --}}
    <x-header title="User Role Assignment" subtitle="Assign role ke user untuk mengatur akses dan permission" separator>
        <x-slot:actions>
            <x-button
                icon="o-arrow-path"
                wire:click="$refresh"
                spinner
                class="btn-ghost btn-sm"
                tooltip="Refresh"
            />
        </x-slot:actions>
    </x-header>

    {{-- Filters --}}
    <x-card class="mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                <x-input
                    wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass"
                    placeholder="Search user name or email..."
                    clearable
                />
            </div>
            <div class="w-full md:w-48">
                @php
                    $filterOptions = array_merge(
                        [['id' => '', 'name' => 'All Roles']],
                        $roleOptions
                    );
                @endphp
                <x-select
                    wire:model.live="roleFilter"
                    :options="$filterOptions"
                    icon="o-shield-check"
                />
            </div>
        </div>
    </x-card>

    {{-- Users Table --}}
    <x-card>
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="w-10 rounded-full bg-base-300">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" />
                                                @else
                                                    <div class="flex items-center justify-center w-full h-full text-lg font-bold text-gray-500">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm">{{ $user->email }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <div class="badge badge-primary badge-sm gap-1">
                                                {{ $role->name }}
                                                <button
                                                    wire:click="removeRole({{ $user->id }}, {{ $role->id }})"
                                                    wire:confirm="Hapus role {{ $role->name }} dari {{ $user->name }}?"
                                                    class="hover:text-error"
                                                >
                                                    <x-icon name="o-x-mark" class="w-3 h-3" />
                                                </button>
                                            </div>
                                        @empty
                                            <span class="text-gray-400 text-sm">No roles assigned</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-right">
                                    @can('user-roles.assign')
                                    <x-button
                                        icon="o-shield-check"
                                        label="Assign Roles"
                                        wire:click="openAssignModal({{ $user->id }})"
                                        spinner
                                        class="btn-ghost btn-sm"
                                    />
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <x-icon name="o-users" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">No users</h3>
                <p class="text-gray-500">
                    @if($search || $roleFilter)
                        No users matching your filters
                    @else
                        No users registered
                    @endif
                </p>
            </div>
        @endif
    </x-card>

    {{-- Assign Role Modal --}}
    <x-modal wire:model="showModal" title="Assign Roles" box-class="max-w-2xl">
        <x-form wire:submit="save">
            <div class="space-y-6">
                <div class="p-4 bg-base-200 rounded-lg">
                    <p class="text-sm text-gray-500">Assign roles for:</p>
                    <p class="font-semibold">{{ $userName }}</p>
                </div>

                <div>
                    <label class="font-medium text-sm mb-3 block">Select Roles</label>
                    <div class="border rounded-lg p-4 max-h-64 overflow-y-auto bg-base-200/50 space-y-2">
                        @forelse($roles as $role)
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-base-300 p-2 rounded" wire:key="role-{{ $role->id }}">
                                <input
                                    type="checkbox"
                                    class="checkbox checkbox-primary"
                                    wire:model="selectedRoles"
                                    value="{{ $role->id }}"
                                />
                                <div class="flex-1">
                                    <span class="font-medium">{{ $role->name }}</span>
                                    <span class="text-xs text-gray-500 ml-2">
                                        ({{ $role->permissions_count ?? 0 }} permissions)
                                    </span>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-4 text-gray-500">
                                <p>No roles available. Please create a role first.</p>
                            </div>
                        @endforelse
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ count($selectedRoles) }} roles selected
                    </p>
                </div>
            </div>

            <x-slot:actions>
                <x-button
                    label="Cancel"
                    wire:click="closeModal"
                    class="btn-ghost"
                />
                <x-button
                    label="Save"
                    type="submit"
                    class="btn-primary"
                    spinner="save"
                />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
