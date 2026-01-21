<div>
    {{-- Header --}}
    <x-header title="Role Management" subtitle="Manage roles and assign permissions to each role" separator>
        <x-slot:actions>
            @can('roles.create')
            <x-button
                icon="o-plus"
                label="Add Role"
                wire:click="openCreateModal"
                class="btn-primary btn-sm"
            />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- Filters --}}
    <x-card class="mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                <x-input
                    wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass"
                    placeholder="Search role..."
                    clearable
                />
            </div>
        </div>
    </x-card>

    {{-- Roles Table --}}
    <x-card>
        @if($roles->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Guard</th>
                            <th>Permissions</th>
                            <th>Created At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr wire:key="role-{{ $role->id }}">
                                <td class="font-mono text-sm">{{ $role->id }}</td>
                                <td>
                                    <div class="font-medium">{{ $role->name }}</div>
                                </td>
                                <td>
                                    <x-badge :value="$role->guard_name" class="badge-info badge-sm" />
                                </td>
                                <td>
                                    <x-badge :value="$role->permissions_count . ' permissions'" class="badge-ghost" />
                                </td>
                                <td class="text-sm text-gray-500">
                                    {{ $role->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('roles.edit')
                                        <x-button
                                            icon="o-pencil"
                                            wire:click="openEditModal({{ $role->id }})"
                                            spinner
                                            class="btn-ghost btn-sm"
                                            title="Edit"
                                        />
                                        @endcan
                                        @if($role->name !== 'super-admin')
                                            @can('roles.delete')
                                            <x-button
                                                icon="o-trash"
                                                wire:click="delete({{ $role->id }})"
                                                wire:confirm="Are you sure you want to delete this role?"
                                                spinner
                                                class="btn-ghost btn-sm text-error"
                                                title="Delete"
                                            />
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $roles->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <x-icon name="o-shield-check" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">No roles</h3>
                <p class="text-gray-500">
                    @if($search)
                        No roles matching your search
                    @else
                        No roles added yet
                    @endif
                </p>
            </div>
        @endif
    </x-card>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Role' : 'Add Role'" class="" box-class="max-w-4xl">
        <x-form wire:submit="save">
            <div class="space-y-6">
                {{-- Role Name --}}
                <x-input
                    wire:model="name"
                    label="Role Name"
                    placeholder="e.g. admin, editor, viewer"
                    error-key="name"
                />

                {{-- Permissions Section --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="font-medium text-sm">Permissions</label>
                        <div class="flex gap-2">
                            <x-button
                                label="Select All"
                                icon="o-check"
                                wire:click="selectAllPermissions"
                                class="btn-xs btn-ghost"
                            />
                            <x-button
                                label="Deselect All"
                                icon="o-x-mark"
                                wire:click="deselectAllPermissions"
                                class="btn-xs btn-ghost"
                            />
                        </div>
                    </div>

                    <div class="border rounded-lg p-4 max-h-96 overflow-y-auto bg-base-200/50">
                        @forelse($permissionsByGroup as $group => $permissions)
                            <div class="mb-4 last:mb-0">
                                {{-- Group Header --}}
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-base-300">
                                    @php
                                        $groupPermissionIds = $permissions->pluck('id')->toArray();
                                        $allGroupSelected = count(array_intersect($groupPermissionIds, $selectedPermissions)) === count($groupPermissionIds);
                                    @endphp
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm checkbox-primary"
                                        wire:click="toggleGroupPermissions('{{ $group === 'Ungrouped' ? '' : $group }}')"
                                        @checked($allGroupSelected && count($groupPermissionIds) > 0)
                                        wire:key="group-{{ $group }}"
                                    />
                                    <span class="font-semibold text-sm">{{ $group }}</span>
                                    <span class="text-xs text-gray-500">({{ $permissions->count() }})</span>
                                </div>

                                {{-- Permissions in Group --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 pl-6">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300 p-1 rounded" wire:key="perm-{{ $permission->id }}">
                                            <input
                                                type="checkbox"
                                                class="checkbox checkbox-sm"
                                                wire:model="selectedPermissions"
                                                value="{{ $permission->id }}"
                                            />
                                            <span class="text-sm">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <x-icon name="o-key" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                                <p>No permissions. Please create a permission first.</p>
                            </div>
                        @endforelse
                    </div>

                    <p class="text-xs text-gray-500 mt-2">
                        {{ count($selectedPermissions) }} permissions selected
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
                    :label="$editMode ? 'Update' : 'Create'"
                    type="submit"
                    class="btn-primary"
                    spinner="save"
                />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
