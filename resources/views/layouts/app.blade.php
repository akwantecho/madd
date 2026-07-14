<?php $rtl = app()->getLocale() === 'ar'; ?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) — {{ __('Event Puls System') }}</title>

    {{-- Bootstrap 5 (RTL or LTR build depending on locale) --}}
    @if ($rtl)
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="layout">
    @include('partials.sidebar')

    <div class="main">
        @include('partials.topbar')

        <main class="content">
            @if (session('status'))
                <div class="flash flash-ok" role="status"><i class="bi bi-check-circle-fill"></i><span>{{ session('status') }}</span><button type="button" class="flash-x" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button></div>
            @endif
            @if ($errors->any())
                <div class="flash flash-err" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><span>{{ $errors->first() }}</span><button type="button" class="flash-x" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button></div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

{{-- Mobile sidebar backdrop --}}
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/dropdown.js') }}"></script>
<script>
    (function () {
        const toggle = document.getElementById('sidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');
        const open = () => document.body.classList.add('sidebar-open');
        const close = () => document.body.classList.remove('sidebar-open');
        if (toggle) toggle.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        if (backdrop) backdrop.addEventListener('click', close);
    })();
</script>
@stack('scripts')
</body>
</html>
