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
        $fromAddress = $this->inquiry->email ?? config('mail.from.address', 'hello@example.com');
        $fromName = $this->inquiry->full_name ?? config('mail.from.name', 'Customer');

        return $this->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject('Reservation ' . $this->statusLabel . ' - ' . $this->inquiry->venue_title)
            ->view('emails.inquiry_status_notification');
    }
}
