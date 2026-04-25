<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AutomationCampaign;
use App\Models\PatientRecall;
use App\Models\CommunicationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecallCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected AutomationCampaign $campaign) {}

    public function handle(): void
    {
        // Find overdue patients for this tenant/location
        $overdue = PatientRecall::where('tenant_id', $this->campaign->tenant_id)
            ->where('status', 'overdue')
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdue as $recall) {
            // Logic to send reminder based on campaign template
            $template = $this->campaign->template;
            
            // Log the communication
            CommunicationLog::create([
                'tenant_id' => $recall->tenant_id,
                'patient_id' => $recall->patient_id,
                'template_id' => $template->id,
                'channel' => $template->channel,
                'direction' => 'outbound',
                'body' => $template->body_text,
                'status' => 'queued',
                'sent_at' => now(),
            ]);
            
            // Dispatch sender job (e.g., SendAppointmentReminder or similar)
        }
    }
}
