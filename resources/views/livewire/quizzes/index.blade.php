<?php

use App\Models\Quiz;
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
    public ?int $quizId = null;

    public string $lesson_id = '';
    public string $title = '';

    // Lessons dropdown
    public function lessons()
{
    return Lesson::with('course')
        ->select('id', 'title', 'course_id')
        ->get()
        ->map(fn($l) => [
            'id'   => $l->id,
            'name' => $l->title . ' (' . $l->course?->title . ')',
        ])
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
    public function edit(Quiz $quiz): void
    {
        $this->quizId = $quiz->id;
        $this->lesson_id = $quiz->lesson_id;
        $this->title = $quiz->title;
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|min:3',
        ]);

        $quiz = $this->quizId ? Quiz::findOrFail($this->quizId) : new Quiz();
        $quiz->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Quiz saved!');
    }

    // Delete
    public function delete($id): void
    {
        Quiz::findOrFail($id)->delete();
        $this->warning(title: 'Quiz deleted!');
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['quizId', 'lesson_id', 'title']);
    }

    public function quizzes()
    {
        return Quiz::query()
            ->with(['lesson.course']) // eager load course
            ->when(
                $this->search,
                fn(Builder $q) => $q
                    ->where('title', 'like', "%$this->search%")
                    ->orWhereHas('lesson', fn($lq) => $lq->where('title', 'like', "%$this->search%"))
                    ->orWhereHas('lesson.course', fn($cq) => $cq->where('title', 'like', "%$this->search%")),
            )
            ->paginate(20);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'lesson.title', 'label' => 'Lesson'],
            ['key' => 'lesson.course.title', 'label' => 'Course'],  
        ];

        return [
            'quizzes' => $this->quizzes(),
            'lessons' => $this->lessons(),
            'headers' => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Quizzes" separator progress-indicator />

    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search quizzes..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="$set('search','')" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$quizzes" striped hoverable with-pagination>
            @scope('actions', $quiz)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $quiz->id }})" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm" wire:click="delete({{ $quiz->id }})"
                        onclick="return confirm('Are you sure?')" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" title="{{ $quizId ? 'Edit Quiz' : 'Create Quiz' }}">
        <x-select label="Lesson" wire:model.defer="lesson_id" :options="$lessons" />
        <x-input label="Title" wire:model.defer="title" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6" @click="$wire.create()" />
</div>
