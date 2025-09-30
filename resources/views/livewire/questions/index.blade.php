<?php

use App\Models\Question;
use App\Models\Quiz;
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
    public ?int $questionId = null;

    public string $quiz_id = '';
    public string $question_text = '';
    public string $question_type = '';

    // Computed: Questions list
    public function questions()
    {
        return Question::query()
            ->with('quiz')
            ->when(
                $this->search,
                fn(Builder $q) => $q
                    ->where('question_text', 'like', "%$this->search%")
                    ->orWhereHas('quiz', fn($qq) => $qq->where('title', 'like', "%$this->search%"))
            )
            ->paginate(20);
    }

    // Dropdown for quizzes
    public function quizzes()
    {
        return Quiz::select('id', 'title as name')
            ->get()
            ->map(fn($q) => ['id' => $q->id, 'name' => $q->name])
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    // Open create modal
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Edit question
    public function edit(Question $question): void
    {
        $this->questionId = $question->id;
        $this->quiz_id = $question->quiz_id;
        $this->question_text = $question->question_text;
        $this->question_type = $question->question_type;
        $this->myModal = true;
    }

    // Save question
    public function save(): void
    {
        $data = $this->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string|min:3',
            'question_type' => 'required|in:mcq,true_false,short_answer',
        ]);

        $question = $this->questionId ? Question::findOrFail($this->questionId) : new Question();
        $question->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Question saved!');
    }

    // Delete question
    public function delete($id): void
    {
        Question::findOrFail($id)->delete();
        $this->warning(title: 'Question deleted!');
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset(['questionId', 'quiz_id', 'question_text', 'question_type']);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'question_text', 'label' => 'Question'],
            ['key' => 'question_type', 'label' => 'Type'],
            ['key' => 'quiz.title', 'label' => 'Quiz'],
        ];

        return [
            'questions' => $this->questions(),
            'quizzes'   => $this->quizzes(),
            'headers'   => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Questions" separator progress-indicator />

    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search questions..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="$set('search','')" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$questions" striped hoverable with-pagination>
            @scope('actions', $q)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $q->id }})" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm" wire:click="delete({{ $q->id }})"
                        onclick="return confirm('Are you sure?')" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" title="{{ $questionId ? 'Edit Question' : 'Create Question' }}">
        <x-select label="Quiz" wire:model="quiz_id" :options="$quizzes" />
        <x-input label="Question Text" wire:model.defer="question_text" />
        <x-select label="Question Type" wire:model="question_type"
            :options="[['id' => '-', 'name' => 'Please select'],['id' => 'mcq', 'name' => 'MCQ'], ['id' => 'true_false', 'name' => 'True/False'], ['id' => 'short_answer', 'name' => 'Short Answer']]" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6" @click="$wire.create()" />
</div>
