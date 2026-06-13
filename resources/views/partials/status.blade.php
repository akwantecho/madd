@php
    $icons = [
        'Active'    => 'bi-broadcast',
        'Upcoming'  => 'bi-clock',
        'Completed' => 'bi-check-circle',
        'Cancelled' => 'bi-x-circle',
        'Paid'      => 'bi-check-circle',
        'Unpaid'    => 'bi-hourglass-split',
        'Overdue'   => 'bi-exclamation-circle',
    ];
    $icon = $icons[$status] ?? 'bi-circle';
@endphp
<span class="pill {{ strtolower($status) }}"><i class="bi {{ $icon }}"></i>{{ __($status) }}</span>
