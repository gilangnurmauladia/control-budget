@extends('layouts.app')
@section('title', 'Laporan Budget')
@section('breadcrumb', 'Laporan / Budget vs Actual')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-file-earmark-bar-graph me-2 text-accent-blue"></i>Laporan Budget vs Actual</h1>
        <p class="page-subtitle">Rekap dual-unit: Kuantitas & Nominal Rupiah per item/departemen</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form method="GET" action="{{ route('reports.index') }}" class="d-flex align-items-center gap-2">
            <select name="period" class="form-select form-select-sm form-select-dark" onchange="this.form.submit()" style="width: auto;">
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ $p === $period ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            @if(!auth()->user()->hasRole('PIC Departemen'))
            <select name="department_id" class="form-select form-select-sm form-select-dark select2-filter" onchange="this.form.submit()" style="width: 200px;">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $deptId == $dept->id ? 'selected' : '' }}>{{ $dept->dept_name }}</option>
                @endforeach
            </select>
            @endif
        </form>
        <a href="{{ route('reports.export.excel', ['period' => $period, 'department_id' => $deptId]) }}" class="btn-outline-dark text-decoration-none" style="font-size: 13px; padding: 7px 14px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary);">
            <i class="bi bi-file-earmark-excel me-1 text-accent-green"></i>Export Excel
        </a>
        <a href="{{ route('reports.export.pdf', ['period' => $period, 'department_id' => $deptId]) }}" class="btn-outline-dark text-decoration-none" style="font-size: 13px; padding: 7px 14px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary);">
            <i class="bi bi-file-earmark-pdf me-1 text-accent-red"></i>Export PDF
        </a>
    </div>
</div>

<!-- Total Summary -->
@php
    $grandPlanQty    = collect($reportData)->sum('plan_qty');
    $grandPlanNom    = collect($reportData)->sum('plan_nominal');
    $grandActualQty  = collect($reportData)->sum('actual_qty');
    $grandActualNom  = collect($reportData)->sum('actual_nominal');
    $grandRemQty     = collect($reportData)->sum('remaining_qty');
    $grandRemNom     = collect($reportData)->sum('remaining_nom');
    $grandPct        = $grandPlanNom > 0 ? round(($grandActualNom/$grandPlanNom)*100, 1) : 0;
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card-dark" style="padding:16px 20px;">
            <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;">Total Plan Qty</div>
            <div style="font-size:18px;font-weight:700;">{{ number_format($grandPlanQty, 0, ',', '.') }}</div>
            <div style="font-size:11px;color:var(--text-secondary);">Rp {{ number_format($grandPlanNom, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-dark" style="padding:16px 20px;">
            <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;">Total Actual Qty</div>
            <div style="font-size:18px;font-weight:700;color:var(--accent-green);">{{ number_format($grandActualQty, 0, ',', '.') }}</div>
            <div style="font-size:11px;color:var(--text-secondary);">Rp {{ number_format($grandActualNom, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-dark" style="padding:16px 20px;">
            <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;">Sisa Qty</div>
            <div style="font-size:18px;font-weight:700;color:{{ $grandRemQty < 0 ? 'var(--accent-red)' : 'var(--accent-orange)' }};">{{ number_format($grandRemQty, 0, ',', '.') }}</div>
            <div style="font-size:11px;color:var(--text-secondary);">Rp {{ number_format(abs($grandRemNom), 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-dark" style="padding:16px 20px;">
            <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;">% Serapan</div>
            <div style="font-size:18px;font-weight:700;" class="{{ $grandPct > 100 ? 'pct-over' : ($grandPct > 80 ? 'pct-warn' : 'pct-high') }}">{{ $grandPct }}%</div>
            <div style="font-size:11px;color:var(--text-secondary);">dari total plan</div>
        </div>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="reportTable">
            <thead>
                <tr>
                    <th>Departemen</th>
                    <th>Kategori</th>
                    <th>Kode Item</th>
                    <th>Nama Item</th>
                    <th>Satuan</th>
                    <th class="text-end">Plan Qty</th>
                    <th class="text-end">Plan (Rp)</th>
                    <th class="text-end">Actual Qty</th>
                    <th class="text-end">Actual (Rp)</th>
                    <th class="text-end">Sisa Qty</th>
                    <th class="text-end">Sisa (Rp)</th>
                    <th class="text-end">% Serap</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Versi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                <tr>
                    <td>
                        <span style="background:rgba(88,166,255,.1);color:#58a6ff;padding:2px 8px;border-radius:4px;font-size:11px;">
                            {{ $row['dept_name'] }}
                        </span>
                    </td>
                    <td><small class="text-muted-custom">{{ $row['category'] }}</small></td>
                    <td style="font-size:12px;font-family:monospace;">{{ $row['item_code'] }}</td>
                    <td style="font-size:13px;font-weight:500;">{{ $row['item_name'] }}</td>
                    <td><small class="text-muted-custom">{{ $row['unit'] }}</small></td>
                    <td class="text-end">{{ number_format($row['plan_qty'], 2, ',', '.') }}</td>
                    <td class="text-end" style="font-size:12px;">{{ number_format($row['plan_nominal'], 0, ',', '.') }}</td>
                    <td class="text-end {{ $row['status'] === 'OVER' ? 'pct-over' : '' }}">{{ number_format($row['actual_qty'], 2, ',', '.') }}</td>
                    <td class="text-end {{ $row['status'] === 'OVER' ? 'pct-over' : 'text-accent-green' }}" style="font-size:12px;">{{ number_format($row['actual_nominal'], 0, ',', '.') }}</td>
                    <td class="text-end {{ $row['remaining_qty'] < 0 ? 'pct-over' : 'pct-warn' }}">{{ number_format($row['remaining_qty'], 2, ',', '.') }}</td>
                    <td class="text-end {{ $row['remaining_nom'] < 0 ? 'pct-over' : '' }}" style="font-size:12px;">{{ number_format(abs($row['remaining_nom']), 0, ',', '.') }}</td>
                    <td class="text-end">
                        <span class="{{ $row['pct'] > 100 ? 'pct-over' : ($row['pct'] > 80 ? 'pct-warn' : 'pct-high') }}" style="font-weight:600;">{{ $row['pct'] }}%</span>
                    </td>
                    <td class="text-center">
                        @if($row['status'] === 'OVER')
                            <span class="badge-over">OVER</span>
                        @else
                            <span class="badge-ok">NORMAL</span>
                        @endif
                    </td>
                    <td class="text-center"><small class="text-muted-custom">v{{ $row['version'] }}</small></td>
                </tr>
                @empty
                <tr><td colspan="14" class="text-center text-muted-custom py-4">Tidak ada data untuk filter ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        background-color: var(--bg-dark) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
        min-height: 30px;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        color: var(--text-primary) !important;
        line-height: 28px;
    }
    .select2-dropdown {
        background-color: var(--bg-card) !important;
        border-color: var(--border-color) !important;
    }
    .select2-results__option {
        color: var(--text-primary) !important;
    }
    .select2-results__option--highlighted {
        background-color: var(--accent-blue) !important;
        color: white !important;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        color: var(--text-primary) !important;
        background-color: var(--bg-dark) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#reportTable').DataTable({
        pageLength: 50,
        order: [[0, 'asc'], [3, 'asc']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: { previous: '‹', next: '›' }
        },
        buttons: [
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i>CSV', className: 'dt-button' },
        ],
        dom: 'Blfrtip',
    });

    $('.select2-filter').select2({
        theme: 'bootstrap-5',
        width: '200px'
    }).on('select2:select', function (e) {
        $(this.form).submit();
    });
});
</script>
@endpush
