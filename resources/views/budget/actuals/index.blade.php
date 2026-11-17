@extends('layouts.app')
@section('title', 'Actual Pengeluaran')
@section('breadcrumb', 'Transaksi / Actual Pengeluaran')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-cart3 me-2 text-accent-green"></i>Actual Pengeluaran</h1>
        <p class="page-subtitle">Pencatatan transaksi pengeluaran barang gudang</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form method="GET" action="{{ route('budget.actuals.index') }}" class="d-flex align-items-center gap-2">
            <select name="period" class="form-select form-select-sm form-select-dark" onchange="this.form.submit()" style="width: auto;">
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ $p === $period ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </form>
        @can('budget_actual.create')
        <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addActualModal">
            <i class="bi bi-plus-lg me-1"></i>Input Actual
        </button>
        @endcan
        <div class="dropdown">
            <button class="btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu" style="background: var(--bg-card); border-color: var(--border-color);">
                <li><a class="dropdown-item" href="{{ route('budget.actuals.export', ['period' => $period]) }}" style="color: var(--text-primary); font-size: 13px;">
                    <i class="bi bi-file-earmark-excel me-2 text-accent-green"></i>Export Excel</a></li>
                <li><a class="dropdown-item" href="{{ route('budget.actuals.export.pdf', ['period' => $period]) }}" style="color: var(--text-primary); font-size: 13px;">
                    <i class="bi bi-file-earmark-pdf me-2 text-accent-red"></i>Export PDF</a></li>
            </ul>
        </div>
        @can('budget_actual.import')
        <button class="btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importActualModal">
            <i class="bi bi-upload me-1"></i>Import
        </button>
        @endcan
    </div>
</div>

