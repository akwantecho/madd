@extends('layouts.app')
@section('title', __('Tasks'))

@php
    $items = collect($tasks);
    $statusClass = ['Active' => 's-active', 'Upcoming' => 's-upcoming', 'Completed' => 's-completed', 'Cancelled' => 's-cancelled'];
    $priorityColor = ['High' => 'red', 'Medium' => 'amber', 'Low' => 'green', 'Normal' => 'gray'];
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Tasks') }}</h1>
            <p class="subtitle">{{ $items->count() }} {{ __('Tasks') }}</p>
        </div>
        <button class="btn-brand" id="addTaskBtn">
            <i class="bi bi-plus-lg"></i>{{ __('Add Task') }}
        </button>
    </div>

    {{-- Toolbar: search + filters on one side, table/cards switch on the other --}}
    <div class="toolbar full-bleed sheet-aligned">
        <div class="toolbar-start">
            <div class="seg" id="taskBucket">
                <button type="button" data-bucket="inbox" class="active"><i class="bi bi-inbox"></i>{{ __('Inbox') }}</button>
                <button type="button" data-bucket="today"><i class="bi bi-calendar-day"></i>{{ __('Today') }}</button>
                <button type="button" data-bucket="upcoming"><i class="bi bi-calendar-week"></i>{{ __('Upcoming') }}</button>
            </div>
            <label class="search-input">
                <i class="bi bi-search"></i>
                <input type="text" id="taskSearch" placeholder="{{ __('Search tasks...') }}">
            </label>
        </div>
        <div class="seg" id="taskViewSwitch">
            <button type="button" data-view="table" class="active"><i class="bi bi-list-ul"></i>{{ __('Table') }}</button>
            <button type="button" data-view="cards"><i class="bi bi-grid-3x3-gap"></i>{{ __('Cards') }}</button>
        </div>
    </div>

    {{-- Table view --}}
    <div id="taskTableView" class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                <table class="data sheet">
                    <thead>
                    <tr>
                        <th style="width:34px"><input type="checkbox" class="checkbox"></th>
                        <th style="width:46px">#</th>
                        <th>{{ __('Title') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Exhibition') }}</th>
                        <th>{{ __('Assignee') }}</th>
                        <th>{{ __('Due Date') }} <i class="bi bi-arrow-down-up"></i></th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th style="width:90px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($tasks as $t)
                        <tr data-title="{{ $t['title'] }} {{ $t['exhibition'] }} {{ $t['assignee'] }}" data-due-state="{{ $t['due_state'] }}" data-priority="{{ $t['priority'] }}" data-status="{{ $t['status'] }}" data-exhibition-id="{{ $t['exhibition_id'] }}" data-task="{{ json_encode($t, JSON_UNESCAPED_UNICODE) }}">
                            <td><input type="checkbox" class="checkbox"></td>
                            <td class="cell-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="cell-strong">@if ($t['flagged'])<i class="bi bi-flag-fill me-1" style="color:var(--amber);font-size:12px;"></i>@endif{{ $t['title'] }}</span>
                            </td>
                            <td class="cell-muted">{{ $t['exhibition'] ?? '—' }}</td>
                            <td class="cell-muted">{{ $t['assignee'] ?: '—' }}</td>
                            <td class="cell-muted" @if ($t['due_state'] === 'overdue') style="color:var(--red);font-weight:600" @elseif ($t['due_state'] === 'today') style="color:var(--amber);font-weight:600" @endif>{{ $t['due'] ?? '—' }}</td>
                            <td>
                                @if (!empty($t['priority']))
                                    <span class="badge-soft {{ $priorityColor[$t['priority']] ?? 'gray' }}">{{ __($t['priority']) }}</span>
                                @else
                                    <span class="cell-muted">—</span>
                                @endif
                            </td>
                            <td>@include('partials.status', ['status' => $t['status']])</td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="edit-task" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('tasks.destroy', $t['id']) }}" onsubmit="return confirm('{{ __('Delete this task?') }}')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="cell-muted" style="text-align:center; padding:2rem;">{{ __('No tasks yet. Add your first task below.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-bar">
            <button class="page-btn">{{ __('Previous') }}</button>
            <div class="page-nums">
                <span class="page-num active">1</span>
            </div>
            <button class="page-btn">{{ __('Next') }}</button>
        </div>
    </div>

    {{-- Cards view --}}
    <div class="ex-grid is-hidden" id="taskCardsView">
        @foreach ($tasks as $t)
            <div class="ex-card edit-task" data-title="{{ $t['title'] }} {{ $t['exhibition'] }} {{ $t['assignee'] }}" data-due-state="{{ $t['due_state'] }}" data-priority="{{ $t['priority'] }}" data-status="{{ $t['status'] }}" data-exhibition-id="{{ $t['exhibition_id'] }}" data-task="{{ json_encode($t, JSON_UNESCAPED_UNICODE) }}" style="cursor:pointer">
                <div class="ex-cover {{ $statusClass[$t['status']] ?? '' }}">
                    <span class="ex-letter">{{ mb_substr($t['title'], 0, 1) }}</span>
                    @if (!empty($t['priority']))
                        <span class="badge-soft {{ $priorityColor[$t['priority']] ?? 'gray' }}">{{ __($t['priority']) }}</span>
                    @endif
                </div>
                <div class="ex-body">
                    <div class="ex-title">@if ($t['flagged'])<i class="bi bi-flag-fill me-1" style="color:var(--amber)"></i>@endif{{ $t['title'] }}</div>
                    <div class="ex-row"><i class="bi bi-easel2"></i>{{ $t['exhibition'] ?? '—' }}</div>
                    <div class="ex-row"><i class="bi bi-person"></i>{{ $t['assignee'] ?: '—' }}</div>
                    <div class="ex-foot">
                        @include('partials.status', ['status' => $t['status']])
                        <span class="ex-days"><i class="bi bi-calendar3"></i>{{ $t['due'] ?? __('No date') }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Upcoming board (Todoist-style day columns) — shown when the "Upcoming" bucket is active --}}
    <div class="full-bleed is-hidden" id="taskBoardView">
        <div class="tb-subhead sheet-aligned">
            <div class="tb-month"><i class="bi bi-calendar3"></i>{{ $board['month'] }}</div>
            <div class="tb-nav">
                <button type="button" class="tb-nav-btn" id="tbPrev"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></button>
                <button type="button" class="tb-today" id="tbToday">{{ __('Today') }}</button>
                <button type="button" class="tb-nav-btn" id="tbNext"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></button>
            </div>
        </div>

        <div class="tb-board sheet-aligned" id="tbScroll">
            {{-- Overdue column --}}
            <div class="tb-col">
                <div class="tb-col-head">
                    <span class="tb-col-title">{{ __('Overdue') }} <span class="tb-count">{{ count($board['overdue']) }}</span></span>
                </div>
                <div class="tb-cards">
                    @foreach ($board['overdue'] as $t)
                        @include('partials.task-card', ['t' => $t])
                    @endforeach
                </div>
            </div>

            {{-- Day columns --}}
            @foreach ($board['days'] as $day)
                <div class="tb-col">
                    <div class="tb-col-head">
                        <span class="tb-col-title">{{ $day['dnum'] }} {{ $day['mon'] }} · {{ $day['rel'] }} <span class="tb-count">{{ count($day['tasks']) }}</span></span>
                    </div>
                    <div class="tb-cards">
                        @foreach ($day['tasks'] as $t)
                            @include('partials.task-card', ['t' => $t])
                        @endforeach
                        <button type="button" class="tb-add" data-date="{{ $day['date'] }}"><i class="bi bi-plus"></i>{{ __('Add task') }}</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Empty search result (shown via JS when nothing matches) --}}
    <div class="empty-state is-hidden" id="taskEmpty">
        <i class="bi bi-search"></i>
        {{ __('No tasks match your search') }}
    </div>

    {{-- Add / Edit Task modal --}}
    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:1px solid var(--line); border-radius:16px;">
                <form method="POST" id="taskForm" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="taskMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taskModalTitle" style="font-size:16px; font-weight:700;">{{ __('New Task') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Title') }} <span class="req">*</span></label>
                            <input type="text" name="title" id="tTitle" class="form-control w-100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" id="tDescription" rows="3" class="form-control w-100"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col">
                                <label class="form-label">{{ __('Exhibition') }}</label>
                                <select name="exhibition_id" id="tExhibition" class="form-select w-100">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($exhibitions as $exId => $exTitle)<option value="{{ $exId }}">{{ $exTitle }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">{{ __('Assignee') }}</label>
                                <input type="text" name="assignee" id="tAssignee" class="form-control w-100">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col">
                                <label class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="tDue" class="form-control w-100">
                            </div>
                            <div class="col">
                                <label class="form-label">{{ __('Priority') }}</label>
                                <select name="priority" id="tPriority" class="form-select w-100">
                                    @foreach ($priorities as $p)<option value="{{ $p }}">{{ __($p) }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-1">
                            <div class="col">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" id="tStatus" class="form-select w-100">
                                    @foreach ($statuses as $s)<option value="{{ $s }}">{{ __($s) }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col d-flex align-items-end">
                                <label class="form-label" style="display:flex; align-items:center; gap:8px; margin-bottom:9px; cursor:pointer;">
                                    <input type="hidden" name="flagged" value="0">
                                    <input type="checkbox" name="flagged" id="tFlagged" value="1" class="checkbox">
                                    <span><i class="bi bi-flag me-1"></i>{{ __('Flagged') }}</span>
                                </label>
                            </div>
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
        const sw = document.getElementById('taskViewSwitch');
        const table = document.getElementById('taskTableView');
        const cards = document.getElementById('taskCardsView');
        const board = document.getElementById('taskBoardView');
        const search = document.getElementById('taskSearch');
        const searchBox = search.closest('.search-input');
        const bucketSw = document.getElementById('taskBucket');
        const empty = document.getElementById('taskEmpty');
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const updateBase = @json(url('tasks'));
        let bucket = 'inbox';
        let view = 'table';

        // Table <-> cards switch (only relevant for Inbox / Today)
        const setView = (v) => {
            view = v;
            sw.querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.view === v));
            try { localStorage.setItem('taskView', v); } catch (e) {}
            render();
        };
        try { const saved = localStorage.getItem('taskView'); if (saved) view = saved; } catch (e) {}

        function inBucket(dueState) {
            if (bucket === 'today') return dueState === 'today' || dueState === 'overdue';
            return true; // inbox = everything
        }

        // Show the right container for the active bucket, then filter the list views.
        function render() {
            const upcoming = bucket === 'upcoming';
            board.classList.toggle('is-hidden', !upcoming);
            sw.classList.toggle('is-hidden', upcoming);
            searchBox.classList.toggle('is-hidden', upcoming);

            if (upcoming) {
                table.classList.add('is-hidden');
                cards.classList.add('is-hidden');
                empty.classList.add('is-hidden');
                return;
            }
            cards.classList.toggle('is-hidden', view !== 'cards');
            table.classList.toggle('is-hidden', view === 'cards');
            applyFilter();
        }

        // Filter only the table/cards (not the board) by search text + bucket.
        function applyFilter() {
            const q = search.value.trim().toLowerCase();
            let visible = 0;
            document.querySelectorAll('#taskTableView [data-task], #taskCardsView [data-task]').forEach(el => {
                const show = el.dataset.title.toLowerCase().includes(q) && inBucket(el.dataset.dueState);
                el.classList.toggle('is-hidden', !show);
                if (show) visible++;
            });
            empty.classList.toggle('is-hidden', visible > 0);
        }

        sw.addEventListener('click', (e) => { const b = e.target.closest('button'); if (b) setView(b.dataset.view); });
        search.addEventListener('input', applyFilter);
        bucketSw.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            bucket = btn.dataset.bucket;
            bucketSw.querySelectorAll('button').forEach(b => b.classList.toggle('active', b === btn));
            render();
        });

        // ---- Add / Edit task modal ----
        const modal = new bootstrap.Modal(document.getElementById('taskModal'));
        const form = document.getElementById('taskForm');
        const storeAction = @json(route('tasks.store'));
        const setVal = (id, v) => { document.getElementById(id).value = (v ?? ''); };

        function openAdd(due) {
            form.reset();
            form.action = storeAction;
            document.getElementById('taskMethod').value = 'POST';
            document.getElementById('taskModalTitle').textContent = @json(__('New Task'));
            document.getElementById('tPriority').value = 'Medium';
            document.getElementById('tStatus').value = 'Upcoming';
            document.getElementById('tFlagged').checked = false;
            if (due) setVal('tDue', due);
            modal.show();
        }
        document.getElementById('addTaskBtn').addEventListener('click', () => openAdd());

        document.querySelectorAll('.edit-task').forEach((el) => {
            el.addEventListener('click', () => {
                const d = JSON.parse(el.closest('[data-task]').dataset.task);
                form.reset();
                form.action = updateBase + '/' + d.id;
                document.getElementById('taskMethod').value = 'PUT';
                document.getElementById('taskModalTitle').textContent = @json(__('Edit Task'));
                setVal('tTitle', d.title);
                setVal('tDescription', d.description);
                setVal('tExhibition', d.exhibition_id);
                setVal('tAssignee', d.assignee);
                setVal('tDue', d.due);
                setVal('tPriority', d.priority);
                setVal('tStatus', d.status);
                document.getElementById('tFlagged').checked = !!d.flagged;
                modal.show();
            });
        });

        // ---- Board interactions ----
        // "Add task" in a day column → open the modal with that date prefilled.
        document.querySelectorAll('.tb-add').forEach((b) => b.addEventListener('click', (e) => {
            e.stopPropagation();
            openAdd(b.dataset.date);
        }));

        // Circular checkbox → mark complete (persists), then drop the card + update the column count.
        document.querySelectorAll('.tb-check').forEach((chk) => chk.addEventListener('click', async (e) => {
            e.stopPropagation();
            const card = chk.closest('.tb-card');
            const col = chk.closest('.tb-col');
            chk.classList.add('is-done');
            try {
                await fetch(updateBase + '/' + chk.dataset.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ status: 'Completed' }),
                });
                card.remove();
                const countEl = col.querySelector('.tb-count');
                if (countEl) countEl.textContent = col.querySelectorAll('.tb-card').length;
            } catch (err) { chk.classList.remove('is-done'); }
        }));

        // Horizontal navigation: scroll the board one column-width at a time.
        const scroll = document.getElementById('tbScroll');
        const rtl = document.documentElement.dir === 'rtl';
        const step = 314;
        document.getElementById('tbNext')?.addEventListener('click', () => scroll.scrollBy({ left: rtl ? -step : step, behavior: 'smooth' }));
        document.getElementById('tbPrev')?.addEventListener('click', () => scroll.scrollBy({ left: rtl ? step : -step, behavior: 'smooth' }));
        document.getElementById('tbToday')?.addEventListener('click', () => scroll.scrollTo({ left: rtl ? scroll.scrollWidth : 0, behavior: 'smooth' }));

        render();
    })();
</script>
@endpush
