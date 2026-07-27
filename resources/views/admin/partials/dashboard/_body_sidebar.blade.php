<aside class="sidebar sidebar-default navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('admin.special-pages.pendaftar') }}" class="navbar-brand sidebar-brand-itk">
            <div class="sidebar-brand-logo-wrap">
                <img src="{{ asset('images/logo/itk-logo-sidebar.png') }}" alt="Institut Teknologi Kalimantan" class="sidebar-brand-logo">
            </div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-inovasi">Inovasi</span>
                <span class="sidebar-brand-sosial">Sosial</span>
            </div>
        </a>
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list" id="sidebar">
        @include('admin.partials.dashboard.vertical-nav') 
        </div>
    </div>
    <div class="sidebar-footer"></div>
</aside>
