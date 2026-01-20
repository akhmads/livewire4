<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\QueueLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

class QueueListenerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ketika job di-queue atau mulai diproses
        Queue::before(function ($event) {
            if ($event instanceof JobProcessing) {
                $jobName = get_class($event->job);
                if (method_exists($event->job, 'resolveName')) {
                    $jobName = $event->job->resolveName();
                }
                $jobId = $event->job->getJobId();

                // Coba update log yang sudah ada ke processing
                $affected = QueueLog::where('job_name', $jobName)
                    ->where('status', 'pending')
                    ->latest()
                    ->update([
                        'job_id' => $jobId,
                        'status' => 'processing',
                        'started_at' => Carbon::now(),
                    ]);

                // Jika tidak ada log yang diupdate, buat baru
                if ($affected == 0) {
                    QueueLog::create([
                        'job_name' => $jobName,
                        'job_id' => $jobId,
                        'status' => 'processing',
                        'started_at' => Carbon::now(),
                    ]);
                }
                // Log::info('Processed log for ' . $jobName . ', affected: ' . $affected);
            }
        });

        Queue::failing(function (JobFailed $event) {
            $exception = $event->exception;
            $errorMessage = $exception->getMessage()
                . "\n\nFile: " . $exception->getFile()
                . "\nLine: " . $exception->getLine()
                . "\n\nStack trace:\n" . $exception->getTraceAsString();

            // Update QueueLog
            QueueLog::where('job_id', $event->job->getJobId())->update([
                'status' => 'failed',
                'finished_at' => Carbon::now(),
                'message' => $errorMessage,
            ]);

            $queueLog = QueueLog::where('job_id', $event->job->getJobId())->first();
            if ($queueLog && !empty($queueLog->data['user_id'])) {
                User::find($queueLog->data['user_id'])->notify(new \App\Notifications\JobFailing($queueLog));
            }
        });

        // Ketika job selesai diproses
        Queue::after(function ($event) {

            // Log::info('Job processed: ' . $event->job->getJobId());

            // Update QueueLog
            QueueLog::where('job_id', $event->job->getJobId())->update([
                'status' => 'completed',
                'finished_at' => Carbon::now(),
            ]);
        });
    }
}
