<div>
    {{-- Header Section --}}
    <x-header title="Failed Jobs" subtitle="Monitor dan kelola job yang gagal dijalankan" separator>
        <x-slot:actions>
            @if($totalFailed > 0)
                <x-button
                    icon="o-arrow-path"
                    wire:click="retryAll"
                    wire:confirm="Apakah Anda yakin ingin retry semua failed jobs?"
                    spinner
                    class="btn-warning btn-sm"
                    label="Retry All"
                />
                <x-button
                    icon="o-trash"
                    wire:click="flushAll"
                    wire:confirm="Apakah Anda yakin ingin menghapus semua failed jobs? Tindakan ini tidak dapat dibatalkan."
                    spinner
                    class="btn-error btn-sm"
                    label="Flush All"
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

    {{-- Stats Card --}}
    <div class="mb-6">
        <x-stat
            title="Total Failed Jobs"
            :value="$totalFailed"
            icon="o-x-circle"
            color="text-error"
            tooltip="Total jobs that have failed"
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
                    placeholder="Cari job, queue, atau exception..."
                    clearable
                />
            </div>

            {{-- Queue Filter --}}
            <div class="w-full md:w-48">
                <x-select
                    wire:model.live="selectedQueue"
                    :options="array_merge(['all' => 'All Queues'], array_combine($queues, $queues))"
                    icon="o-queue-list"
                />
            </div>
        </div>
    </x-card>

    {{-- Failed Jobs Table --}}
    <x-card>
        @if($failedJobs->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Job Name</th>
                            <th>Queue</th>
                            <th>Exception</th>
                            <th>Failed At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failedJobs as $job)
                            <tr wire:key="failed-job-{{ $job->id }}">
                                <td class="font-mono text-sm">{{ $job->id }}</td>
                                <td>
                                    <div class="font-medium">{{ $job->job_name }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $job->uuid }}</div>
                                </td>
                                <td>
                                    <x-badge :value="$job->queue" class="badge-ghost" />
                                </td>
                                <td>
                                    <div class="max-w-md">
                                        <p class="text-sm truncate text-error">
                                            {{ $job->exception_message }}
                                        </p>
                                        <x-button
                                            label="View Full Exception"
                                            wire:click="showException({{ $job->id }})"
                                            class="btn-link btn-xs p-0 h-auto min-h-0 text-primary"
                                        />
                                    </div>
                                </td>
                                <td class="text-sm">
                                    {{ $job->failed_at->format('d M Y, H:i:s') }}
                                    <div class="text-xs text-gray-500">
                                        ({{ $job->failed_at->diffForHumans() }})
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-button
                                            icon="o-arrow-path"
                                            wire:click="retryJob({{ $job->id }})"
                                            wire:confirm="Apakah Anda yakin ingin retry job ini?"
                                            spinner
                                            class="btn-ghost btn-sm text-warning"
                                            tooltip="Retry Job"
                                        />
                                        <x-button
                                            icon="o-trash"
                                            wire:click="deleteJob({{ $job->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus job ini?"
                                            spinner
                                            class="btn-ghost btn-sm text-error"
                                            tooltip="Delete Job"
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
                {{ $failedJobs->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <x-icon name="o-check-circle" class="w-16 h-16 mx-auto mb-4 text-success" />
                <h3 class="mb-2 text-lg font-medium text-gray-900">Tidak ada failed jobs</h3>
                <p class="text-gray-500">
                    @if($search)
                        Tidak ada failed jobs yang sesuai dengan filter Anda
                    @else
                        Bagus! Tidak ada job yang gagal
                    @endif
                </p>
            </div>
        @endif
    </x-card>

    {{-- Exception Modal --}}
    <x-modal wire:model="showExceptionModal" title="Exception Details" class="backdrop-blur" persistent>
        <div class="space-y-4">
            <div class="p-4 bg-base-200 rounded-lg overflow-x-auto">
                <pre class="text-xs whitespace-pre-wrap font-mono">{{ $exceptionDetails }}</pre>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.closeModal()" />
        </x-slot:actions>
    </x-modal>
</div>

@script
<script>
    $wire.on('success', (event) => {
        window.toast.success(event.message);
    });

    $wire.on('error', (event) => {
        window.toast.error(event.message);
    });
</script>
@endscript
