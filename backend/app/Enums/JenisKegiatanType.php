<?php

namespace App\Enums;

enum JenisKegiatanType: string
{
    case PERSIAPAN = 'PERSIAPAN';
    case PENGUMPULAN_DATA = 'PENGUMPULAN DATA';
    case PENGOLAHAN = 'PENGOLAHAN';
    case DISEMINASI = 'DISEMINASI';
    case SUPERVISI = 'PENGAWASAN/SUPERVISI';

    // public function color(): string
    // {
    //     return match ($this) {
    //         self::STARTED => 'border-blue-500',
    //         self::IN_PROGRESS => 'border-yellow-500',
    //         self::DONE => 'border-green-500',
    //     };
    // }
}