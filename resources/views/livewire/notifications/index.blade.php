<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Notification;

new class extends Component {
    use WithPagination;

    public $userId = null;
    public $type = null;
    public $showUnreadOnly = false;

    public function getNotifications()
    {
        return Notification::query()
            ->when($this->userId, function ($q) {
                $q->where('user_id', $this->userId);
            })
            ->when($this->type, function ($q) {
                $q->where('type', $this->type);
            })
            ->when($this->showUnreadOnly, function ($q) {
                $q->where('is_read', false);
            })
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->update(['is_read' => true]);
            $this->dispatch('notification.updated');
        }
    }

    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);
        $this->dispatch('all-notifications.marked-read');
    }

    public function deleteNotification($notificationId)
    {
        Notification::find($notificationId)?->delete();
        $this->dispatch('notification.deleted');
    }

    public function with(): array
    {
        return [
            'notifications' => $this->getNotifications(),
        ];
    }
};
?>
<div class="min-h-screen bg-gray-50 p-6">
    <x-header title="Notifications" subtitle="System notifications and messages" separator />

    <div class="mb-6 flex items-center gap-4">
        <x-select label="Filter by Type" wire:model.live="type" :options="[
            ['value' => '', 'label' => 'All Types'],
            ['value' => 'assignment', 'label' => 'Assignment'],
            ['value' => 'quiz', 'label' => 'Quiz'],
            ['value' => 'announcement', 'label' => 'Announcement'],
            ['value' => 'session', 'label' => 'Session'],
            ['value' => 'grade', 'label' => 'Grade'],
            ['value' => 'message', 'label' => 'Message'],
        ]" />
        <x-checkbox label="Unread Only" wire:model.live="showUnreadOnly" />
        <button wire:click="markAllAsRead" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Mark All as Read
        </button>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <x-card shadow class="border-l-4 @if(!$notification->is_read) border-l-blue-500 bg-blue-50 @else border-l-gray-300 @endif">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $notification->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                            <span>{{ $notification->user->name ?? '—' }}</span>
                            <span class="px-2 py-1 bg-gray-200 rounded">{{ ucfirst($notification->type) }}</span>
                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if(!$notification->is_read)
                        <button wire:click="markAsRead({{ $notification->id }})" class="ml-4 text-sm text-blue-600 hover:text-blue-900">
                            Mark as Read
                        </button>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card shadow class="text-center text-gray-500 py-8">
                No notifications found
            </x-card>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
