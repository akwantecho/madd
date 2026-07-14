@php
    $map = [
        'High'   => 'red',
        'Medium' => 'amber',
        'Low'    => 'green',
        'Normal' => 'blue',
    ];
    $color = $map[$priority] ?? 'gray';
@endphp
<span class="badge-soft {{ $color }}">{{ __($priority) }}</span>
