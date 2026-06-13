@extends('layouts.app')
@section('title', __('Exhibitions'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Exhibitions') }}</h1>
            <p class="subtitle">{{ count($exhibitions) }} {{ __('Exhibitions') }}</p>
        </div>
        <button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Exhibition') }}</button>
    </div>

    <div class="chip-row">
        <button class="chip"><i class="bi bi-search"></i>{{ __('Search...') }}</button>
        <button class="chip"><i class="bi bi-geo-alt"></i>{{ __('Location') }}<i class="bi bi-chevron-down"></i></button>
        <button class="chip"><i class="bi bi-funnel"></i>{{ __('Status') }}<i class="bi bi-chevron-down"></i></button>
        <button class="chip"><i class="bi bi-calendar3"></i>{{ __('Date') }}<i class="bi bi-chevron-down"></i></button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" class="checkbox"></th>
                        <th>{{ __('Title') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Start Date') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('End Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($exhibitions as $ex)
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
                            <td class="cell-muted">{{ $ex['end'] }}</td>
                            <td>@include('partials.status', ['status' => $ex['status']])</td>
                            <td>
                                <div class="row-actions">
                                    <a href="#" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                    <a href="#" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                    <button class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                </div>
                            </td>
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
            </div>
            <button class="page-btn">{{ __('Next') }}</button>
        </div>
    </div>
@endsection
