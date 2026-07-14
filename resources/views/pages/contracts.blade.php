@extends('layouts.app')
@section('title', __('Contracts & Invoices'))

@php
    $active = request('tab', 'contracts');
    if (! in_array($active, ['contracts', 'invoices'], true)) {
        $active = 'contracts';
    }
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Contracts & Invoices') }}</h1>
            <p class="subtitle">{{ __('Create and track contracts and invoices') }}</p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('contracts.create') }}" class="chip"><i class="bi bi-file-earmark-plus"></i>{{ __('Create Contract') }}</a>
            <a href="{{ route('invoices.create') }}" class="btn-brand"><i class="bi bi-receipt"></i>{{ __('Create Invoice') }}</a>
        </div>
    </div>

    <div class="toolbar full-bleed sheet-aligned" style="padding-block:0;">
        <div class="tabs" style="border:0; margin:0;">
            <a href="{{ route('contracts', ['tab' => 'contracts']) }}" class="tab {{ $active === 'contracts' ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>{{ __('Contracts') }}
            </a>
            <a href="{{ route('contracts', ['tab' => 'invoices']) }}" class="tab {{ $active === 'invoices' ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>{{ __('Invoices') }}
            </a>
        </div>
        <label class="search-input"><i class="bi bi-search"></i><input type="text" placeholder="{{ __('Search...') }}"></label>
    </div>

    @if ($active === 'contracts')
        <div class="full-bleed">
            <div class="sheet-frame">
                <div class="table-wrap">
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Contract No.') }}</th><th>{{ __('Client') }}</th><th>{{ __('Exhibition') }}</th>
                            <th>{{ __('Value') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach ($contracts as $c)
                            <tr class="row-link" onclick="window.location='{{ route('contracts.show', $c['no']) }}'">
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-strong">{{ $c['no'] }}</td>
                                <td class="cell-muted">{{ $c['client'] }}</td>
                                <td class="cell-muted">{{ $c['exhibition'] }}</td>
                                <td class="cell-strong">{{ $c['value'] }}</td>
                                <td class="cell-muted">{{ $c['date'] }}</td>
                                <td>@include('partials.status', ['status' => $c['status']])</td>
                                <td><div class="row-actions" onclick="event.stopPropagation()">
                                    <a href="{{ route('contracts.show', $c['no']) }}" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('contracts.edit', $c['id']) }}" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('contracts.destroy', $c['id']) }}" onsubmit="return confirm('{{ __('Delete this contract?') }}')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="{{ __('Delete') }}" style="background:none;border:0;padding:0;cursor:pointer;color:inherit"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="full-bleed">
            <div class="sheet-frame">
                <div class="table-wrap">
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Invoice No.') }}</th><th>{{ __('Client') }}</th><th>{{ __('Contract') }}</th>
                            <th>{{ __('Amount') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach ($invoices as $inv)
                            <tr class="row-link" onclick="window.location='{{ route('invoices.show', $inv['no']) }}'">
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-strong">{{ $inv['no'] }}</td>
                                <td class="cell-muted">{{ $inv['client'] }}</td>
                                <td class="cell-muted">{{ $inv['contract'] }}</td>
                                <td class="cell-strong">{{ $inv['amount'] }}</td>
                                <td class="cell-muted">{{ $inv['date'] }}</td>
                                <td>@include('partials.status', ['status' => $inv['status']])</td>
                                <td><div class="row-actions" onclick="event.stopPropagation()">
                                    <a href="{{ route('invoices.show', $inv['no']) }}" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('invoices.edit', $inv['id']) }}" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('invoices.destroy', $inv['id']) }}" onsubmit="return confirm('{{ __('Delete this invoice?') }}')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="{{ __('Delete') }}" style="background:none;border:0;padding:0;cursor:pointer;color:inherit"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
