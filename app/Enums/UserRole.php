<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case LabStaff = 'lab_staff';
    case Researcher = 'researcher';
}
