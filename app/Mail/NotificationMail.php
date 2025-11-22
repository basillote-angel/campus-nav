<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $title;
    public string $body;
    public string $type;
    public ?int $relatedId;
    public ?string $score;
    public string $appName;

    public function __construct(
        string $recipientName,
        string $title,
        string $body,
        string $type,
        ?int $relatedId = null,
        ?string $score = null
    ) {
        $this->recipientName = $recipientName;
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->relatedId = $relatedId;
        $this->score = $score;
        $this->appName = config('app.name', 'NavistFind');
    }

    public function build(): self
    {
        return $this
            ->subject($this->title)
            ->view('emails.notification')
            ->with([
                'recipientName' => $this->recipientName,
                'title' => $this->title,
                'body' => $this->body,
                'type' => $this->type,
                'relatedId' => $this->relatedId,
                'score' => $this->score,
                'appName' => $this->appName,
            ]);
    }
}










