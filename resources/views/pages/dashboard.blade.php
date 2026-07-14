@extends('layouts.app')
@section('title', __('Dashboard'))

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Welcome back') }}, Admin</h1>
            <p class="subtitle">{{ __('Recent Exhibitions') }}</p>
        </div>
        <a href="{{ route('exhibitions') }}" class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Exhibition') }}</a>
    </div>

    <div class="toolbar full-bleed sheet-aligned">
        <div class="toolbar-start">
            <label class="search-input"><i class="bi bi-search"></i><input type="text" placeholder="{{ __('Search...') }}"></label>
            <button class="chip"><i class="bi bi-geo-alt"></i>{{ __('Location') }}<i class="bi bi-chevron-down"></i></button>
            <button class="chip"><i class="bi bi-funnel"></i>{{ __('Status') }}<i class="bi bi-chevron-down"></i></button>
        </div>
        <a href="{{ route('exhibitions') }}" class="section-link">{{ __('View all') }} →</a>
    </div>

    <div class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                <table class="data sheet">
                    <thead>
                    <tr>
                        <th style="width:34px"><input type="checkbox" class="checkbox"></th>
                        <th style="width:46px">#</th>
                        <th>{{ __('Title') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Start Date') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Tag') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($recent as $ex)
                        <tr>
                            <td><input type="checkbox" class="checkbox"></td>
                            <td class="cell-muted">{{ $loop->iteration }}</td>
                            <td class="cell-strong">{{ $ex['title'] }}</td>
                            <td class="cell-muted"><i class="bi bi-geo-alt me-1"></i>{{ $ex['location'] }}</td>
                            <td class="cell-muted">{{ $ex['start'] }}</td>
                            <td>@include('partials.status', ['status' => $ex['status']])</td>
                            <td><span class="badge-soft {{ $ex['tagColor'] }}">{{ __($ex['tag']) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cell-muted" style="text-align:center; padding:2rem;">{{ __('No exhibitions yet') }}</td></tr>
                    @endforelse
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
            </div>
            <button class="page-btn">{{ __('Next') }}</button>
        </div>
    </div>
@endsection
