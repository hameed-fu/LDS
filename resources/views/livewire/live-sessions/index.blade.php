<?php

use Livewire\Volt\Component;
use App\Models\LiveSession;
use App\Models\VirtualClass;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    // Modal state
    public bool $showModal = false;
    public bool $isEdit = false;

    // form fields
    public ?int $sessionId = null;
    public string $title = '';
    public string $description = '';
    public string $scheduled_at = '';
    public string $meeting_url = '';
    public string $meeting_code = '';
    public $class_id = '';

    protected $rules = [
        'class_id' => 'required|exists:classes,id',
        'title' => 'required|min:3',
        'scheduled_at' => 'required|date',
        'meeting_url' => 'nullable|url',
        'description' => 'nullable|string',
    ];

    // Load data for page
    public function with()
    {
        return [
            'classes' => VirtualClass::orderBy('name')
                ->get()
                ->prepend(['id' => '', 'name' => 'Select class']),
            'sessions' => LiveSession::with('virtualClass')
                ->when($this->class_id, function ($q) {
                    $q->where('class_id', $this->class_id);
                })
                ->orderBy('scheduled_at', 'desc')
                ->paginate(100),
        ];
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isEdit = false;
        $code = Str::random(16);
        // generate random meeting URL
        $this->meeting_url = url('/meeting/' . $code);
        $this->meeting_code = $code;

        $this->showModal = true;
    }

    public function edit($id)
    {
        $session = LiveSession::findOrFail($id);

        $this->sessionId = $session->id;
        $this->class_id = $session->class_id;
        $this->title = $session->title;
        $this->description = $session->description;
        $this->scheduled_at = Carbon::parse($session->scheduled_at)->format('Y-m-d\TH:i');
        $this->meeting_url = $session->meeting_url;
        $this->meeting_code = $session->meeting_code;

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $session_number = LiveSession::where('class_id', $this->class_id)->count() + 1;

        LiveSession::updateOrCreate(
            ['id' => $this->sessionId],
            [
                'class_id' => $this->class_id,
                'title' => $this->title,
                'description' => $this->description,
                'scheduled_at' => Carbon::parse($this->scheduled_at),
                'meeting_url' => $this->meeting_url,
                'meeting_code' => $this->meeting_code,
                'session_number' => $this->sessionId ? LiveSession::find($this->sessionId)->session_number : $session_number,
            ],
        );

        $this->resetForm();
        $this->showModal = false;
        $this->success('Session saved successfully!');
    }

    public function delete($id)
    {
        LiveSession::findOrFail($id)->delete();
        $this->success(title: 'Session deleted!');
    }

    private function resetForm()
    {
        $this->reset(['sessionId', 'class_id', 'title', 'description', 'scheduled_at', 'meeting_url', 'isEdit']);
    }
};
?>

<div class="min-h-screen bg-gray-50 p-6">

    <x-header title="Live Sessions" subtitle="Manage virtual class sessions" separator />

    <div class="mb-4 flex justify-between items-center">

        <div class="w-64">

            {{-- <x-select label="Class" wire:model.defer="filter_class_id" :options="$classes" /> --}}
        </div>

        <x-button wire:click="openModal" class="btn-primary">
            + Add New Session
        </x-button>
    </div>

    <div class="space-y-4">
        @forelse($sessions as $session)
            <div class="bg-white p-4 rounded-lg shadow flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg">{{ $session->title }}</h3>

                    <div class="text-sm text-gray-500">
                        {{ $session->virtualClass->name }}

                        — Scheduled: {{ $session->scheduled_at->format('M d, Y h:i A') }}
                    </div>

                    @if ($session->meeting_url)
                        <a href="{{ $session->meeting_url }}" target="_blank"
                            class="text-blue-500 text-sm hover:underline">
                            Join Meeting
                        </a>
                    @endif
                </div>

                <div class="flex gap-2">
                    <x-button icon="o-pencil" wire:click="edit({{ $session->id }})" class="btn-sm btn-ghost" />

                    <x-button icon="o-trash" wire:click="delete({{ $session->id }})" wire:confirm="Are you sure?"
                        class="btn-sm btn-error" />
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-10">
                No sessions found.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $sessions->links() }}
        </div>
    </div>

    {{-- Modal --}}
    <x-modal wire:model="showModal" title="{{ $isEdit ? 'Edit Session' : 'Create Session' }}">

        <div class="space-y-4">

            {{-- Class Select --}}
            <x-select label="Select Class" wire:model="class_id" :options="$classes" option-value="id"
                option-label="name" />

            {{-- Title --}}
            <x-input label="Title" wire:model="title" />

            {{-- Description --}}
            <x-textarea label="Description" wire:model="description" />

            {{-- Date Time --}}
            <x-input type="datetime-local" label="Date & Time" wire:model="scheduled_at" />

            {{-- Meeting URL --}}
            <x-input label="Meeting Code" wire:model="meeting_code" readonly />
            <x-input label="Meeting URL" wire:model="meeting_url" readonly />

        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showModal = false" />
            <x-button label="{{ $isEdit ? 'Update' : 'Save' }}" wire:click="save" class="btn-primary" spinner="save" />
        </x-slot:actions>

    </x-modal>

</div>
