<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: folio; }
        body { font-family: "Cambria", sans-serif; margin: 5px 20px 20px 20px; }
        h1, h2, h3 { font-family: "Cambria", serif; }
        .center { text-align: center; }
        .no-border { border-collapse: collapse; width: 100%; }
        .no-border td { border: none; padding: 0; }
    </style>
</head>
<body>
    <table class="no-border" style="text-align: center;">
        <tr>
            <td style="border: none; padding: 0;">
                <p style="text-align: center; margin: 0;">
                    <img src="{{ public_path('images/bps-logo.png') }}" alt="BPS Logo" width="68" height="48" style="display: inline-block; margin: 0 auto 6px auto;" />
                </p>
                <h2 style="text-align: center; margin: 0; padding: 0; font-weight: bold; font-size: 24px;">{{ $kantor }}</h2>
                <p style="margin: 6px 0 0 0;"><strong>SURAT TUGAS</strong><br />NOMOR: {{ $noSurat }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
