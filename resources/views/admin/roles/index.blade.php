@extends('layouts.app')
@section('title', 'Roles')
@section('breadcrumb', 'Administrasi / Roles')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Roles</h1>
        <p class="page-subtitle">Kelola peran pengguna</p>
    </div>
    <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah Role</button>
</div>

<div class="card-dark">
    <table class="table-dark-custom">
        <thead>
            <tr>
                <th>Nama Role</th>
                <th>Permissions</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td>
                    @foreach($role->permissions as $permission)
                        <span class="badge-ok">{{ $permission->name }}</span>
                    @endforeach
                </td>
                <td>
                    <button class="btn-outline-dark" onclick="editRole({{ $role->id }}, '{{ $role->name }}', {{ json_encode($role->permissions) }})">Edit</button>
                    <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" class="d-inline" onsubmit="return confirm('Hapus role?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger-dark" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Add Role -->
<div class="modal fade modal-dark" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Tambah Role</h5></div>
            <div class="modal-body">
                <input type="text" name="name" class="form-control form-control-dark" placeholder="Role Name" required>
                <div class="mt-3">
                    <label class="form-label-dark">Permissions</label>
                    <select name="permissions[]" id="addRolePermissions" class="form-select form-select-dark" multiple>
                        @foreach($permissions as $permission)
                        <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-dark">Simpan</button>
            </div>
        </form>
    </div>
</div>

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
    $('#addRolePermissions').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addRoleModal'),
        width: '100%'
    });

    $('#editRolePermissions').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#editRoleModal'),
        width: '100%'
    });
});

function editRole(id, name, permissions) {
    document.getElementById('editRoleForm').action = '/admin/roles/' + id;
    document.getElementById('editRoleName').value = name;
    
    // Set selected permissions
    const permNames = permissions.map(p => p.name);
    $('#editRolePermissions').val(permNames).trigger('change');
    
    new bootstrap.Modal(document.getElementById('editRoleModal')).show();
}
</script>
@endpush
<!-- Modal Edit Role -->
<div class="modal fade modal-dark" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editRoleForm" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5>Edit Role</h5></div>
            <div class="modal-body">
                <input type="text" name="name" id="editRoleName" class="form-control form-control-dark" placeholder="Role Name" required>
                <div class="mt-3">
                    <label class="form-label-dark">Permissions</label>
                    <select name="permissions[]" id="editRolePermissions" class="form-select form-select-dark" multiple>
                        @foreach($permissions as $permission)
                        <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-dark">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
