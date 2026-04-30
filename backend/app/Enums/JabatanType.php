<?php

namespace App\Enums;

enum JabatanType: string
{
    case KEPALA = 'Kepala';
    case STATISTISI_TERAMPIL = 'Statistisi Terampil';
    case STATISTISI_MAHIR = 'Statistisi Mahir';
    case STATISTISI_PENYELIA = 'Statistisi Penyelia';
    case STATISTISI_AHLI_PERTAMA = 'Statistisi Ahli Pertama';
    case STATISTISI_AHLI_MUDA = 'Statistisi Ahli Muda';
    case STATISTISI_AHLI_MADYA = 'Statistisi Ahli Madya';
    case STATISTISI_AHLI_UTAMA = 'Statistisi Ahli Utama';
    case PRANATA_KOMPUTER_MAHIR = 'Pranata Komputer Mahir';
    case PRANATA_KOMPUTER_PENYELIA = 'Pranata Komputer Penyelia';
    case PRANATA_KOMPUTER_AHLI_MUDA = 'Pranata Komputer Ahli Muda';
    case PRANATA_KOMPUTER_AHLI_MADYA = 'Pranata Komputer Ahli Madya';
    case PRANATA_KOMPUTER_AHLI_UTAMA = 'Pranata Komputer Ahli Utama';
    case PRANATA_KEUANGAN_APBN_TERAMPIL = 'Pranata Keuangan APBN Terampil';
    case PRANATA_KEUANGAN_APBN_MAHIR = 'Pranata Keuangan APBN Mahir';
    case PRANATA_KEUANGAN_APBN_PENYELIA = 'Pranata Keuangan APBN Penyelia';
    case PRANATA_KEUANGAN_APBN_AHLI_PERTAMA = 'Pranata Keuangan APBN Ahli Pertama';
    case PRANATA_KEUANGAN_APBN_AHLI_MUDA = 'Pranata Keuangan APBN Ahli Muda';
    case PRANATA_KEUANGAN_APBN_AHLI_MADYA = 'Pranata Keuangan APBN Ahli Madya';
    case PRANATA_KEUANGAN_APBN_AHLI_UTAMA = 'Pranata Keuangan APBN Ahli Utama';
    case PEGOLAH_DATA = 'Pegolah Data';
}
