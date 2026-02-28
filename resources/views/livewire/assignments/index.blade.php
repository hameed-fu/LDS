<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Assignment;

new class extends Component {
    use WithPagination;

    public $classId = null;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getAssignments()
    {
        return Assignment::query()
            ->when($this->classId, function ($q) {
                $q->where('class_id', $this->classId);
            })
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%");
            })
            ->with(['virtualClass', 'creator', 'submissions', 'liveSession'])
            ->withCount('submissions')
            ->orderBy('due_date', 'asc')
            ->paginate(15);
    }

    public function with(): array
    {
        return [
            'assignments' => $this->getAssignments(),
        ];
    }
};
?>
<div class="min-h-screen bg-gray-50 p-6">
    <x-header title="Assignments" subtitle="Track student assignments" separator />

    <div class="mb-6">
        <x-input placeholder="Search assignments..." wire:model.live="search" icon="o-magnifying-glass" />
    </div>

    <x-card shadow class="overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
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
            <tbody class="divide-y">
                @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $assignment->title }}</td>
                        <td class="px-6 py-4">{{ $assignment->virtualClass->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $assignment->creator->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="@if($assignment->due_date < now()) text-red-600 font-semibold @else text-gray-700 @endif">
                                {{ $assignment->due_date->format('M d, Y H:i') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-semibold">{{ $assignment->max_score }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-medium">
                                {{ $assignment->submissions_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex gap-2 justify-end">
                                <a href="#" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                <a href="#" class="text-green-600 hover:text-green-900 text-sm">Grade</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No assignments found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="bg-white px-6 py-4 border-t">
            {{ $assignments->links() }}
        </div>
    </x-card>
</div>