<!-- Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-dark" style="padding: 16px 20px;">
            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Total Transaksi</div>
            <div style="font-size: 20px; font-weight: 700;">{{ $actuals->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-dark" style="padding: 16px 20px;">
            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Total Qty</div>
            <div style="font-size: 20px; font-weight: 700;">{{ number_format($actuals->sum('actual_qty'), 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card-dark" style="padding: 16px 20px;">
            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Total Nominal</div>
            <div style="font-size: 20px; font-weight: 700; color: var(--accent-green);">
                Rp {{ number_format($actuals->sum(fn($a) => $a->actual_qty * $a->unit_price_snapshot), 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="actualTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Departemen</th>
                    <th>Item</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-end">Nominal</th>
                    <th>Catatan</th>
                    <th>Input Oleh</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actuals as $i => $actual)
                <tr>
                    <td class="text-muted-custom">{{ $i + 1 }}</td>
                    <td>{{ $actual->transaction_date->format('d/m/Y') }}</td>
                    <td>
                        <span style="background: rgba(88,166,255,.1); color: #58a6ff; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                            {{ $actual->department->dept_code }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 13px; font-weight: 500;">{{ $actual->item->item_name }}</div>
                        <div style="font-size: 11px; color: var(--text-secondary);">{{ $actual->item->item_code }}</div>
                    </td>
                    <td><small class="text-muted-custom">{{ $actual->item->category->category_name ?? '-' }}</small></td>
                    <td><small class="text-muted-custom">{{ $actual->location ? $actual->location->location_name : 'GENERAL' }}</small></td>
                    <td class="text-end">{{ number_format($actual->actual_qty, 2, ',', '.') }} {{ $actual->item->unit }}</td>
                    <td class="text-end" style="font-size: 12px;">Rp {{ number_format($actual->unit_price_snapshot, 0, ',', '.') }}</td>
                    <td class="text-end text-accent-green" style="font-size: 12px; font-weight: 600;">
                        Rp {{ number_format($actual->actual_qty * $actual->unit_price_snapshot, 0, ',', '.') }}
                    </td>
                    <td><small class="text-muted-custom">{{ Str::limit($actual->notes, 30) ?? '-' }}</small></td>
                    <td><small class="text-muted-custom">{{ $actual->creator->name ?? '-' }}</small></td>
                    <td class="text-center">
                        @can('budget_actual.edit')
                        <a href="{{ route('budget.actuals.edit', $actual->id) }}"
                            class="btn-outline-dark text-decoration-none" style="font-size: 12px; padding: 5px 10px; display: inline-block;">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                        @can('budget_actual.delete')
                        <form method="POST" action="{{ route('budget.actuals.destroy', $actual->id) }}" class="d-inline"
                            onsubmit="return confirm('Yakin hapus transaksi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger-dark" style="padding: 5px 10px; margin-left: 4px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted-custom py-4">Belum ada transaksi untuk periode {{ $period }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Add Actual -->
<div class="modal fade modal-dark" id="addActualModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 700;"><i class="bi bi-cart-plus me-2 text-accent-green"></i>Input Actual Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('budget.actuals.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-dark">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" class="form-control form-control-dark" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-dark">Departemen</label>
                            <select name="department_id" id="actualDeptSelect" class="form-select form-select-dark select2-actual-dept" onchange="loadActualData(this.value)" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (auth()->user()->department_id == $dept->id) ? 'selected' : '' }}>{{ $dept->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-dark">Item</label>
                            <select name="item_id" id="actualItemSelect" class="form-select form-select-dark select2-actual-item" data-placeholder="-- Pilih Item --" required>
                                <option value=""></option>
                                @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->item_code }} — {{ $item->item_name }} @if($item->unit_price > 0)(Rp {{ number_format($item->unit_price, 0, ',', '.') }}/{{ $item->unit }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-dark">Qty Actual</label>
                            <input type="number" name="actual_qty" class="form-control form-control-dark" step="0.001" min="0.001" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label-dark">Lokasi (Opsional)</label>
                            <select name="location_id" id="actualLocationSelect" class="form-select form-select-dark">
                                <option value="">GENERAL</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-dark">Catatan</label>
                            <input type="text" name="notes" class="form-control form-control-dark" placeholder="Opsional...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg me-1"></i>Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Import -->
<div class="modal fade modal-dark" id="importActualModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 15px;">Import Actual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('budget.actuals.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3" style="background: rgba(88,166,255,.08); border: 1px solid rgba(88,166,255,.2); border-radius: 10px; padding: 14px; font-size: 12px; color: var(--text-secondary);">
                        <strong class="text-accent-blue">Format Kolom:</strong><br>
                        tanggal_transaksi | kode_dept | kode_item | qty_actual | catatan
                    </div>
                    <input type="file" name="file" class="form-control form-control-dark" accept=".xls,.xlsx,.csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark">Import</button>
                </div>
            </form>
        </div>
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
        min-height: 38px;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        color: var(--text-primary) !important;
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
    .select2-container {
        z-index: 1060 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#actualTable').DataTable({
        pageLength: 25,
        order: [[1, 'desc']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: { previous: '‹', next: '›' }
        },
        dom: 'lfrtip',
    });

    $('.select2-actual-dept').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addActualModal'),
        width: '100%'
    });

    $('.select2-actual-item').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addActualModal'),
        width: '100%',
        placeholder: '-- Pilih Item --',
        allowClear: true,
        minimumResultsForSearch: 0
    });
});

function loadActualData(deptId) {
    loadActualLocations(deptId);
    loadActualDepartmentItems(deptId);
}

function loadActualLocations(deptId) {
    if (!deptId) return;
    fetch('/api/locations/' + deptId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('actualLocationSelect');
            sel.innerHTML = '<option value="">GENERAL</option>';
            data.forEach(loc => {
                sel.innerHTML += `<option value="${loc.id}">${loc.location_name}${loc.is_default ? ' ★' : ''}</option>`;
            });
        });
}

function loadActualDepartmentItems(deptId) {
    const itemSelect = $('#actualItemSelect');
    const period = document.querySelector('input[name="transaction_date"]').value.substring(0, 7);
    if (!itemSelect.length) return;

    if (!deptId) {
        itemSelect.html('<option value="">-- Pilih Departemen Terlebih Dahulu --</option>').trigger('change');
        return;
    }

    itemSelect.html('<option value=""></option>').trigger('change');
    fetch('/api/departments/' + deptId + '/items?period=' + period)
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                itemSelect.html('<option value="">-- Tidak Ada Item untuk Departemen Ini --</option>').trigger('change');
                return;
            }
            let options = '<option value=""></option>';
            data.forEach(item => {
                const priceFormatted = item.unit_price > 0 ? ` (Rp ${Number(item.unit_price).toLocaleString('id-ID')}/${item.unit})` : '';
                options += `<option value="${item.id}">${item.item_code} — ${item.item_name}${priceFormatted}</option>`;
            });
            itemSelect.html(options).trigger('change');
            itemSelect.select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addActualModal'),
                width: '100%',
                placeholder: '-- Pilih Item --',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        })
        .catch(() => {
            itemSelect.html('<option value="">Gagal memuat item</option>').trigger('change');
        });
}

const initDept = document.getElementById('actualDeptSelect')?.value;
if (initDept) {
    loadActualLocations(initDept);
    // If select2 is already initialized, we can load items
    setTimeout(() => {
        loadActualDepartmentItems(initDept);
    }, 200);
}

$('#actualDeptSelect').on('select2:select', function (e) {
    loadActualData(e.params.data.id);
});
</script>
@endpush
