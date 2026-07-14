@extends('layouts.app')
@section('title', __('Data'))

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Data') }}</h1>
            <p class="subtitle">{{ __('Master data and reference directories') }}</p>
        </div>
    </div>

    <div class="full-bleed sheet-aligned" style="padding-block:16px;">
        <div class="grid-3">
            @foreach ($sections as $s)
                <a href="{{ route($s['route'], $s['param']) }}" class="hub-card">
                    <span class="hub-icon {{ $s['color'] }}"><i class="bi {{ $s['icon'] }}"></i></span>
                    <div>
                        <div class="hub-title">{{ __($s['title']) }}</div>
                        <div class="hub-desc">{{ $s['desc'] }}</div>
                    </div>
                    <span class="hub-count">{{ $s['count'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endsection
