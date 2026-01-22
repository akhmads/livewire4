<?php

namespace App\Livewire\Permission;

use Mary\Traits\Toast;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
#[Title('User Role Assignment')]
class UserRoleIndex extends Component
{
    use Toast, WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $perPage = 15;

    // Modal
    public $showModal = false;
    public $userId = null;
    public $userName = '';
    public $selectedRoles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        Gate::authorize('user-roles.view');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function openAssignModal($userId)
    {
        // Reset state terlebih dahulu
        $this->reset(['selectedRoles']);

        $user = User::with('roles')->findOrFail($userId);
        $this->userId = $user->id;
        $this->userName = $user->name;

        // Load roles yang sudah di-assign
        $this->selectedRoles = $user->roles()->pluck('roles.id')->toArray();

        $this->showModal = true;
    }

    public function save()
    {
        Gate::authorize('user-roles.assign');
        $user = User::findOrFail($this->userId);
        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $user->syncRoles($roles);

        $this->success('Role successfully assigned to user');
        $this->closeModal();
    }

    public function removeRole($userId, $roleId)
    {
        Gate::authorize('user-roles.assign');
        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);
        $user->removeRole($role);

        $this->success('Role removed from user successfully');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName', 'selectedRoles']);
        $this->resetValidation();
    }

    public function getRolesProperty()
    {
        return Role::withCount('permissions')->orderBy('name')->get();
    }

    public function getRoleOptionsProperty()
    {
        return Role::orderBy('name')
            ->get()
            ->map(fn($role) => ['id' => $role->id, 'name' => $role->name])
            ->toArray();
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('id', $this->roleFilter);
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.permission.user-role-index', [
            'users' => $users,
            'roles' => $this->roles,
            'roleOptions' => $this->roleOptions,
        ]);
    }
}
