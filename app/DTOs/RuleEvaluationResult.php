<?php

namespace App\DTOs;

final readonly class RuleEvaluationResult
{
    public function __construct(
        public string $ruleName,
        public bool $matched,
        public string $severity = 'info',
        public ?string $message = null,
        public array $evidence = [],
    ) {}
}
