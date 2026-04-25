<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected AppointmentReminder $reminder) {}

    public function handle(): void
    {
        $appointment = $this->reminder->appointment;
        $patient = $appointment->patient;

        // Check opt-outs
        if ($this->reminder->reminder_type === 'sms' && $patient->do_not_text) return;
        if ($this->reminder->reminder_type === 'email' && $patient->do_not_email) return;

        try {
            if ($this->reminder->reminder_type === 'sms') {
                $this->sendSms($patient->phone, $this->reminder->message_content);
            } else {
                $this->sendEmail($patient->email, $this->reminder->message_content);
            }

            $this->reminder->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $this->reminder->update(['status' => 'failed']);
            throw $e;
        }
    }

    protected function sendSms(string $to, string $message): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if ($sid && $token && $from) {
            $client = new TwilioClient($sid, $token);
            $client->messages->create($to, ['from' => $from, 'body' => $message]);
        }
    }

    protected function sendEmail(string $to, string $message): void
    {
        Mail::raw($message, function ($mail) use ($to) {
            $mail->to($to)->subject('Appointment Reminder');
        });
    }
}
