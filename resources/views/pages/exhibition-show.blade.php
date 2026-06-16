@extends('layouts.app')
@section('title', $exhibition['title'])

@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('exhibitions') }}" class="section-link"><i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}-short"></i> {{ __('Exhibitions') }}</a>
            <h1 class="mt-1">{{ $exhibition['title'] }}</h1>
            <p class="subtitle">
                <i class="bi bi-geo-alt"></i> {{ $exhibition['location'] }}
                · {{ $exhibition['start'] }} → {{ $exhibition['end'] }}
                · @include('partials.status', ['status' => $exhibition['status']])
                @if (!empty($exhibition['tag']))
                    · <span class="badge-soft {{ $exhibition['tagColor'] ?? 'gray' }}">{{ __($exhibition['tag']) }}</span>
                @endif
            </p>
        </div>
        <button class="btn-brand"><i class="bi bi-pencil"></i>{{ __('Edit') }}</button>
    </div>

    <div class="tabs">
        @foreach ($tabs as $key => $tab)
            <a href="{{ route('exhibitions.show', [$exhibition['id'], 'tab' => $key]) }}"
               class="tab {{ $active === $key ? 'active' : '' }}">
                <i class="bi {{ $tab['icon'] }}"></i>{{ __($tab['label']) }}
            </a>
        @endforeach
    </div>

    @if ($active === 'summary')
        <div class="kpi-grid">
            @foreach ($summary as $s)
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-icon {{ $s['color'] }}"><i class="bi {{ $s['icon'] }}"></i></span>
                    </div>
                    <div class="k-value">{{ $s['value'] }}</div>
                    <div class="k-label">{{ __($s['key']) }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-head"><h2>{{ __('Exhibition Details') }}</h2></div>
                <div class="card-body">
                    <ul class="info-list">
                        <li><span class="k">{{ __('Title') }}</span><span class="v">{{ $exhibition['title'] }}</span></li>
                        <li><span class="k">{{ __('Location') }}</span><span class="v">{{ $exhibition['location'] }}</span></li>
                        <li><span class="k">{{ __('Start Date') }}</span><span class="v">{{ $exhibition['start'] }}</span></li>
                        <li><span class="k">{{ __('End Date') }}</span><span class="v">{{ $exhibition['end'] }}</span></li>
                        <li><span class="k">{{ __('Status') }}</span><span class="v">{{ __($exhibition['status']) }}</span></li>
                    </ul>

                    @php
                        $taskList = collect($tasks);
                        $done = $taskList->where('status', 'Completed')->count();
                        $totalTasks = max($taskList->count(), 1);
                        $pct = (int) round($done / $totalTasks * 100);
                    @endphp
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="form-label mb-0">{{ __('Tasks Progress') }}</span>
                            <span class="cell-muted" style="font-size:12px;">{{ $done }} / {{ $taskList->count() }}</span>
                        </div>
                        <div class="progress-mini"><span style="width: {{ $pct }}%"></span></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-head"><h2>{{ __('Recent Activity') }}</h2></div>
                <div class="card-body">
                    <ul class="activity">
                        <li><span class="act-icon primary"><i class="bi bi-box-seam"></i></span><div><div class="act-text">تم تخصيص 6 شاشات للجناح</div><div class="act-time">قبل ساعة</div></div></li>
                        <li><span class="act-icon success"><i class="bi bi-check2"></i></span><div><div class="act-text">اكتمل استلام الموقع</div><div class="act-time">قبل 4 ساعات</div></div></li>
                        <li><span class="act-icon info"><i class="bi bi-file-earmark"></i></span><div><div class="act-text">تم رفع مخطط الجناح</div><div class="act-time">أمس</div></div></li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if ($active === 'documents')
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Documents') }}</h2>
                <button class="btn-brand"><i class="bi bi-upload"></i>{{ __('Upload') }}</button>
            </div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Size') }}</th><th>{{ __('Date') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                        <tbody>
                        @foreach ($documents as $d)
                            <tr>
                                <td class="cell-strong"><i class="bi bi-file-earmark-text me-1"></i>{{ $d['title'] }}</td>
                                <td class="cell-muted">{{ $d['type'] }}</td>
                                <td class="cell-muted">{{ $d['size'] }}</td>
                                <td class="cell-muted">{{ $d['date'] }}</td>
                                <td><div class="row-actions"><a href="#" title="{{ __('Download') }}"><i class="bi bi-download"></i></a><button class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button></div></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($active === 'stock')
        <div class="card">
            <div class="card-head"><h2>{{ __('Stock') }}</h2><button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Assign Item') }}</button></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Category') }}</th><th>{{ __('Quantity') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ($stockItems as $i)
                            <tr>
                                <td class="cell-strong">{{ $i['name'] }}</td>
                                <td class="cell-muted">{{ $i['category'] }}</td>
                                <td class="cell-muted">{{ $i['qty'] }}</td>
                                <td>@include('partials.status', ['status' => $i['status']])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($active === 'expenses')
        <div class="card">
            <div class="card-head"><h2>{{ __('Expenses') }}</h2><button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Expense') }}</button></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Date') }}</th></tr></thead>
                        <tbody>
                        @foreach ($expenses as $e)
                            <tr>
                                <td class="cell-strong">{{ $e['item'] }}</td>
                                <td class="cell-muted">{{ $e['vendor'] }}</td>
                                <td class="cell-strong">{{ $e['amount'] }}</td>
                                <td class="cell-muted">{{ $e['date'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($active === 'setup')
        <div class="card">
            <div class="card-head"><h2>{{ __('Setup') }}</h2><button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Step') }}</button></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>{{ __('Step') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ($setup as $s)
                            <tr>
                                <td class="cell-strong">{{ $s['step'] }}</td>
                                <td class="cell-muted">{{ $s['owner'] }}</td>
                                <td class="cell-muted">{{ $s['date'] }}</td>
                                <td>@include('partials.status', ['status' => $s['status']])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($active === 'tasks')
        <div class="card">
            <div class="card-head"><h2>{{ __('Tasks') }}</h2><button class="btn-brand"><i class="bi bi-plus-lg"></i>{{ __('Add Task') }}</button></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>{{ __('Task') }}</th><th>{{ __('Assignee') }}</th><th>{{ __('Due Date') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ($tasks as $t)
                            <tr>
                                <td class="cell-strong">{{ $t['title'] }}</td>
                                <td class="cell-muted">{{ $t['assignee'] }}</td>
                                <td class="cell-muted">{{ $t['due'] }}</td>
                                <td>@include('partials.priority', ['priority' => $t['priority']])</td>
                                <td>@include('partials.status', ['status' => $t['status']])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
