@extends('layouts.app')
@section('title', __('Finance'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Finance') }}</h1>
            <p class="subtitle">{{ __('Revenue Overview') }}</p>
        </div>
        <button class="chip"><i class="bi bi-download"></i>{{ __('Export') }}</button>
    </div>

    <div class="kpi-grid">
        @foreach ($summary as $s)
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon {{ $s['color'] }}"><i class="bi {{ $s['icon'] }}"></i></div>
                </div>
                <div class="k-value">{{ $s['value'] }}</div>
                <div class="k-label">{{ __($s['key']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-head">
            <h2>{{ __('Recent Invoices') }}</h2>
            <a href="#" class="section-link">{{ __('View all') }} →</a>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($invoices as $inv)
                        <tr>
                            <td class="cell-strong">{{ $inv['no'] }}</td>
                            <td>{{ $inv['customer'] }}</td>
                            <td class="cell-strong">{{ $inv['amount'] }}</td>
                            <td class="cell-muted">{{ $inv['date'] }}</td>
                            <td>@include('partials.status', ['status' => $inv['status']])</td>
                            <td>
                                <div class="row-actions">
                                    <a href="#" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                    <a href="#" title="Download"><i class="bi bi-download"></i></a>
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
