<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Attendance;

new class extends Component {
    use WithPagination;

    public $classId = null;
    public $sessionId = null;
    public $status = null;

    public function getAttendance()
    {
        return Attendance::query()
            ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
            ->when($this->sessionId, fn($q) => $q->where('session_id', $this->sessionId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->with(['liveSession', 'virtualClass', 'student'])
            ->orderByDesc('date')
            ->orderByDesc('timestamp')
            ->paginate(15);
    }

    public function with(): array
    {
        return [
            'attendance' => $this->getAttendance(),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-50 p-6">
    <x-header title="Attendance" subtitle="Monitor class attendance" separator />

    {{-- <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-select label="Filter by Status" wire:model.live="status" :options="[
            ['value' => '', 'label' => 'All'],
            ['value' => 'present', 'label' => 'Present'],
            ['value' => 'absent', 'label' => 'Absent'],
            ['value' => 'late', 'label' => 'Late'],
            ['value' => 'excused', 'label' => 'Excused'],
        ]" />
    </div> --}}

    <x-card shadow class="overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Student</th>
                    <th class="px-6 py-3 text-left">Class</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Session</th>
                    <th class="px-6 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($attendance as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $record->student->name ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $record->virtualClass->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $record->date->format('M d, Y') }}</td>
                        <td class="px-6 py-4">{{ $record->liveSession->title ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($record->status === 'present') bg-green-100 text-green-800
                                @elseif($record->status === 'absent') bg-red-100 text-red-800
                                @elseif($record->status === 'late') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800
                                @endif
                            ">{{ ucfirst($record->status) }}</span>
                        </td>
                         
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No attendance records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="bg-white px-6 py-4 border-t">
            {{ $attendance->links() }}
        </div>
    </x-card>
</div>
