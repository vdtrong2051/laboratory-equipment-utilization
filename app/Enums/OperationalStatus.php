<?php

namespace App\Enums;

enum OperationalStatus: string
{
    case Off = 'OFF';
    case PoweredIdle = 'POWERED_IDLE';
    case Active = 'ACTIVE';
}
