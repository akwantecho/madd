@extends('layouts.app')
@section('title', __('Reports'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Reports') }}</h1>
            <p class="subtitle">{{ __('Analytics and exportable reports across the system') }}</p>
        </div>
        <button class="btn-brand"><i class="bi bi-download"></i>{{ __('Export') }}</button>
    </div>

    {{-- Summary KPIs --}}
    <div class="kpi-grid">
        @foreach ($summary as $s)
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-icon {{ $s['color'] }}"><i class="bi {{ $s['icon'] }}"></i></span>
                    <span class="delta {{ $s['dir'] }}">
                        <i class="bi bi-arrow-{{ $s['dir'] === 'up' ? 'up' : 'down' }}"></i>{{ $s['change'] }}
                    </span>
                </div>
                <div class="k-value">{{ $s['value'] }}</div>
                <div class="k-label">{{ __($s['key']) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Available reports --}}
    <div class="card-head" style="border:0; padding:0 0 14px;">
        <h2>{{ __('Available Reports') }}</h2>
    </div>
    <div class="grid-3" style="margin-bottom: 24px;">
        @foreach ($reports as $r)
            <a href="{{ route($r['route']) }}" class="hub-card">
                <span class="hub-icon {{ $r['color'] }}"><i class="bi {{ $r['icon'] }}"></i></span>
                <div>
                    <div class="hub-title">{{ __($r['title']) }}</div>
                    <div class="hub-desc">{{ $r['desc'] }}</div>
                </div>
                <span class="hub-count"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}" style="font-size:16px;color:var(--muted-2);"></i></span>
            </a>
        @endforeach
    </div>

    {{-- Recently generated reports --}}
    <div class="card">
        <div class="card-head">
            <h2>{{ __('Recently Generated') }}</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th>{{ __('Report') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Format') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($recent as $item)
                        <tr>
                            <td class="cell-strong"><i class="bi bi-file-earmark-bar-graph me-1"></i>{{ $item['title'] }}</td>
                            <td class="cell-muted">{{ __($item['type']) }}</td>
                            <td class="cell-muted">{{ $item['date'] }}</td>
                            <td><span class="badge-soft gray">{{ $item['format'] }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="#" title="{{ __('Download') }}"><i class="bi bi-download"></i></a>
                                    <a href="#" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
