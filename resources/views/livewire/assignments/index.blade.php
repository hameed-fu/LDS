<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public $classId = null;
    public $search = '';

    // Assignment Form
    public $assignmentId;
    public $session_id;
    public $title;
    public $description;
    public $due_date;
    public $max_score = 100;

    public $showModal = false;

    // Grading
    public $gradingSubmissionId;
    public $gradingScore;
    public $gradingFeedback;
    public $showGradeModal = false;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'max_score' => 'required|integer|min:1',
        ];
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $assignment = Assignment::findOrFail($id);

        $this->assignmentId = $assignment->id;
        $this->title = $assignment->title;
        $this->description = $assignment->description;
        $this->due_date = $assignment->due_date->format('Y-m-d\TH:i');
        $this->max_score = $assignment->max_score;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $session = \App\Models\LiveSession::findOrFail($this->session_id);

        Assignment::updateOrCreate(
            ['id' => $this->assignmentId],
            [
                'class_id'   => $session->class_id,
                'session_id' => $this->session_id,
                'title'      => $this->title,
                'description'=> $this->description,
                'due_date'   => $this->due_date,
                'max_score'  => $this->max_score,
                'created_by' => Auth::id(),
            ],
        );

        $this->resetForm();
        $this->showModal = false;

        session()->flash('success', 'Assignment saved successfully.');
    }

    public function delete($id)
    {
        Assignment::findOrFail($id)->delete();
        session()->flash('success', 'Assignment deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | Grade Submission
    |--------------------------------------------------------------------------
    */

    public function openGradeModal($submissionId)
    {
        $submission = Submission::findOrFail($submissionId);

        $this->gradingSubmissionId = $submission->id;
        $this->gradingScore = $submission->score;
        $this->gradingFeedback = $submission->feedback;

        $this->showGradeModal = true;
    }

    public function saveGrade()
    {
        $submission = Submission::findOrFail($this->gradingSubmissionId);

        $submission->update([
            'score' => $this->gradingScore,
            'feedback' => $this->gradingFeedback,
            'status' => 'graded',
        ]);

        $this->showGradeModal = false;

        session()->flash('success', 'Submission graded successfully.');
    }

    public function resetForm()
    {
        $this->reset(['assignmentId','title','description','due_date']);
        $this->max_score = 100;
    }

    public function getAssignments()
    {
        return Assignment::query()
            ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
            ->when($this->search, fn($q) => $q->where('title','like',"%{$this->search}%"))
            ->with(['class','creator'])
            ->withCount([
                'submissions',
                'submissions as submitted_count' => fn($q) => $q->whereIn('status',['submitted','late','graded']),
                'submissions as graded_count' => fn($q) => $q->where('status','graded'),
                'submissions as late_count' => fn($q) => $q->where('status','late'),
            ])
            ->orderBy('due_date','asc')
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'classes' => \App\Models\VirtualClass::orderBy('name')->get(),
            'sessions' => \App\Models\LiveSession::orderBy('created_at','desc')->get(),
            'assignments' => $this->getAssignments(),
        ];
    }
};

?>


