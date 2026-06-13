@extends('layouts.app')
@section('title', __('Archive'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Archive') }}</h1>
            <p class="subtitle">{{ __('Archived Items') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" class="checkbox"></th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Archived On') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td><input type="checkbox" class="checkbox"></td>
                            <td class="cell-strong"><i class="bi bi-archive me-2 cell-muted"></i>{{ $item['title'] }}</td>
                            <td><span class="pill completed"><i class="bi bi-tag"></i>{{ __($item['type']) }}</span></td>
                            <td class="cell-muted">{{ $item['date'] }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="#" title="{{ __('Restore') }}"><i class="bi bi-arrow-counterclockwise"></i></a>
                                    <button class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
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
