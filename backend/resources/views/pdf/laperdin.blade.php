<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 portrait; }
        html, body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            height: auto;
            margin: 0;
            padding: 0;
        }
        .document {
            border: 3px solid #000;
            margin: 1cm;
            min-height: 960px;
            padding: 20px 25px;
            box-sizing: border-box;
            position: relative;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .metadata-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .metadata-table td { padding: 3px 8px; border: none; }
        .metadata-label { width: 35%; font-weight: normal; }
        .metadata-value { width: 65%; }
        .divider { border-bottom: 2px solid #000; margin: 10px 0; }
        .section-title { font-weight: bold; font-size: 11pt; margin-bottom: 8px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .details-table th {
            border: 1px solid #000; padding: 4px; background-color: #f0f0f0;
            font-weight: bold; font-size: 9pt; text-align: left;
        }
        .details-table td { border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 9pt; }
        
        /* Signature Section - New Layout */
        .signature-section {
            position: absolute;
            bottom: 50px;
            left: 25px;
            right: 25px;
            padding-top: 10px;
            border-top: 1px solid #000;
        }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { border: none; padding: 0; }
        
        .dokumentasi-page {
            border: 3px solid #000; margin: 1cm; min-height: 960px;
            padding: 20px 25px; box-sizing: border-box; position: relative; page-break-before: always;
        }
        .dokumentasi-header { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px; text-transform: uppercase; }
        .image-gallery { display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start; gap: 8px; margin-top: 15px; }
        .image-item { text-align: center; page-break-inside: avoid; }
        .image-item img { max-width: 160px; max-height: 160px; border: 1px solid #000; object-fit: contain; }
        .image-caption { font-size: 8pt; margin-top: 3px; font-style: italic; }
    </style>
</head>
<body>
    <div class="document">
        <div class="header">LAPORAN HASIL PERJALANAN DINAS</div>

        <table class="metadata-table">
            <tr><td class="metadata-label">Nama yang Bepergian</td><td class="metadata-value">: {{ $laporan->nama_traveler }}</td></tr>
            <tr><td class="metadata-label">Tujuan</td><td class="metadata-value">: {{ $laporan->tujuan }}</td></tr>
            <tr><td class="metadata-label">Lama/Tanggal</td><td class="metadata-value">: {{ $laporan->lama_tanggal }}</td></tr>
            <tr><td class="metadata-label">Dalam Rangka</td><td class="metadata-value">: {{ $laporan->dalam_rangka }}</td></tr>
            @if($laporan->pembebanan)
            <tr><td class="metadata-label">Pembebanan</td><td class="metadata-value">: {{ $laporan->pembebanan }}</td></tr>
            @endif
        </table>

        <div class="divider"></div>

        @if($laporan->details && $laporan->details->count() > 0)
        <div class="section-title">URAIAN PELAKSANAAN PERJALANAN DINAS</div>
        @php
            $isOneDay = str_contains($laporan->lama_tanggal, '1 Hari');
        @endphp
        <table class="details-table">
            <thead><tr>
                @if(!$isOneDay)
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 38%;">Uraian LHP</th>
                <th style="width: 24%;">Kendala</th>
                <th style="width: 23%;">Solusi</th>
                @else
                <th style="width: 45%;">Uraian LHP</th>
                <th style="width: 27.5%;">Kendala</th>
                <th style="width: 27.5%;">Solusi</th>
                @endif
            </tr></thead>
            <tbody>
                @foreach($laporan->details as $detail)
                <tr>
                    @if(!$isOneDay)
                    <td>{{ \Carbon\Carbon::parse($detail->tanggal)->format('d/m/Y') }}</td>
                    @endif
                    <td style="text-align: left; vertical-align: top;">{!! $detail->uraian_lhp !!}</td>
                    <td style="text-align: left; vertical-align: top;">{!! $detail->kendala ?? '-' !!}</td>
                    <td style="text-align: left; vertical-align: top;">{!! $detail->solusi ?? '-' !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Signature Section - Full Table Format -->
        <div class="signature-section">
            <table class="signature-table" style="width: 100%;">
                <tr>
                    <!-- Left Column: Place/Date and Maker -->
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 5px;">Karawang, {{ $formattedDate }}</div>
                        <div>Pembuat Laporan ,</div>
                        <div style="height: 60px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">{{ $laporan->nama_traveler }}</div>
                    </td>
                    
                    <!-- Right Column: Signer Placeholder -->
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 5px;">&nbsp;</div>
                        <div>Tanda Tangan,</div>
                        <div style="height: 60px;"></div>
                        <div>______________________________</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if($laporan->dokumentasi && $laporan->dokumentasi->count() > 0 && isset($imageLayout))
    <div class="dokumentasi-page">
        <div class="dokumentasi-header">DOKUMENTASI PERJALANAN DINAS</div>
        
        <div style="position: relative; width: 100%; height: 700px;">
            @foreach($imageLayout as $index => $layout)
            @php 
                $doc = $laporan->dokumentasi[$index] ?? null;
                // Calculate absolute position relative to the page content area
                // The layout x,y from controller includes margins, so we need to adjust
                // Since this div is inside .dokumentasi-page which has padding/margin
                // We'll trust the controller's X/Y are "page coordinates" and specific layout needs
                // Actually, the controller returns X/Y including MARGIN (56).
                // The .dokumentasi-page has margin: 1cm (approx 28px) and padding 25px.
                // Resetting to absolute page positioning might be safer or relative to container.
                
                // Let's rely on the controller's logic which assumes a full page canvas.
                // To make this work easily in HTML/CSS without negative margins:
                // We'll treat the container as the "printable area" inside margins.
                // The controller logic: x = MARGIN + offset.
                // So if we position absolute, we should subtract MARGIN if our container is already inside margins.
                
                $left = $layout['x'] - 56; // Remove left margin offset
                $top = $layout['y'] - 60 - 56; // Remove header height and top margin offset
                
            @endphp
            @if($doc)
            <div style="position: absolute; left: {{ $left }}pt; top: {{ $top }}pt; width: {{ $layout['width'] }}pt; height: {{ $layout['height'] }}pt; text-align: center;">
                @php 
                    $imagePath = public_path($layout['path']);
                    if (!file_exists($imagePath)) {
                        $imagePath = storage_path('app/public/' . $layout['path']); 
                    }
                @endphp
                @if(file_exists($imagePath))
                <img src="file://{{ str_replace('\\', '/', $imagePath) }}" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #000;">
                @else
                <div style="width: 100%; height: 100%; border: 1px solid #000; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                    <span style="font-size: 8pt;">Image Not Found</span>
                </div>
                @endif
                @if($doc->deskripsi)
                <div class="image-caption">{{ $doc->deskripsi }}</div>
                @endif
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
</body>
</html>
