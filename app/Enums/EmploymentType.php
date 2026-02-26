<?php

declare(strict_types=1);

namespace App\Enums;

enum EmploymentType: string
{
    case Salaried = 'salaried';
    case SelfEmployed = 'self_employed';
}
