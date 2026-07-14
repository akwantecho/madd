@extends('layouts.app')
@section('title', __('Exhibitions'))

@php
    use Illuminate\Support\Carbon;

    $items = collect($exhibitions);
    $statusClass = ['Active' => 's-active', 'Upcoming' => 's-upcoming', 'Completed' => 's-completed', 'Cancelled' => 's-cancelled'];
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Exhibitions') }}</h1>
            <p class="subtitle">{{ $items->count() }} {{ __('Exhibitions') }}</p>
        </div>
        <button class="btn-brand" id="addExhibitionBtn">
            <i class="bi bi-plus-lg"></i>{{ __('Add Exhibition') }}
        </button>
    </div>

    {{-- Toolbar: search + filters on one side, table/cards switch on the other --}}
    <div class="toolbar full-bleed sheet-aligned">
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
    <div id="exTableView" class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                <table class="data sheet">
                    <thead>
                    <tr>
                        <th style="width:34px"><input type="checkbox" class="checkbox"></th>
                        <th style="width:46px">#</th>
                        <th>{{ __('Title') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Start Date') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('End Date') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Tag') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th style="width:120px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($exhibitions as $ex)
                        @php $days = Carbon::parse($ex['start'])->diffInDays(Carbon::parse($ex['end'])) + 1; @endphp
                        <tr data-title="{{ $ex['title'] }} {{ $ex['location'] }}" data-exhibition="{{ json_encode($ex, JSON_UNESCAPED_UNICODE) }}">
                            <td><input type="checkbox" class="checkbox"></td>
                            <td class="cell-muted">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('exhibitions.show', $ex['id']) }}" class="cell-strong">{{ $ex['title'] }}</a>
                            </td>
                            <td class="cell-muted"><i class="bi bi-geo-alt me-1"></i>{{ $ex['location'] }}</td>
                            <td class="cell-muted">{{ $ex['start'] }}</td>
                            <td class="cell-muted">{{ $ex['end'] }}</td>
                            <td class="cell-muted">{{ $days }} {{ __('days') }}</td>
                            <td>
                                @if (!empty($ex['tag']))
                                    <span class="badge-soft {{ $ex['tagColor'] ?? 'gray' }}">{{ __($ex['tag']) }}</span>
                                @else
                                    <span class="cell-muted">—</span>
                                @endif
                            </td>
                            <td>@include('partials.status', ['status' => $ex['status']])</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('exhibitions.show', $ex['id']) }}" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                    <button type="button" class="edit-exhibition" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('exhibitions.destroy', $ex['id']) }}" onsubmit="return confirm('{{ __('Delete this record?') }}')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                    </form>
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
            <a href="{{ route('exhibitions.show', $ex['id']) }}" class="ex-card" data-title="{{ $ex['title'] }} {{ $ex['location'] }}">
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

    {{-- Add / Edit Exhibition modal --}}
    <div class="modal fade" id="exhibitionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:1px solid var(--line); border-radius:16px;">
                <form method="POST" id="exhibitionForm" action="{{ route('exhibitions.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="exMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exModalTitle" style="font-size:16px; font-weight:700;">{{ __('New Exhibition') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Title') }} <span class="req">*</span></label>
                            <input type="text" name="title" id="exTitle" class="form-control w-100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Location') }}</label>
                            <input type="text" name="location" id="exLocation" class="form-control w-100">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col">
                                <label class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" id="exStart" class="form-control w-100">
                            </div>
                            <div class="col">
                                <label class="form-label">{{ __('End Date') }}</label>
                                <input type="date" name="end_date" id="exEnd" class="form-control w-100">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col">
                                <label class="form-label">{{ __('Tag') }}</label>
                                <input type="text" name="tag" id="exTag" class="form-control w-100">
                            </div>
                            <div class="col">
                                <label class="form-label">{{ __('Tag') }} {{ __('Color') }}</label>
                                <select name="tag_color" id="exTagColor" class="form-select w-100">
                                    <option value="">—</option>
                                    <option value="blue">{{ __('Blue') }}</option>
                                    <option value="green">{{ __('Green') }}</option>
                                    <option value="amber">{{ __('Amber') }}</option>
                                    <option value="red">{{ __('Red') }}</option>
                                    <option value="gray">{{ __('Gray') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="exStatus" class="form-select w-100">
                                <option value="Upcoming">{{ __('Upcoming') }}</option>
                                <option value="Active">{{ __('Active') }}</option>
                                <option value="Completed">{{ __('Completed') }}</option>
                                <option value="Cancelled">{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="page-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-brand"><i class="bi bi-check-lg"></i>{{ __('Save') }}</button>
                    </div>
                </form>
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

        // Add / Edit exhibition modal
        const modal = new bootstrap.Modal(document.getElementById('exhibitionModal'));
        const form = document.getElementById('exhibitionForm');
        const storeAction = @json(route('exhibitions.store'));
        const updateBase = @json(url('exhibitions'));
        const setVal = (id, v) => { document.getElementById(id).value = (v ?? ''); };

        document.getElementById('addExhibitionBtn').addEventListener('click', () => {
            form.reset();
            form.action = storeAction;
            document.getElementById('exMethod').value = 'POST';
            document.getElementById('exModalTitle').textContent = @json(__('New Exhibition'));
            modal.show();
        });

        document.querySelectorAll('.edit-exhibition').forEach((btn) => {
            btn.addEventListener('click', () => {
                const d = JSON.parse(btn.closest('tr').dataset.exhibition);
                form.reset();
                form.action = updateBase + '/' + d.id;
                document.getElementById('exMethod').value = 'PUT';
                document.getElementById('exModalTitle').textContent = @json(__('Edit Exhibition'));
                setVal('exTitle', d.title);
                setVal('exLocation', d.location);
                setVal('exStart', d.start);
                setVal('exEnd', d.end);
                setVal('exTag', d.tag);
                setVal('exTagColor', d.tagColor);
                setVal('exStatus', d.status);
                modal.show();
            });
        });
    })();
</script>
@endpush
