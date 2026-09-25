@extends('layouts.app')
@section('title', 'Edit Departemen')

@section('content')
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('master.departments.index') }}" class="btn-outline-dark text-decoration-none" style="font-size:13px;padding:7px 14px;border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h1 class="page-title" style="font-size:18px;">Edit Departemen: {{ $department->dept_name }}</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card-dark">
            <form method="POST" action="{{ route('master.departments.update', $department->id) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label-dark">Kode Departemen</label>
                    <input type="text" name="dept_code" class="form-control form-control-dark" value="{{ $department->dept_code }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Nama Departemen</label>
                    <input type="text" name="dept_name" class="form-control form-control-dark" value="{{ $department->dept_name }}" required>
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" style="color:var(--text-secondary);font-size:13px;">Departemen Aktif</label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-primary-dark">Simpan Perubahan</button>
                    <a href="{{ route('master.departments.index') }}" class="btn-outline-dark text-decoration-none" style="font-size:13px;padding:8px 18px;border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dark">
            <div class="card-title">Lokasi Departemen Ini</div>
            @foreach($department->locations as $loc)
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-dark-custom">
                <div>
                    <div style="font-size:13px;">{{ $loc->location_name }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $loc->location_code }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($loc->is_default)<span class="badge-ok" style="font-size:10px;">Default</span>@endif
                    <span class="{{ $loc->status === 'active' ? 'badge-ok' : 'badge-over' }}" style="font-size:10px;">{{ $loc->status }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
