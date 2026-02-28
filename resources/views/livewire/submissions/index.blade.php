<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Submission;

new class extends Component {
    use WithPagination;

    public $assignmentId = null;
    public $status = null;

    public function getSubmissions()
    {
        return Submission::query()
            ->when($this->assignmentId, function ($q) {
                $q->where('assignment_id', $this->assignmentId);
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            })
            ->with(['assignment', 'student', 'gradedBy'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);
    }

    public function with(): array
    {
        return [
            'submissions' => $this->getSubmissions(),
        ];
    }
};
?>
<div class="min-h-screen bg-gray-50 p-6">
    <x-header title="Submissions" subtitle="Track student submissions" separator />

    <div class="mb-6">
        <x-select label="Filter by Status" wire:model.live="status" :options="[
            ['value' => '', 'label' => 'All'],
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'submitted', 'label' => 'Submitted'],
            ['value' => 'graded', 'label' => 'Graded'],
            ['value' => 'late', 'label' => 'Late'],
        ]" />
    </div>

    <x-card shadow class="overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Student</th>
                    <th class="px-6 py-3 text-left">Assignment</th>
                    <th class="px-6 py-3 text-left">Submitted</th>
                    <th class="px-6 py-3 text-center">Score</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Graded By</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($submissions as $submission)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $submission->student->name ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $submission->assignment->title ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $submission->submitted_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="px-6 py-4 text-center font-semibold">
                            @if($submission->score !== null)
                                <span class="inline-flex items-center justify-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm font-medium">
                                    {{ $submission->score }}/{{ $submission->assignment->max_score ?? 100 }}
                                </span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($submission->status === 'graded') bg-green-100 text-green-800
                                @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                                @elseif($submission->status === 'late') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">{{ ucfirst($submission->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $submission->gradedBy?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex gap-2 justify-end">
                                <a href="#" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                @if($submission->status !== 'graded')
                                    <a href="#" class="text-green-600 hover:text-green-900 text-sm">Grade</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No submissions found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="bg-white px-6 py-4 border-t">
            {{ $submissions->links() }}
        </div>
    </x-card>
</div>
