<?php

use App\Models\Quiz;
use App\Models\VirtualClass;
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

    public string $class_id = '';
    public string $name = '';
    public string $description = '';
    public ?int $duration = null;
    public ?int $total_marks = null;
    public ?string $start_time = null;
    public ?string $end_time = null;

    // Classes dropdown
    public function virtualClasses()
    {
        return VirtualClass::select('id', 'name')->get()
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
    public function edit(Quiz $quiz): void
    {
        $this->quizId = $quiz->id;
        $this->class_id = $quiz->class_id;
        $this->name = $quiz->name;
        $this->description = $quiz->description;
        $this->duration = $quiz->duration;
        $this->total_marks = $quiz->total_marks;
        $this->start_time = $quiz->start_time?->format('Y-m-d\TH:i');
        $this->end_time = $quiz->end_time?->format('Y-m-d\TH:i');
        $this->myModal = true;
    }

    // Save
    public function save(): void
    {
        $data = $this->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'total_marks' => 'nullable|integer|min:0',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        $quiz = $this->quizId ? Quiz::findOrFail($this->quizId) : new Quiz();
        $quiz->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(name: 'Quiz saved!');
    }

    // Delete
    public function delete($id): void
    {
        Quiz::findOrFail($id)->delete();
        $this->warning(name: 'Quiz deleted!');
    }

    // Reset form
    public function resetForm(): void
    {
        $this->reset([
            'quizId', 'class_id', 'name', 'description', 'duration',
            'total_marks', 'start_time', 'end_time'
        ]);
    }

    public function quizzes()
    {
        return Quiz::query()
            ->with(['virtualClass'])
            ->when(
                $this->search,
                fn(Builder $q) => $q
                    ->where('name', 'like', "%$this->search%")
                    ->orWhereHas('virtualClass', fn($cq) => $cq->where('name', 'like', "%$this->search%"))
            )
            ->paginate(20);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'name'],
            ['key' => 'virtualClass.name', 'label' => 'Class'],
            ['key' => 'duration', 'label' => 'Duration'],
            ['key' => 'total_marks', 'label' => 'Total Marks'],
        ];

        return [
            'quizzes' => $this->quizzes(),
            'virtualClasses' => $this->virtualClasses(),
            'headers' => $headers,
        ];
    }
};
?>

<div>
    <x-header name="Quizzes" separator progress-indicator />

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

    <x-modal wire:model="myModal" name="{{ $quizId ? 'Edit Quiz' : 'Create Quiz' }}">
        <x-select label="Class" wire:model.defer="class_id" :options="$virtualClasses" />
        <x-input label="name" wire:model.defer="name" />
        <x-input label="Description" wire:model.defer="description" />
        <x-input type="number" label="Duration (mins)" wire:model.defer="duration" />
        <x-input type="number" label="Total Marks" wire:model.defer="total_marks" />
        <x-input type="datetime-local" label="Start Time" wire:model.defer="start_time" />
        <x-input type="datetime-local" label="End Time" wire:model.defer="end_time" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6" @click="$wire.create()" />
</div>
