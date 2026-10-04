<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Lottary;

class LottaryPrizesAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lottary;

    /**
     * Create a new message instance.
     */
    public function __construct(Lottary $lottary)
    {
        $this->lottary = $lottary;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lottary Prizes Added Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lottaries_prizes',
        );
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
        return $this->subject('🎉 New Lottary Announced for ' . $this->lottary->title)
                    ->view('emails.lottaries_prizes')
                    ->with([
                        'lottary' => $this->lottary,
                    ]);
    }
}
