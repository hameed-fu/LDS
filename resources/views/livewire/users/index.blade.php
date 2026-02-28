<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    public bool $showModal = false;
    public ?int $userId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $role = 'student';
    public ?string $email_verified_at = null;

    public ?string $roleFilter = null;

    // Available roles list
    public array $roles = [
        // 'admin' => 'Admin',
        'teacher' => 'Teacher', 
        'student' => 'Student'
    ];

    // Mount
    public function mount(): void
    {
        // No need to load classes for users
    }

    // Validation rules
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($this->userId ? ',' . $this->userId : ''),
            'username' => 'required|string|unique:users,username' . ($this->userId ? ',' . $this->userId : ''),
            'role' => 'required|in:admin,teacher,student',
        ];

        // Only require password for new users
        if (!$this->userId) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        return $rules;
    }

    // Fetch users with filters
    public function users()
    {
        return User::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('username', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn(Builder $q) => $q->where('role', $this->roleFilter))
            ->where('id','!=', auth()->user()->id)
            ->orderBy('name', 'asc')
            ->paginate(10);
    }

    // Reset all filters
    public function resetFilters(): void
    {
        $this->reset(['search', 'roleFilter']);
        $this->resetPage(); // Reset pagination to page 1
    }

    // Check if any filter is active
    public function getHasFiltersProperty(): bool
    {
        return !empty($this->search) || !is_null($this->roleFilter);
    }

    // Open create modal
    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    // Open edit modal
    public function edit(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username;
        $this->role = $user->role;
        $this->email_verified_at = $user->email_verified_at ? $user->email_verified_at->format('Y-m-d\TH:i') : null;
        $this->showModal = true;
    }

    // Save or update user
    public function save(): void
    {
        $data = $this->validate($this->rules());

        // Remove password if empty (for updates)
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            // Hash the password
            $data['password'] = bcrypt($data['password']);
        }

        // Handle email verification
        if ($data['email_verified_at'] ?? false) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }

        $user = $this->userId ? User::findOrFail($this->userId) : new User();
        $user->fill($data);
        $user->save();

        $this->success(
            title: 'User Saved!',
            description: $this->userId ? 'User updated successfully.' : 'New user created.'
        );

        $this->closeModal();
    }

    // Delete user
    public function delete($id): void
    {
        // Prevent deleting yourself
        if ($id == auth()->id()) {
            $this->error(title: 'Cannot Delete', description: 'You cannot delete your own account.');
            return;
        }

        User::findOrFail($id)->delete();
        $this->warning(title: 'Deleted', description: 'User has been deleted.');
    }

    // Toggle email verification
    public function toggleVerification(User $user): void
    {
        $user->email_verified_at = $user->email_verified_at ? null : now();
        $user->save();

        $this->success(
            title: 'Email Verification Updated',
            description: $user->email_verified_at ? 'Email marked as verified.' : 'Email verification removed.'
        );
    }

    // Close modal
    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['userId', 'name', 'email', 'username', 'password', 'role', 'email_verified_at']);
        $this->role = 'student';
    }

    // Data for Blade
    public function with(): array
    {
        $headers = [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'username', 'label' => 'Username'],
            ['key' => 'role', 'label' => 'Role'],
            ['key' => 'email_verified_at', 'label' => 'Verified'],
        ];

        return [
            'users' => $this->users(),
            'headers' => $headers,
            'showModal' => $this->showModal,
            'hasFilters' => $this->hasFilters,
            'roles' => $this->roles,    
        ];
    }
};
?>
<div>
    <x-header title="Users" separator progress-indicator />

    {{-- Filters --}}
    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search users..." wire:model.live.debounce="search" class="w-50" icon="o-magnifying-glass" />
            
            <select wire:model.live="roleFilter" class="select select-bordered">
                <option value="">All Roles</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($hasFilters)
                <x-button icon="o-x-mark" wire:click="resetFilters" class="btn-ghost" title="Reset Filters" />
            @endif
        </div>
        <x-button icon="o-plus" @click="$wire.create()">Add User</x-button>
    </div>
 

    {{-- Users Table --}}
    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$users" striped hoverable with-pagination>
            @scope('role', $user)
                <x-badge :label="ucfirst($user->role)" class="px-2 py-1 text-sm" :color="match($user->role) {
                    'admin' => 'error',
                    'teacher' => 'warning',
                    'student' => 'success',
                    default => 'neutral',
                }" />
            @endscope

            @scope('email_verified_at', $user)
                @if($user->email_verified_at)
                    <x-badge label="Yes" class="px-2 py-1 text-sm badge-success" />
                @else
                    <x-badge label="No" class="px-2 py-1 text-sm badge-warning" />
                @endif
            @endscope

            @scope('actions', $user)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-envelope" 
                        :title="$user->email_verified_at ? 'Mark as unverified' : 'Mark as verified'"
                        wire:click="toggleVerification({{ $user->id }})"
                        spinner />
                    <x-button sm icon="o-pencil" wire:click="edit({{ $user->id }})" title="Edit" />
                    <x-button sm icon="o-trash" class="btn-error" 
                        wire:click="delete({{ $user->id }})"
                        onclick="return confirm('Are you sure you want to delete this user?')"
                        spinner />
                </div>
            @endscope
        </x-table>

        @if ($users->isEmpty())
            <x-alert title="No users found" 
                description="{{ $hasFilters ? 'Try adjusting or clearing your filters.' : 'No users in the system yet.' }}" 
                icon="o-exclamation-triangle" 
                class="bg-base-100 border-none mt-4" />
        @endif
    </x-card>

    {{-- Modal --}}
    <x-modal wire:model="showModal" title="{{ $userId ? 'Edit User' : 'Create User' }}">
        <x-input label="Full Name" wire:model.defer="name" required />
        <x-input label="Email" type="email" wire:model.defer="email" required />
        <x-input label="Username" wire:model.defer="username" required />
        
        @if(!$userId)
            <x-input label="Password" type="password" wire:model.defer="password" required />
        @else
            <x-input label="Password (leave blank to keep current)" type="password" wire:model.defer="password" />
        @endif
        
        <div class="form-control">
            <label class="label">
                <span class="label-text">Role</span>
            </label>
            <select wire:model.defer="role" class="select select-bordered w-full">
                <option value="">Select Role</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('role')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>
        
        <x-checkbox label="Email Verified" wire:model.defer="email_verified_at" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.closeModal()" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>
</div>