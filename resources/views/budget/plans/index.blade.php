@extends('layouts.app')
@section('title', 'Budget Plan')
@section('breadcrumb', 'Transaksi / Budget Plan')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar3 me-2 text-accent-blue"></i>Budget Plan</h1>
        <p class="page-subtitle">Kelola rencana anggaran bulanan per departemen</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <!-- Period & Department Filter -->
        <form method="GET" action="{{ route('budget.plans.index') }}" class="d-flex align-items-center gap-2">
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

        @can('budget_plan.create')
        <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Plan
        </button>
        @endcan

        <!-- Export/Import Buttons -->
        <div class="dropdown">
            <button class="btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu" style="background: var(--bg-card); border-color: var(--border-color);">
                <li><a class="dropdown-item" href="{{ route('budget.plans.export', ['period' => $period, 'department_id' => $deptId]) }}" style="color: var(--text-primary); font-size: 13px;">
                    <i class="bi bi-file-earmark-excel me-2 text-accent-green"></i>Export Excel</a></li>
                <li><a class="dropdown-item" href="{{ route('budget.plans.export.pdf', ['period' => $period, 'department_id' => $deptId]) }}" style="color: var(--text-primary); font-size: 13px;">
                    <i class="bi bi-file-earmark-pdf me-2 text-accent-red"></i>Export PDF</a></li>
            </ul>
        </div>
        @can('budget_plan.import')
        <button class="btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload me-1"></i>Import
        </button>
        @endcan
    </div>
</div>

<!-- Plan Table -->
<div class="card-dark">
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="planTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Departemen</th>
                    <th>Item</th>
                    <th>Kategori</th>
                    <th class="text-end">Plan Qty</th>
                    <th class="text-end">Plan Nominal</th>
                    <th class="text-end">Actual Qty</th>
                    <th class="text-end">Actual Nominal</th>
                    <th class="text-end">Serapan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Versi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $i => $plan)
                <tr>
                    <td class="text-muted-custom">{{ $i + 1 }}</td>
                    <td>
                        <span style="background: rgba(88,166,255,.1); color: #58a6ff; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                            {{ $plan->department->dept_code }}
                        </span>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">{{ $plan->department->dept_name }}</div>
                    </td>
                    <td>
                        <div style="font-size: 13px; font-weight: 500;">{{ $plan->item->item_name }}</div>
                        <div style="font-size: 11px; color: var(--text-secondary);">{{ $plan->item->item_code }} · {{ $plan->item->unit }}</div>
                    </td>
                    <td><small class="text-muted-custom">{{ $plan->item->category->category_name ?? '-' }}</small></td>
                    <td class="text-end">{{ number_format($plan->plan_qty, 0, ',', '.') }}</td>
                    <td class="text-end" style="font-size: 12px;">{{ 'Rp ' . number_format($plan->plan_nominal, 0, ',', '.') }}</td>
                    <td class="text-end {{ $plan->is_over ? 'pct-over' : '' }}">{{ number_format($plan->actual_qty, 0, ',', '.') }}</td>
                    <td class="text-end {{ $plan->is_over ? 'pct-over' : '' }}" style="font-size: 12px;">{{ 'Rp ' . number_format($plan->actual_nominal, 0, ',', '.') }}</td>
                    <td class="text-end">
                        <div class="{{ $plan->pct > 100 ? 'pct-over' : ($plan->pct > 80 ? 'pct-warn' : 'pct-high') }}" style="font-weight: 600;">{{ $plan->pct }}%</div>
                        <div class="budget-bar mt-1" style="width: 80px; margin-left: auto;">
                            <div class="budget-bar-fill {{ $plan->pct > 100 ? 'over' : ($plan->pct > 80 ? 'warn' : '') }}" style="width: min({{ $plan->pct }}%, 100%);"></div>
                        </div>
                    </td>
                    <td class="text-center">
                        @if($plan->is_over)
                            <span class="badge-over">OVER</span>
                        @elseif($plan->pct > 80)
                            <span class="badge-warn">PERLU PERHATIAN</span>
                        @else
                            <span class="badge-ok">NORMAL</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn-outline-dark" style="font-size: 11px; padding: 3px 10px;" onclick="showRevisionHistory({{ $plan->id }})">
                            v{{ $plan->current_version }}
                        </button>
                    </td>
                    <td class="text-center">
                        @can('budget_plan.revise')
                        <button class="btn-outline-dark" style="font-size: 12px; padding: 5px 10px;"
                            onclick="openReviseModal({{ $plan->id }}, '{{ addslashes($plan->item->item_name) }}', {{ $plan->plan_qty }}, {{ $plan->current_version }})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @endcan
                        <a href="{{ route('budget.plans.revisions', $plan->id) }}" class="btn-outline-dark" style="font-size: 12px; padding: 5px 10px; text-decoration: none; display: inline-block; margin-left: 4px;">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted-custom py-4">Belum ada data plan untuk periode {{ $period }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Add Plan -->
