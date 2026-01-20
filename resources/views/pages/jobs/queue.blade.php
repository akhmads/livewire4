<?php

use App\Models\QueueLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mary\Traits\Toast;

new class extends Component
{
    use WithPagination, Toast;

    #[Session(key: 'queue_jobs_per_page')]
    public int $perPage = 10;

    #[Session(key: 'queue_jobs_name')]
    public string $name = '';

    #[Session(key: 'queue_jobs_status')]
    public string $status = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public array $selected = [];
    public bool $selectAll = false;

    public string $selectedMessage = '';
    public string $selectedTitle = '';
    public bool $showModal = false;

    public function mount(): void
    {
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'job_name', 'label' => 'Job Name'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'progress', 'label' => 'Progress'],
            ['key' => 'started_at', 'label' => 'Started', 'format' => ['date', 'd-M-y, H:i']],
            ['key' => 'finished_at', 'label' => 'Finished', 'format' => ['date', 'd-M-y, H:i']],
            ['key' => 'duration', 'label' => 'Duration'],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
        ];
    }

    public function queueJobs(): LengthAwarePaginator
    {
        $query = QueueLog::query()
            ->orderBy(...array_values($this->sortBy));

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty($this->name)) {
            $query->where('job_name', 'like', '%' . $this->name . '%');
        }

        return $query->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'queueJobs' => $this->queueJobs(),
            'headers' => $this->headers(),
        ];
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
            $this->updateFilterCount();
        }
    }

    public function search(): void
    {
        $data = $this->validate([
            'status' => 'nullable|string',
        ]);
    }

    public function clear(): void
    {
        $this->success('Filters cleared.');
        $this->reset(['name', 'status']);
        $this->resetPage();
        $this->updateFilterCount();
        $this->drawer = false;
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->name)) $count++;
        if (!empty($this->status)) $count++;
        $this->filterCount = $count;
    }

    public function delete($queueLogId): void
    {
        \App\Models\QueueLog::find($queueLogId)->delete();
        $this->success("Queue log successfully deleted.");
    }

    public function deleteSelected(): void
    {
        \App\Models\QueueLog::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->success("Selected queue logs deleted.");
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->queueJobs()->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function showMessage($queueLogId): void
    {
        $queueLog = QueueLog::find($queueLogId);
        if ($queueLog && $queueLog->message) {
            $this->selectedTitle = 'Error Message for ' . $queueLog->job_name;
            $this->selectedMessage = $queueLog->message;
            $this->showModal = true;
        }
    }

    public function deleteAll(): void
    {
        QueueLog::query()->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->success('All queue logs deleted successfully.');
    }

    public function getStatusBadge($status): string
    {
        return match($status) {
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'completed' => 'badge-success',
            'failed' => 'badge-error',
            default => 'badge-ghost',
        };
    }
};
?>

<div>
    {{-- HEADER --}}
    <x-header title="Queue Jobs" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" badge="{{ $filterCount }}" />
            <x-button wire:click="deleteAll" wire:confirm="Are you sure you want to delete all queue logs?" spinner="deleteAll" icon="o-trash" class="btn-error" label="Delete All" />
            @if(count($selected) > 0)
            <x-button wire:click="deleteSelected" wire:confirm="Delete selected queue logs?" class="btn-error" icon="o-trash" label="Delete Selected ({{ count($selected) }})" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$queueJobs"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            show-empty-text
        >
            @scope('cell_checkbox', $queueJob)
                <x-checkbox wire:model="selected" :value="$queueJob->id" />
            @endscope

            @scope('cell_status', $queueJob)
                <x-badge :value="ucfirst($queueJob->status)" class="{{ $this->getStatusBadge($queueJob->status) }}" />
            @endscope

            @scope('cell_progress', $queueJob)
                <progress class="progress progress-primary w-20" value="{{ $queueJob->progress }}" max="100"></progress>
            @endscope

            @scope('cell_actions', $queueJob)
                <x-button wire:click="delete({{ $queueJob->id }})" spinner="delete({{ $queueJob->id }})" wire:confirm="Are you sure you want to delete this queue log?" icon="o-trash" class="btn btn-sm btn-ghost text-error" />
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-filter-drawer>
        <x-input label="Job Name" wire:model="name" />
        <x-select label="Status" wire:model="status" :options="['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'failed' => 'Failed']" />
    </x-filter-drawer>

    {{-- MODAL FOR MESSAGE --}}
    <x-modal wire:model="showModal">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">{{ $selectedTitle }}</h2>
            <pre class="whitespace-pre-wrap text-sm">{{ $selectedMessage }}</pre>
            <div class="flex justify-end mt-6">
                <x-button @click="$wire.showModal = false" class="btn">Close</x-button>
            </div>
        </div>
    </x-modal>
</div>
