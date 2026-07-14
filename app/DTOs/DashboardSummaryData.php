<?php

namespace App\DTOs;

final readonly class DashboardSummaryData
{
    public function __construct(
        public array $cards = [],
        public array $tables = [],
        public array $charts = [],
    ) {}
}
