<?php

use App\Models\Lesson;
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
    public ?int $lessonId = null;

    public string $course_id = '';
    public string $title = '';
    public string $content = '';
    public string $video_url = '';

    // Lessons list
    public function lessons()
    {
        return Lesson::query()
            ->with('course')
            ->when(
                $this->search,
                fn(Builder $q) => $q->where('title', 'like', "%$this->search%")
                    ->orWhereHas('course', fn($cq) => $cq->where('title', 'like', "%$this->search%"))
            )
            ->paginate(20);
    }

    // Courses dropdown
    public function courses()
    {
        return Course::select('id as id', 'title as name')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
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
    public function edit(Lesson $lesson): void
    {
        $this->lessonId = $lesson->id;
        $this->course_id = $lesson->course_id;
        $this->title = $lesson->title;
        $this->content = $lesson->content;
        $this->video_url = $lesson->video_url;
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|min:3',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
        ]);

        $lesson = $this->lessonId ? Lesson::findOrFail($this->lessonId) : new Lesson();
        $lesson->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Lesson saved!');
    }

    // Delete
    public function delete($id): void
    {
        Lesson::findOrFail($id)->delete();
        $this->warning(title: 'Lesson deleted!');
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['lessonId', 'course_id', 'title', 'content', 'video_url']);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'course.title', 'label' => 'Course'],
            ['key' => 'video_url', 'label' => 'Video URL'],
        ];

        return [
            'lessons' => $this->lessons(),
            'courses' => $this->courses(),
            'headers' => $headers,
        ];
    }
};
?>


<div>
    <x-header title="Lessons" separator progress-indicator />

    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search lessons..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="resetForm" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$lessons" striped hoverable with-pagination>
            @scope('actions', $lesson)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm"
                        wire:click="edit({{ $lesson->id }})" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm"
                        wire:click="delete({{ $lesson->id }})"
                        onclick="return confirm('Are you sure?')" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" title="{{ $lessonId ? 'Edit Lesson' : 'Create Lesson' }}">
        <x-select label="Course" wire:model.defer="course_id" :options="$courses" />
        <x-input label="Title" wire:model.defer="title" />
        <x-textarea label="Content" wire:model.defer="content" />
        <x-input label="Video URL" wire:model.defer="video_url" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6"
        @click="$wire.create()" />
</div>
