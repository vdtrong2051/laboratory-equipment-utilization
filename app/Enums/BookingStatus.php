<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Booked = 'BOOKED';
    case CheckedIn = 'CHECKED_IN';
    case Completed = 'COMPLETED';
    case NoShow = 'NO_SHOW';
    case Overdue = 'OVERDUE';
    case Cancelled = 'CANCELLED';
}
