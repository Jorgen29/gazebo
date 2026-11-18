<?php

namespace App\Mail;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundSucceededMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Refund $refund
    ) {
    }

    public function build()
    {
        $inquiry = $this->refund->payment->inquiry;
        $reference = $inquiry->booking_reference ?? $inquiry->getBookingReference();
        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'The Gazebo Events Place'));

        return $this->from($fromAddress, $fromName)
            ->subject('Refund confirmation - ' . $reference)
            ->replyTo($fromAddress, $fromName)
            ->view('emails.refund_succeeded');
    }
}
