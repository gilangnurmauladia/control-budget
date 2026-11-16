@extends('layouts.app')
@section('title', 'Kategori Item')
@section('breadcrumb', 'Master Data / Kategori Item')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="page-title"><i class="bi bi-tags me-2 text-accent-blue"></i>Kategori Item</h1>
        <p class="page-subtitle">Kelola kategori untuk pengelompokan barang dan material</p>
    </div>
    <div>
        <button class="btn-primary-dark" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </button>
    </div>
</div>

<div class="card-dark">
    <div class="table-responsive">
        <table class="table-dark-custom w-100" id="categoryTable">
            <thead>
                <tr>
                    <th width="10%">No</th>
                    <th>Nama Kategori</th>
                    <th class="text-center">Jumlah Item</th>
                    <th class="text-center" width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $index => $cat)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: 500;">{{ $cat->category_name }}</td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $cat->items_count }} Item</span>
                    </td>
                    <td class="text-center">
                        <button class="btn-outline-dark" style="font-size:12px;padding:5px 10px;"
                            onclick="openEditCategory({{ $cat->id }}, '{{ addslashes($cat->category_name) }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('master.categories.destroy', $cat->id) }}" class="d-inline"
                            onsubmit="return confirm('Hapus kategori {{ $cat->category_name }}?')">
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

<!-- MODAL: Add Category -->
<div class="modal fade modal-dark" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;font-weight:700;">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('master.categories.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-dark">Nama Kategori</label>
                        <input type="text" name="category_name" class="form-control form-control-dark" required placeholder="Contoh: Consumable, APD, Toolings..." maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-dark">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Edit Category -->
<div class="modal fade modal-dark" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:15px;font-weight:700;">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-dark">Nama Kategori</label>
                        <input type="text" name="category_name" id="editCategoryName" class="form-control form-control-dark" required maxlength="100">
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#categoryTable').DataTable({
        pageLength: 25,
        language: { search: 'Cari:', info: 'Menampilkan _START_-_END_ dari _TOTAL_ kategori', paginate: { previous: '‹', next: '›' } }
    });
});

function openEditCategory(id, name) {
    document.getElementById('editCategoryForm').action = '/master/categories/' + id;
    document.getElementById('editCategoryName').value = name;
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>
@endpush
