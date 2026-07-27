<x-app-layout :assets="$assets ?? []">
<div class="row justify-content-center">
   <div class="col-lg-10">

      @if(session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      @if(session('error'))
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>{{ session('error') }}
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

      @if(session('import_summary'))
         @php($sum = session('import_summary'))
         <div class="card rounded mb-4 border-0 shadow-sm">
            <div class="card-header bg-light">
               <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-1"></i>Ringkasan Hasil Impor</h5>
            </div>
            <div class="card-body">
               <div class="row text-center mb-3">
                  <div class="col-md-4">
                     <div class="p-3 rounded bg-success bg-opacity-10">
                        <h3 class="mb-0 text-success">{{ $sum['created'] }}</h3>
                        <small class="text-muted">Berhasil dibuat</small>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="p-3 rounded bg-warning bg-opacity-10">
                        <h3 class="mb-0 text-warning">{{ count($sum['duplicates']) }}</h3>
                        <small class="text-muted">Duplikat (dilewati)</small>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="p-3 rounded bg-danger bg-opacity-10">
                        <h3 class="mb-0 text-danger">{{ count($sum['errors']) }}</h3>
                        <small class="text-muted">Baris bermasalah</small>
                     </div>
                  </div>
               </div>

               @if(count($sum['duplicates']))
                  <details class="mb-2">
                     <summary class="text-warning fw-bold">Lihat daftar duplikat ({{ count($sum['duplicates']) }})</summary>
                     <ul class="mt-2 small text-muted">
                        @foreach(array_slice($sum['duplicates'], 0, 200) as $d)
                           <li>{{ $d }}</li>
                        @endforeach
                        @if(count($sum['duplicates']) > 200)
                           <li>... dan {{ count($sum['duplicates']) - 200 }} lainnya</li>
                        @endif
                     </ul>
                  </details>
               @endif

               @if(count($sum['errors']))
                  <details>
                     <summary class="text-danger fw-bold">Lihat daftar baris bermasalah ({{ count($sum['errors']) }})</summary>
                     <ul class="mt-2 small text-muted">
                        @foreach(array_slice($sum['errors'], 0, 200) as $e)
                           <li>{{ $e }}</li>
                        @endforeach
                        @if(count($sum['errors']) > 200)
                           <li>... dan {{ count($sum['errors']) - 200 }} lainnya</li>
                        @endif
                     </ul>
                  </details>
               @endif
            </div>
         </div>
      @endif

      <div class="card rounded">
         <div class="card-header">
            <h4 class="card-title mb-0"><i class="fas fa-file-import me-2"></i>Impor {{ $cfg['label'] }} Massal</h4>
         </div>
         <div class="card-body">

            <div class="alert alert-info">
               <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i>Petunjuk</h6>
               <ol class="mb-2 ps-3">
                  <li>Klik <strong>Unduh Template Excel</strong>, lalu isi datanya di Excel (bisa ratusan/ribuan baris).</li>
                  <li>Pastikan tiap kolom terpisah (NIP/NIM, Nama, Email, …) — <strong>jangan</strong> menaruh seluruh baris di satu kolom A.</li>
                  <li>Simpan sebagai <strong>.xlsx</strong> (disarankan) atau CSV, lalu unggah di bawah.</li>
                  <li>Baris yang NIM/NIP/email/username-nya sudah terdaftar akan otomatis dilewati.</li>
               </ol>
               <div class="mb-0"><strong>Catatan role ini:</strong> {{ $cfg['note'] }}</div>
            </div>

            <div class="mb-4">
               <span class="me-2">Kolom yang dibutuhkan:</span>
               @foreach($cfg['headers'] as $h)
                  <span class="badge bg-secondary me-1">{{ $h }}@if(in_array($h, $cfg['required'])) *@endif</span>
               @endforeach
               <div class="form-text">Kolom bertanda <strong>*</strong> wajib diisi.</div>
            </div>

            <a href="{{ route('admin.import.template', $cfg['key']) }}" class="btn btn-outline-primary mb-4">
               <i class="fas fa-download me-1"></i>Unduh Template Excel
            </a>

            <hr>

            <form action="{{ route('admin.import.store', $cfg['key']) }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="mb-3">
                  <label for="file" class="form-label fw-bold">Pilih file (CSV / Excel)</label>
                  <input type="file" class="form-control @error('file') is-invalid @enderror"
                         id="file" name="file" accept=".csv,.txt,.xlsx,.xls" required>
                  @error('file')
                     <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Maksimal 20 MB.</div>
               </div>
               <button type="submit" class="btn btn-primary">
                  <i class="fas fa-upload me-1"></i>Impor Sekarang
               </button>
            </form>

         </div>
      </div>

      {{-- Tabel verifikasi data terdaftar --}}
      <div class="card rounded mt-4">
         <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
               <h4 class="card-title mb-0">
                  <i class="fas fa-users me-2"></i>Data {{ $cfg['label'] }} Terdaftar
                  <span class="badge bg-primary ms-1">{{ $totalTerdaftar }}</span>
               </h4>
               <form action="{{ route('admin.import.show', $cfg['key']) }}" method="GET" class="d-flex" style="max-width: 320px;">
                  <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm me-2"
                         placeholder="Cari {{ $cfg['key'] === 'mahasiswa' ? 'NIM' : 'NIP' }} / nama / email...">
                  <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                  @if($search !== '')
                     <a href="{{ route('admin.import.show', $cfg['key']) }}" class="btn btn-sm btn-outline-secondary ms-1" title="Reset"><i class="fas fa-times"></i></a>
                  @endif
               </form>
            </div>
         </div>
         <div class="card-body">
            @if($search !== '')
               <p class="text-muted small mb-2">Menampilkan hasil pencarian untuk "<strong>{{ $search }}</strong>".</p>
            @endif
            <div class="table-responsive">
               <table class="table table-striped table-hover align-middle">
                  <thead class="table-primary">
                     <tr>
                        <th width="5%">No</th>
                        @if($cfg['key'] === 'mahasiswa')
                           <th>NIM</th>
                           <th>Nama</th>
                           <th>Email</th>
                           <th>Program Studi</th>
                        @else
                           <th>NIP</th>
                           <th>Nama</th>
                           <th>Email</th>
                           <th>Username</th>
                           <th>No. Telepon</th>
                        @endif
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($users as $index => $u)
                        <tr>
                           <td>{{ $users->firstItem() + $index }}</td>
                           @if($cfg['key'] === 'mahasiswa')
                              <td><span class="badge bg-secondary">{{ $u->nim ?? '-' }}</span></td>
                              <td>{{ $u->name }}</td>
                              <td><small class="text-muted">{{ $u->email ?? '-' }}</small></td>
                              <td><small class="text-muted">{{ $u->program_studi ?? '-' }}</small></td>
                           @else
                              <td><span class="badge bg-secondary">{{ $u->nip ?? '-' }}</span></td>
                              <td>{{ $u->name }}</td>
                              <td><small class="text-muted">{{ $u->email ?? '-' }}</small></td>
                              <td><small class="text-muted">{{ $u->username ?? '-' }}</small></td>
                              <td><small class="text-muted">{{ $u->phone_number ?? '-' }}</small></td>
                           @endif
                        </tr>
                     @empty
                        <tr>
                           <td colspan="{{ $cfg['key'] === 'mahasiswa' ? 5 : 6 }}" class="text-center py-4">
                              <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                              <h6 class="text-muted mb-0">
                                 {{ $search !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data ' . $cfg['label'] . ' terdaftar.' }}
                              </h6>
                           </td>
                        </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>

            @if($users->hasPages())
               <div class="mt-3">
                  {{ $users->links('pagination::bootstrap-5') }}
               </div>
            @endif
         </div>
      </div>

   </div>
</div>

<script>
$(document).ready(function() {
    setTimeout(function() { $('.alert').fadeOut('slow'); }, 8000);
});
</script>
</x-app-layout>
