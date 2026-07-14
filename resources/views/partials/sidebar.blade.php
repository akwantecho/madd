<?php
    $links = [
        ['route' => 'tasks',       'label' => 'Tasks',                'icon' => 'bi-list-check'],
        ['route' => 'exhibitions', 'label' => 'Exhibitions',          'icon' => 'bi-easel2'],
        ['route' => 'contacts',    'label' => 'Contacts',             'icon' => 'bi-person-rolodex'],
        ['route' => 'finance',     'label' => 'Finance',              'icon' => 'bi-wallet2'],
        ['route' => 'contracts',   'label' => 'Contracts & Invoices', 'icon' => 'bi-file-earmark-text'],
        ['route' => 'archive',     'label' => 'Archive',              'icon' => 'bi-archive'],
        ['route' => 'stock',       'label' => 'Stock',                'icon' => 'bi-box-seam'],
        ['route' => 'reports',     'label' => 'Reports',              'icon' => 'bi-bar-chart'],
        ['route' => 'settings',    'label' => 'Settings',             'icon' => 'bi-gear'],
    ];
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/logo.svg') }}" alt="{{ __('Event Puls') }}" class="brand-logo">
        <i class="bi bi-chevron-double-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} brand-collapse"></i>
    </div>

    <div class="sidebar-user">
        <div class="user-chip">
            <span class="avatar">SA</span>
            <div class="user-meta">
                <strong>Admin</strong>
                <small>{{ Auth::user()->email ?? 'admin@eventpuls.sa' }}</small>
            </div>
            <i class="bi bi-chevron-expand"></i>
        </div>
    </div>

    <div class="sidebar-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="{{ __('Search...') }}">
        <kbd class="search-kbd">⌘K</kbd>
    </div>

    <nav class="sidebar-nav">
        @foreach ($links as $item)
            <a href="{{ route($item['route']) }}"
               class="nav-link {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'active' : '' }}">
                <i class="bi {{ $item['icon'] }}"></i>
                <span>{{ __($item['label']) }}</span>
            </a>
        @endforeach
    </nav>
</aside>
