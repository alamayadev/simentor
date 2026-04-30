<?php

namespace App\Enums;

enum JabatanTugasType: string
{
    case PCL = 'PCL';
    case PML = 'PML';
    case OPERATOR = 'OPERATOR';
    case SUPERVISOR = 'SUPERVISOR';

    // public function color(): string
    // {
    //     return match ($this) {
    //         self::STARTED => 'border-blue-500',
    //         self::IN_PROGRESS => 'border-yellow-500',
    //         self::DONE => 'border-green-500',
    //     };
    // }
}
