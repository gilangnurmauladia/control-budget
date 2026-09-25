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
                    <select name="department_id" class="form-select form-select-dark" required>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $actual->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->dept_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Item</label>
                    <select name="item_id" class="form-select form-select-dark" required>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $actual->item_id == $item->id ? 'selected' : '' }}>{{ $item->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-dark">Lokasi</label>
                    <select name="location_id" class="form-select form-select-dark">
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
