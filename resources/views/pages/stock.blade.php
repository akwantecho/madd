@extends('layouts.app')
@section('title', __('Stock'))

@php
    $stockType = $active === 'services' ? 'service' : 'equipment';
    $addLabel = $active === 'services' ? 'Add Service' : ($active === 'notices' ? 'Add Notice' : 'Add Item');
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Stock') }}</h1>
            <p class="subtitle">{{ __('Devices, equipment and services') }}</p>
        </div>
        @if ($active === 'notices')
            <button class="btn-brand" id="addNoticeBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Notice') }}</button>
        @else
            <button class="btn-brand" id="addStockBtn"><i class="bi bi-plus-lg"></i>{{ __($addLabel) }}</button>
        @endif
    </div>

    <div class="toolbar full-bleed sheet-aligned" style="padding-block:0;">
        <div class="tabs" style="border:0; margin:0;">
            @foreach ($types as $key => $type)
                <a href="{{ route('stock', ['type' => $key]) }}" class="tab {{ $active === $key ? 'active' : '' }}">
                    <i class="bi {{ $type['icon'] }}"></i>{{ __($type['label']) }}
                </a>
            @endforeach
        </div>
        <label class="search-input"><i class="bi bi-search"></i><input type="text" placeholder="{{ __('Search...') }}"></label>
    </div>

    <div class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                @if ($active === 'equipment')
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Name') }}</th><th>{{ __('SKU') }}</th><th>{{ __('Quantity') }}</th>
                            <th>{{ __('Available') }}</th><th>{{ __('Status') }}</th><th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($equipment as $e)
                            <tr data-stock="{{ json_encode($e, JSON_UNESCAPED_UNICODE) }}">
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-strong"><i class="bi bi-box-seam me-1"></i>{{ $e['name'] }}</td>
                                <td class="cell-muted" dir="ltr">{{ $e['sku'] }}</td>
                                <td class="cell-muted">{{ $e['qty'] }}</td>
                                <td class="cell-muted">{{ $e['available'] }}</td>
                                <td>@include('partials.status', ['status' => $e['status']])</td>
                                <td>@include('partials.stock-actions', ['id' => $e['id']])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>{{ __('No records yet. Click “:add” to create one.', ['add' => __($addLabel)]) }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                @elseif ($active === 'notices')
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Notice / Clause') }}</th><th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($notices as $n)
                            <tr data-notice="{{ json_encode($n, JSON_UNESCAPED_UNICODE) }}">
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td><i class="bi bi-info-circle me-1"></i>{{ $n['body'] }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="edit-notice" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('notices.destroy', $n['id']) }}" onsubmit="return confirm('{{ __('Delete this record?') }}')" style="display:inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="empty-state"><i class="bi bi-inbox"></i>{{ __('No records yet. Click “:add” to create one.', ['add' => __('Add Notice')]) }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Name') }}</th><th>{{ __('Unit') }}</th><th>{{ __('Price') }}</th>
                            <th>{{ __('Status') }}</th><th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($services as $s)
                            <tr data-stock="{{ json_encode($s, JSON_UNESCAPED_UNICODE) }}">
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-strong"><i class="bi bi-tools me-1"></i>{{ $s['name'] }}</td>
                                <td class="cell-muted">{{ $s['unit'] }}</td>
                                <td class="cell-strong">{{ $s['price'] }}</td>
                                <td>@include('partials.status', ['status' => $s['status']])</td>
                                <td>@include('partials.stock-actions', ['id' => $s['id']])</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>{{ __('No records yet. Click “:add” to create one.', ['add' => __($addLabel)]) }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Add / Edit stock modal --}}
    <div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:1px solid var(--line); border-radius:16px;">
                <form method="POST" id="stockForm" action="{{ route('stock.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="stockMethod" value="POST">
                    <input type="hidden" name="type" value="{{ $stockType }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockModalTitle" style="font-size:16px; font-weight:700;">{{ __($addLabel) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }} <span class="req">*</span></label>
                            <input type="text" name="name" id="sName" class="form-control" required>
                        </div>
                        @if ($active === 'equipment')
                            <div class="mb-3">
                                <label class="form-label">{{ __('SKU') }}</label>
                                <input type="text" name="sku" id="sSku" class="form-control" dir="ltr">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Quantity') }}</label>
                                    <input type="number" name="quantity" id="sQty" class="form-control" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Available') }}</label>
                                    <input type="number" name="available" id="sAvail" class="form-control" min="0">
                                </div>
                            </div>
                        @else
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Unit') }}</label>
                                    <input type="text" name="unit" id="sUnit" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Price') }}</label>
                                    <input type="number" name="price" id="sPrice" class="form-control" min="0" step="0.01">
                                </div>
                            </div>
                        @endif
                        <div class="mt-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="sStatus" class="form-select">
                                @foreach ($stockStatuses as $st)
                                    <option value="{{ $st }}">{{ __($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="page-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-brand">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add / Edit notice modal --}}
    <div class="modal fade" id="noticeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:1px solid var(--line); border-radius:16px;">
                <form method="POST" id="noticeForm" action="{{ route('notices.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="noticeMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="noticeModalTitle" style="font-size:16px; font-weight:700;">{{ __('Add Notice') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">{{ __('Notice / Clause') }} <span class="req">*</span></label>
                        <textarea name="body" id="nBody" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="page-btn" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-brand">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = new bootstrap.Modal(document.getElementById('stockModal'));
        const form = document.getElementById('stockForm');
        const isEquip = @json($active === 'equipment');
        const storeAction = @json(route('stock.store'));
        const updateBase = @json(url('stock'));
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v ?? ''); };

        const addStockBtn = document.getElementById('addStockBtn');
        if (addStockBtn) addStockBtn.addEventListener('click', () => {
            form.reset();
            form.action = storeAction;
            document.getElementById('stockMethod').value = 'POST';
            document.getElementById('stockModalTitle').textContent = @json(__($addLabel));
            modal.show();
        });

        document.querySelectorAll('.edit-stock').forEach((btn) => {
            btn.addEventListener('click', () => {
                const d = JSON.parse(btn.closest('tr').dataset.stock);
                form.reset();
                form.action = updateBase + '/' + d.id;
                document.getElementById('stockMethod').value = 'PUT';
                document.getElementById('stockModalTitle').textContent = @json(__('Edit'));
                setVal('sName', d.name);
                setVal('sStatus', d.status);
                if (isEquip) {
                    setVal('sSku', d.sku);
                    setVal('sQty', d.qty);
                    setVal('sAvail', d.available);
                } else {
                    setVal('sUnit', d.unit);
                    setVal('sPrice', d.price_raw);
                }
                modal.show();
            });
        });

        /* ---- Terms & notices ---- */
        const noticeModalEl = document.getElementById('noticeModal');
        if (noticeModalEl) {
            const nModal = new bootstrap.Modal(noticeModalEl);
            const nForm = document.getElementById('noticeForm');
            const nStore = @json(route('notices.store'));
            const nBase = @json(url('notices'));
            const addNoticeBtn = document.getElementById('addNoticeBtn');
            if (addNoticeBtn) addNoticeBtn.addEventListener('click', () => {
                nForm.reset();
                nForm.action = nStore;
                document.getElementById('noticeMethod').value = 'POST';
                document.getElementById('noticeModalTitle').textContent = @json(__('Add Notice'));
                nModal.show();
            });
            document.querySelectorAll('.edit-notice').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const d = JSON.parse(btn.closest('tr').dataset.notice);
                    nForm.reset();
                    nForm.action = nBase + '/' + d.id;
                    document.getElementById('noticeMethod').value = 'PUT';
                    document.getElementById('noticeModalTitle').textContent = @json(__('Edit'));
                    document.getElementById('nBody').value = d.body;
                    nModal.show();
                });
            });
        }
    })();
</script>
@endpush
