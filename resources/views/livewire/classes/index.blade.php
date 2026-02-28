<?php

use App\Models\VirtualClass;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public bool $myModal = false;
    public ?int $classId = null;

    public string $name = '';
    public ?int $teacher_id = null;
    public string $description = '';
    public string $schedule = '';

    // Fetch classes with search
    public function classes()
    {
        return VirtualClass::query()
            ->when($this->search, fn(Builder $q) =>
                $q->where('name', 'like', "%$this->search%")
                  ->orWhereHas('teacher', fn($tq) => $tq->where('name', 'like', "%$this->search%"))
            )
            ->with('teacher')
            ->paginate(20);
    }

    // Teachers dropdown (role = teacher)
    public function teachers(): array
    {
        return User::where('role', 'teacher')
            ->select('id', 'name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
            ->prepend(['id' => '', 'name' => 'Select Teacher'])
            ->toArray();
    }

    // Open create modal
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Open edit modal
    public function edit(VirtualClass $class): void
    {
        $this->classId = $class->id;
        $this->name = $class->name;
        $this->teacher_id = $class->teacher_id;
        $this->description = $class->description;
        $this->schedule = $class->schedule;
        $this->myModal = true;
    }

    // Save class
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|min:3',
            'teacher_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'schedule' => 'nullable|string',
        ]);

        $class = $this->classId ? VirtualClass::findOrFail($this->classId) : new VirtualClass();
        $class->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Class saved!', description: $this->classId ? 'Class updated successfully.' : 'New class created.');
    }

    // Delete class
    public function delete($id): void
    {
        VirtualClass::findOrFail($id)->delete();
        $this->warning(title: 'Class deleted!', description: 'The class has been removed.');
    }

    // Clear search
    public function clearFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
        $this->info(title: 'Filters cleared');
    }

    // Reset form fields
    public function resetForm(): void
    {
        $this->reset(['classId', 'name', 'teacher_id', 'description', 'schedule']);
    }

    // Data for Blade
    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'teacher.name', 'label' => 'Teacher'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'schedule', 'label' => 'Schedule'],
        ];

        return [
            'classes' => $this->classes(),
            'teachers' => $this->teachers(),
            'headers' => $headers,
        ];
    }
};

 ?>

<div>
    <x-header title="Classes" separator progress-indicator />

    {{-- Search + Clear --}}
    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search classes..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="clearFilters" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>

        <x-button label="Add Class" class="btn-primary" @click="$wire.create()" />
    </div>

    {{-- Classes Table --}}
    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$classes" striped hoverable with-pagination>
            @scope('teacher.name', $class)
                <x-badge :label="$class->teacher->name ?? '—'" class="px-2 py-1 text-sm" />
            @endscope

            @scope('actions', $class)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $class->id }})" title="Edit" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm" wire:click="delete({{ $class->id }})"
                        onclick="return confirm('Are you sure?')" />
                </div>
            @endscope
        </x-table>

        @if ($classes->isEmpty())
            <x-alert title="No classes found" description="Try adjusting or clearing your filters."
                icon="o-exclamation-triangle" class="bg-base-100 border-none mt-4" />
        @endif
    </x-card>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="myModal" :title="$classId ? 'Edit Class' : 'Create Class'">
    <x-input label="Name" wire:model.defer="name" />
    <x-select label="Teacher" wire:model.defer="teacher_id" :options="$teachers" />
    <x-input label="Description" wire:model.defer="description" />
    <x-input label="Schedule" type="date" wire:model.defer="schedule" />

    <x-slot:actions>
        <x-button label="Cancel" @click="$wire.myModal = false" />
        <x-button label="Save" class="btn-primary" wire:click="save" spinner />
    </x-slot:actions>
</x-modal>


 
 </div>
