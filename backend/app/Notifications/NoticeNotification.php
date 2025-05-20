<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoticeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    public $recordId;
    public $title;
    public $content;
    public $file;

    // Constructor to accept the recoredId and title
    public function __construct($recordId, $title, $content, $file)
    {
        $this->recordId = $recordId;
        $this->title = $title;
        $this->content = $content;
        $this->file = $file;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approveUrl = url("/notice/approve/{$this->recordId}");
        $imageUrl = url("storage/{$this->file}");

        if ($this->file == null) {
            return (new MailMessage)
                ->subject('New Notice for Approval')
                ->view('emails.notice_approval', [
                    'title' => $this->title,
                    'content' => $this->content,
                    'approveUrl' => $approveUrl,
                ]);
        } else {
            return (new MailMessage)
                ->subject('New Notice for Approval')
                ->view('emails.notice_approval', [
                    'title' => $this->title,
                    'content' => $this->content,
                    'imageUrl' => $imageUrl,
                    'approveUrl' => $approveUrl,
                ]);
        }
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'notice_id' => $this->recordId,
            'content' => $this->content,
            'file' => $this->file
        ];
    }
}
