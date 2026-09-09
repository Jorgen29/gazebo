<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentInstructionsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function build()
    {
        $subject = 'Booking Request Accepted - Payment Instructions for ' . $this->inquiry->venue_title . ' - Booking Request - R' . $this->inquiry->id;

        return $this->subject($subject)
                    ->replyTo('bacolodjorgen29@gmail.com', 'The Gazebo Events Place')
                    ->view('emails.payment_instructions');
    }
}