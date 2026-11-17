@extends('layouts.app')
@section('title', 'Edit Actual Pengeluaran')

@section('content')
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('budget.actuals.index') }}" class="btn-outline-dark text-decoration-none" style="font-size:13px;padding:7px 14px;border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h1 class="page-title" style="font-size:18px;">Edit Transaksi Actual</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card-dark">
            <form method="POST" action="{{ route('budget.actuals.update', $actual->id) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label-dark">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" class="form-control form-control-dark" value="{{ $actual->transaction_date->format('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Departemen</label>
                    <select name="department_id" id="editActualDeptSelect" class="form-select form-select-dark select2-edit-dept" required onchange="loadEditLocations(this.value); loadEditDepartmentItems(this.value);">
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $actual->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->dept_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Item</label>
                    <select name="item_id" id="editActualItemSelect" class="form-select form-select-dark select2-edit-item" data-placeholder="-- Pilih Item --" required>
                        <option value=""></option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $actual->item_id == $item->id ? 'selected' : '' }}>
                            {{ $item->item_code }} — {{ $item->item_name }} @if($item->unit_price > 0)(Rp {{ number_format($item->unit_price, 0, ',', '.') }}/{{ $item->unit }})@endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Lokasi</label>
                    <select name="location_id" id="editActualLocationSelect" class="form-select form-select-dark">
                        <option value="">GENERAL</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $actual->location_id == $loc->id ? 'selected' : '' }}>{{ $loc->location_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Qty Actual</label>
                    <input type="number" name="actual_qty" class="form-control form-control-dark" value="{{ $actual->actual_qty }}" step="0.001" min="0.001" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Catatan</label>
                    <input type="text" name="notes" class="form-control form-control-dark" value="{{ $actual->notes }}">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-primary-dark">Simpan Perubahan</button>
                    <a href="{{ route('budget.actuals.index') }}" class="btn-outline-dark text-decoration-none" style="font-size:13px;padding:8px 18px;border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);">Batal</a>
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
    $('.select2-edit-dept').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    $('.select2-edit-item').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Item --',
        allowClear: true,
        minimumResultsForSearch: 0
    });
});

function loadEditLocations(deptId) {
    if (!deptId) return;
    fetch('/api/locations/' + deptId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('editActualLocationSelect');
            sel.innerHTML = '<option value="">GENERAL</option>';
            data.forEach(loc => {
                sel.innerHTML += `<option value="${loc.id}">${loc.location_name}${loc.is_default ? ' ★' : ''}</option>`;
            });
        });
}

function loadEditDepartmentItems(deptId) {
    const itemSelect = $('#editActualItemSelect');
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
</script>
@endpush
