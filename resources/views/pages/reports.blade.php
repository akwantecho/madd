@extends('layouts.app')
@section('title', __('Reports'))

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Reports') }}</h1>
            <p class="subtitle">{{ __('Analytics and exportable reports across the system') }}</p>
        </div>
        <button class="btn-brand"><i class="bi bi-download"></i>{{ __('Export') }}</button>
    </div>

    {{-- Available reports --}}
    <div class="toolbar full-bleed sheet-aligned">
        <strong>{{ __('Available Reports') }}</strong>
    </div>
    <div class="full-bleed sheet-aligned" style="padding-block:16px;">
        <div class="grid-3">
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
    </div>

    {{-- Recently generated --}}
    <div class="toolbar full-bleed sheet-aligned">
        <strong>{{ __('Recently Generated') }}</strong>
    </div>
    <div class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                <table class="data sheet">
                    <thead>
                    <tr>
                        <th style="width:46px">#</th>
                        <th>{{ __('Report') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Format') }}</th>
                        <th style="width:90px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($recent as $item)
                        <tr>
                            <td class="cell-muted">{{ $loop->iteration }}</td>
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
                    @empty
                        <tr><td colspan="6" class="cell-muted" style="text-align:center; padding:2rem;">{{ __('No reports generated yet') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
