<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentInstructionsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function build()
    {
        $reference = $this->inquiry->booking_reference ?? $this->inquiry->getBookingReference();
        $subject = 'Booking Request - ' . $reference;
        $fromAddress = $this->inquiry->email ?? config('mail.from.address', 'hello@example.com');
        $fromName = $this->inquiry->full_name ?? config('mail.from.name', 'Customer');

        return $this->from($fromAddress, $fromName)
            ->subject($subject)
            ->replyTo($fromAddress, $fromName)
            ->view('emails.payment_instructions');
    }
}
