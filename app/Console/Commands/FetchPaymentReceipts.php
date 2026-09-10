<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\PHPIMAP\ClientManager;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FetchPaymentReceipts extends Command
{
    protected $signature = 'inquiries:fetch-payments';
    protected $description = 'Fetch unread payment proofs from customer email replies sent in the past 2 days.';

    public function handle()
    {
        try {
            $username = env('IMAP_USERNAME');
            $host = env('IMAP_HOST', 'imap.gmail.com');
            $port = (int) env('IMAP_PORT', 993);
            $encryption = env('IMAP_ENCRYPTION', 'ssl');

            $this->info("--------------------------------------------------");
            $this->info("Connecting to IMAP Server...");
            $this->line("Host: {$host}:{$port} ({$encryption})");
            $this->line("Username: {$username}");
            $this->info("--------------------------------------------------");

            $cm = new ClientManager([
                'options' => [
                    'debug' => false,
                    'open' => [
                        'DISABLE_AUTHENTICATOR' => 'GSSAPI'
                    ]
                ],
                'accounts' => [
                    'default' => [
                        'host'          => $host,
                        'port'          => $port,
                        'encryption'    => $encryption,
                        'validate_cert' => false,
                        'username'      => $username,
                        'password'      => env('IMAP_PASSWORD'),
                        'protocol'      => 'imap',
                        'timeout'       => 10,
                        'options'       => [
                            'ssl' => [
                                'verify_peer'       => false,
                                'verify_peer_name'  => false,
                                'allow_self_signed' => true,
                            ],
                        ],
                    ]
                ]
            ]);

            $client = $cm->account('default');
            $client->connect();

            $this->info("STATUS: Connected successfully!");

            $folder = $client->getFolder('INBOX');
            $this->line("Active Folder: " . $folder->name);

            // Fetch unread messages received within the last 48 hours containing 'Booking Request'
            $twoDaysAgo = Carbon::now()->subDays(2);
            $this->line("Querying UNREAD messages since: " . $twoDaysAgo->toDateTimeString());

            $messages = $folder->query()
                ->unseen()
                ->since($twoDaysAgo)
                ->subject('Booking Request')
                ->setFetchBody(true)
                ->limit(10)
                ->get();

            $this->info("Found " . $messages->count() . " unread message(s) from the past 2 days.");

            if ($messages->count() === 0) {
                $this->warn("No unread matching emails found in the last 2 days.");
                return Command::SUCCESS;
            }

            foreach ($messages as $message) {
                $senderEmail = $message->getFrom()[0]->mail;
                $subject = $message->getSubject();

                $this->line("--------------------------------------------------");
                $this->line("Processing Email:");
                $this->line("From: " . $senderEmail);
                $this->line("Subject: " . $subject);

                // Skip emails sent from your own address
                if (strtolower($senderEmail) === strtolower($username)) {
                    $this->line("Skipping email sent by self.");
                    continue;
                }

                // Extract booking reference
                if (!preg_match('/Booking Request - R(\d+)/i', $subject, $matches)) {
                    $this->warn("No valid booking reference found in subject. Skipping...");
                    continue;
                }

                $reference = 'R' . $matches[1];

                // Match inquiry record by the generated booking reference
                $inquiry = Inquiry::where('booking_reference', $reference)
                    ->whereNull('payment_proof')
                    ->first();

                if (!$inquiry) {
                    $this->warn("No pending inquiry without payment proof found for reference {$reference}.");
                    continue;
                }

                if (!$message->hasAttachments()) {
                    $this->warn("Client reply has no image attachments.");
                    continue;
                }

                $attachmentSaved = false;

                foreach ($message->getAttachments() as $attachment) {
                    $mimeType = $attachment->getMimeType();
                    $this->line("Attachment: " . $attachment->getName() . " (" . $mimeType . ")");

                    if (str_contains($mimeType, 'image')) {
                        $extension = $attachment->getExtension() ?? 'png';
                        $filename = 'payment_proofs/' . $inquiry->id . '_' . time() . '.' . $extension;

                        Storage::disk('public')->put($filename, $attachment->getContent());

                        $inquiry->update([
                            'payment_proof' => $filename,
                        ]);

                        $this->info("SUCCESS: Saved receipt attachment and linked to Inquiry ID: {$inquiry->id}");
                        $attachmentSaved = true;
                        break;
                    }
                }

                if ($attachmentSaved) {
                    $message->setFlag('Seen');
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("IMAP Execution Error!");
            $this->error("Message: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
