<?php

use App\Models\ClassEnrollment;
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
    public ?int $enrollmentId = null;
    public string $student_id = '';
    public string $class_id = '';

    // Fetch enrollments
    public function enrollments()
    {
        return ClassEnrollment::query()
            ->with(['student', 'virtualClass'])
            ->when(
                $this->search,
                fn(Builder $q) => $q->whereHas('student', fn($uq) => $uq->where('name', 'like', "%$this->search%"))
                                    ->orWhereHas('virtualClass', fn($cq) => $cq->where('name', 'like', "%$this->search%"))
            )
            ->paginate(20);
    }

    // Get students
    public function students()
    {
        return User::where('role', 'student')
            ->select('id as id', 'name as name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    // Get classes
    public function classes()
    {
        return VirtualClass::select('id as id', 'name as name')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    // Create new
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Edit existing
    public function edit(ClassEnrollment $enrollment): void
    {
        $this->enrollmentId = $enrollment->id;
        $this->student_id = $enrollment->student_id;
        $this->class_id = $enrollment->class_id;
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $enrollment = $this->enrollmentId
            ? ClassEnrollment::findOrFail($this->enrollmentId)
            : new ClassEnrollment();

        $enrollment->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Enrollment saved!');
    }

    // Delete
    public function delete($id): void
    {
        ClassEnrollment::findOrFail($id)->delete();
        $this->warning(title: 'Enrollment deleted!');
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
        $this->reset(['enrollmentId', 'student_id', 'class_id']);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'student.name', 'label' => 'Student'],
            ['key' => 'virtualClass.name', 'label' => 'Class'],
            ['key' => 'enrolled_at', 'label' => 'Enrolled At'],
            ['key' => 'status', 'label' => 'Status'],
        ];

        return [
            'enrollments' => $this->enrollments(),
            'students' => $this->students(),
            'classes' => $this->classes(),
            'headers' => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Class Enrollments" separator progress-indicator />

    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="clearFilters" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$enrollments" striped hoverable with-pagination>
             
            @scope('actions', $enrollment)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $enrollment->id }})" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm"
                        wire:click="delete({{ $enrollment->id }})"
                        onclick="return confirm('Are you sure?')" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" title="{{ $enrollmentId ? 'Edit Enrollment' : 'Create Enrollment' }}">
        <x-select label="Student" wire:model.defer="student_id" :options="$students" />
        <x-select label="Class" wire:model.defer="class_id" :options="$classes" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6"
        @click="$wire.create()" />
</div>
