<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LotteryPurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lottary;
    public $ticketNumber;
    public $transactionNumber;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $lottary, $ticketNumber, $transactionNumber)
    {
        $this->user = $user;
        $this->lottary = $lottary;
        $this->ticketNumber = $ticketNumber;
        $this->transactionNumber = $transactionNumber;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Lottery Ticket Purchase Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lottery_purchased',
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
        return $this->subject('Your Lottery Ticket Purchase Confirmation')
            ->view('emails.lottery_purchased');
    }
}
