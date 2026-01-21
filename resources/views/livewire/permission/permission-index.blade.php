<div>
    {{-- Header --}}
    <x-header title="Permission Management" subtitle="Manage all available permissions in the application" separator>
        <x-slot:actions>
            @can('permissions.create')
            <x-button
                icon="o-plus"
                label="Add Permission"
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
                    placeholder="Search permission or group..."
                    clearable
                />
            </div>
        </div>
    </x-card>

    {{-- Permissions Table --}}
    <x-card>
        @if($permissions->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Group</th>
                            <th>Guard</th>
                            <th>Created At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                            <tr wire:key="permission-{{ $permission->id }}">
                                <td class="font-mono text-sm">{{ $permission->id }}</td>
                                <td>
                                    <div class="font-medium">{{ $permission->name }}</div>
                                </td>
                                <td>
                                    @if($permission->group)
                                        <x-badge :value="$permission->group" class="badge-ghost" />
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    <x-badge :value="$permission->guard_name" class="badge-info badge-sm" />
                                </td>
                                <td class="text-sm text-gray-500">
                                    {{ $permission->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        @can('permissions.edit')
                                        <x-button
                                            icon="o-pencil"
                                            wire:click="openEditModal({{ $permission->id }})"
                                            spinner
                                            class="btn-ghost btn-sm"
                                            title="Edit"
                                        />
                                        @endcan
                                        @can('permissions.delete')
                                        <x-button
                                            icon="o-trash"
                                            wire:click="delete({{ $permission->id }})"
                                            wire:confirm="Are you sure you want to delete this permission?"
                                            spinner
                                            class="btn-ghost btn-sm text-error"
                                            title="Delete"
                                        />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $permissions->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <x-icon name="o-key" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">No permissions</h3>
                <p class="text-gray-500">
                    @if($search)
                        No permissions matching your search
                    @else
                        No permissions added yet
                    @endif
                </p>
            </div>
        @endif
    </x-card>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Permission' : 'Add Permission'" box-class="max-w-2xl">
        <x-form wire:submit="save">
            <div class="space-y-6">
                <x-input
                    wire:model="name"
                    label="Permission Name"
                    placeholder="e.g. users.create, posts.edit"
                    hint="Use format: module.action (example: users.create)"
                    error-key="name"
                />

                <x-input
                    wire:model="group"
                    label="Group (Optional)"
                    placeholder="e.g. Users, Posts, Settings"
                    hint="Group to organize permissions"
                    list="group-list"
                    error-key="group"
                />
                <datalist id="group-list">
                    @foreach($groups as $g)
                        <option value="{{ $g }}">
                    @endforeach
                </datalist>
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
