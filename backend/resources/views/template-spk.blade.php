<html>
<head>
    <title>PDF SPK MITRA BPS KABUPATEN KARAWANG</title>

    {{-- <link href="{{ asset('spk-fonts/style.css') }}" rel="stylesheet" /> --}}
</head>
<body>

    <div>
        <div class="wctext001">
            <h3 style="text-align: center;">PERJANJIAN KERJA</h3>

            <h4 style="text-align: center;">
                PETUGAS PENDATAAN LAPANGAN/PENGOLAHAN<br>KEGIATAN SURVEI/SENSUS TAHUN {{ $contents['settings']['tahun'] }}<br>
                PADA BADAN PUSAT STATISTIK KABUPATEN KARAWANG
            </h4>
            <h4 style="text-align: center;">NOMOR: {{ $contents['settings']['nomorSPK'] }}</h4>
        </div>
        <div class="wctext002">
            <p style="text-align: justify; text-justify: inter-word;">
                Pada hari ini {{ $contents['hari_sk'] }}, tanggal {{ $contents['tgl_sk'] }}, bulan {{ $contents['bln_sk'] }}, tahun {{ $contents['thn_sk'] }},
                bertempat di Karawang, yang bertanda tangan di bawah ini:
            </p>
            <table style="width: 100%;">
                <tr>
                    <th style="width: 250px"></th>
                    <th style="width: 10px"></th>
                    <th style="width: 400px"></th>
                </tr>
                <tr>
                    <td>1. {{ $contents['settings']['ppk'] }}</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Karawang; berkedudukan di Jl. Cakradireja
                        No
                        36 Nagasari Karawang, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Karawang,
                        selanjutnya disebut sebagai <b>PIHAK PERTAMA</b>.
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>2. {{ ucwords($contents['data']['petugas']) }}</td>
                    <td>:</td>
                    <td style="text-align: justify; text-justify: inter-word;">
                        NIK {{ $contents['data']['nik'] }}, Pekerjaan {{ $contents['data']['pekerjaan'] }}, berkedudukan di {{ $contents['data']['alamat'] }}, {{ $contents['data']['kecamatan'] }}, {{ $contents['data']['kabupaten'] }},
                        bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <b>PIHAK KEDUA</b>.
                    </td>
                </tr>
                <!-- Additional rows and cells -->
            </table>

            <p style="text-align: justify; text-justify: inter-word;">
                bahwa <b>PIHAK PERTAMA</b> dan <b>PIHAK KEDUA</b> yang secara bersama-sama disebut <b>PARA PIHAK</b>, sepakat untuk
                mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun
                {{ $contents['settings']['tahun'] }} pada Badan Pusat Statistik Kabupaten Karawang, yang selanjutnya disebut Perjanjian,
                dengan ketentuan-ketentuan sebagai berikut:
            </p>

            <h4 style="text-align: center;">Pasal 1</h4>
            <p style="text-align: justify; text-justify: inter-word;">
                <b>PIHAK PERTAMA</b> memberikan pekerjaan kepada <b>PIHAK KEDUA</b> dan <b>PIHAK KEDUA</b> menerima
                pekerjaan dari <b>PIHAK PERTAMA</b> sebagai Petugas Pendataan Lapangan/Pengolahan Kegiatan Survei/Sensus
                Tahun {{ $contents['settings']['tahunKegiatan'] }} pada Badan Pusat Statistik
                Kabupaten Karawang, dengan lingkup pekerjaan yang ditetapkan oleh <b>PIHAK PERTAMA</b>.
            </p>

            <h4 style="text-align: center;">Pasal 2</h4>

            <p style="text-align: justify; text-justify: inter-word;">
                Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana
                tertuang dalam lampiran Perjanjian, Pedoman Petugas Pendataan Lapangan Wilayah Kegiatan Survei/Sensus
                Tahun {{ $contents['settings']['tahunKegiatan'] }} pada Badan Pusat Statistik Kabupaten Karawang, dan
                ketentuan-ketentuan yang ditetapkan oleh <b>PIHAK PERTAMA</b>.
            </p>

            <h4 style="text-align: center;">Pasal 3</h4>

            <p style="text-align: justify; text-justify: inter-word;">
                Jangka Waktu Perjanjian terhitung sejak tanggal
                {{ $contents['settings']['jadwalMulai'] }}
                sampai dengan tanggal
                {{ $contents['settings']['jadwalSelesai'] }}.
            </p>

            <h4 style="text-align: center;">Pasal 4</h4>

            <p style="text-align: justify; text-justify: inter-word;">
                <b>PIHAK KEDUA</b> berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh <b>PIHAK PERTAMA</b>
                sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud dalam Pasal 2, dengan menerapkan protokol kesehatan
                pencegahan Covid-19 yang berlaku di wilayah kerja masing-masing.
            </p>

            <h4 style="text-align: center;">Pasal 5</h4>

            <ol style="text-align: justify; text-justify: inter-word;">
                <li><b>PIHAK KEDUA</b> berhak untuk mendapatkan honorarium petugas dari <b>PIHAK PERTAMA</b> sebesar
                    Rp.{{ number_format($contents['total'],0,",",".") }} ({{ $contents['terbilang'] }})
                    untuk pekerjaan sebagaimana dimaksud dalam Pasal 2,
                    termasuk biaya pajak, bea materai, pulsa dan kuota internet untuk komunikasi, dan jasa pelayanan keuangan.</li>
                <li>Selain mendapatkan honorarium sebagaimana dimaksud pada ayat (1), <b>PIHAK KEDUA</b> berhak mendapatkan
                    asuransi petugas (khusus sensus) dari <b>PIHAK PERTAMA</b>.</li>
                <li><b>PIHAK KEDUA</b> tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal atau
                    terdapat tambahan waktu pelaksanaan pekerjaan lapangan.</li>
            </ol>

            <h4 style="text-align: center;">Pasal 6</h4>

            <ol style="text-align: justify; text-justify: inter-word;">
                <li>Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 dilakukan setelah <b>PIHAK KEDUA</b> menyelesaikan
                    dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada <b>PIHAK PERTAMA</b>.</li>
                <li>Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh <b>PIHAK PERTAMA</b> kepada <b>PIHAK KEDUA</b>
                    sesuai dengan ketentuan peraturan perundang-undangan.</li>
                <li><b>PIHAK KEDUA</b> menyatakan tidak ada pungutan apapun yang dilakukan oleh <b>PIHAK PERTAMA</b> dan organik BPS lainnya
                    dalam pembayaran honorarium.</li>
                <li><b>PIHAK KEDUA</b> menyatakan tidak ada penyetoran apapun kepada <b>PIHAK PERTAMA</b> dan organik BPS lainnya dalam pembayaran honorarium. </li>
                <li><b>PIHAK KEDUA</b> menyatakan tidak akan melakukan subkontrak/maklon pekerjaan kepada pihak lain.</li>
            </ol>

            <h4 style="text-align: center;">Pasal 7</h4>

            <p style="text-align: justify; text-justify: inter-word;">
                Penyerahan hasil pekerjaan lapangan sebagaimana dimaksud dalam Pasal 2 dilakukan secara bertahap dan
                selambat-lambatnya seluruh hasil pekerjaan lapangan diserahkan sesuai jadwal yang tercantum dalam
                Lampiran, yang dinyatakan dalam Berita Acara Serah Terima Hasil Pekerjaan yang ditandatangani oleh <b>PARA
                PIHAK</b>.
            </p>

            <h4 style="text-align: center;">Pasal 8</h4>

            <p style="text-align: justify; text-justify: inter-word;">
                <b>PIHAK PERTAMA</b> dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam hal <b>PIHAK
                    KEDUA</b>
                tidak
                dapat melaksanakan kewajibannya sebagaimana dimaksud dalam Pasal 4, termasuk dalam kondisi terindikasi
                terinfeksi virus Covid-19, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.
            </p>

            <h4 style="text-align: center;">Pasal 9</h4>

            <ol style="text-align: justify; text-justify: inter-word;">
                <li>Apabila <b>PIHAK KEDUA</b> mengundurkan diri pada saat/setelah pelaksanaan pekerjaan lapangan dengan tidak
                    menyelesaikan pekerjaan yang menjadi tanggung jawabnya, maka wajib membayar ganti rugi kepada PIHAK
                    PERTAMA sebesar Rp. {{ number_format($contents['total'],0,",",".") }} ({{ $contents['terbilang'] }})</li>
                <li>Dikecualikan tidak membayar ganti rugi sebagaimana dimaksud pada ayat (1) kepada <b>PIHAK PERTAMA</b>,
                    apabila <b>PIHAK KEDUA</b> meninggal dunia, mengundurkan diri karena sakit dengan keterangan rawat inap, terindikasi
                    terinfeksi virus Covid-19, kecelakaan dengan keterangan kepolisian, dan/atau telah diberikan Surat
                    Pemutusan Perjanjian Kerja dari <b>PIHAK PERTAMA</b>.</li>
                <li>Dalam hal terjadi peristiwa sebagaimana dimaksud pada ayat (2), <b>PIHAK PERTAMA</b> membayarkan honorarium
                    kepada <b>PIHAK KEDUA</b> secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
            </ol>

            <h4 style="text-align: center;">Pasal 10</h4>

            <ol style="text-align: justify; text-justify: inter-word;">
                <li>Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, <b>PIHAK KEDUA</b>
                    memberitahukan  kepada <b>PIHAK PERTAMA</b> dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui
                    atas kejadian Keadaan Kahar dengan menyertakan bukti.</li>
                <li>Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh <b>PIHAK KEDUA</b> dihentikan sementara dan
                    dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar tidak memungkinkan
                    dilanjutkan/diselesaikannya pelaksanaan pekerjaan, <b>PIHAK KEDUA</b> berhak menerima honorarium secara
                    proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
            </ol>

            <h4 style="text-align: center;">Pasal 11</h4>

            <p style="text-align: justify; text-justify: inter-word;">
                Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan dalam perjanjian
                tambahan/<i>addendum</i> dan merupakan bagian tidak terpisahkan dari perjanjian ini.
            </p>

            <h4 style="text-align: center;">Pasal 12</h4>

            <ol style="text-align: justify; text-justify: inter-word;">
                <li>Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan
                    diselesaikan secara musyawarah untuk mufakat.</li>
                <li>Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), <b>PARA PIHAK</b> sepakat
                    menyelesaikan perselisihan dengan memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri Kabupaten
                    Karawang.</li>
                <li>Demikian Perjanjian ini dibuat dan ditandatangani oleh <b>PARA PIHAK</b> dalam 2 (dua) rangkap asli bermeterai
                    cukup, tanpa paksaan dari PIHAK manapun dan untuk dilaksanakan oleh <b>PARA PIHAK</b>.
                </li>
            </ol>

            <div style="break-after:page; margin-top:60px;">
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

    <style>
        @font-face {
            font-family: 'Book Antiqua';
            font-style: normal;
            font-weight: normal;
            src: local('☺'), url('spk_fonts/font1.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: bold;
            src: local('☺'), url('spk_fonts/font2.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: italic;
            font-weight: normal;
            src: local('☺'), url('spk_fonts/font3.woff') format('woff');
        }

        @font-face {
            font-family: 'Bookman Old Style';
            font-style: normal;
            font-weight: normal;
            src: local('☺'), url('spk_fonts/font4.woff') format('woff');
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
