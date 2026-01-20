<?php

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Session;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'failed_per_page')]
    public int $perPage = 10;

    #[Session(key: 'failed_connection')]
    public string $connection = '';

    #[Session(key: 'failed_queue')]
    public string $queue = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'failed_at', 'direction' => 'desc'];

    public string $selectedException = '';
    public string $selectedJobTitle = '';
    public bool $showExceptionModal = false;

    public function mount(): void
    {
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'connection', 'label' => 'Connection'],
            ['key' => 'queue', 'label' => 'Queue'],
            ['key' => 'class', 'label' => 'Job'],
            ['key' => 'exception', 'label' => 'Exception'],
            ['key' => 'failed_at', 'label' => 'Failed At'],
        ];
    }

    public function failedJobs()
    {
        $query = DB::table('failed_jobs');

        if (!empty($this->connection)) {
            $query->where('connection', $this->connection);
        }

        if (!empty($this->queue)) {
            $query->where('queue', $this->queue);
        }

        return $query
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function connections(): array
    {
        return DB::table('failed_jobs')
            ->select('connection')
            ->distinct()
            ->pluck('connection')
            ->map(fn($conn) => ['id' => $conn, 'name' => $conn])
            ->toArray();
    }

    public function queues(): array
    {
        return DB::table('failed_jobs')
            ->select('queue')
            ->distinct()
            ->pluck('queue')
            ->map(fn($q) => ['id' => $q, 'name' => $q])
            ->toArray();
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
            $this->updateFilterCount();
        }
    }

    public function clear(): void
    {
        $this->success('Filters cleared.');
        $this->reset(['connection', 'queue']);
        $this->resetPage();
        $this->updateFilterCount();
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->connection)) $count++;
        if (!empty($this->queue)) $count++;
        $this->filterCount = $count;
    }

    public function retry($id): void
    {
        $failedJob = DB::table('failed_jobs')->find($id);
        if ($failedJob) {
            $exitCode = \Artisan::call('queue:retry', ['id' => $id]);
            if ($exitCode === 0) {
                $this->success('Job retried successfully.');
            } else {
                $this->error('Failed to retry job.');
            }
        }
    }

    public function delete($id): void
    {
        DB::table('failed_jobs')->delete($id);
        $this->success('Failed job deleted.');
    }

    public function retryAll(): void
    {
        $exitCode = \Artisan::call('queue:retry', ['--all' => true]);
        if ($exitCode === 0) {
            $this->success('All failed jobs retried.');
        } else {
            $this->error('Failed to retry all jobs.');
        }
    }

    public function deleteAll(): void
    {
        DB::table('failed_jobs')->delete();
        $this->success('All failed jobs deleted.');
    }

    public function showException(int $jobId): void
    {
        $failedJob = DB::table('failed_jobs')->find($jobId);
        if ($failedJob && $failedJob->exception) {
            $payload = json_decode($failedJob->payload, true);
            $this->selectedJobTitle = $payload['displayName'] ?? 'Unknown Job';
            $this->selectedException = $failedJob->exception;
            $this->showExceptionModal = true;
        }
    }

    public function closeExceptionModal(): void
    {
        $this->showExceptionModal = false;
        $this->selectedException = '';
        $this->selectedJobTitle = '';
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'failedJobs' => $this->failedJobs(),
            'connections' => array_merge([['id' => '', 'name' => 'All']], $this->connections()),
            'queues' => array_merge([['id' => '', 'name' => 'All']], $this->queues()),
        ];
    }
};
?>

<div>
    {{-- HEADER --}}
    <x-header title="Failed Jobs" separator progress-indicator>
        <x-slot:actions>
            {{-- <x-button label="Retry All" wire: click="retryAll" icon="o-arrow-path" class="btn-warning" /> --}}
            <x-button label="Delete All" wire:click="deleteAll" wire:confirm="Are you sure you want to delete all failed jobs?" icon="o-trash" class="btn-error" />
            <x-button label="Filters" @click="$wire.drawer = true" icon="o-funnel" badge="{{ $filterCount }}" />
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$failedJobs"
            :sort-by="$sortBy"
            with-pagination
            show-empty-text
            per-page="perPage"
        >
            @scope('cell_class', $job)
                @php
                    $payload = json_decode($job->payload, true);
                    $jobClass = $payload['displayName'] ?? 'Unknown';
                @endphp
                {{ $jobClass }}
            @endscope

            @scope('cell_exception', $job)
                @if($job->exception)
                    <x-button
                        label="{{ Str::limit(strip_tags($job->exception), 30) }}"
                        wire:click="showException({{ $job->id }})"
                        class="btn-sm btn-ghost text-error"
                        title="Click to view full exception"
                    />
                @else
                    <span class="text-gray-400">-</span>
                @endif
            @endscope

            @scope('cell_failed_at', $job)
                {{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i:s') }}
            @endscope

            @scope('actions', $job)
                <div class="flex gap-1">
                    {{-- <x-button
                        icon="o-arrow-path"
                        wire: click="retry({{ $job->id }})"
                        class="btn-sm btn-ghost text-success"
                        title="Retry"
                    /> --}}
                    <x-button
                        icon="o-trash"
                        wire:click="delete({{ $job->id }})"
                        wire:confirm="Are you sure?"
                        class="btn-sm btn-ghost text-error"
                        title="Delete"
                    />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-select label="Connection" wire:model.live="connection" :options="$connections" icon="o-server" />
        <x-select label="Queue" wire:model.live="queue" :options="$queues" icon="o-queue-list" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" />
            <x-button label="Close" icon="o-check" @click="$wire.drawer = false" class="btn-primary" />
        </x-slot:actions>
    </x-drawer>

    {{-- EXCEPTION MODAL --}}
    @if($showExceptionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeExceptionModal"></div>
                <div class="relative bg-base-100 rounded-lg max-w-4xl w-full p-6 shadow-xl">
                    <div class="flex items-center gap-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-lg font-bold text-error">Exception: {{ $selectedJobTitle }}</h3>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg max-h-[500px] overflow-auto">
                        <pre class="text-xs whitespace-pre-wrap font-mono">{{ $selectedException }}</pre>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <x-button
                            label="Copy"
                            @click="navigator.clipboard.writeText({{ json_encode($selectedException) }}); $wire.success('Copied to clipboard!')"
                            icon="o-clipboard"
                            class="btn-secondary"
                        />
                        <x-button label="Close" wire:click="closeExceptionModal" class="btn-primary" />
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
