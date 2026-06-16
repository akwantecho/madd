@extends('layouts.app')
@section('title', __('Exhibitions'))

@php
    use Illuminate\Support\Carbon;

    $items = collect($exhibitions);
    $stats = [
        ['key' => 'Total Exhibitions', 'value' => $items->count(),                       'icon' => 'bi-easel2',       'color' => 'brand'],
        ['key' => 'Active',            'value' => $items->where('status', 'Active')->count(),    'icon' => 'bi-broadcast', 'color' => 'green'],
        ['key' => 'Upcoming',          'value' => $items->where('status', 'Upcoming')->count(),  'icon' => 'bi-clock',     'color' => 'blue'],
        ['key' => 'Completed',         'value' => $items->where('status', 'Completed')->count(), 'icon' => 'bi-check-circle', 'color' => 'amber'],
    ];
    $statusClass = ['Active' => 's-active', 'Upcoming' => 's-upcoming', 'Completed' => 's-completed', 'Cancelled' => 's-cancelled'];
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Exhibitions') }}</h1>
            <p class="subtitle">{{ $items->count() }} {{ __('Exhibitions') }}</p>
        </div>
        <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#addExhibitionModal">
            <i class="bi bi-plus-lg"></i>{{ __('Add Exhibition') }}
        </button>
    </div>

    {{-- Summary --}}
    <div class="kpi-grid">
        @foreach ($stats as $s)
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-icon {{ $s['color'] }}"><i class="bi {{ $s['icon'] }}"></i></span>
                </div>
                <div class="k-value">{{ $s['value'] }}</div>
                <div class="k-label">{{ __($s['key']) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Toolbar: search + filters on one side, table/cards switch on the other --}}
    <div class="toolbar">
        <div class="toolbar-start">
            <label class="search-input">
                <i class="bi bi-search"></i>
                <input type="text" id="exSearch" placeholder="{{ __('Search exhibitions...') }}">
            </label>
            <button class="chip"><i class="bi bi-geo-alt"></i>{{ __('Location') }}<i class="bi bi-chevron-down"></i></button>
            <button class="chip"><i class="bi bi-funnel"></i>{{ __('Status') }}<i class="bi bi-chevron-down"></i></button>
        </div>
        <div class="seg" id="exViewSwitch">
            <button type="button" data-view="table" class="active"><i class="bi bi-list-ul"></i>{{ __('Table') }}</button>
            <button type="button" data-view="cards"><i class="bi bi-grid-3x3-gap"></i>{{ __('Cards') }}</button>
        </div>
    </div>

    {{-- Table view --}}
    <div class="card" id="exTableView">
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
                        <tr data-title="{{ $ex['title'] }} {{ $ex['location'] }}">
                            <td><input type="checkbox" class="checkbox"></td>
                            <td>
                                <a href="{{ route('exhibitions.show', $loop->iteration) }}" class="user-cell">
                                    <span class="avatar sm">{{ mb_substr($ex['title'], 0, 1) }}</span>
                                    <span>
                                        <span class="cell-strong d-block">{{ $ex['title'] }}</span>
                                        @if (!empty($ex['tag']))
                                            <span class="badge-soft {{ $ex['tagColor'] ?? 'gray' }}">{{ __($ex['tag']) }}</span>
                                        @endif
                                    </span>
                                </a>
                            </td>
                            <td class="cell-muted"><i class="bi bi-geo-alt me-1"></i>{{ $ex['location'] }}</td>
                            <td class="cell-muted">{{ $ex['start'] }}</td>
                            <td class="cell-muted">{{ $ex['end'] }}</td>
                            <td>@include('partials.status', ['status' => $ex['status']])</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('exhibitions.show', $loop->iteration) }}" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
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

    {{-- Cards view --}}
    <div class="ex-grid is-hidden" id="exCardsView">
        @foreach ($exhibitions as $ex)
            @php $days = Carbon::parse($ex['start'])->diffInDays(Carbon::parse($ex['end'])) + 1; @endphp
            <a href="{{ route('exhibitions.show', $loop->iteration) }}" class="ex-card" data-title="{{ $ex['title'] }} {{ $ex['location'] }}">
                <div class="ex-cover {{ $statusClass[$ex['status']] ?? '' }}">
                    <span class="ex-letter">{{ mb_substr($ex['title'], 0, 1) }}</span>
                    @if (!empty($ex['tag']))
                        <span class="badge-soft {{ $ex['tagColor'] ?? 'gray' }}">{{ __($ex['tag']) }}</span>
                    @endif
                </div>
                <div class="ex-body">
                    <div class="ex-title">{{ $ex['title'] }}</div>
                    <div class="ex-row"><i class="bi bi-geo-alt"></i>{{ $ex['location'] }}</div>
                    <div class="ex-row"><i class="bi bi-calendar3"></i>{{ $ex['start'] }} → {{ $ex['end'] }}</div>
                    <div class="ex-foot">
                        @include('partials.status', ['status' => $ex['status']])
                        <span class="ex-days"><i class="bi bi-hourglass-split"></i>{{ $days }} {{ __('days') }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Empty search result (shown via JS when nothing matches) --}}
    <div class="empty-state is-hidden" id="exEmpty">
        <i class="bi bi-search"></i>
        {{ __('No exhibitions match your search') }}
    </div>

    {{-- Add Exhibition modal (UI only) --}}
    <div class="modal fade" id="addExhibitionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:1px solid var(--line); border-radius: var(--radius);">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:16px; font-weight:700;">{{ __('New Exhibition') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Title') }}</label>
                        <input type="text" class="form-control w-100" placeholder="{{ __('Title') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Location') }}</label>
                        <input type="text" class="form-control w-100" placeholder="{{ __('Location') }}">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label">{{ __('Start Date') }}</label>
                            <input type="date" class="form-control w-100">
                        </div>
                        <div class="col">
                            <label class="form-label">{{ __('End Date') }}</label>
                            <input type="date" class="form-control w-100">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select class="form-select w-100">
                            <option>{{ __('Upcoming') }}</option>
                            <option>{{ __('Active') }}</option>
                            <option>{{ __('Completed') }}</option>
                            <option>{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="page-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn-brand"><i class="bi bi-check-lg"></i>{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        // View switch: table <-> cards (remembered per browser)
        const sw = document.getElementById('exViewSwitch');
        const table = document.getElementById('exTableView');
        const cards = document.getElementById('exCardsView');
        const setView = (view) => {
            const isCards = view === 'cards';
            cards.classList.toggle('is-hidden', !isCards);
            table.classList.toggle('is-hidden', isCards);
            sw.querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.view === view));
            try { localStorage.setItem('exView', view); } catch (e) {}
        };
        sw.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (btn) setView(btn.dataset.view);
        });
        try { const saved = localStorage.getItem('exView'); if (saved) setView(saved); } catch (e) {}

        // Client-side search filter across both views
        const search = document.getElementById('exSearch');
        const empty = document.getElementById('exEmpty');
        search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            let visible = 0;
            document.querySelectorAll('[data-title]').forEach(el => {
                const match = el.dataset.title.toLowerCase().includes(q);
                el.classList.toggle('is-hidden', !match);
                if (el.matches('tr') && match) visible++;
                if (el.matches('.ex-card') && match) visible++;
            });
            empty.classList.toggle('is-hidden', visible > 0);
        });
    })();
</script>
@endpush
