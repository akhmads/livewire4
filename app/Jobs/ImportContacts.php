<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\User;
use App\Models\QueueLog;
use App\Notifications\ImportCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportContacts implements ShouldQueue
{
    use Queueable;

    protected QueueLog $queueLog;

    /**
     * Create a new job instance.
     */
    public function __construct(QueueLog $queueLog)
    {
        $this->queueLog = $queueLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $file = $this->queueLog->data['file'];

        // Check if file exists
        if (!file_exists($file)) {
            $user = User::find($this->queueLog->data['user_id']);
            if ($user) {
                $user->notify(new ImportCompleted('Contacts', route('contact.index'), 0, ['File not found']));
            }
            return;
        }

        $rows = SimpleExcelReader::create($file)->getRows();

        $imported = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                Contact::create([
                    'name' => $row['name'] ?? '',
                    'email' => $row['email'] ?? '',
                    'phone' => $row['phone'] ?? null,
                    'mobile' => $row['mobile'] ?? null,
                    'address' => $row['address'] ?? null,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + count($errors) + 1) . ": " . $e->getMessage();
            }
        }

        // Send notification to user
        $user = User::find($this->queueLog->data['user_id']);
        if ($user) {
            $user->notify(new ImportCompleted('Contacts', route('contact.index'), $imported, $errors));
        }

        // Clean up file with delay to ensure it's not being used
        if (file_exists($file)) {
            sleep(1);
            try {
                unlink($file);
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup import file: ' . $e->getMessage());
            }
        }
    }
}
