<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public Payment $payment,
    ) {}

    public function build()
    {
        $reference = $this->inquiry->booking_reference ?? $this->inquiry->getBookingReference();
        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'The Gazebo Events Place'));

        return $this->from($fromAddress, $fromName)
            ->subject('Payment Link - ' . $reference)
            ->replyTo($fromAddress, $fromName)
            ->view('emails.payment_link');
    }
}