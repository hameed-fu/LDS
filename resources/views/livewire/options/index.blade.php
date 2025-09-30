<?php

use App\Models\Option;
use App\Models\Question;
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

    // For multiple options form
    public array $optionsForm = [['option_text' => '', 'is_correct' => false]];

    #--------------------------------------------------
    # Queries
    #--------------------------------------------------
   public function questionsWithOptions()
{
    return Question::with('options')
        ->whereHas('options') 
        ->when(
            $this->search,
            fn(Builder $q) => $q->where('question_text', 'like', "%$this->search%")
        )
        ->paginate(10);
}


    public function questions()
    {
        return Question::select('id', 'question_text as name')
            ->get()
            ->map(fn($q) => ['id' => $q->id, 'name' => $q->name])
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    #--------------------------------------------------
    # CRUD Methods
    #--------------------------------------------------
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    public function addOptionField(): void
    {
        $this->optionsForm[] = ['option_text' => '', 'is_correct' => false];
    }

    public function removeOptionField($index): void
    {
        unset($this->optionsForm[$index]);
        $this->optionsForm = array_values($this->optionsForm);
    }

    public function save(): void
    {
        $this->validate([
            'questionId' => 'required|exists:questions,id',
            'optionsForm.*.option_text' => 'required|string|min:1',
            'optionsForm.*.is_correct' => 'boolean',
        ]);

        foreach ($this->optionsForm as $opt) {
            Option::create([
                'question_id' => $this->questionId,
                'option_text' => $opt['option_text'],
                'is_correct' => $opt['is_correct'],
            ]);
        }

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Options saved!');
    }

    public function edit($questionId): void
{
    $question = Question::with('options')->findOrFail($questionId);

    $this->questionId = $question->id;
    $this->optionsForm = $question->options->map(fn($opt) => [
        'option_text' => $opt->option_text,
        'is_correct'  => (bool) $opt->is_correct,
        'id'          => $opt->id, // keep ID for updating
    ])->toArray();

    $this->myModal = true;
}

    public function delete($id): void
    {
        Option::findOrFail($id)->delete();
        $this->warning(title: 'Option deleted!');
    }

    public function resetForm(): void
    {
        $this->reset(['questionId', 'optionsForm']);
        $this->optionsForm = [['option_text' => '', 'is_correct' => false]];
    }

    public function with(): array
    {
        return [
            'questionsWithOptions' => $this->questionsWithOptions(),
            'questions' => $this->questions(),
        ];
    }
};

?>
<div>
    <x-header title="Questions & Options" separator progress-indicator />

    <!-- Search -->
    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search questions..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="$set('search','')" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <!-- Table -->
    <x-card class="!p-0 sm:!p-2" shadow>
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($questionsWithOptions as $q)
                    <tr>
                        <td>{{ $q->id }}</td>
                        <td class="font-semibold">{{ $q->question_text }}</td>
                        <td>
                            <ul class="space-y-1">
                                @foreach ($q->options as $opt)
                                    <li class="flex items-center gap-2">
                                        <span>{{ $opt->option_text }}</span>
                                        @if ($opt->is_correct)
                                            <x-badge label="Correct" class="badge-success" />
                                        @else
                                            <x-badge label="Wrong" class="badge-error" />
                                        @endif

                                        <!-- Actions -->
                                        <x-button sm icon="o-pencil" class="btn-ghost btn-sm"
                                            wire:click="edit({{ $opt->id }})" />
                                        <x-button sm icon="o-trash" class="btn-error btn-sm"
                                            wire:click="delete({{ $opt->id }})"
                                            onclick="return confirm('Are you sure?')" />
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-2">
            {{ $questionsWithOptions->links() }}
        </div>
    </x-card>

    <!-- Modal -->
    <x-modal wire:model="myModal" title="Add Options">
        <x-select label="Question" wire:model="questionId" :options="$questions" />

        <div class="space-y-3">
            @foreach ($optionsForm as $i => $opt)
                <div class="flex gap-2 items-center">
                    <x-input label="Option" wire:model.defer="optionsForm.{{ $i }}.option_text" />
                    <x-checkbox label="Correct" wire:model="optionsForm.{{ $i }}.is_correct" />
                    @if ($i > 0)
                        <x-button icon="o-trash" class="btn-error btn-sm"
                            wire:click="removeOptionField({{ $i }})" />
                    @endif
                </div>
            @endforeach

            <x-button label="Add More Option" class="btn-secondary" wire:click="addOptionField" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <!-- Floating Add Button -->
    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6" @click="$wire.create()" />
</div>
