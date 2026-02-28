<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\StudyGroup;
use App\Models\VirtualClass;
use Mary\Traits\Toast;
new class extends Component {
    use WithPagination, Toast;
    public $search = '';
    public $classId = null;

    // Modal properties
    public $showModal = false;
    public $groupId = null;
    public $name = '';
    public $description = '';
    public $max_members = 20;
    public $selectedClassId = null;

    public function getStudyGroups()
    {
        return StudyGroup::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
            ->with(['virtualClass', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function with(): array
    {
        return [
            'studyGroups' => $this->getStudyGroups(),
            'classes' => VirtualClass::all()->prepend(['id' => '', 'name' => 'Select class']),
        ];
    }

    // Open modal for create
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    // Open modal for edit
    public function editGroup($id)
    {
        $group = StudyGroup::findOrFail($id);
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->max_members = $group->max_members;
        $this->selectedClassId = $group->class_id;
        $this->showModal = true;
    }

    // Save group (create/update)
    public function saveGroup()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'selectedClassId' => 'required|exists:classes,id',
            'max_members' => 'required|integer|min:1',
        ]);

        if ($this->groupId) {
            $group = StudyGroup::findOrFail($this->groupId);
            $group->update([
                'name' => $this->name,
                'description' => $this->description,
                'class_id' => $this->selectedClassId,
                'max_members' => $this->max_members,
            ]);
        } else {
            StudyGroup::create([
                'name' => $this->name,
                'description' => $this->description,
                'class_id' => $this->selectedClassId,
                'max_members' => $this->max_members,
                'created_by' => auth()->id(),
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->success(title: 'Group added!');
    }

    public function deleteGroup($id)
    {
        StudyGroup::findOrFail($id)->delete();
        $this->success(title: 'Group deleted!');
    }

    private function resetForm()
    {
        $this->groupId = null;
        $this->name = '';
        $this->description = '';
        $this->max_members = 20;
        $this->selectedClassId = null;
    }
};
?>


<div class="min-h-screen bg-gray-50 p-6">
    <x-header title="Study Groups" subtitle="Manage study groups" separator />

    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-input label="Search by Name" wire:model.live="search" placeholder="Search..." />

        <x-select label="Filter by Class" wire:model.live="classId" :options="$classes" />

        <button class="btn btn-primary mt-6" wire:click="openModal">Create Study Group</button>
    </div>

    <x-card shadow class="overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Class</th>
                    <th class="px-6 py-3 text-left">Creator</th>
                    <th class="px-6 py-3 text-left">Max Members</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($studyGroups as $group)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $group->name }}</td>
                        <td class="px-6 py-4">{{ $group->virtualClass->name ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $group->creator->name ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $group->max_members }}</td>
                        <td class="px-6 py-4 text-right">

                            <x-button icon="o-pencil"
                                wire:click="editGroup({{ $group->id }})" class="btn-sm btn-ghost" />

                            <x-button icon="o-trash" wire:click="deleteGroup({{ $group->id }})"
                                wire:confirm="Are you sure?" class="btn-sm btn-error" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No study groups found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bg-white px-6 py-4 border-t">
            {{ $studyGroups->links() }}
        </div>
    </x-card>

    {{-- Modal --}}
    @if ($showModal)
        <x-modal wire:model.live="showModal">
            <x-slot name="title">{{ $groupId ? 'Edit Study Group' : 'Create Study Group' }}</x-slot>

            <div class="space-y-4 mb-4">
                <x-input label="Name" wire:model.live="name" />
                <x-textarea label="Description" wire:model.live="description" />
                <x-select label="Class" wire:model.live="selectedClassId" :options="$classes" />
                <x-input label="Max Members" type="number" wire:model.live="max_members" />
            </div>

            <div name="mt-2">
                <button class="btn btn-secondary mr-2" wire:click="$set('showModal', false)">Cancel</button>

                <x-button label="Save" class="btn-primary" wire:click="saveGroup" spinner />
            </div>
        </x-modal>
    @endif
</div>
