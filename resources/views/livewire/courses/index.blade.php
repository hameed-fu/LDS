<?php

use App\Models\Course;
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
    public ?int $courseId = null;

    public string $title = '';
    public string $description = '';
    public string $level = 'beginner';
    public int $created_by = 0;

    // Levels options
    public function levels(): array
    {
        return [
            ['id' => 'beginner', 'name' => 'Beginner'],
            ['id' => 'intermediate', 'name' => 'Intermediate'],
            ['id' => 'advanced', 'name' => 'Advanced'],
        ];
    }

    // Instructor options
    public function instructors(): array
    {
        $list = User::where('role', 'teacher')
        ->select('id as id', 'name as name')
        ->get()
        ->toArray();

    return array_merge([['id' => '', 'name' => 'Please select']], $list);

    }

    // Fetch courses with search
    public function courses()
    {
        return Course::with('instructor')
            ->when(
                $this->search,
                fn (Builder $q) => $q->where('title', 'like', "%$this->search%")
                    ->orWhere('description', 'like', "%$this->search%")
                    ->orWhere('level', 'like', "%$this->search%")
            )
            ->paginate(20);
    }

    // Create new
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Edit existing
    public function edit(Course $course): void
    {
        $this->courseId = $course->id;
        $this->title = $course->title;
        $this->description = $course->description;
        $this->level = $course->level;
        $this->created_by = $course->created_by;
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'title' => 'required|string|min:3',
            'description' => 'required|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'created_by' => 'required|exists:users,id',
        ]);

        $course = $this->courseId ? Course::findOrFail($this->courseId) : new Course();
        $course->fill($data);
        $course->save();

        $this->resetForm();
        $this->myModal = false;

        $this->success(
            title: 'Course saved!',
            description: $this->courseId ? 'Course updated successfully.' : 'New course created.'
        );
    }

    // Delete
    public function delete($id): void
    {
        Course::findOrFail($id)->delete();
        $this->warning(title: 'Course deleted!', description: 'The course has been removed.');
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
        $this->reset(['courseId', 'title', 'description', 'level', 'created_by']);
        $this->level = 'beginner';
    }

    // Data for Blade
    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'level', 'label' => 'Level'],
            ['key' => 'instructor.name', 'label' => 'Instructor'],
        ];

        return [
            'courses' => $this->courses(),
            'levels' => $this->levels(),
            'instructors' => $this->instructors(),
            'headers' => $headers,
        ];
    }
};
?>
 

<div>
    <x-header title="Courses" separator progress-indicator />

    {{-- Search --}}
    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search courses..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="clearFilters" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    {{-- Courses Table --}}
    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$courses" striped hoverable with-pagination>
            {{-- Level --}}
            @scope('level', $course)
                <x-badge :label="ucfirst($course->level)" class="px-2 py-1 text-sm" />
            @endscope

            {{-- Action buttons --}}
            @scope('actions', $course)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $course->id }})"
                        title="Edit" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm" wire:click="delete({{ $course->id }})"
                        onclick="return confirm('Are you sure?')" spinner class="btn-sm" />
                </div>
            @endscope
        </x-table>

        {{-- Empty state --}}
        @if ($courses->isEmpty())
            <x-alert title="No courses found" description="Try adjusting or clearing your filters."
                icon="o-exclamation-triangle" class="bg-base-100 border-none  mt-4">
                 
            </x-alert>
        @endif
    </x-card>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="myModal" title="{{ $courseId ? 'Edit Course' : 'Create Course' }}">
        <x-input label="Title" wire:model.defer="title" />
        <x-textarea label="Description" wire:model.defer="description" rows="3" />
        <x-select label="Level" wire:model.defer="level" :options="$levels" />
        <x-select label="Instructor" wire:model.defer="created_by" :options="$instructors" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    {{-- Floating Add Button --}}
    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6"
        @click="$wire.create()" />
</div>
