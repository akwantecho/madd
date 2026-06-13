@extends('layouts.app')
@section('title', __('Customers'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Customers') }}</h1>
            <p class="subtitle">{{ count($customers) }} {{ __('Customers') }}</p>
        </div>
        <button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Customer') }}</button>
    </div>

    <div class="chip-row">
        <button class="chip"><i class="bi bi-search"></i>{{ __('Search...') }}</button>
        <button class="chip"><i class="bi bi-building"></i>{{ __('Company') }}<i class="bi bi-chevron-down"></i></button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" class="checkbox"></th>
                        <th>{{ __('Name') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Bookings') }}</th>
                        <th>{{ __('Joined') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($customers as $c)
                        <tr>
                            <td><input type="checkbox" class="checkbox"></td>
                            <td>
                                <div class="user-cell">
                                    <span class="avatar sm">{{ mb_substr($c['name'], 0, 1) }}</span>
                                    <span class="cell-strong">{{ $c['name'] }}</span>
                                </div>
                            </td>
                            <td class="cell-muted">{{ $c['email'] }}</td>
                            <td class="cell-muted" dir="ltr">{{ $c['phone'] }}</td>
                            <td class="cell-muted">{{ $c['company'] }}</td>
                            <td class="cell-strong">{{ $c['bookings'] }}</td>
                            <td class="cell-muted">{{ $c['joined'] }}</td>
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
    </div>
@endsection
