<?php

namespace App\Livewire\Permission;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
#[Title('Role Management')]
class RoleIndex extends Component
{
    use Toast, WithPagination, AuthorizesRequests;

    public $search = '';
    public $perPage = 15;

    // Form
    public $showModal = false;
    public $editMode = false;
    public $roleId = null;
    public $name = '';
    public $selectedPermissions = [];

    // Cache permissions to avoid repeated DB queries
    private static $permissionsCache = null;
    private static $permissionsByGroupCache = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            'selectedPermissions' => 'array',
        ];
    }

    public function mount(): void
    {
        Gate::authorize('roles.view');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        Gate::authorize('roles.create');
        $this->reset(['name', 'roleId', 'editMode', 'selectedPermissions']);
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        Gate::authorize('roles.edit');
        $role = Role::with('permissions:id,name')->findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (int)$id)->toArray();
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editMode) {
            Gate::authorize('roles.edit');
        } else {
            Gate::authorize('roles.create');
        }

        $this->validate();

        try {
            // Cast selectedPermissions to integers
            $permissionIds = array_map('intval', $this->selectedPermissions);

            if ($this->editMode) {
                $role = Role::findOrFail($this->roleId);
                $role->update(['name' => $this->name]);
                $role->syncPermissions(Permission::whereIn('id', $permissionIds)->get());
                $this->success('Role updated successfully');
            } else {
                $role = Role::create([
                    'name' => $this->name,
                    'guard_name' => 'web',
                ]);
                $role->syncPermissions(Permission::whereIn('id', $permissionIds)->get());
                $this->success('Role created successfully');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        Gate::authorize('roles.delete');
        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            $this->error('Super-admin role cannot be deleted');
            return;
        }

        $role->delete();
        $this->success('Role deleted successfully');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'roleId', 'editMode', 'selectedPermissions']);
        $this->resetValidation();
    }

    public function togglePermission($permissionId)
    {
        if (in_array($permissionId, $this->selectedPermissions)) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permissionId]);
        } else {
            $this->selectedPermissions[] = $permissionId;
        }
    }

    #[Computed]
    public function permissionsByGroup()
    {
        if (self::$permissionsByGroupCache === null) {
            $permissions = Permission::orderBy('group')->orderBy('name')->get();
            self::$permissionsByGroupCache = $permissions->groupBy(function ($permission) {
                return $permission->group ?? 'Ungrouped';
            });
        }
        return self::$permissionsByGroupCache;
    }

    public function toggleGroupPermissions($group)
    {
        $groupPermissions = Permission::where('group', $group)->pluck('id')->toArray();
        $allSelected = count(array_intersect($groupPermissions, $this->selectedPermissions)) === count($groupPermissions);

        if ($allSelected) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }
    }

    public function selectAllPermissions()
    {
        $this->selectedPermissions = Permission::pluck('id')->map(fn($id) => (int)$id)->toArray();
    }

    public function deselectAllPermissions()
    {
        $this->selectedPermissions = [];
    }

    public function render()
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.permission.role-index', [
            'roles' => $roles,
            'permissionsByGroup' => $this->permissionsByGroup,
        ]);
    }
}
