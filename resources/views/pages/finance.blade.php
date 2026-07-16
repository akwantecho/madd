@extends('layouts.app')
@section('title', __('Finance'))

@php
    $active = request('tab', 'accounts');
    if (! in_array($active, ['accounts', 'invoices'], true)) {
        $active = 'accounts';
    }
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Finance') }}</h1>
            <p class="subtitle">{{ $active === 'invoices' ? __('Recent Invoices') : __('Bank Accounts') }}</p>
        </div>
        @if ($active === 'accounts')
            <button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Bank Account') }}</button>
        @else
            <button class="btn-brand"><i class="bi bi-download"></i>{{ __('Export') }}</button>
        @endif
    </div>

    <div class="toolbar full-bleed sheet-aligned" style="padding-block:0;">
        <div class="tabs">
            <a href="{{ route('finance', ['tab' => 'accounts']) }}" class="tab {{ $active === 'accounts' ? 'active' : '' }}">
                <i class="bi bi-bank"></i>{{ __('Bank Accounts') }}
            </a>
            <a href="{{ route('finance', ['tab' => 'invoices']) }}" class="tab {{ $active === 'invoices' ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>{{ __('Invoices') }}
            </a>
        </div>
    </div>

    @if ($active === 'accounts')
        <div class="full-bleed" style="padding:16px 400px;">
            <div class="acct-grid">
                @foreach ($accounts as $a)
                    <a href="{{ route('finance.account', $a['id']) }}" class="card acct-card-link">
                        <div class="card-head">
                            <div class="acct-title">
                                <span class="acct-icon"><i class="bi {{ $a['icon'] }}"></i></span>
                                <h2>{{ __($a['name']) }}</h2>
                            </div>
                            <div class="acct-actions">
                                <button type="button" class="chip" onclick="event.preventDefault(); event.stopPropagation();"><i class="bi bi-upload"></i>{{ __('Import Statement') }}</button>
                                <button type="button" class="btn-icon" onclick="event.preventDefault(); event.stopPropagation();"><i class="bi bi-three-dots-vertical"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="info-list">
                                <li>
                                    <span class="k"><i class="bi bi-journal-text me-1"></i>{{ __('Book Balance') }}</span>
                                    <span class="v">{{ $a['book'] }} {{ __('OMR') }}</span>
                                </li>
                                <li>
                                    <span class="k"><i class="bi bi-receipt me-1"></i>{{ __('Statement Balance') }}</span>
                                    <span class="v">{{ $a['statement'] }} {{ __('OMR') }}</span>
                                </li>
                                <li>
                                    <span class="k">
                                        <i class="bi bi-credit-card-2-front me-1"></i>{{ __('Difference') }}
                                        @if ($a['balanced'])
                                            <span class="badge-soft green">{{ __('Balanced') }}</span>
                                        @endif
                                    </span>
                                    <span class="v">{{ $a['difference'] }} {{ __('OMR') }}</span>
                                </li>
                            </ul>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="full-bleed">
            <div class="sheet-frame">
                <div class="table-wrap">
                    <table class="data sheet">
                        <thead>
                        <tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Invoice') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="width:120px">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($invoices as $inv)
                            <tr>
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-strong">{{ $inv['no'] }}</td>
                                <td>{{ $inv['customer'] }}</td>
                                <td class="cell-strong">{{ $inv['amount'] }}</td>
                                <td class="cell-muted">{{ $inv['date'] }}</td>
                                <td>@include('partials.status', ['status' => $inv['status']])</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="#" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        <a href="#" title="{{ __('Download') }}"><i class="bi bi-download"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
