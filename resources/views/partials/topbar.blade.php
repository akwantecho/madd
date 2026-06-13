<header class="topbar">
    <div class="topbar-start">
        <button class="btn-icon d-lg-none" id="sidebarToggle" type="button" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <nav class="breadcrumb-nav">
            <i class="bi bi-house-door"></i>
            <span class="sep"><i class="bi {{ app()->getLocale() === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right' }}"></i></span>
            <span class="current">@yield('title', __('Dashboard'))</span>
        </nav>
    </div>

    <div class="topbar-end">
        @php $isAr = app()->getLocale() === 'ar'; @endphp
        <a class="btn-icon" href="{{ route('locale.switch', $isAr ? 'en' : 'ar') }}"
           title="{{ $isAr ? __('English') : __('Arabic') }}">
            <i class="bi bi-translate"></i>
            <span class="lang-label">{{ $isAr ? 'EN' : 'ع' }}</span>
        </a>

        <a class="btn-icon" href="{{ route('settings') }}" title="{{ __('Settings') }}"><i class="bi bi-gear"></i></a>

        <button class="btn-icon position-relative" type="button" title="{{ __('Notifications') }}">
            <i class="bi bi-bell"></i>
            <span class="badge-dot"></span>
        </button>

        <div class="dropdown">
            <button class="btn-icon topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar sm">SA</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-person me-2"></i>{{ __('Profile') }}</a></li>
                <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i>{{ __('Settings') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}</a></li>
            </ul>
        </div>
    </div>
</header>
