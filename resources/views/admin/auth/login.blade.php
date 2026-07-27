@extends('admin.layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login-itk.css') }}">
@endpush

@section('content')
<div class="login-itk-page">
    <div class="login-itk-card">
        {{-- Form login --}}
        <div class="login-itk-form-side">
            <img src="{{ asset('images/logo/itk-logo-full.png') }}" alt="Institut Teknologi Kalimantan" class="login-itk-logo">

            <h1 class="login-itk-title">Selamat Datang</h1>

            <div class="login-itk-subtitle-wrap">
                <p class="login-itk-subtitle">Silahkan Melakukan Log In</p>
            </div>

            @if(session('status'))
                <div class="login-itk-alert login-itk-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="login-itk-alert login-itk-alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="login-itk-field">
                    <label for="username">Email / NIM</label>
                    <div class="login-itk-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ env('IS_DEMO') ? 'admin@example.com' : old('username') }}"
                            placeholder="Masukkan Email atau NIM"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="login-itk-field">
                    <label for="password">Password</label>
                    <div class="login-itk-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            value="{{ env('IS_DEMO') ? 'password' : '' }}"
                            placeholder="Masukkan Password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <div class="login-itk-remember">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label for="remember">Remember Me</label>
                </div>

                <button type="submit" class="login-itk-btn">Masuk</button>
            </form>
        </div>

        {{-- Ilustrasi --}}
        <div class="login-itk-illustration-side">
            <img src="{{ asset('images/auth/login-illustration.png') }}" alt="Ilustrasi edukasi">
        </div>
    </div>
</div>
@endsection