<div class="modal fade modal-dark" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 700;"><i class="bi bi-plus-circle me-2 text-accent-blue"></i>Tambah Budget Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('budget.plans.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-dark">Periode</label>
                        <input type="month" name="period" class="form-control form-control-dark" value="{{ $period }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-dark">Departemen</label>
                        <select name="department_id" id="planDeptSelect" class="form-select form-select-dark select2-plan-dept"
                            onchange="onDeptChange(this.value)" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (auth()->user()->department_id == $dept->id || $deptId == $dept->id) ? 'selected' : '' }}>{{ $dept->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-dark">Item</label>
                        <select name="item_id" id="planItemSelect" class="form-select form-select-dark select2-plan-item" data-placeholder="-- Pilih Item --" required>
                            <option value=""></option>
                            @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->item_code }} — {{ $item->item_name }} ({{ $item->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-dark">Lokasi (Opsional)</label>
                        <select name="location_id" id="planLocationSelect" class="form-select form-select-dark">
                            <option value="">GENERAL (Default)</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label-dark">Plan Qty</label>
                        <input type="number" name="plan_qty" class="form-control form-control-dark" step="0.001" min="0.001" required placeholder="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg me-1"></i>Simpan Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Revise Plan -->
<div class="modal fade modal-dark" id="reviseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 700;"><i class="bi bi-arrow-repeat me-2 text-accent-orange"></i>Revisi Budget Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reviseForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3 p-3" style="background: rgba(240,136,62,.1); border: 1px solid rgba(240,136,62,.2); border-radius: 10px; font-size: 13px;">
                        <div id="reviseItemName" style="font-weight: 600;"></div>
                        <div style="color: var(--text-secondary);">Versi saat ini: <span id="reviseVersion" class="text-accent-orange"></span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-dark">Plan Qty Baru</label>
                        <input type="number" name="new_plan_qty" id="reviseQtyInput" class="form-control form-control-dark" step="0.001" min="0.001" required>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Qty saat ini: <span id="reviseCurrentQty" class="text-accent-blue"></span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-dark">Kategori Alasan Revisi</label>
                        <select name="reason_category" class="form-select form-select-dark" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option>Penambahan Kebutuhan</option>
                            <option>Pengurangan Kebutuhan</option>
                            <option>Kesalahan Input Awal</option>
                            <option>Perubahan Jadwal Produksi</option>
                            <option>Efisiensi Bahan</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label-dark">Detail Penjelasan</label>
                        <textarea name="reason_detail" class="form-control form-control-dark" rows="3" required minlength="10" placeholder="Jelaskan alasan revisi plan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg me-1"></i>Simpan Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Import -->
<div class="modal fade modal-dark" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 15px;">Import Budget Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('budget.plans.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3" style="background: rgba(88,166,255,.08); border: 1px solid rgba(88,166,255,.2); border-radius: 10px; padding: 14px; font-size: 12px; color: var(--text-secondary);">
                        <strong class="text-accent-blue">Format Kolom Excel:</strong><br>
                        periode | kode_dept | kode_item | plan_qty
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
    $('#planTable').DataTable({
        pageLength: 25,
        order: [[1, 'asc'], [2, 'asc']],
        language: {
            search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: { previous: '‹', next: '›' }
        },
        dom: 'lfrtip',
    });

    $('.select2-plan-dept').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addPlanModal'),
        width: '100%'
    });

    $('.select2-plan-item').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addPlanModal'),
        width: '100%',
        placeholder: '-- Pilih Item --',
        allowClear: true
    });

    $('.select2-filter').select2({
        theme: 'bootstrap-5',
        width: '200px'
    }).on('select2:select', function (e) {
        $(this.form).submit();
    });
});

function openReviseModal(planId, itemName, currentQty, currentVersion) {
    document.getElementById('reviseForm').action = '/budget/plans/' + planId + '/revise';
    document.getElementById('reviseItemName').textContent = itemName;
    document.getElementById('reviseVersion').textContent = 'v' + currentVersion;
    document.getElementById('reviseCurrentQty').textContent = currentQty.toLocaleString('id-ID');
    document.getElementById('reviseQtyInput').value = currentQty;
    new bootstrap.Modal(document.getElementById('reviseModal')).show();
}

function onDeptChange(deptId) {
    loadLocations(deptId);
    loadDepartmentItems(deptId);
}

function loadLocations(deptId) {
    if (!deptId) return;
    fetch('/api/locations/' + deptId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('planLocationSelect');
            sel.innerHTML = '<option value="">GENERAL (Default)</option>';
            data.forEach(loc => {
                sel.innerHTML += `<option value="${loc.id}">${loc.location_name}${loc.is_default ? ' ★' : ''}</option>`;
            });
        });
}

function loadDepartmentItems(deptId) {
    const itemSelect = $('#planItemSelect');
    const period = document.querySelector('input[name="period"]').value;
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
                const plannedLabel = item.is_planned ? ' [SUDAH DI-PLAN]' : '';
                const disabled = item.is_planned ? 'disabled' : '';
                options += `<option value="${item.id}" ${disabled}>${item.item_code} — ${item.item_name} (${item.unit})${plannedLabel}</option>`;
            });
            itemSelect.html(options).trigger('change');
        })
        .catch(() => {
            itemSelect.html('<option value="">Gagal memuat item</option>').trigger('change');
        });
}

// Load initial department data
const initDept = document.getElementById('planDeptSelect')?.value;
if (initDept) {
    loadLocations(initDept);
    loadDepartmentItems(initDept);
}
</script>
@endpush
