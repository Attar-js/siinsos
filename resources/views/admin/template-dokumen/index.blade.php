<x-app-layout :assets="$assets ?? []">
<div class="row">
   <div class="col-lg-12">
      @if(session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      @if(session('error'))
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      @if($errors->any())
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
               @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <div class="card rounded">
         <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
               <div>
                  <h4 class="card-title mb-0">Template Dokumen</h4>
                  <small class="text-muted">Atur link unduh yang tampil di halaman Template Dokumen.</small>
               </div>
               <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahTemplateModal">
                  <i class="fas fa-plus"></i> Tambah Template
               </button>
            </div>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-hover align-middle">
                  <thead class="table-primary">
                     <tr>
                        <th width="5%">No</th>
                        <th width="28%">Judul</th>
                        <th>Link unduh</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="16%" class="text-center">Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($templates as $index => $template)
                     <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                           <strong>{{ $template->title }}</strong>
                        </td>
                        <td>
                           <small class="text-break">{{ $template->download_url }}</small>
                        </td>
                        <td class="text-center">
                           @if($template->is_active)
                              <span class="badge bg-success">Aktif</span>
                           @else
                              <span class="badge bg-secondary">Nonaktif</span>
                           @endif
                        </td>
                        <td class="text-center">
                           <button type="button" class="btn btn-sm btn-info text-white"
                                   onclick="editTemplate({{ $template->id }}, {{ json_encode($template->title) }}, {{ json_encode($template->download_url) }}, {{ $template->is_active ? 'true' : 'false' }})">
                              <i class="fas fa-edit"></i> Edit
                           </button>
                           <form action="{{ route('admin.template-dokumen.destroy', $template) }}" method="POST" class="d-inline"
                                 onsubmit="return confirm('Hapus template {{ addslashes($template->title) }}?')">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger">
                                 <i class="fas fa-trash"></i>
                              </button>
                           </form>
                        </td>
                     </tr>
                     @empty
                     <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada template. Tambahkan link unduh agar tampil di halaman mahasiswa.</td>
                     </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="tambahTemplateModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
      <form method="POST" action="{{ route('admin.template-dokumen.store') }}" class="modal-content">
         @csrf
         <div class="modal-header">
            <h5 class="modal-title">Tambah Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <div class="mb-3">
               <label class="form-label">Judul</label>
               <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="mb-3">
               <label class="form-label">Link unduh</label>
               <input type="url" name="download_url" class="form-control" value="{{ old('download_url') }}" placeholder="https://" required>
            </div>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
               <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tambahAktif" checked>
               <label class="form-check-label" for="tambahAktif">Tampilkan di halaman mahasiswa</label>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
         </div>
      </form>
   </div>
</div>

<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
      <form method="POST" id="editTemplateForm" class="modal-content">
         @csrf
         @method('PUT')
         <div class="modal-header">
            <h5 class="modal-title">Edit Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <div class="mb-3">
               <label class="form-label">Judul</label>
               <input type="text" name="title" id="editTitle" class="form-control" required>
            </div>
            <div class="mb-3">
               <label class="form-label">Link unduh</label>
               <input type="url" name="download_url" id="editUrl" class="form-control" required>
            </div>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
               <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editAktif">
               <label class="form-check-label" for="editAktif">Tampilkan di halaman mahasiswa</label>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
         </div>
      </form>
   </div>
</div>

<script>
   function editTemplate(id, title, url, isActive) {
      const form = document.getElementById('editTemplateForm');
      form.action = @json(url('/admin/template-dokumen')) + '/' + id;
      document.getElementById('editTitle').value = title;
      document.getElementById('editUrl').value = url;
      document.getElementById('editAktif').checked = isActive;
      new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
   }
</script>
</x-app-layout>
