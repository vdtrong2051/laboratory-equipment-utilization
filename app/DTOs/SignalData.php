<?php

namespace App\DTOs;

use Carbon\CarbonImmutable;

final readonly class SignalData
{
    public function __construct(
        public string $equipmentCode,
        public string $signalType,
        public mixed $signalValue,
        public CarbonImmutable $recordedAt,
        public string $source,
        public ?bool $isActive = null,
        public ?array $rawPayload = null,
    ) {}
}
