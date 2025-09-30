<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast;
    use WithPagination;
    #[Url]
    public string $search = '';

    public bool $myModal = false;
    public ?int $userId = null;

    public string $name = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $role = 'student'; // default role

    // Roles options
    public function roles(): array
    {
        return [['id' => 'admin', 'name' => 'Admin'], ['id' => 'teacher', 'name' => 'Teacher'], ['id' => 'student', 'name' => 'Student']];
    }

    // Fetch users with search
    public function users() 
    {
        return User::query()
            ->when(
                $this->search,
                fn(Builder $q) => $q
                    ->where('name', 'like', "%$this->search%")
                    ->orWhere('email', 'like', "%$this->search%")
                    ->orWhere('username', 'like', "%$this->search%")
                    ->orWhere('role', 'like', "%$this->search%"),
            )
           

            ->paginate(20);
    }

    // Create new user modal
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Edit existing user
    public function edit(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username;
        $this->role = $user->role ?? 'student';
        $this->password = '';
        $this->myModal = true;
    }

    // Save user
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'username' => 'required|string|min:3|unique:users,username,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:admin,teacher,student',
        ]);

        $user = $this->userId ? User::findOrFail($this->userId) : new User();
        $user->fill($data);

        if (!empty($this->password)) {
            $user->password = bcrypt($this->password);
        }

        $user->save();

        $this->resetForm();
        $this->myModal = false;

        // Toast notification
        $this->success(title: 'User saved!', description: $this->userId ? 'User updated successfully.' : 'New user created.');
    }

    // Delete user
    public function delete($id): void
    {
        User::findOrFail($id)->delete();

        $this->warning(title: 'User deleted!', description: 'The user has been removed.');
    }

    // Clear filters
    public function clearFilters(): void
    {
        $this->reset(['search']);
         $this->resetPage();
        $this->info(title: 'Filters cleared');
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['userId', 'name', 'email', 'username', 'password', 'role']);
        $this->role = 'student';
    }

    // Data for Blade
    public function with(): array
    {
        $headers = [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'username', 'label' => 'Username'], ['key' => 'role', 'label' => 'Role']];

        return [
            'users' => $this->users(),
            'roles' => $this->roles(),
            'headers' => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Users" separator progress-indicator />

    {{-- Search + Clear --}}
    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search users..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="clearFilters" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    {{-- Users Table --}}
    <x-card class="!p-0 sm:!p-2" shadow >
        <x-table :headers="$headers" :rows="$users" striped hoverable with-pagination>
            {{-- Role badge --}}
            @scope('role', $user)
                <x-badge :label="ucfirst($user->role)" class="px-2 py-1 text-sm" />
            @endscope

            {{-- Action buttons --}}
            @scope('actions', $user)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $user->id }})"
                        title="Edit" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm" wire:click="delete({{ $user->id }})"
                        onclick="return confirm('Are you sure?')" spinner class="btn-sm" />
                </div>
            @endscope
        </x-table>

        {{-- Empty state --}}
        @if ($users->isEmpty())
            <x-alert title="No users found" description="Try adjusting or clearing your filters."
                icon="o-exclamation-triangle" class="bg-base-100 border-none mt-4">
                 
            </x-alert>
        @endif
    </x-card>


    {{-- Create/Edit Modal --}}
    <x-modal wire:model="myModal" title="{{ $userId ? 'Edit User' : 'Create User' }}">
        <x-input label="Name" wire:model.defer="name" />
        <x-input label="Email" wire:model.defer="email" />
        <x-input label="Username" wire:model.defer="username" />
        <x-select label="Role" wire:model.defer="role" :options="$roles" />
        <x-input label="Password" type="password" wire:model.defer="password" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    {{-- Floating Add Button --}}
    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6 " @click="$wire.create()" />
</div>
