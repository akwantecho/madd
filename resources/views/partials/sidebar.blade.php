<?php
    $groups = [
        'Main' => [
            ['route' => 'dashboard',   'label' => 'Dashboard',   'icon' => 'bi-house'],
            ['route' => 'exhibitions', 'label' => 'Exhibitions', 'icon' => 'bi-easel2'],
            ['route' => 'customers',   'label' => 'Customers',   'icon' => 'bi-people'],
            ['route' => 'finance',     'label' => 'Finance',     'icon' => 'bi-wallet2'],
        ],
        'Utility' => [
            ['route' => 'archive',  'label' => 'Archive',  'icon' => 'bi-archive'],
            ['route' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear'],
        ],
    ];
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-mark"><i class="bi bi-broadcast-pin"></i></span>
        <span class="brand-text">{{ __('Event Puls') }}</span>
    </div>

    <div class="sidebar-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="{{ __('Search...') }}">
    </div>

    <nav class="sidebar-nav">
        @foreach ($groups as $label => $items)
            <div class="nav-group-label">{{ __($label) }}</div>
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ __($item['label']) }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="user-chip">
            <span class="avatar">SA</span>
            <div class="user-meta">
                <strong>Admin</strong>
                <small>{{ Auth::user()->email ?? 'admin@eventpuls.sa' }}</small>
            </div>
            <i class="bi bi-three-dots"></i>
        </div>
    </div>
</aside>
