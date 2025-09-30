<?php

use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;
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
    public string $user_id = '';
    public string $course_id = '';

    // Fetch enrollments
    public function enrollments()
    {
        return Enrollment::query()
            ->with(['user', 'course'])
            ->when(
                $this->search,
                fn(Builder $q) => $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%$this->search%"))
                                    ->orWhereHas('course', fn($cq) => $cq->where('title', 'like', "%$this->search%"))
            )
            ->paginate(20);
    }

    // Get users (students only)
    public function students()
    {
        return User::where('role', 'student')
            ->select('id as id', 'name as name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    // Get courses
    public function courses()
    {
        return Course::select('id as id', 'title as name')
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
    public function edit(Enrollment $enrollment): void
    {
        $this->enrollmentId = $enrollment->id;
        $this->user_id = $enrollment->user_id;
        $this->course_id = $enrollment->course_id;
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $enrollment = $this->enrollmentId
            ? Enrollment::findOrFail($this->enrollmentId)
            : new Enrollment();

        $enrollment->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Enrollment saved!');
    }

    // Delete
    public function delete($id): void
    {
        Enrollment::findOrFail($id)->delete();
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
        $this->reset(['enrollmentId', 'user_id', 'course_id']);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'user.name', 'label' => 'Student'],
            ['key' => 'course.title', 'label' => 'Course'],
            ['key' => 'enrolled_at', 'label' => 'Enrolled At'],
        ];

        return [
            'enrollments' => $this->enrollments(),
            'students' => $this->students(),
            'courses' => $this->courses(),
            'headers' => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Enrollments" separator progress-indicator />

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
        <x-select label="Student" wire:model.defer="user_id" :options="$students" />
        <x-select label="Course" wire:model.defer="course_id" :options="$courses" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6"
        @click="$wire.create()" />
</div>
