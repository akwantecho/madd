@extends('layouts.app')
@section('title', __('Archive'))

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Archive') }}</h1>
            <p class="subtitle">{{ __('Archived Items') }}</p>
        </div>
    </div>

    <div class="toolbar full-bleed sheet-aligned">
        <div class="toolbar-start">
            <label class="search-input"><i class="bi bi-search"></i><input type="text" placeholder="{{ __('Search...') }}"></label>
            <button class="chip"><i class="bi bi-funnel"></i>{{ __('Type') }}<i class="bi bi-chevron-down"></i></button>
        </div>
    </div>

    <div class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                <table class="data sheet">
                    <thead>
                    <tr>
                        <th style="width:34px"><input type="checkbox" class="checkbox"></th>
                        <th style="width:46px">#</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Archived On') }}</th>
                        <th style="width:90px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><input type="checkbox" class="checkbox"></td>
                            <td class="cell-muted">{{ $loop->iteration }}</td>
                            <td class="cell-strong"><i class="bi bi-archive me-2 cell-muted"></i>{{ $item['title'] }}</td>
                            <td><span class="badge-soft gray"><i class="bi bi-tag"></i>{{ __($item['type']) }}</span></td>
                            <td class="cell-muted">{{ $item['date'] }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="#" title="{{ __('Restore') }}"><i class="bi bi-arrow-counterclockwise"></i></a>
                                    <button class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="cell-muted" style="text-align:center; padding:2rem;">{{ __('No archived items') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
