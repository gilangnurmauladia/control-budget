@extends('layouts.app')
@section('title', 'Master Departemen')
@section('breadcrumb', 'Master Data / Departemen')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-building me-2 text-accent-blue"></i>Master Departemen</h1>
        <p class="page-subtitle">Kelola data departemen dan lokasi mesin</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addDeptModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Departemen
        </button>
        <a href="{{ route('master.departments.export') }}" class="btn-outline-dark text-decoration-none" style="font-size: 13px; padding: 7px 14px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary);">
            <i class="bi bi-download me-1"></i>Export Excel
        </a>
        <button class="btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importDeptModal">
            <i class="bi bi-upload me-1"></i>Import
        </button>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="deptTable">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Departemen</th>
                    <th class="text-center">Jml Lokasi</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $dept)
                <tr>
                    <td style="font-family:monospace; font-size:13px;">{{ $dept->dept_code }}</td>
                    <td style="font-size:14px; font-weight:500;">{{ $dept->dept_name }}</td>
                    <td class="text-center">
                        <span style="background:rgba(88,166,255,.1);color:#58a6ff;padding:2px 10px;border-radius:20px;font-size:12px;">{{ $dept->locations_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($dept->is_active)
                        <span class="badge-ok">Aktif</span>
                        @else
                        <span class="badge-over">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('master.departments.edit', $dept->id) }}"
                            class="btn-outline-dark text-decoration-none" style="font-size:12px;padding:5px 10px;display:inline-block;">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('master.departments.destroy', $dept->id) }}" class="d-inline"
                            onsubmit="return confirm('Hapus departemen {{ $dept->dept_name }}?')">
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

<!-- MODAL: Add Dept -->
<div class="modal fade modal-dark" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;font-weight:700;">Tambah Departemen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.departments.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-dark">Kode Departemen</label>
                        <input type="text" name="dept_code" class="form-control form-control-dark" required placeholder="PRD, MTC, QC..." maxlength="20">
                    </div>
                    <div class="mb-0">
                        <label class="form-label-dark">Nama Departemen</label>
                        <input type="text" name="dept_name" class="form-control form-control-dark" required placeholder="Produksi, Maintenance..." maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Import Dept -->
<div class="modal fade modal-dark" id="importDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;">Import Departemen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.departments.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3" style="background:rgba(88,166,255,.08);border:1px solid rgba(88,166,255,.2);border-radius:10px;padding:14px;font-size:12px;color:var(--text-secondary);">
                        <strong class="text-accent-blue">Format Kolom:</strong><br>kode_dept | nama_departemen
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#deptTable').DataTable({
        pageLength: 25,
        language: { search: 'Cari:', info: '_TOTAL_ departemen', paginate: { previous: '‹', next: '›' } }
    });
});
</script>
@endpush
