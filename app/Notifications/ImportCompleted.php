<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $module;
    protected string $url;
    protected int $imported;
    protected array $errors;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $module, string $url, int $imported, array $errors = [])
    {
        $this->module = $module;
        $this->url = $url;
        $this->imported = $imported;
        $this->errors = $errors;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("{$this->module} Import Completed")
            ->line("Your {$this->module} import has been completed.")
            ->line("Successfully imported: {$this->imported} {$this->module}");

        if (!empty($this->errors)) {
            $message->line("Errors: " . implode(', ', $this->errors));
        }

        return $message->action("View {$this->module}", $this->url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "{$this->module} Import Completed",
            'message' => "Successfully imported {$this->imported} {$this->module}" .
                        (!empty($this->errors) ? ". Errors: " . implode(', ', $this->errors) : ''),
            'action_url' => $this->url,
            'action_text' => "View {$this->module}",
        ];
    }
}
