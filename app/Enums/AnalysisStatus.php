<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case Normal = 'NORMAL';
    case Underutilized = 'UNDERUTILIZED';
    case CapacityPressure = 'CAPACITY_PRESSURE';
    case IdleWhilePowered = 'IDLE_WHILE_POWERED';
}
