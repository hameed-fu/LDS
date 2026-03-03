<?php

use App\Models\Quiz;
use App\Models\LiveSession;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {

    use WithPagination, Toast;

    #[Url]
    public string $search = '';

    public bool $myModal = false;
    public ?int $quizId = null;

    public string $title = '';
    public string $session_id = '';

    /* ================================
        Sessions Dropdown
    ================================= */
    public function sessions()
    {
        return LiveSession::select('id','title')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->title
            ])
            ->prepend(['id' => '', 'name' => 'Please select'])
            ->toArray();
    }

    /* ================================
        Create
    ================================= */
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    /* ================================
        Edit
    ================================= */
    public function edit(Quiz $quiz): void
    {
        $this->quizId    = $quiz->id;
        $this->title     = $quiz->title;
        $this->session_id = $quiz->session_id;

        $this->myModal = true;
    }

    /* ================================
        Save
    ================================= */
    public function save(): void
    {
        $data = $this->validate([
            'title'      => 'required|string|min:3',
            'session_id' => 'required|exists:live_sessions,id',
        ]);

        $quiz = $this->quizId
            ? Quiz::findOrFail($this->quizId)
            : new Quiz();

        $quiz->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;

        $this->success('Quiz saved successfully!');
    }

    /* ================================
        Delete
    ================================= */
    public function delete($id): void
    {
        Quiz::findOrFail($id)->delete();
        $this->warning('Quiz deleted!');
    }

    /* ================================
        Reset
    ================================= */
    public function resetForm(): void
    {
        $this->reset([
            'quizId',
            'title',
            'session_id'
        ]);
    }

    /* ================================
        Listing
    ================================= */
    public function quizzes()
    {
        return Quiz::query()
            ->with('liveSession')
            ->when($this->search, function (Builder $query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhereHas('liveSession', fn ($q) =>
                        $q->where('title', 'like', "%{$this->search}%")
                    );
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'quizzes' => $this->quizzes(),
            'sessions' => $this->sessions(),
            'headers' => [
                ['key' => 'id', 'label' => '#'],
                ['key' => 'title', 'label' => 'Quiz Title'],
                ['key' => 'liveSession.title', 'label' => 'Session'],
            ],
        ];
    }
};
?>

 <div>
    <x-header name="Quizzes" separator progress-indicator />

    <!-- Search + Create -->
    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input 
                placeholder="Search quizzes..." 
                wire:model.live.debounce.300ms="search" 
                icon="o-magnifying-glass" 
            />

            @if ($search)
                <x-button 
                    label="Clear" 
                    wire:click="$set('search','')" 
                    icon="o-x-mark" 
                    class="btn-ghost" 
                />
            @endif
        </div>
    </div>

    <!-- Table -->
    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table 
            :headers="$headers" 
            :rows="$quizzes" 
            striped 
            hoverable 
            with-pagination
        >
            @scope('actions', $quiz)
                <div class="flex gap-2 justify-center">
                    <x-button 
                        sm 
                        icon="o-pencil" 
                        class="btn-ghost btn-sm" 
                        wire:click="edit({{ $quiz->id }})" 
                    />

                    <x-button 
                        sm 
                        icon="o-trash" 
                        class="btn-error btn-sm" 
                        wire:click="delete({{ $quiz->id }})"
                        onclick="return confirm('Are you sure?')" 
                    />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- Modal -->
    <x-modal 
        wire:model="myModal" 
        name="{{ $quizId ? 'Edit Quiz' : 'Create Quiz' }}"
    >

        <!-- Session Dropdown -->
        <x-select 
            label="Live Session"
            wire:model.defer="session_id" 
            :options="$sessions" 
        />

        <!-- Quiz Title -->
        <x-input 
            label="Quiz Title" 
            wire:model.defer="title" 
        />

        <x-slot:actions>
            <x-button 
                label="Cancel" 
                @click="$wire.myModal = false" 
            />

            <x-button 
                label="Save" 
                class="btn-primary" 
                wire:click="save" 
                spinner 
            />
        </x-slot:actions>
    </x-modal>

    <!-- Floating Add Button -->
    <x-button 
        icon="o-plus" 
        class="btn-circle btn-primary btn-lg fixed bottom-6 right-6" 
        @click="$wire.create()" 
    />
</div>