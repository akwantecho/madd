@extends('layouts.app')
@section('title', __('Dashboard'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Welcome back') }}, Admin</h1>
            <p class="subtitle">{{ __('Here is what is happening today') }}</p>
        </div>
        <a href="{{ route('exhibitions') }}" class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Exhibition') }}</a>
    </div>

    {{-- Filter chips --}}
    <div class="chip-row">
        <button class="chip"><i class="bi bi-calendar3"></i>{{ __('Last 7 days') }}<i class="bi bi-chevron-down"></i></button>
        <button class="chip"><i class="bi bi-funnel"></i>{{ __('All Status') }}<i class="bi bi-chevron-down"></i></button>
    </div>

    {{-- Stat cards (two metrics each) --}}
    <div class="stat-grid">
        @foreach ($cards as $card)
            <div class="stat-card">
                <div class="sc-head">
                    <h3>{{ __($card['title']) }}</h3>
                    <i class="bi bi-three-dots"></i>
                </div>
                <div class="stat-metrics">
                    @foreach ($card['metrics'] as $m)
                        <div class="stat-metric">
                            <div class="m-label">{{ __($m['label']) }}</div>
                            <div class="m-row">
                                <i class="bi {{ $m['icon'] }}"></i>
                                <span class="m-value">{{ $m['value'] }}</span>
                                <span class="delta {{ $m['dir'] }}">
                                    <i class="bi bi-arrow-{{ $m['dir'] }}-short"></i>{{ ltrim($m['change'], '+-') }}
                                </span>
                            </div>
                            <div class="m-foot">{{ __('vs last week') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Recent exhibitions table --}}
    <div class="card">
        <div class="card-head">
            <h2>{{ __('Recent Exhibitions') }}</h2>
            <a href="{{ route('exhibitions') }}" class="section-link">{{ __('View all') }} →</a>
        </div>

        <div class="chip-row" style="padding: 14px 18px 0; margin: 0;">
            <button class="chip"><i class="bi bi-search"></i>{{ __('Search...') }}</button>
            <button class="chip"><i class="bi bi-geo-alt"></i>{{ __('Location') }}<i class="bi bi-chevron-down"></i></button>
            <button class="chip"><i class="bi bi-funnel"></i>{{ __('Status') }}<i class="bi bi-chevron-down"></i></button>
        </div>

        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" class="checkbox"></th>
                        <th>{{ __('Title') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Start Date') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Tags') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($recent as $ex)
                        <tr>
                            <td><input type="checkbox" class="checkbox"></td>
                            <td>
                                <div class="user-cell">
                                    <span class="avatar sm">{{ mb_substr($ex['title'], 0, 1) }}</span>
                                    <span class="cell-strong">{{ $ex['title'] }}</span>
                                </div>
                            </td>
                            <td class="cell-muted"><i class="bi bi-geo-alt me-1"></i>{{ $ex['location'] }}</td>
                            <td class="cell-muted">{{ $ex['start'] }}</td>
                            <td>@include('partials.status', ['status' => $ex['status']])</td>
                            <td><span class="tag {{ $ex['tagColor'] }}">{{ __($ex['tag']) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-bar">
            <button class="page-btn">{{ __('Previous') }}</button>
            <div class="page-nums">
                <span class="page-num active">1</span>
                <span class="page-num">2</span>
                <span class="page-num">3</span>
                <span class="cell-muted">…</span>
                <span class="page-num">8</span>
            </div>
            <button class="page-btn">{{ __('Next') }}</button>
        </div>
    </div>
@endsection
