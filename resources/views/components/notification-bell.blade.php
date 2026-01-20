<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Notifications\DatabaseNotification;

new class extends Component
{
    public $page = 1;
    public $notifications = []; // Disimpan sebagai array untuk stabilitas state
    public $notificationDrawer = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    /**
     * Data counter yang selalu diupdate setiap polling
     */
    public function with(): array
    {
        return [
            'unreadNotificationsCounter' => auth()->user()->unreadNotifications->count(),
        ];
    }

    /**
     * Mengambil data notifikasi terbaru
     */
    public function loadMore()
    {
        $this->page++;
        $this->loadNotifications();
    }

    private function loadNotifications()
    {
        // toArray() mencegah Livewire mencoba mensinkronisasi model yang mungkin sudah dihapus
        $newNotifications = auth()->user()->notifications()
            ->latest()
            ->forPage($this->page, 5)
            ->get()
            ->toArray();

        $this->notifications = collect($this->notifications)
            ->merge($newNotifications)
            ->unique('id')
            ->values()
            ->all();
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();

            // Update state local agar UI berubah instan (Optimistic UI)
            foreach ($this->notifications as &$n) {
                if ($n['id'] === $notificationId) {
                    $n['read_at'] = now()->toDateTimeString();
                }
            }
        }
    }

    public function deleteNotification($notificationId)
    {
        $notification = auth()->user()->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->delete();

            $this->notifications = collect($this->notifications)
                ->filter(fn($n) => $n['id'] !== $notificationId)
                ->values()
                ->all();
        }
    }

    public function clearAll()
    {
        auth()->user()->notifications()->delete();
        $this->notifications = [];
        $this->page = 1;
    }

    public function refreshNotifications()
    {
        $this->page = 1;
        $this->notifications = [];
        $this->loadNotifications();
    }

    #[Computed]
    public function hasMorePages()
    {
        $totalNotifications = auth()->user()->notifications()->count();
        return count($this->notifications) < $totalNotifications;
    }
};
?>

@placeholder
<div class="flex justify-center items-center">
    <span class="loading loading-spinner loading-sm text-primary"></span>
</div>
@endplaceholder

<div wire:poll.30s="refreshNotifications">
    {{-- BUTTON --}}
    <x-button class="btn-sm btn-ghost indicator" @click="$wire.notificationDrawer = true" icon="o-bell">
        @if ($unreadNotificationsCounter > 0)
            <x-badge value="{{ $unreadNotificationsCounter }}" class="badge-secondary badge-sm indicator-item" />
        @endif
    </x-button>

    {{-- DRAWER --}}
    <x-drawer wire:model="notificationDrawer" right class="lg:w-1/3 bg-base-200">
        <x-header title="Notifications" size="text-xl" separator class="top-0 pt-2 sticky bg-base-200 z-50 mb-4">
            <x-slot:actions>
                <x-button icon="o-arrow-path" class="btn-sm btn-ghost" wire:click="refreshNotifications" spinner="refreshNotifications" />
                @if(count($this->notifications) > 0)
                    <x-button label="Clear All" class="btn-sm btn-ghost text-error" wire:click="clearAll" wire:confirm="Hapus semua?" spinner="clearAll" />
                @endif
                <x-button icon="o-x-mark" class="btn-sm btn-ghost" @click="$wire.notificationDrawer = false" />
            </x-slot:actions>
        </x-header>

        <div class="h-full overflow-y-auto space-y-3 pb-20">
            @forelse ($this->notifications as $notification)
                <div wire:key="notif-{{ $notification['id'] }}"
                     class="p-4 bg-base-100 border border-base-300 rounded-lg flex justify-between items-start">

                    <div class="flex flex-col gap-1 pr-2">
                        <div class="text-sm font-semibold {{ $notification['read_at'] ? 'text-gray-400' : 'text-primary' }}">
                            {{ $notification['data']['title'] ?? 'System' }}
                        </div>
                        <div class="text-xs {{ $notification['read_at'] ? 'text-gray-400' : 'text-base-content' }}">
                            {{ $notification['data']['message'] ?? '' }}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-1">
                            {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                        </div>
                        @if(isset($notification['data']['action_url']))
                        <div>
                            <a href="{{ $notification['data']['action_url'] }}" class="btn btn-xs {{ $notification['read_at'] ? 'text-gray-400!' : 'btn-primary btn-soft' }}">
                                {{ $notification['data']['action_text'] ?? 'View' }}
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="flex flex-row gap-0">
                        @if(!$notification['read_at'])
                            <x-button
                                icon="o-check"
                                wire:click="markAsRead('{{ $notification['id'] }}')"
                                spinner="markAsRead('{{ $notification['id'] }}')"
                                class="btn-sm btn-circle btn-ghost text-success!"
                                title="Mark as Read"
                            />
                        @endif
                        <x-button
                            icon="o-trash"
                            wire:click="deleteNotification('{{ $notification['id'] }}')"
                            spinner="deleteNotification('{{ $notification['id'] }}')"
                            class="btn-sm btn-circle btn-ghost text-error!"
                            title="Delete Notification"
                        />
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-700 dark:text-gray-400 space-y-2">
                    <x-icon name="o-bell" class="w-10 h-10 mx-auto mb-2 text-gray-400" />
                    <div class="text-lg">No notifications yet.</div>
                    <div class="text-sm mt-1">Please check again later.</div>
                </div>
            @endforelse

            @if($this->hasMorePages)
                <div class="flex justify-center pt-4">
                    <x-button label="Load More" wire:click="loadMore" spinner="loadMore" class="btn-sm btn-primary btn-soft" />
                </div>
            @endif
        </div>
    </x-drawer>
</div>
