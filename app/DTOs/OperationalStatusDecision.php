<?php

namespace App\DTOs;

use App\Enums\OperationalStatus;

final readonly class OperationalStatusDecision
{
    public function __construct(
        public OperationalStatus $status,
        public string $reason,
        public array $evidence = [],
    ) {}
}
