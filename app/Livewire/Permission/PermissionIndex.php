<?php

namespace App\Livewire\Permission;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
#[Title('Permission Management')]
class PermissionIndex extends Component
{
    use Toast, WithPagination, AuthorizesRequests;

    public $search = '';
    public $perPage = 10;

    // Form
    public $showModal = false;
    public $editMode = false;
    public $permissionId = null;
    public $name = '';
    public $group = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        Gate::authorize('permissions.view');
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name,' . $this->permissionId,
            'group' => 'nullable|string|max:255',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        Gate::authorize('permissions.create');
        $this->reset(['name', 'group', 'permissionId', 'editMode']);
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        Gate::authorize('permissions.edit');
        $permission = Permission::findOrFail($id);
        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->group = $permission->group ?? '';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editMode) {
            Gate::authorize('permissions.edit');
        } else {
            Gate::authorize('permissions.create');
        }

        $this->validate();

        if ($this->editMode) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update([
                'name' => $this->name,
                'group' => $this->group ?: null,
            ]);
            $this->success('Permission updated successfully');
        } else {
            Permission::create([
                'name' => $this->name,
                'group' => $this->group ?: null,
                'guard_name' => 'web',
            ]);
            $this->success('Permission created successfully');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Gate::authorize('permissions.delete');
        $permission = Permission::findOrFail($id);
        $permission->delete();
        $this->success('Permission deleted successfully');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'group', 'permissionId', 'editMode']);
        $this->resetValidation();
    }

    public function getGroupsProperty()
    {
        return Permission::whereNotNull('group')
            ->distinct()
            ->pluck('group')
            ->toArray();
    }

    public function render()
    {
        $permissions = Permission::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('group', 'like', '%' . $this->search . '%');
            })
            ->orderBy('group')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.permission.permission-index', [
            'permissions' => $permissions,
            'groups' => $this->groups,
        ]);
    }
}
