<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Submission;
use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $assignmentId = null;
    public $status = null;

    // Grading
    public $gradeSubmissionId;
    public $score;
    public $feedback;
    public $showGradeModal = false;

    /*
    |--------------------------------------------------------------------------
    | Grade Modal
    |--------------------------------------------------------------------------
    */

    public function openGradeModal($id)
    {
        $submission = Submission::findOrFail($id);

        $this->gradeSubmissionId = $submission->id;
        $this->score = $submission->score;
        $this->feedback = $submission->feedback;

        $this->showGradeModal = true;
    }

    public function saveGrade()
    {
        $submission = Submission::findOrFail($this->gradeSubmissionId);

        $submission->update([
            'score' => $this->score,
            'feedback' => $this->feedback,
            'status' => 'graded',
            'graded_by' => Auth::id(),
        ]);

        $this->showGradeModal = false;

        session()->flash('success', 'Submission graded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    */

    public function getSubmissions()
    {
        return Submission::query()
            ->when($this->assignmentId, fn($q) =>
                $q->where('assignment_id', $this->assignmentId)
            )
            ->when($this->status, fn($q) =>
                $q->where('status', $this->status)
            )
            ->with(['assignment', 'student'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);
    }

    public function with(): array
    {
        return [
            'submissions' => $this->getSubmissions(),
            'assignments' => Assignment::orderBy('title')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">

    <x-header title="Submissions"
              subtitle="Track and grade student submissions"
              separator />

    {{-- SUCCESS MESSAGE --}}
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
                    <th class="px-6 py-3 text-left">Student</th>
                    <th class="px-6 py-3 text-left">Assignment</th>
                    <th class="px-6 py-3 text-left">Submitted</th>
                    <th class="px-6 py-3 text-center">Score</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-gray-700">

                @forelse($submissions as $submission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">

                        {{-- Student --}}
                        <td class="px-6 py-4 font-medium">
                            {{ $submission->student->name ?? '—' }}
                        </td>

                        {{-- Assignment --}}
                        <td class="px-6 py-4">
                            {{ $submission->assignment->title ?? '—' }}
                        </td>

                        {{-- Submitted At --}}
                        <td class="px-6 py-4 text-sm">
                            {{ $submission->submitted_at?->format('M d, Y H:i') ?? '—' }}
                        </td>

                        {{-- Score --}}
                        <td class="px-6 py-4 text-center font-semibold">
                            @if($submission->score !== null)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">
                                    {{ $submission->score }}/{{ $submission->assignment->max_score ?? 100 }}
                                </span>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($submission->status === 'graded') bg-green-100 text-green-800
                                @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                                @elseif($submission->status === 'late') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex gap-3 justify-end">

                                {{-- View File --}}
                                @if($submission->file_path)
                                    <a href="{{ asset('storage/'.$submission->file_path) }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-900 text-sm">
                                        View
                                    </a>
                                @endif

                                {{-- Grade --}}
                                @if($submission->status !== 'graded')
                                    <button wire:click="openGradeModal({{ $submission->id }})"
                                        class="text-green-600 hover:text-green-900 text-sm">
                                        Grade
                                    </button>
                                @endif

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="px-6 py-6 text-center text-gray-500">
                            No submissions found
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

        <div class="bg-white dark:bg-gray-800 px-6 py-4 border-t">
            {{ $submissions->links() }}
        </div>

    </x-card>

    {{-- ========================= GRADE MODAL ========================= --}}
    <x-modal wire:model="showGradeModal" class="backdrop-blur">

        <x-card class="w-full max-w-lg">

            <x-slot:title>
                <div class="text-lg font-semibold">
                    Grade Submission
                </div>
            </x-slot:title>

            <div class="space-y-4">

                <x-input label="Score"
                         type="number"
                         wire:model="score" />

                <x-textarea label="Feedback"
                            wire:model="feedback"
                            rows="4" />

            </div>

            <x-slot:actions>
                <x-button label="Cancel"
                          wire:click="$set('showGradeModal', false)"
                          flat />

                <x-button label="Save Grade"
                          wire:click="saveGrade"
                          primary />
            </x-slot:actions>

        </x-card>

    </x-modal>

</div>