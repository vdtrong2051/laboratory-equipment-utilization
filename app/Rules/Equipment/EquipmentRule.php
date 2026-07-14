<?php

namespace App\Rules\Equipment;

use App\DTOs\RuleEvaluationResult;
use App\Models\Equipment;

interface EquipmentRule
{
    public function evaluate(Equipment $equipment): RuleEvaluationResult;
}
