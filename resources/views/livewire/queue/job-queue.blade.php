<div>
    {{-- Header Section --}}
    <x-header title="Job History" subtitle="History of all jobs that have been executed along with their status and execution duration" separator>
        <x-slot:actions>
            @if($stats['total'] > 0)
                <x-button
                    icon="o-trash"
                    wire:click="clearCompletedLogs"
                    wire:confirm="Delete all completed logs? This action cannot be undone."
                    spinner
                    class="btn-ghost btn-sm"
                    tooltip="Clear Completed"
                />
                <x-button
                    icon="o-trash"
                    wire:click="clearAllLogs"
                    wire:confirm="Delete ALL logs? This action cannot be undone."
                    spinner
                    class="btn-error btn-sm"
                    tooltip="Clear All"
                />
            @endif
            <x-button
                icon="o-arrow-path"
                wire:click="$refresh"
                spinner
                class="btn-ghost btn-sm"
                tooltip="Refresh"
            />
        </x-slot:actions>
    </x-header>

    {{-- Stats Cards --}}
    <div class="grid gap-4 mb-6 md:grid-cols-2 lg:grid-cols-5">
        <x-stat
            title="Total Jobs"
            :value="$stats['total']"
            icon="o-queue-list"
            color="text-info"
            tooltip="Total jobs yang pernah dijalankan"
        />
        <x-stat
            title="Hari Ini"
            :value="$stats['today']"
            icon="o-calendar"
            color="text-primary"
            tooltip="Jobs yang dijalankan hari ini"
        />
        <x-stat
            title="Completed"
            :value="$stats['completed']"
            icon="o-check-circle"
            color="text-success"
            tooltip="Jobs that completed successfully"
        />
        <x-stat
            title="Failed"
            :value="$stats['failed']"
            icon="o-x-circle"
            color="text-error"
            tooltip="Jobs that failed"
        />
        <x-stat
            title="Average Duration"
            :value="$stats['avg_duration']"
            icon="o-clock"
            color="text-warning"
            tooltip="Average job execution duration"
        />
    </div>

    {{-- Filters --}}
    <x-card class="mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            {{-- Search --}}
            <div class="flex-1">
                <x-input
                    wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass"
                    placeholder="Search job name, ID, or error message..."
                    clearable
                />
            </div>

            {{-- Date Range Filter --}}
            <div class="w-full md:w-40">
                @php
                    $dateOptions = [
                        ['id' => 'all', 'name' => 'All Time'],
                        ['id' => 'today', 'name' => 'Today'],
                        ['id' => 'yesterday', 'name' => 'Yesterday'],
                        ['id' => 'week', 'name' => 'This Week'],
                        ['id' => 'month', 'name' => 'This Month'],
                    ];
                @endphp
                <x-select
                    wire:model.live="dateRange"
                    :options="$dateOptions"
                    icon="o-calendar"
                />
            </div>

            {{-- Status Filter --}}
            <div class="w-full md:w-40">
                @php
                    $statusOptions = [
                        ['id' => 'all', 'name' => 'All Status'],
                        ['id' => 'pending', 'name' => 'Pending'],
                        ['id' => 'processing', 'name' => 'Processing'],
                        ['id' => 'completed', 'name' => 'Completed'],
                        ['id' => 'failed', 'name' => 'Failed'],
                    ];
                @endphp
                <x-select
                    wire:model.live="statusFilter"
                    :options="$statusOptions"
                    icon="o-funnel"
                />
            </div>
        </div>
    </x-card>

    {{-- Jobs Table --}}
    <x-card class="overflow-visible">
        @if($jobs->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Job Name</th>
                            <th>Status</th>
                            <th>Started At</th>
                            <th>Finished At</th>
                            <th>Duration</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                            <tr wire:key="job-{{ $job->id }}">
                                <td class="font-mono text-sm">{{ $job->id }}</td>
                                <td>
                                    <div class="font-medium">{{ $job->short_job_name }}</div>
                                    <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $job->job_name }}">
                                        {{ $job->job_name }}
                                    </div>
                                    @if($job->job_id)
                                        <div class="text-xs text-gray-400 font-mono">ID: {{ $job->job_id }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'pending' => 'badge-warning',
                                            'processing' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'failed' => 'badge-error'
                                        ];
                                        $statusIcons = [
                                            'pending' => 'o-clock',
                                            'processing' => 'o-arrow-path',
                                            'completed' => 'o-check-circle',
                                            'failed' => 'o-x-circle'
                                        ];
                                    @endphp
                                    <div class="flex items-center gap-1">
                                        <x-icon name="{{ $statusIcons[$job->status] ?? 'o-question-mark-circle' }}" class="w-4 h-4" />
                                        <x-badge
                                            :value="ucfirst($job->status)"
                                            class="{{ $statusClasses[$job->status] ?? 'badge-ghost' }}"
                                        />
                                    </div>
                                    @if($job->status === 'failed' && $job->exception_message)
                                        <div class="text-xs text-error mt-1 truncate max-w-xs" title="{{ $job->exception_message }}">
                                            {{ Str::limit($job->exception_message, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-sm">
                                    @if($job->started_at)
                                        <div>{{ $job->started_at->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $job->started_at->format('H:i:s') }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="text-sm">
                                    @if($job->finished_at)
                                        <div>{{ $job->finished_at->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $job->finished_at->format('H:i:s') }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($job->duration)
                                        <span class="font-mono text-sm font-medium {{ $job->duration_in_seconds > 60 ? 'text-warning' : 'text-success' }}">
                                            {{ $job->duration }}
                                        </span>
                                    @elseif($job->status === 'processing')
                                        <span class="loading loading-spinner loading-xs"></span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <x-button
                                            icon="o-eye"
                                            wire:click="showJobDetail({{ $job->id }})"
                                            spinner
                                            class="btn-ghost btn-sm"
                                            tooltip="View Details"
                                        />
                                        <x-button
                                            icon="o-trash"
                                            wire:click="deleteJob({{ $job->id }})"
                                            wire:confirm="Are you sure you want to delete this log?"
                                            spinner
                                            class="btn-ghost btn-sm text-error"
                                            tooltip="Delete Log"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <x-icon name="o-queue-list" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">No job history</h3>
                <p class="text-gray-500">
                    @if($search || $statusFilter !== 'all' || $dateRange !== 'all')
                        No jobs match your filters
                    @else
                        No jobs have been executed yet
                    @endif
                </p>
            </div>
        @endif
    </x-card>

    {{-- Job Detail Modal --}}
    <x-modal wire:model="showDetailModal" title="Job Details" class="" persistent>
        @if($jobDetail)
            <div class="space-y-4">
                {{-- Job Info --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Job Name</label>
                        <p class="font-medium">{{ $jobDetail->short_job_name }}</p>
                        <p class="text-xs text-gray-500 break-all">{{ $jobDetail->job_name }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Status</label>
                        <div class="mt-1">
                            @php
                                $statusClasses = [
                                    'pending' => 'badge-warning',
                                    'processing' => 'badge-info',
                                    'completed' => 'badge-success',
                                    'failed' => 'badge-error'
                                ];
                            @endphp
                            <x-badge
                                :value="ucfirst($jobDetail->status)"
                                class="{{ $statusClasses[$jobDetail->status] ?? 'badge-ghost' }}"
                            />
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="grid grid-cols-3 gap-4 p-4 bg-base-200 rounded-lg">
                    <div class="text-center">
                        <label class="text-xs text-gray-500 uppercase block">Started At</label>
                        <p class="font-mono text-sm">
                            {{ $jobDetail->started_at ? $jobDetail->started_at->format('d M Y, H:i:s') : '-' }}
                        </p>
                    </div>
                    <div class="text-center">
                        <label class="text-xs text-gray-500 uppercase block">Finished At</label>
                        <p class="font-mono text-sm">
                            {{ $jobDetail->finished_at ? $jobDetail->finished_at->format('d M Y, H:i:s') : '-' }}
                        </p>
                    </div>
                    <div class="text-center">
                        <label class="text-xs text-gray-500 uppercase block">Duration</label>
                        <p class="font-mono text-sm font-bold {{ ($jobDetail->duration_in_seconds ?? 0) > 60 ? 'text-warning' : 'text-success' }}">
                            {{ $jobDetail->duration ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- Job ID --}}
                @if($jobDetail->job_id)
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Job ID</label>
                        <p class="font-mono text-sm bg-base-200 p-2 rounded">{{ $jobDetail->job_id }}</p>
                    </div>
                @endif

                {{-- Payload --}}
                @php
                    $payload = $jobDetail->payload;
                @endphp
                @if($payload)
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Payload</label>
                        <div class="p-4 bg-base-200 rounded-lg overflow-x-auto max-h-96">
                            <pre class="text-xs whitespace-pre-wrap font-mono">{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>
                @endif

                {{-- Additional Data --}}
                @if($jobDetail->data)
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Additional Data</label>
                        <pre class="text-xs bg-base-200 p-3 rounded-lg overflow-x-auto">{{ json_encode($jobDetail->data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif

                {{-- Error Message --}}
                @if($jobDetail->message)
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Message / Error</label>
                        <div class="p-4 bg-error/10 border border-error/20 rounded-lg overflow-x-auto max-h-80">
                            <pre class="text-xs whitespace-pre-wrap font-mono text-error">{{ $jobDetail->message }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-button label="Close" @click="$wire.closeModal()" />
        </x-slot:actions>
    </x-modal>
</div>

@script
<script>
    $wire.on('success', (event) => {
        window.toast?.success?.(event.message) || alert(event.message);
    });

    $wire.on('error', (event) => {
        window.toast?.error?.(event.message) || alert(event.message);
    });
</script>
@endscript
