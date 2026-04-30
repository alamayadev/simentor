<?php

namespace App\Enums;

enum JenisKeluhanType: string
{
    case SISTEM = 'Sistem';
    case SOFTWARE = 'Software';
    case PRINTER = 'Printer';
    case HARDWARE_PC_LAPTOP = 'Hardware PC/Laptop';
    case JARINGAN = 'Jaringan';
    case AKUN_BPS = 'Akun BPS';
    case LAINNYA = 'Lainnya';

    /**
     * Get all jenis keluhan values as an array
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::SISTEM->value,
            self::SOFTWARE->value,
            self::PRINTER->value,
            self::HARDWARE_PC_LAPTOP->value,
            self::JARINGAN->value,
            self::AKUN_BPS->value,
            self::LAINNYA->value,
        ];
    }
}
