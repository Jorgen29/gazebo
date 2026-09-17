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
        // Use application sender so replies go to admin mailbox by default
        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'The Gazebo Events Place'));

        return $this->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject('New Booking Request Received - ' . $this->inquiry->venue_title)
            ->view('emails.admin_notification');
    }
}
