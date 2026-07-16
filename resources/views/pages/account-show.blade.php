@extends('layouts.app')
@section('title', __($account['name']))

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned" style="align-items:flex-start;">
        <div>
            <a href="{{ route('finance', ['tab' => 'accounts']) }}" class="section-link"><i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}-short"></i> {{ __('Bank Accounts') }}</a>
            <h1 class="mt-1"><i class="bi {{ $account['icon'] }} me-1"></i>{{ __($account['name']) }}</h1>
            <p class="subtitle">
                {{ __('Book Balance') }}: {{ $account['book'] }} {{ __('OMR') }}
                · {{ __('Difference') }}: {{ $account['difference'] }} {{ __('OMR') }}
                @if ($account['balanced'])
                    · <span class="badge-soft green">{{ __('Balanced') }}</span>
                @endif
            </p>
        </div>
        <button class="btn-brand"><i class="bi bi-upload"></i>{{ __('Import Statement') }}</button>
    </div>

    <div class="toolbar full-bleed sheet-aligned" style="padding-block:0;">
        <div class="tabs">
            @foreach ($tabs as $key => $tab)
                <a href="{{ route('finance.account', [$account['id'], 'tab' => $key]) }}"
                   class="tab {{ $active === $key ? 'active' : '' }}">
                    <i class="bi {{ $tab['icon'] }}"></i>{{ __($tab['label']) }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($active === 'expenses')
        <div class="full-bleed">
            <div class="sheet-frame">
                <div class="table-wrap">
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach ($expenses as $e)
                            <tr>
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-muted">{{ $e['date'] }}</td>
                                <td class="cell-strong">{{ $e['desc'] }}</td>
                                <td class="cell-muted">{{ $e['category'] }}</td>
                                <td class="cell-strong">{{ $e['amount'] }} {{ __('OMR') }}</td>
                                <td>@include('partials.status', ['status' => $e['status']])</td>
                                <td><div class="row-actions"><a href="#" title="{{ __('View') }}"><i class="bi bi-eye"></i></a><button class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button></div></td>
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
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Source') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach ($revenues as $r)
                            <tr>
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-muted">{{ $r['date'] }}</td>
                                <td class="cell-strong">{{ $r['source'] }}</td>
                                <td class="cell-muted">{{ $r['ref'] }}</td>
                                <td class="cell-strong">{{ $r['amount'] }} {{ __('OMR') }}</td>
                                <td>@include('partials.status', ['status' => $r['status']])</td>
                                <td><div class="row-actions"><a href="#" title="{{ __('View') }}"><i class="bi bi-eye"></i></a><button class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button></div></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
