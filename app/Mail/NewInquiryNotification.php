<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewInquiryNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function build()
    {
        $fromAddress = $this->inquiry->email ?? config('mail.from.address', 'hello@example.com');
        $fromName = $this->inquiry->full_name ?? config('mail.from.name', 'Customer');

        return $this->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject('New Booking Request Received - ' . $this->inquiry->venue_title)
            ->view('emails.admin_notification');
    }
}
