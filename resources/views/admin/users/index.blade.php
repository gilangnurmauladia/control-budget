@extends('layouts.app')
@section('title', 'Manajemen User')
@section('breadcrumb', 'Administrasi / <span>User</span>')

@section('content')
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="page-title"><i class="bi bi-people me-2 text-accent-blue"></i>Manajemen User</h1>
            <p class="page-subtitle">Kelola akun pengguna dan hak akses peran</p>
        </div>
        <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i>Tambah User
        </button>
    </div>

    <div class="card-dark">
        <div class="table-responsive">
            <table class="table-dark-custom w-100" id="userTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Departemen</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#1f6feb,#388bfd);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:white;flex-shrink:0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span style="font-size:13px;font-weight:500;">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td style="font-family:monospace;font-size:13px;">{{ $user->username }}</td>
                            <td style="font-size:12px;color:var(--text-secondary);">{{ $user->email ?? '-' }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    @php
                                        $roleColors = ['Super Admin' => '#f85149', 'PIC Departemen' => '#58a6ff', 'Admin Gudang' => '#3fb950', 'Manajemen' => '#bc8cff'];
                                        $rc = $roleColors[$role->name] ?? '#8b949e';
                                    @endphp
                                    <span
                                        style="background:{{ $rc }}22;color:{{ $rc }};border:1px solid {{ $rc }}44;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td style="font-size:12px;color:var(--text-secondary);">
                                {{ $user->department->dept_name ?? 'Semua Dept' }}</td>
                            <td class="text-center">
                                @if($user->is_active)<span class="badge-ok">Aktif</span>@else<span
                                class="badge-over">Non-Aktif</span>@endif
                            </td>
                            <td class="text-center">
                                <button class="btn-outline-dark" style="font-size:12px;padding:5px 10px;"
                                    onclick="openEditUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->username }}', '{{ $user->email }}', {{ $user->roles->first()?->id ?? 'null' }}, {{ $user->department_id ?? 'null' }}, {{ $user->is_active ? 'true' : 'false' }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="d-inline"
                                        onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-danger-dark" type="submit" style="padding:5px 10px;margin-left:4px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Add User -->
    <div class="modal fade modal-dark" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:15px;font-weight:700;">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-dark">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control form-control-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Username</label>
                                <input type="text" name="username" class="form-control form-control-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Email (Opsional)</label>
                                <input type="email" name="email" class="form-control form-control-dark">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Peran</label>
                                <select name="role_id" class="form-select form-select-dark" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label-dark">Departemen (Opsional)</label>
                                <select name="department_id" class="form-select form-select-dark select2-dept-add">
                                    <option value="">-- Tanpa Departemen (Semua Akses) --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Password</label>
                                <input type="password" name="password" class="form-control form-control-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-dark"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-dark">Buat Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Edit User -->
    <div class="modal fade modal-dark" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:15px;font-weight:700;">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-dark">Nama Lengkap</label>
                                <input type="text" name="name" id="editUserName" class="form-control form-control-dark"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Username</label>
                                <input type="text" name="username" id="editUserUsername"
                                    class="form-control form-control-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Email</label>
                                <input type="email" name="email" id="editUserEmail" class="form-control form-control-dark">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Peran</label>
                                <select name="role_id" id="editUserRole" class="form-select form-select-dark" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label-dark">Departemen</label>
                                <select name="department_id" id="editUserDept"
                                    class="form-select form-select-dark select2-dept-edit">
                                    <option value="">-- Tanpa Departemen --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="editUserActive">
                                    <label class="form-check-label" style="color:var(--text-secondary);font-size:13px;">User
                                        Aktif</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Password Baru (kosongkan jika tidak ganti)</label>
                                <input type="password" name="password" class="form-control form-control-dark">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-dark">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-dark">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-dark">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
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
        $(document).ready(function () {
            $('#userTable').DataTable({
                pageLength: 25,
                language: { search: 'Cari:', info: '_TOTAL_ user', paginate: { previous: '‹', next: '›' } }
            });

            $('.select2-dept-add').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addUserModal'),
                width: '100%'
            });

            $('.select2-dept-edit').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#editUserModal'),
                width: '100%'
            });
        });

        function openEditUser(id, name, username, email, roleId, deptId, isActive) {
            document.getElementById('editUserForm').action = '/admin/users/' + id;
            document.getElementById('editUserName').value = name;
            document.getElementById('editUserUsername').value = username;
            document.getElementById('editUserEmail').value = email || '';
            if (roleId) document.getElementById('editUserRole').value = roleId;

            // Update Select2
            var deptSelect = $('#editUserDept');
            deptSelect.val(deptId || '').trigger('change');

            document.getElementById('editUserActive').checked = isActive;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
    </script>
@endpush