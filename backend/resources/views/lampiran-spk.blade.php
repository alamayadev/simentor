<html>

<head>
    <title>PDF SPK MITRA BPS KABUPATEN KARAWANG</title>
</head>

<body>
    {{-- <div class="landscape-content" style="width: 1123px;"> --}}
    <div>
        <div class="wctext002" style="margin-top: 0.3cm">
            <div style="padding-left: 490px;">
                <p>Lampiran </p>

                <p>PERJANJIAN KERJA PETUGAS PENCACAHAN/PENDATAAN LAPANGAN<br>
                    DAN ATAU PENGOLAHAN KEGIATAN SURVEI/SENSUS TAHUN {{ $contents['settings']['tahunKegiatan'] }}<br>
                    PADA BADAN PUSAT STATISTIK KABUPATEN KARAWANG</p>

                <p>NOMOR: {{ $contents['settings']['nomorSPK'] }}</p>
            </div>

            <h5 style="text-align: center;">DAFTAR URAIAN TUGAS, JANGKA WAKTU, NILAI PERJANJIAN, DAN BEBAN ANGGARAN</h5>

            <table border='1' style='border-collapse:collapse; margin-left: 30px'>
                <tbody>
                    <tr style="text-align: center">
                        <td rowspan="2" width="23">
                            No
                        </td>
                        <td rowspan="2" width="250">
                            Uraian Tugas
                        </td>
                        <td colspan="2">
                            Target Pekerjaan
                        </td>
                        <td rowspan="2" width="55">
                            Satuan
                        </td>
                        <td rowspan="2" width="90">
                            Harga<br>Satuan
                        </td>
                        <td rowspan="2" width="100">
                            Nilai Perjanjian
                        </td>
                        <td rowspan="2" width="79">
                            Beban Anggaran
                            <br>{{ $contents['settings']['kodeRing'] }}</br>
                        </td>
                    </tr>
                    <tr style="text-align: center">
                        <td width="120">Jangka Waktu</td>
                        <td width="50">Volume</td>
                    </tr>
                    <tr style="text-align: center">
                        <td>(1)</td>
                        <td>(2)</td>
                        <td>(3)</td>
                        <td>(3)</td>
                        <td>(4)</td>
                        <td>(5)</td>
                        <td>(6)</td>
                        <td>(7)</td>
                    </tr>
                    @foreach ($contents['data']['penugasan'] as $index => $tugas)
                        <tr>
                            <td style="text-align: center">
                                {{ $index + 1 }}
                            </td>
                            <td>{{ $tugas->kegiatan->nama }}</td>
                            <td style="text-align: center">
                                    {{ $tugas->jangka_waktu_mulai->format('j') }} -
                                    {{ \Carbon\Carbon::parse($tugas->jangka_waktu_selesai)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y') }}
                            </td>
                            <td style="text-align: center">
                                {{ $tugas->volume }}
                            </td>
                            <td style="text-align: center">
                                {{ $tugas->kegiatan->satuan }}
                            </td>
                            <td style="text-align: right; padding-right: 10px">
                                Rp {{ number_format($tugas->rate_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; padding-right: 10px">
                                Rp. {{ number_format($tugas->nilai, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center">
                                {{ $tugas->kegiatan->kode_kegiatan }}
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="6" style="text-align: center">
                            Terbilang: {{ $contents['terbilang'] }}
                        </td>
                        <td style="text-align: right; padding-right: 10px">
                            Rp. {{ number_format($contents['total'], 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <style>
        @font-face {
            font-family: 'Book Antiqua';
            font-style: normal;
            font-weight: normal;
            src: local('☺'), url('spk_assets/font1.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: bold;
            src: local('☺'), url('spk_assets/font2.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: italic;
            font-weight: normal;
            src: local('☺'), url('spk_assets/font3.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: normal;
            src: local('☺'), url('spk_assets/font4.woff') format('woff');
        }

        .wctext001 {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: bold;
        }

        .wctext002 {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: normal;
        }

        .wctext003 {
            font-family: 'Bookman Old Style';
            font-style: italic;
            font-weight: normal;
        }

        .wctext004 {
            font-family: 'Book Antiqua';
            font-style: normal;
            font-weight: normal;
        }

        @page portrait {
            size: 8.3in 11.7in;
        }

        @page {
            margin: 0px;
        }

        body {
            margin: 0px;
        }

        @page landscape {
            size: 11.7in 8.3in;
        }

        div.landscape-content {
            page: landscape;
        }

        div.portrait-content {
            page: portrait;
        }

        ol {
            counter-reset: item;
            margin-left: 0;
            padding-left: 0;
        }

        li {
            display: block;
            margin-bottom: .5em;
            margin-left: 2em;
        }

        li::before {
            display: inline-block;
            content: "(" counter(item) ") ";
            counter-increment: item;
            width: 2em;
            margin-left: -2em;
        }
    </style>

</body>

</html>
