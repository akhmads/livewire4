<?php

namespace App\Livewire\Queue;

use App\Models\QueueLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
#[Title('Job History')]
class JobQueue extends Component
{
    use WithPagination;

    public $statusFilter = 'all';
    public $search = '';
    public $perPage = 15;
    public $dateRange = 'all';
    public $selectedJobId = null;
    public $showDetailModal = false;
    public $jobDetail = null;

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'dateRange' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function getStatsProperty()
    {
        return [
            'total' => QueueLog::count(),
            'today' => QueueLog::today()->count(),
            'completed' => QueueLog::completed()->count(),
            'failed' => QueueLog::failed()->count(),
            'processing' => QueueLog::processing()->count(),
            'pending' => QueueLog::pending()->count(),
            'avg_duration' => $this->getAverageDuration(),
        ];
    }

    protected function getAverageDuration()
    {
        $completedJobs = QueueLog::completed()
            ->whereNotNull('started_at')
            ->whereNotNull('finished_at')
            ->get();

        if ($completedJobs->isEmpty()) {
            return '0s';
        }

        $totalSeconds = $completedJobs->sum(function ($job) {
            return $job->duration_in_seconds ?? 0;
        });

        $avg = $totalSeconds / $completedJobs->count();

        if ($avg < 60) {
            return round($avg, 1) . 's';
        } elseif ($avg < 3600) {
            return round($avg / 60, 1) . 'm';
        }
        return round($avg / 3600, 1) . 'h';
    }

    public function showJobDetail($jobId)
    {
        $this->jobDetail = QueueLog::find($jobId);
        $this->selectedJobId = $jobId;
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->jobDetail = null;
        $this->selectedJobId = null;
    }

    public function deleteJob($jobId)
    {
        $job = QueueLog::find($jobId);

        if ($job) {
            $job->delete();
            $this->dispatch('success', message: 'Log job berhasil dihapus');
        }
    }

    public function clearCompletedLogs()
    {
        $count = QueueLog::completed()->count();
        QueueLog::completed()->delete();
        $this->dispatch('success', message: "{$count} completed logs berhasil dihapus");
    }

    public function clearAllLogs()
    {
        $count = QueueLog::count();
        QueueLog::truncate();
        $this->dispatch('success', message: "{$count} logs berhasil dihapus");
    }

    #[On('refresh-jobs')]
    public function refreshJobs()
    {
        // This will trigger a re-render
    }

    public function render()
    {
        $query = QueueLog::query()->orderBy('created_at', 'desc');

        // Filter by status
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Filter by date range
        switch ($this->dateRange) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('job_name', 'like', '%' . $this->search . '%')
                  ->orWhere('job_id', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%');
            });
        }

        $jobs = $query->paginate($this->perPage);

        return view('livewire.queue.job-queue', [
            'jobs' => $jobs,
            'stats' => $this->stats,
        ]);
    }
}
