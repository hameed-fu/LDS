<?php

use App\Models\Exercise;
use App\Models\Lesson;
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
    public ?int $exerciseId = null;

    public string $lesson_id = '';
    public string $title = '';
    public string $description = '';
    public string $sample_code = '';
    public string $solution_code = '';

    // Exercises list
    public function exercises()
    {
        return Exercise::query()
            ->with('lesson')
            ->when(
                $this->search,
                fn(Builder $q) => $q->where('title', 'like', "%$this->search%")
                    ->orWhereHas('lesson', fn($lq) => $lq->where('title', 'like', "%$this->search%"))
            )
            ->paginate(20);
    }

    // Lessons dropdown
    public function lessons()
    {
        return Lesson::with('course')
        ->select('id as id', 'title as name', 'course_id')
            ->get()
            ->map(fn($l) => ['id' => $l->id, 'name' => $l->name . ' (' . $l->course?->title . ')',])
            
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    // Create
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Edit
    public function edit(Exercise $exercise): void
    {
        $this->exerciseId = $exercise->id;
        $this->lesson_id = $exercise->lesson_id;
        $this->title = $exercise->title;
        $this->description = $exercise->description;
        $this->sample_code = $exercise->sample_code;
        $this->solution_code = $exercise->solution_code;
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|min:3',
            'description' => 'nullable|string',
            'sample_code' => 'nullable|string',
            'solution_code' => 'nullable|string',
        ]);

        $exercise = $this->exerciseId ? Exercise::findOrFail($this->exerciseId) : new Exercise();
        $exercise->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Exercise saved!');
    }

    // Delete
    public function delete($id): void
    {
        Exercise::findOrFail($id)->delete();
        $this->warning(title: 'Exercise deleted!');
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['exerciseId', 'lesson_id', 'title', 'description', 'sample_code', 'solution_code']);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'lesson.title', 'label' => 'Lesson'],
        ];

        return [
            'exercises' => $this->exercises(),
            'lessons' => $this->lessons(),
            'headers' => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Exercises" separator progress-indicator />

    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search exercises..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="resetForm" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$exercises" striped hoverable with-pagination>
            @scope('actions', $exercise)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm"
                        wire:click="edit({{ $exercise->id }})" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm"
                        wire:click="delete({{ $exercise->id }})"
                        onclick="return confirm('Are you sure?')" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" title="{{ $exerciseId ? 'Edit Exercise' : 'Create Exercise' }}">
        <x-select label="Lesson" wire:model.defer="lesson_id" :options="$lessons" />
        <x-input label="Title" wire:model.defer="title" />
        <x-textarea label="Description" wire:model.defer="description" />
        <x-textarea label="Sample Code" wire:model.defer="sample_code" />
        <x-textarea label="Solution Code" wire:model.defer="solution_code" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6"
        @click="$wire.create()" />
</div>

