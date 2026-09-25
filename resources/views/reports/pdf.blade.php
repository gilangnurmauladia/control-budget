<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a2e; margin: 0; }
    .header { background: #1e3a5f; color: white; padding: 14px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 15px; margin: 0 0 3px; font-weight: 700; }
    .header p { font-size: 9px; margin: 0; opacity: 0.8; }
    table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
    thead th { background: #1e3a5f; color: white; padding: 6px 7px; text-align: left; font-weight: 600; }
    tbody tr:nth-child(even) td { background: #f5f7fa; }
    tbody td { padding: 5px 7px; border-bottom: 1px solid #e0e0e0; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-over { background: #ffe0e0; color: #c0392b; padding: 1px 5px; border-radius: 3px; font-weight: 700; }
    .badge-ok { background: #e0f7e9; color: #1a7a3c; padding: 1px 5px; border-radius: 3px; }
    .footer { margin-top: 16px; font-size: 8px; color: #888; text-align: right; }
</style>
</head>
<body>
<div class="header">
    <h1>Laporan Budget vs Actual — {{ $deptName }}</h1>
    <p>Periode: {{ $period }} | Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Departemen</th>
            <th>Item</th>
            <th>Satuan</th>
            <th class="text-right">Plan Qty</th>
            <th class="text-right">Plan (Rp Rb)</th>
            <th class="text-right">Actual Qty</th>
            <th class="text-right">Actual (Rp Rb)</th>
            <th class="text-right">Sisa Qty</th>
            <th class="text-right">% Serap</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData as $row)
        <tr>
            <td>{{ $row['dept_name'] }}</td>
            <td>{{ $row['item_name'] }}</td>
            <td>{{ $row['unit'] }}</td>
            <td class="text-right">{{ number_format($row['plan_qty'], 2) }}</td>
            <td class="text-right">{{ number_format($row['plan_nominal']/1000, 0) }}</td>
            <td class="text-right">{{ number_format($row['actual_qty'], 2) }}</td>
            <td class="text-right">{{ number_format($row['actual_nominal']/1000, 0) }}</td>
            <td class="text-right">{{ number_format($row['remaining_qty'], 2) }}</td>
            <td class="text-right">{{ $row['pct'] }}%</td>
            <td class="text-center">
                @if($row['status'] === 'OVER')<span class="badge-over">OVER</span>@else<span class="badge-ok">OK</span>@endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">Budget Control System &copy; {{ date('Y') }}</div>
</body></html>
