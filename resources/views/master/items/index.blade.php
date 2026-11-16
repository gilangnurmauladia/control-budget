@extends('layouts.app')
@section('title', 'Master Item')
@section('breadcrumb', 'Master Data / Master Item')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-box-seam me-2 text-accent-blue"></i>Master Item</h1>
        <p class="page-subtitle">Kelola data barang/material beserta harga satuan per departemen</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if(!auth()->user()->hasRole('PIC Departemen'))
        <form method="GET" action="{{ route('master.items.index') }}" class="d-flex align-items-center gap-2">
            <select name="department_id" class="form-select form-select-sm form-select-dark select2-filter" onchange="this.form.submit()" style="width: 200px;">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $deptId == $dept->id ? 'selected' : '' }}>{{ $dept->dept_name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Item
        </button>
        <a href="{{ route('master.items.export') }}" class="btn-outline-dark text-decoration-none" style="font-size:13px;padding:7px 14px;border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);">
            <i class="bi bi-download me-1"></i>Export Excel
        </a>
        <button class="btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importItemModal">
            <i class="bi bi-upload me-1"></i>Import
        </button>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="itemTable">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Item</th>
                    <th>Kategori</th>
                    <th>Departemen</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="font-family:monospace;font-size:12px;">{{ $item->item_code }}</td>
                    <td style="font-weight:500;">{{ $item->item_name }}</td>
                    <td>
                        @php
                            $catColors = ['Consumable' => '#58a6ff', 'Toolings' => '#f0883e', 'Material' => '#bc8cff', 'APD' => '#3fb950'];
                            $catColor = $catColors[$item->category->category_name ?? ''] ?? '#8b949e';
                        @endphp
                        <span style="background: {{ $catColor }}22; color: {{ $catColor }}; border: 1px solid {{ $catColor }}44; padding: 2px 9px; border-radius: 20px; font-size: 11px;">
                            {{ $item->category->category_name ?? '-' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($item->departments as $dept)
                                <span style="background: rgba(88,166,255,.1); color: #58a6ff; border: 1px solid rgba(88,166,255,.2); padding: 1px 6px; border-radius: 4px; font-size: 11px;" title="{{ $dept->dept_name }}">
                                    {{ $dept->dept_code }}
                                </span>
                            @empty
                                <span class="text-muted-custom" style="font-size: 11px;">-</span>
                            @endforelse
                        </div>
                    </td>
                    <td><small class="text-muted-custom">{{ $item->unit }}</small></td>
                    <td class="text-end" style="font-weight:600;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item->status === 'active')
                        <span class="badge-ok">Aktif</span>
                        @else
                        <span class="badge-over">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn-outline-dark" style="font-size:12px;padding:5px 10px;"
                            onclick="openEditItem({{ $item->id }}, '{{ $item->category_id }}', '{{ addslashes($item->item_code) }}', '{{ addslashes($item->item_name) }}', '{{ $item->unit }}', {{ $item->unit_price }}, '{{ $item->status }}', '{{ json_encode($item->departments->pluck('id')->toArray()) }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('master.items.destroy', $item->id) }}" class="d-inline"
                            onsubmit="return confirm('Hapus item {{ $item->item_name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn-danger-dark" type="submit" style="padding:5px 10px;margin-left:4px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Add Item -->
<div class="modal fade modal-dark" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;font-weight:700;">Tambah Item Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.items.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-dark">Kategori</label>
                            <select name="category_id" class="form-select form-select-dark" required>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-dark">Kode Item</label>
                            <input type="text" name="item_code" class="form-control form-control-dark" required placeholder="APD-001, CSM-002..." maxlength="30">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label-dark">Nama Item</label>
                            <input type="text" name="item_name" class="form-control form-control-dark" required maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-dark">Satuan</label>
                            <input type="text" name="unit" class="form-control form-control-dark" required placeholder="Pcs, Box, Kg, Liter..." maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-dark">Harga Satuan (Rp)</label>
                            <input type="text" name="unit_price" class="form-control form-control-dark" required onkeyup="formatRupiah(this)" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-dark">Status</label>
                            <select name="status" class="form-select form-select-dark">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-dark">Departemen Terkait</label>
                            <select name="department_ids[]" class="form-select form-select-dark select2-dept-add" multiple required>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->dept_name }} ({{ $dept->dept_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark" onclick="cleanNumber(this.form)">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Edit Item -->
<div class="modal fade modal-dark" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;font-weight:700;">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-dark">Kategori</label>
                            <select name="category_id" id="editCategoryId" class="form-select form-select-dark" required>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-dark">Kode Item</label>
                            <input type="text" name="item_code" id="editItemCode" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label-dark">Nama Item</label>
                            <input type="text" name="item_name" id="editItemName" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-dark">Satuan</label>
                            <input type="text" name="unit" id="editItemUnit" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-dark">Harga Satuan (Rp)</label>
                            <input type="text" name="unit_price" id="editItemPrice" class="form-control form-control-dark" required onkeyup="formatRupiah(this)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-dark">Status</label>
                            <select name="status" id="editItemStatus" class="form-select form-select-dark">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-dark">Departemen Terkait</label>
                            <select name="department_ids[]" id="editItemDepts" class="form-select form-select-dark select2-dept-edit" multiple required>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->dept_name }} ({{ $dept->dept_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark" onclick="cleanNumber(this.form)">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Import Item -->
<div class="modal fade modal-dark" id="importItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;">Import Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.items.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3" style="background:rgba(88,166,255,.08);border:1px solid rgba(88,166,255,.2);border-radius:10px;padding:14px;font-size:12px;color:var(--text-secondary);">
                        <strong class="text-accent-blue">Format Kolom:</strong><br>
                        kode_item | kategori | nama_item | satuan | harga_satuan | departemen
                        <div class="text-muted mt-1">*Departemen dipisah koma (contoh: PROD, ENG)</div>
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
    .select2-container--bootstrap-5 .select2-search__field {
        color: var(--text-primary) !important;
        background-color: var(--bg-dark) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: var(--accent-blue) !important;
        color: white !important;
        border: none !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#itemTable').DataTable({
        pageLength: 25,
        order: [[2, 'asc'], [0, 'asc']],
        language: { search: 'Cari:', info: 'Menampilkan _START_-_END_ dari _TOTAL_ item', paginate: { previous: '‹', next: '›' } }
    });

    $('.select2-filter').select2({
        theme: 'bootstrap-5',
        width: '200px'
    }).on('select2:select', function (e) {
        $(this.form).submit();
    });

    $('.select2-dept-add').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addItemModal'),
        width: '100%'
    });

    $('.select2-dept-edit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#editItemModal'),
        width: '100%'
    });
});

function openEditItem(id, catId, code, name, unit, price, status, deptIdsJson) {
    document.getElementById('editItemForm').action = '/master/items/' + id;
    document.getElementById('editCategoryId').value = catId;
    document.getElementById('editItemCode').value = code;
    document.getElementById('editItemName').value = name;
    document.getElementById('editItemUnit').value = unit;
    
    // Format price for edit modal
    const priceInput = document.getElementById('editItemPrice');
    priceInput.value = price.toString().replace('.', ',');
    formatRupiah(priceInput);

    document.getElementById('editItemStatus').value = status;

    // Update Select2
    var deptSelect = $('#editItemDepts');
    try {
        const deptIds = JSON.parse(deptIdsJson || '[]');
        deptSelect.val(deptIds).trigger('change');
    } catch(e) {
        deptSelect.val([]).trigger('change');
    }

    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function formatRupiah(input) {
    let value = input.value.replace(/[^,\d]/g, '').toString();
    let split = value.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    input.value = rupiah;
}

function cleanNumber(form) {
    const priceInput = form.querySelector('input[name="unit_price"]');
    if (priceInput) {
        priceInput.value = priceInput.value.replace(/\./g, '').replace(',', '.');
    }
}
</script>
@endpush
