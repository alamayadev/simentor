<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Cambria", sans-serif; }
        .center { text-align: center; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px; }
    </style>
</head>
<body>
    <p>Lampiran 1<br>Surat Tugas No. {{ $noSurat }}</p>
    <p class="center">Petugas {{ $namaKegiatan }}</p>

    <table border="1" style="border-collapse: collapse; width: 100%; border: 1px solid #000;">
        <tr>
            <th style="width: 5%; text-align: center; border: 1px solid #000;">No</th>
            <th style="width: 45%; border: 1px solid #000;">Nama</th>
            <th style="width: 25%; border: 1px solid #000;">Sobat ID</th>
            <th style="width: 25%; border: 1px solid #000;">Kecamatan</th>
        </tr>
        @foreach ($personnel as $person)
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $person['no'] }}</td>
                <td style="border: 1px solid #000;">{{ $person['nama'] }}</td>
                <td style="border: 1px solid #000;">{{ $person['sobat_id'] }}</td>
                <td style="border: 1px solid #000;">{{ $person['kecamatan'] }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
