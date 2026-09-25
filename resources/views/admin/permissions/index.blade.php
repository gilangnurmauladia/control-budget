@extends('layouts.app')
@section('title', 'Permissions')
@section('breadcrumb', 'Administrasi / Permissions')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Permissions</h1>
        <p class="page-subtitle">Kelola izin akses</p>
    </div>
    <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addPermissionModal">Tambah Permission</button>
</div>

<div class="card-dark">
    <table class="table-dark-custom">
        <thead>
            <tr>
                <th>Nama Permission</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permissions as $permission)
            <tr>
                <td>{{ $permission->name }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.permissions.destroy', $permission->id) }}" class="d-inline" onsubmit="return confirm('Hapus permission?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger-dark" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Add Permission -->
<div class="modal fade modal-dark" id="addPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.permissions.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Tambah Permission</h5></div>
            <div class="modal-body">
                <input type="text" name="name" class="form-control form-control-dark" placeholder="Permission Name" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-dark">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
