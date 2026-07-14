{{-- Board task card (Todoist-style). Expects $t (task payload array). --}}
<div class="tb-card edit-task" data-task="{{ json_encode($t, JSON_UNESCAPED_UNICODE) }}">
    <button type="button" class="tb-check pr-{{ strtolower($t['priority'] ?? 'normal') }}" data-id="{{ $t['id'] }}" title="{{ __('Mark Complete') }}"><i class="bi bi-check-lg"></i></button>
    <div class="tb-card-main">
        <div class="tb-card-top">
            <span class="tb-title">{{ $t['title'] }}</span>
            @if (!empty($t['assignee']))
                <span class="tb-avatar">{{ mb_strtoupper(mb_substr($t['assignee'], 0, 2)) }}</span>
            @endif
        </div>
        @if (!empty($t['description']))
            <div class="tb-desc">{{ \Illuminate\Support\Str::limit($t['description'], 70) }}</div>
        @endif
        <div class="tb-meta">
            @if (!empty($t['due']))
                <span class="tb-chip {{ ($t['due_state'] ?? '') === 'overdue' ? 'is-red' : (($t['due_state'] ?? '') === 'today' ? 'is-amber' : '') }}">
                    <i class="bi bi-calendar-event"></i>{{ \Illuminate\Support\Carbon::parse($t['due'])->format('j M') }}
                </span>
            @endif
            @if (($t['subtasks_total'] ?? 0) > 0)
                <span class="tb-chip"><i class="bi bi-diagram-2"></i>{{ $t['subtasks_done'] ?? 0 }}/{{ $t['subtasks_total'] }}</span>
            @endif
            @if (!empty($t['exhibition']))
                <span class="tb-proj">{{ $t['exhibition'] }} <i class="bi bi-hash"></i></span>
            @endif
        </div>
    </div>
</div>
