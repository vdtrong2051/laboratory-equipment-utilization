<?php

namespace App\Services\Ingestion;

use App\DTOs\SignalData;

interface SignalIngestionService
{
    public function ingest(SignalData $signal): void;
}
