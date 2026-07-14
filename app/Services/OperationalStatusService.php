<?php

namespace App\Services;

use App\DTOs\OperationalStatusDecision;
use App\Models\Equipment;
use App\Rules\Equipment\OperationalStatusRule;

class OperationalStatusService
{
    public function __construct(
        private readonly OperationalStatusRule $operationalStatusRule,
    ) {}

    public function evaluateAndPersist(Equipment $equipment): OperationalStatusDecision
    {
        $decision = $this->operationalStatusRule->classify($equipment);

        $equipment->update([
            'current_operational_status' => $decision->status,
        ]);

        return $decision;
    }
}
