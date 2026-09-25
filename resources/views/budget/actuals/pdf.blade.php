<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; margin: 0; }
    .header { background: #1e3a5f; color: white; padding: 14px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; margin: 0 0 3px; font-weight: 700; }
    .header p { font-size: 10px; margin: 0; opacity: 0.8; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    thead th { background: #1e3a5f; color: white; padding: 7px 8px; text-align: left; font-weight: 600; }
    tbody tr:nth-child(even) td { background: #f5f7fa; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #e0e0e0; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-over { background: #ffe0e0; color: #c0392b; padding: 2px 6px; border-radius: 3px; font-weight: 700; }
    .badge-ok { background: #e0f7e9; color: #1a7a3c; padding: 2px 6px; border-radius: 3px; }
    .footer { margin-top: 20px; font-size: 9px; color: #888; text-align: right; }
</style>
</head>
<body>
<div class="header">
    <h1>Laporan Transaksi Actual Pengeluaran</h1>
    <p>Periode: {{ $period }} | Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Departemen</th>
            <th>Item</th>
            <th>Satuan</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Harga Satuan</th>
            <th class="text-right">Nominal (Rp)</th>
            <th>Input Oleh</th>
        </tr>
    </thead>
    <tbody>
        @php $total = 0; @endphp
        @foreach($actuals as $actual)
        @php $nominal = $actual->actual_qty * $actual->unit_price_snapshot; $total += $nominal; @endphp
        <tr>
            <td>{{ $actual->transaction_date->format('d/m/Y') }}</td>
            <td>{{ $actual->department->dept_name ?? '-' }}</td>
            <td>{{ $actual->item->item_name ?? '-' }}</td>
            <td>{{ $actual->item->unit ?? '-' }}</td>
            <td class="text-right">{{ number_format($actual->actual_qty, 2) }}</td>
            <td class="text-right">{{ number_format($actual->unit_price_snapshot, 0) }}</td>
            <td class="text-right">{{ number_format($nominal, 0) }}</td>
            <td>{{ $actual->creator->name ?? '-' }}</td>
        </tr>
        @endforeach
        <tr style="background:#1e3a5f;color:white;font-weight:700;">
            <td colspan="6" class="text-right">TOTAL</td>
            <td class="text-right">{{ number_format($total, 0) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
<div class="footer">Budget Control System &copy; {{ date('Y') }}</div>
</body></html>
