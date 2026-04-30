<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0.5cm;
        }
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2.2cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            margin-top: 1.8cm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .header-box {
            border: 1px solid #000;
            width: 100%;
        }
        .header-box td {
            border: none;
            padding: 5px;
            vertical-align: middle;
        }
        .logo-left {
            width: 80px;
            text-align: center;
        }
        .logo-right {
            width: 80px;
            text-align: center;
        }
        .categories-grid {
            font-size: 6pt;
        }
        .categories-grid td {
            border: none;
            padding: 1px 3px;
        }
        .checkbox-symbol {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #000;
            margin-right: 3px;
            text-align: center;
            line-height: 7px;
            font-size: 6pt;
            vertical-align: middle;
        }
        .checked {
            font-weight: bold;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .info-label {
            font-weight: bold;
            width: 25%;
            background-color: #f2f2f2;
        }
        .section-header {
            background-color: #e6e6e6;
            font-weight: bold;
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .content-box {
            border: 1px solid #000;
            border-top: none;
            padding: 8px;
            min-height: 50px;
        }
        .rich-text ul {
            margin: 0;
            padding-left: 20px;
        }
        .signature-box {
            border: 1px solid #000;
            border-top: none;
        }
        .signature-box td {
            width: 50%;
            text-align: center;
            padding: 10px;
            vertical-align: bottom;
        }
        .footer-logos {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <table class="header-box">
            <tr>
                <td class="logo-left">
                    <img src="{{ public_path('images/bps_text.png') }}" style="height: 50px; width: auto;">
                </td>
                <td style="padding: 0;">
                    <table class="categories-grid">
                        @php
                            $catlist = [
                                1 => 'Penataan dan Penguatan Organisasi',
                                2 => 'Penataan Peraturan Perundang-Undangan',
                                3 => 'Penataan Sumber Daya Manusia',
                                4 => 'Penataan Tata Laksana',
                                5 => 'Peningkatan Kualitas Pelayanan Publik',
                                6 => 'Penguatan Pengawasan',
                                7 => 'Penguatan Akuntabilitas Kinerja',
                                8 => 'Manaiemen Perubahan'
                            ];
                        @endphp
                        @foreach(array_chunk($catlist, 2, true) as $chunk)
                        <tr>
                            @foreach($chunk as $id => $name)
                            <td>
                                <div class="checkbox-symbol">@if($notulensi->kategori == $id) v @endif</div>
                                {{ $name }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </table>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/rb.png') }}" style="height: 50px; width: auto;">
                </td>
            </tr>
        </table>
    </header>

    <table class="info-table" style="border-top: none;">
        @php
            // Prefer relational data for leader/notetaker names and NIP, fall back to stored values/placeholders
            $pimpinanNama = optional($notulensi->pimpinan)->nama ?? '-';
            $pimpinanNip = optional($notulensi->pimpinan)->nip ?? ($notulensi->nip_pimpinan ?? '.......................');
            $notulisNama = optional($notulensi->notulis)->nama ?? '.......................';
            $notulisNip = optional($notulensi->notulis)->nip ?? ($notulensi->nip_notulis ?? '.......................');
        @endphp
        <tr>
            <td class="info-label">Judul Rapat</td>
            <td style="width: 35%;">{{ $notulensi->judul }}</td>
            <td class="info-label" style="width: 20%; vertical-align: top;">Hari/Tanggal<br>Waktu</td>
            <td style="vertical-align: top;">
                {{ $notulensi->tanggal_rapat ? $notulensi->tanggal_rapat->locale('id')->translatedFormat('l, d F Y') : '-' }}<br>
                @if($notulensi->waktu_mulai && $notulensi->waktu_selesai)
                    {{ \Carbon\Carbon::parse($notulensi->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($notulensi->waktu_selesai)->format('H:i') }} WIB
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Pimpinan Rapat</td>
            <td>{{ $pimpinanNama }}</td>
            <td class="info-label">Tempat</td>
            <td>{{ $notulensi->tempat }}</td>
        </tr>
        <tr>
            <td class="info-label">Topik</td>
            <td colspan="3" style="text-align: left;">{{ $notulensi->topik }}</td>
        </tr>
    </table>

    <div class="section-header">Peserta Rapat :</div>
    <div class="content-box">
        @if(is_array($notulensi->peserta))
            @php
                $count = count($notulensi->peserta);
                $half = ceil($count / 2);
            @endphp
            @if($count > 18)
                @php
                    $limit = 18;
                @endphp
                <table style="border: none;">
                    <tr>
                        <td style="border: none; width: 50%; vertical-align: top; padding: 0;">
                            <ol style="margin: 0; padding-left: 20px;">
                                @for($i = 0; $i < $limit; $i++)
                                    <li>{{ $notulensi->peserta[$i] }}</li>
                                @endfor
                            </ol>
                        </td>
                        <td style="border: none; width: 50%; vertical-align: top; padding: 0;">
                            <ol style="margin: 0; padding-left: 20px;" start="{{ $limit + 1 }}">
                                @for($i = $limit; $i < $count; $i++)
                                    <li>{{ $notulensi->peserta[$i] }}</li>
                                @endfor
                            </ol>
                        </td>
                    </tr>
                </table>
            @else
                <ol style="margin: 0; padding-left: 20px;">
                    @foreach($notulensi->peserta as $p)
                        <li>{{ $p }}</li>
                    @endforeach
                </ol>
            @endif
        @endif
    </div>

    <div class="section-header">Agenda Rapat :</div>
    <div class="content-box rich-text">
        {!! $notulensi->agenda !!}
    </div>

    <div class="section-header">Resume Hasil Rapat :</div>
    <div class="content-box rich-text">
        {!! $notulensi->resume !!}
    </div>

    <div class="section-header">Pertanyaan dan Jawaban :</div>
    <div class="content-box rich-text">
        {!! $notulensi->tanya_jawab !!}
    </div>

    <table class="signature-box">
        <tr>
            <td>
                <div>Notulis</div>
                <div>&nbsp;</div>
                <br><br><br><br>
                <div style="font-weight: bold;">{{ $notulisNama }}</div>
                <div>NIP. {{ $notulisNip }}</div>
            </td>
            <td>
                <div>Pimpinan Rapat</div>
                <div>{{ $notulensi->jabatan_pimpinan_rapat }}</div>
                <br><br><br><br>
                <div style="font-weight: bold;">{{ $pimpinanNama }}</div>
                <div>NIP. {{ $pimpinanNip }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