<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">

    <x-header title="Assignments" subtitle="Manage and track student assignments" separator />

    {{-- ========================= MODAL ========================= --}}
    <x-modal wire:model="showModal" class="backdrop-blur">
        <x-card class="w-full max-w-2xl">

            <x-slot:title>
                <div class="text-lg font-semibold">
                    {{ $assignmentId ? 'Edit Assignment' : 'Create Assignment' }}
                </div>
            </x-slot:title>

            <x-form wire:submit.prevent="save" class="space-y-6">

                {{-- Live Session --}}
                <x-select label="Live Session"
                    wire:model="session_id"
                    placeholder="Select Session"
                    :options="$sessions
                        ->map(fn($s) => [
                            'id' => $s->id,
                            'name' => $s->title . ' (' . $s->virtualClass->name . ')'
                        ])->toArray()"
                    option-value="id"
                    option-label="name" />

                {{-- Title --}}
                <x-input label="Assignment Title"
                    wire:model="title"
                    placeholder="Enter assignment title" />

                {{-- Description --}}
                <x-textarea label="Description"
                    wire:model="description"
                    placeholder="Enter assignment details"
                    rows="4" />

                {{-- Due Date --}}
                <x-input label="Due Date & Time"
                    type="datetime-local"
                    wire:model="due_date" />

                {{-- Max Score --}}
                <x-input label="Maximum Score"
                    type="number"
                    wire:model="max_score"
                    min="1" />

                <x-slot:actions>
                    <x-button label="Cancel"
                        wire:click="$set('showModal', false)"
                        flat />

                    <x-button label="{{ $assignmentId ? 'Update' : 'Create' }}"
                        type="submit"
                        spinner="save"
                        primary />
                </x-slot:actions>

            </x-form>
        </x-card>
    </x-modal>

    {{-- ========================= FILTERS ========================= --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6 mt-6">

        <div class="w-full md:w-1/3">
            <x-input placeholder="Search assignments..."
                wire:model.live="search"
                icon="o-magnifying-glass" />
        </div>

        <div class="w-full md:w-1/4">
            <select wire:model.live="classId"
                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Classes</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 text-right">
            <button wire:click="openModal"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                + Add Assignment
            </button>
        </div>

    </div>

    {{-- ========================= SUCCESS MESSAGE ========================= --}}
    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- ========================= TABLE ========================= --}}
    <x-card shadow class="overflow-hidden bg-white dark:bg-gray-800">

        <table class="w-full text-sm">

            <thead class="bg-gray-100 dark:bg-gray-700 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Title</th>
                    <th class="px-6 py-3 text-left">Class</th>
                    <th class="px-6 py-3 text-left">Creator</th>
                    <th class="px-6 py-3 text-left">Due Date</th>
                    <th class="px-6 py-3 text-center">Max Score</th>
                    <th class="px-6 py-3 text-center">Submissions</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-gray-700">

                @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">

                        {{-- Title --}}
                        <td class="px-6 py-4 font-medium">
                            {{ $assignment->title }}
                        </td>

                        {{-- Class --}}
                        <td class="px-6 py-4">
                            {{ $assignment->class->name ?? '—' }}
                        </td>

                        {{-- Creator --}}
                        <td class="px-6 py-4 text-sm">
                            {{ $assignment->creator->name ?? '—' }}
                        </td>

                        {{-- Due Date --}}
                        <td class="px-6 py-4 text-sm">
                            <span class="{{ $assignment->due_date < now()
                                    ? 'text-red-600 font-semibold'
                                    : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $assignment->due_date->format('M d, Y H:i') }}
                            </span>
                        </td>

                        {{-- Max Score --}}
                        <td class="px-6 py-4 text-center font-semibold">
                            {{ $assignment->max_score }}
                        </td>

                        {{-- Submission Stats --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center gap-1 text-xs">

                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                    Total: {{ $assignment->submissions_count }}
                                </span>

                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded">
                                    Graded: {{ $assignment->graded_count }}
                                </span>

                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded">
                                    Late: {{ $assignment->late_count }}
                                </span>

                            </div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex gap-3 justify-end">

                                <button wire:click="edit({{ $assignment->id }})"
                                    class="text-green-600 hover:text-green-900 text-sm">
                                    Edit
                                </button>

                                <button wire:click="delete({{ $assignment->id }})"
                                    onclick="confirm('Are you sure?') || event.stopImmediatePropagation()"
                                    class="text-red-600 hover:text-red-900 text-sm">
                                    Delete
                                </button>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            class="px-6 py-6 text-center text-gray-500">
                            No assignments found
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="bg-white dark:bg-gray-800 px-6 py-4 border-t dark:border-gray-700">
            {{ $assignments->links() }}
        </div>

    </x-card>
</div>