<x-app-layout :assets="$assets ?? []">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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

<div class="profile-header-card">
    <div class="profile-avatar" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
    </div>
    <div>
        <h1 class="profile-header-name">{{ $displayName }}</h1>
        <p class="profile-header-email">{{ $user->email }}</p>
        <span class="profile-role-badge">
            <i class="fas fa-user"></i>
            {{ $roleLabel }}
        </span>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        @php
            $profileEditMode = $errors->hasAny(['name', 'email', 'username', 'phone_number']);
            $phoneValue = old('phone_number', $user->phone_number ?? $user->userProfile?->phone_number ?? '');
        @endphp
        <div class="profile-card">
            <div class="profile-card-header profile-card-header-actions">
                <span class="profile-card-title">
                    <i class="fas fa-id-card text-primary"></i>
                    Informasi Akun
                </span>
                <button type="button" class="profile-btn-edit" id="profile-edit-toggle" @if($profileEditMode) style="display:none" @endif>
                    <i class="fas fa-pen"></i>
                    Edit Profil
                </button>
            </div>
            <div class="profile-card-body">
                <div class="profile-info-grid" id="profile-info-view" @if($profileEditMode) style="display:none" @endif>
                    <div class="profile-info-item">
                        <label>Nama Lengkap</label>
                        <p id="profile-view-name">{{ $displayName }}</p>
                    </div>
                    <div class="profile-info-item">
                        <label>Hak Akses</label>
                        <p>{{ $roleLabel }}</p>
                    </div>
                    <div class="profile-info-item">
                        <label>Email</label>
                        <p id="profile-view-email">{{ $user->email }}</p>
                    </div>
                    <div class="profile-info-item">
                        <label>NIM / NIP</label>
                        <p>{{ $user->nim ?? $user->nip ?? '-' }}</p>
                    </div>
                    <div class="profile-info-item">
                        <label>Nomor Telepon</label>
                        <p id="profile-view-phone">{{ $phoneValue !== '' ? $phoneValue : '-' }}</p>
                    </div>
                    <div class="profile-info-item">
                        <label>Username</label>
                        <p id="profile-view-username">{{ $user->username ?? '-' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" id="profile-info-form" @if(!$profileEditMode) style="display:none" @endif>
                    @csrf
                    @method('PUT')

                    <div class="profile-info-grid">
                        <div class="profile-field">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name" class="profile-input" value="{{ old('name', $displayName) }}" required>
                            @error('name')
                                <span class="profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="profile-info-item">
                            <label>Hak Akses</label>
                            <p class="profile-readonly-value">{{ $roleLabel }}</p>
                        </div>
                        <div class="profile-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="profile-input" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="profile-info-item">
                            <label>NIM / NIP</label>
                            <p class="profile-readonly-value">{{ $user->nim ?? $user->nip ?? '-' }}</p>
                        </div>
                        <div class="profile-field">
                            <label for="phone_number">Nomor Telepon</label>
                            <input type="text" id="phone_number" name="phone_number" class="profile-input" value="{{ $phoneValue }}" placeholder="Contoh: 081234567890">
                            @error('phone_number')
                                <span class="profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="profile-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="profile-input" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <span class="profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="profile-form-actions">
                        <button type="button" class="profile-btn-cancel" id="profile-edit-cancel">
                            Batal
                        </button>
                        <button type="submit" class="profile-btn-update">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="profile-card">
            <div class="profile-card-header">
                <i class="fas fa-lock text-primary"></i>
                Keamanan Akun
            </div>
            <div class="profile-card-body">
                <form method="POST" action="{{ route('admin.profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="profile-field">
                        <label for="current_password">Password Saat Ini</label>
                        <div class="profile-password-wrap">
                            <input type="password" id="current_password" name="current_password" placeholder="Masukkan password lama" required autocomplete="current-password">
                            <button type="button" class="profile-toggle-password" data-target="current_password" aria-label="Tampilkan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="profile-password-row">
                        <div class="profile-field">
                            <label for="password">Password Baru</label>
                            <div class="profile-password-wrap">
                                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                                <button type="button" class="profile-toggle-password" data-target="password" aria-label="Tampilkan password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="profile-field">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <div class="profile-password-wrap">
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" required autocomplete="new-password">
                                <button type="button" class="profile-toggle-password" data-target="password_confirmation" aria-label="Tampilkan password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="profile-btn-update">
                            <i class="fas fa-save"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var editToggle = document.getElementById('profile-edit-toggle');
    var editCancel = document.getElementById('profile-edit-cancel');
    var infoView = document.getElementById('profile-info-view');
    var infoForm = document.getElementById('profile-info-form');

    function showProfileEdit() {
        if (!infoView || !infoForm || !editToggle) return;
        infoView.style.display = 'none';
        infoForm.style.display = 'block';
        editToggle.style.display = 'none';
    }

    function hideProfileEdit() {
        if (!infoView || !infoForm || !editToggle) return;
        infoView.style.display = 'grid';
        infoForm.style.display = 'none';
        editToggle.style.display = 'inline-flex';
    }

    if (editToggle) {
        editToggle.addEventListener('click', showProfileEdit);
    }

    if (editCancel) {
        editCancel.addEventListener('click', hideProfileEdit);
    }
})();

document.querySelectorAll('.profile-toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var icon = btn.querySelector('i');
        if (!input) return;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
    });
});
</script>
</x-app-layout>
