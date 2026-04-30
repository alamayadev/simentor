<html>

<head>
    <title>PDF BAST MITRA BPS KABUPATEN KARAWANG</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <link href="{{ asset('spk_fonts/style.css') }}" media="all" />
</head>

<body>
    <div>
        <div class="wctext001">

            <h3 style="text-align: center;">BERITA ACARA SERAH TERIMA</h3>

            <h4 style="text-align: center;">
                HASIL PEKERJAAN PENDATAAN LAPANGAN/PENGOLAHAN<br>
                KEGIATAN SURVEI/SENSUS TAHUN {{ $contents['settings']['tahunKegiatan'] }}<br>
                PADA BADAN PUSAT STATISTIK KABUPATEN KARAWANG
            </h4>
            <h4 style="text-align: center;">NOMOR: {{ $contents['settings']['nomorBAST'] }}</h4>
        </div>
        <div class="wctext002">
            <p style="text-align: justify; text-justify: inter-word;">
                Pada hari ini {{ $contents['hari_bast'] }}, tanggal {{ $contents['tgl_bast'] }}, bulan
                {{ $contents['bln_bast'] }}, tahun
                {{ $contents['thn_bast'] }},
                bertempat di Karawang, yang bertanda tangan di bawah ini:
            </p>
            <table style="width: 100%;">
                <tr>
                    <th style="width: 10px"></th>
                    <th style="width: 250px"></th>
                    <th style="width: 10px"></th>
                    <th style="width: 400px"></th>
                </tr>
                <tr>
                    <td>1.</td>
                    <td>Nama</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        {{ $contents['settings']['ppk'] }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>NIP</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        {{ $contents['settings']['nipPpk'] }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        Pejabat Pembuat Komitmen
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Unit Kerja</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        BPS Kabupaten Karawang
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td style="vertical-align: top;">Alamat Unit Kerja</td>
                    <td style="vertical-align: top;">:</td>
                    <td>
                        Jalan Cakradireja No 36 Desa Nagasari Kecamatan Karawang Barat Kabupaten Karawang
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3" style="vertical-align: top;">
                        bertindak untuk dan atas nama BPS Kabupaten Karawang, selanjutnya disebut sebagai <b>PIHAK
                            PERTAMA</b>.
                    </td>
                </tr>

                <tr>
                    <td>2.</td>
                    <td>Nama</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        {{ ucwords($contents['data']['petugas']) }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>NIK</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        {{ $contents['data']['nik'] }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td style="vertical-align: top;">Alamat</td>
                    <td style="vertical-align: top;">:</td>
                    <td>{{ $contents['data']['alamat'] }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3" style="vertical-align: top;">
                        bertindak untuk dan atas nama sendiri, selanjutnya disebut sebagai <b>PIHAK KEDUA</b>.
                    </td>
                </tr>
            </table>

            <p style="text-align: justify; text-justify: inter-word;">
                Dengan ini menyatakan:
            </p>

            <ol style="text-align: justify; text-justify: inter-word;">
                <li><b>PIHAK KEDUA</b> telah melaksanakan pekerjaan pendataan lapangan/pengolahan kegiatan survei/sensus
                    tahun {{ $contents['settings']['tahunKegiatan'] }} berdasarkan Perjanjian Kerja Nomor
                    {{ $contents['settings']['nomorBAST'] }}.</li>
                <li><b>PIHAK KEDUA</b> telah menyelesaikan pekerjaan pendataan lapangan/pengolahan kegiatan
                    survei/sensus tahun
                    {{ $contents['settings']['tahunKegiatan'] }} berdasarkan hasil pemeriksaan dan evaluasi pekerjaan
                    sebagaimana tercantum dalam nomor 4.</li>
                <li>Berdasarkan nomor 2 tersebut di atas, <b>PIHAK KEDUA</b> menyerahkan hasil pekerjaan pendataan
                    lapangan/ pengolahan
                    kegiatan survei/sensus tahun {{ $contents['settings']['tahunKegiatan'] }} kepada <b>PIHAK PERTAMA</b>, dan <b>PIHAK PERTAMA</b> menerima
                    hasil
                    pekerjaan tersebut yang telah sesuai dengan seharusnya.</li>
                <li>Hasil pekerjaan pendataan lapangan/pengolahan kegiatan survei/sensus tahun
                    {{ $contents['settings']['tahunKegiatan'] }}
                    sebagaimana dimaksud dalam nomor 3 di atas, dengan rincian sebagai berikut :
                    <table border='1' style='margin-top: 15px; margin-bottom: 15px; border-collapse:collapse;'>
                        <tr style="text-align: center;">
                            <td>No</td>
                            <td>Kegiatan</td>
                            <td>Satuan</td>
                            <td>Volume</td>
                        </tr>
                        <tr style="text-align: center;">
                            <td>(1)</td>
                            <td>(2)</td>
                            <td>(3)</td>
                            <td>(4)</td>
                        </tr>
                        @foreach ($contents['data']['penugasan'] as $index => $tugas)
                            <tr>
                                <td width="20" style="text-align: center;">
                                    {{ $index + 1 }}.
                                </td>
                                <td width="300" >{{ $tugas->kegiatan->nama }}</td>
                                <td width="70" style="text-align: center;">{{ $tugas->kegiatan->satuan }}</td>
                                <td width="80" style="text-align: center;">{{ $tugas->volume }}</td>
                            </tr>
                        @endforeach
                    </table>

                </li>
                <li>Untuk hasil pendataan lapangan/pengolahan sebagaimana dimaksud pada nomor 4 yang memerlukan pemeriksaan lanjutan, akan
                    dilakukan
                    pengecekan, perubahan, dan/atau kunjungan kembali ke lapangan merujuk pada perjanjian dan adendum
                    perjanjian
                    ini yang ditandatangani oleh <b>PARA PIHAK</b>.</li>

            </ol>

            <ol start="5" style="text-align: justify; text-justify: inter-word;">
            </ol>
            <div style="page-break-inside: avoid;">
                <p style="text-align: justify; text-justify: inter-word;">
                    Demikian Berita Acara ini dibuat dengan sebenarnya dan menjadi sah berlaku setelah ditandatangani
                    oleh
                    <b>PARA PIHAK</b>.
                </p>


                <div style="page-break-inside: avoid; margin-top:30px;">
                    <table style="width: 100%">
                        <tr>
                            <th style="width: 300px"><b>PIHAK KEDUA</b>,</th>
                            <th style="width: 300px"><b>PIHAK PERTAMA</b>,</th>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <th>{{ ucwords($contents['data']['petugas']) }}</th>
                            <th>{{ $contents['settings']['ppk'] }}</th>
                        </tr>
                        <!-- Additional rows and cells -->
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        @font-face {
            font-family: 'Book Antiqua';
            font-style: normal;
            font-weight: normal;
            src: url('spk_fonts/font1.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: bold;
            src: url('spk_fonts/font2.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: italic;
            font-weight: normal;
            src: url('spk_fonts/font3.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: normal;
            src: url('spk_fonts/font4.woff') format('woff');
        }
        body {
            font-family: 'Book Antiqua', sans-serif;
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
            content: counter(item) ". ";
            counter-increment: item;
            width: 2em;
            margin-left: -2em;
        }
    </style>

</body>

</html>
