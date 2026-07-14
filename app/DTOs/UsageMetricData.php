<?php

namespace App\DTOs;

final readonly class UsageMetricData
{
    public function __construct(
        public int $equipmentId,
        public int $totalBookedMinutes = 0,
        public int $totalPoweredMinutes = 0,
        public int $totalActiveMinutes = 0,
        public int $totalIdleMinutes = 0,
        public float $bookingUtilizationRate = 0.0,
        public float $actualUtilizationRate = 0.0,
        public float $poweredIdleRate = 0.0,
    ) {}
}
