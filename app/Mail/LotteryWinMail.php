<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Lottary;
use App\Models\LottaryPrice;

class LotteryWinMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lottary;
    public $prize;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Lottary $lottary, LottaryPrice $prize)
    {
        $this->user = $user;
        $this->lottary = $lottary;
        $this->prize = $prize;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! You Won a Lottery Prize',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lottery_win',
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
        return $this->subject('Congratulations! You Won a Lottery Prize')
                    ->view('emails.lottery_win');
    }
}
