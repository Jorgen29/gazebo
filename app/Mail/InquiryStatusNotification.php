<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryStatusNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Inquiry $inquiry;
    public string $statusLabel;

    public function __construct(Inquiry $inquiry, string $statusLabel)
    {
        $this->inquiry = $inquiry;
        $this->statusLabel = ucfirst(strtolower($statusLabel));
    }

    public function build()
    {
        $adminAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $adminName = config('mail.from.name', env('MAIL_FROM_NAME', 'The Gazebo Events Place'));

        return $this->from($adminAddress, $adminName)
            ->replyTo($adminAddress, $adminName) // Client replies go to Admin
            ->subject('Reservation ' . $this->statusLabel . ' - ' . $this->inquiry->venue_title)
            ->view('emails.inquiry_status_notification');
    }
}
