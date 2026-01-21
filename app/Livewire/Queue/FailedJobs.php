<?php

namespace App\Livewire\Queue;

use App\Models\FailedJob;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
#[Title('Failed Jobs')]
class FailedJobs extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedQueue = 'all';
    public $perPage = 15;
    public $selectedJobId = null;
    public $showExceptionModal = false;
    public $exceptionDetails = '';

    protected $queryString = [
        'selectedQueue' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedQueue()
    {
        $this->resetPage();
    }

    public function getQueuesProperty()
    {
        return FailedJob::query()
            ->select('queue')
            ->distinct()
            ->orderBy('queue')
            ->pluck('queue')
            ->toArray();
    }

    public function retryJob($jobId)
    {
        $job = FailedJob::find($jobId);

        if ($job) {
            try {
                Artisan::call('queue:retry', ['id' => $job->uuid]);
                $job->delete();

                $this->dispatch('job-retried', jobName: $job->job_name);
                $this->dispatch('success', message: 'Job berhasil di-retry dan dipindahkan ke queue');
            } catch (\Exception $e) {
                $this->dispatch('error', message: 'Gagal retry job: ' . $e->getMessage());
            }
        }
    }

    public function deleteJob($jobId)
    {
        $job = FailedJob::find($jobId);

        if ($job) {
            try {
                Artisan::call('queue:forget', ['id' => $job->uuid]);
                $job->delete();

                $this->dispatch('job-deleted');
                $this->dispatch('success', message: 'Job berhasil dihapus');
            } catch (\Exception $e) {
                $this->dispatch('error', message: 'Gagal hapus job: ' . $e->getMessage());
            }
        }
    }

    public function retryAll()
    {
        try {
            $count = FailedJob::count();

            if ($count > 0) {
                Artisan::call('queue:retry', ['id' => 'all']);
                FailedJob::query()->delete();

                $this->dispatch('all-jobs-retried');
                $this->dispatch('success', message: "{$count} job berhasil di-retry");
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Gagal retry semua job: ' . $e->getMessage());
        }
    }

    public function flushAll()
    {
        try {
            $count = FailedJob::count();

            if ($count > 0) {
                Artisan::call('queue:flush');

                $this->dispatch('all-jobs-flushed');
                $this->dispatch('success', message: "{$count} job berhasil dihapus");
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Gagal hapus semua job: ' . $e->getMessage());
        }
    }

    public function showException($jobId)
    {
        $job = FailedJob::find($jobId);

        if ($job) {
            $this->selectedJobId = $jobId;
            $this->exceptionDetails = $job->exception;
            $this->showExceptionModal = true;
        }
    }

    public function closeModal()
    {
        $this->showExceptionModal = false;
        $this->exceptionDetails = '';
        $this->selectedJobId = null;
    }

    #[On('refresh-failed-jobs')]
    public function refreshJobs()
    {
        // This will trigger a re-render
    }

    public function render()
    {
        $query = FailedJob::query()->orderBy('failed_at', 'desc');

        // Filter by queue
        if ($this->selectedQueue !== 'all') {
            $query->where('queue', $this->selectedQueue);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('payload', 'like', '%' . $this->search . '%')
                  ->orWhere('queue', 'like', '%' . $this->search . '%')
                  ->orWhere('exception', 'like', '%' . $this->search . '%');
            });
        }

        $failedJobs = $query->paginate($this->perPage);

        return view('livewire.queue.failed-jobs', [
            'failedJobs' => $failedJobs,
            'queues' => $this->queues,
            'totalFailed' => FailedJob::count(),
        ]);
    }
}
