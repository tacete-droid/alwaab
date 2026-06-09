<?php

namespace App\Domain\CRM\Services;

use App\Domain\CRM\Models\Lead;
use App\Enums\LeadStatus;

class LeadService
{
    public function updateStage(Lead $lead, LeadStatus $status): Lead
    {
        $lead->update(['status' => $status]);

        if ($status === LeadStatus::Won) {
            $lead->update(['probability' => 100]);
        }

        if ($status === LeadStatus::Lost) {
            $lead->update(['probability' => 0]);
        }

        return $lead->fresh(['contact', 'assignee']);
    }
}
