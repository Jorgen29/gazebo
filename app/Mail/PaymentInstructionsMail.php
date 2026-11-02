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
        // Use application sender details so replies go to the admin mailbox
        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'The Gazebo Events Place'));

        return $this->from($fromAddress, $fromName)
            ->subject($subject)
            ->replyTo($fromAddress, $fromName)
            ->view('emails.payment_instructions');
    }
}
