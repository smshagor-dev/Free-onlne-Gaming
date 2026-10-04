<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class pointConvertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $messageContent;

    /**
     * Create a new message instance.
     */
    public function __construct($title, $messageContent)
    {
        $this->title = $title;
        $this->messageContent = $messageContent;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.pointConvertMail')
                    ->with([
                        'title' => $this->title,
                        'messageContent' => $this->messageContent
                    ]);
    }
}
