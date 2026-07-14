@extends('layouts.app')
@section('title', $isEdit ? __('Edit Invoice') : __('Create Invoice'))

@php $cur = $invoice['currency'] ?? 'ر.ع'; @endphp

@section('content')
    {{-- Editor action bar --}}
    <div class="editor-bar full-bleed">
        <div class="editor-bar-start">
            <a href="{{ route('contracts', ['tab' => 'invoices']) }}" class="btn-icon" title="{{ __('Close') }}"><i class="bi bi-x-lg"></i></a>
            <strong>{{ $isEdit ? __('Edit Invoice') : __('Create Invoice') }}</strong>
        </div>
        <div class="editor-bar-end">
            <button type="button" class="chip"><i class="bi bi-paperclip"></i>{{ __('Attach files') }} <span class="badge-soft gray">0</span></button>
            <button type="button" class="chip" onclick="window.print()"><i class="bi bi-printer"></i>{{ __('Print / Download') }}</button>
            <button type="submit" form="docForm" name="intent" value="draft" class="chip"><i class="bi bi-floppy"></i>{{ __('Save as Draft') }}</button>
            <button type="submit" form="docForm" name="intent" value="send" class="btn-brand"><i class="bi bi-send"></i>{{ __('Save') }}</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger full-bleed" style="margin:0 0 1rem;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Invoice document --}}
    <form id="docForm" method="POST" action="{{ $action }}" class="invoice-doc">
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <input type="hidden" name="vat_rate" value="0">

        <div class="inv-head">
            <h2 class="inv-title">{{ __('Invoice') }}</h2>
            <span class="section-link"><i class="bi bi-hash me-1"></i>{{ $invoice['number'] }}</span>
        </div>

        <div class="row g-4 inv-top">
            <div class="col-lg-7 order-2 order-lg-1">
                {{-- Customer: type-to-search + quick add --}}
                <div class="inv-field">
                    <label>{{ __('Customer') }} <span class="req">*</span></label>
                    <div class="combo" id="clientCombo"
                         data-options='@json($clients)'
                         data-empty="{{ __('No matching customers') }}">
                        <input type="hidden" name="client_id" value="{{ $invoice['clientId'] }}">
                        <input type="text" class="form-control combo-input" autocomplete="off" placeholder="{{ __('Type customer name to search…') }}">
                        <div class="combo-pop">
                            <ul class="combo-list"></ul>
                            <button type="button" class="combo-add" data-modal="addClientModal"><i class="bi bi-plus-lg"></i>{{ __('Add new customer') }}</button>
                        </div>
                    </div>
                </div>

                {{-- Contract link: fills the period + bills the contract's installments --}}
                <div class="inv-field">
                    <label>{{ __('Linked Contract') }}</label>
                    <div class="combo" id="contractCombo"
                         data-options='@json(collect($contracts)->map(fn ($c) => ['id' => $c['id'], 'name' => $c['name']]))'
                         data-empty="{{ __('No matching contracts') }}">
                        <input type="hidden" name="contract_id" value="{{ $invoice['contractId'] }}">
                        <input type="text" class="form-control combo-input" autocomplete="off" placeholder="{{ __('Type contract number to search…') }}">
                        <div class="combo-pop">
                            <ul class="combo-list"></ul>
                        </div>
                    </div>
                    <div class="cell-muted" style="font-size:12px;margin-top:5px">{{ __('Selecting a contract fills the period and bills its installments.') }}</div>
                </div>

                <div class="inv-field">
                    <label>{{ __('Invoice Number') }} <span class="req">*</span></label>
                    <input type="text" name="number" class="form-control" value="{{ old('number', $invoice['number']) }}">
                </div>

                <div class="inv-field">
                    <label>{{ __('Currency') }} <span class="req">*</span></label>
                    <select name="currency" class="form-select">
                        <option value="ر.ع" selected>{{ __('Omani Rial') }} ر.ع</option>
                    </select>
                </div>

                <div class="inv-field">
                    <label>{{ __('Date') }} <span class="req">*</span></label>
                    <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', $invoice['date']) }}">
                </div>

                <div class="inv-field">
                    <label>{{ __('Due Date') }} <span class="req">*</span></label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice['due']) }}">
                </div>

                {{-- Contract period (from → to), linked to the selected contract --}}
                <div class="inv-field">
                    <label>{{ __('Contract Period') }}</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="period-sub">{{ __('From') }}</span>
                            <input type="date" name="period_from" id="periodFrom" class="form-control" value="{{ old('period_from', $invoice['periodFrom']) }}">
                        </div>
                        <div class="col-6">
                            <span class="period-sub">{{ __('To') }}</span>
                            <input type="date" name="period_to" id="periodTo" class="form-control" value="{{ old('period_to', $invoice['periodTo']) }}">
                        </div>
                    </div>
                    <div class="period-note" id="periodDuration"></div>
                </div>

                {{-- Exhibition: type-to-search + quick add --}}
                <div class="inv-field">
                    <label>{{ __('Exhibition') }}</label>
                    <div class="combo" id="exhibitionCombo"
                         data-options='@json($exhibitions)'
                         data-empty="{{ __('No matching exhibitions') }}">
                        <input type="hidden" name="exhibition_id" value="{{ $invoice['exhibitionId'] }}">
                        <input type="text" class="form-control combo-input" autocomplete="off" placeholder="{{ __('Type exhibition name to search…') }}">
                        <div class="combo-pop">
                            <ul class="combo-list"></ul>
                            <button type="button" class="combo-add" data-modal="addExhibitionModal"><i class="bi bi-plus-lg"></i>{{ __('Add new exhibition') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 order-1 order-lg-2">
                <div class="inv-company">
                    <span class="inv-logo">{{ $company['logo'] }}</span>
                    <div class="inv-company-info">
                        <strong>{{ $company['name'] }}</strong>
                        <div class="cell-muted">{{ $company['address'] }}</div>
                        <div class="cell-muted">{{ $company['country'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice items — بنود الفاتورة (with price + total) --}}
        <div class="doc-sec">
            <div class="doc-sec-head"><span class="doc-sec-bar"></span><h3>{{ __('Invoice items') }}</h3></div>
            <div class="table-wrap">
                <table class="doc-sec-table">
                    <thead><tr>
                        <th>{{ __('Item') }}</th>
                        <th style="width:16%">{{ __('Quantity / Count') }}</th>
                        <th style="width:22%">{{ __('Price') }}</th>
                        <th style="width:18%">{{ __('Total') }}</th>
                        <th style="width:44px"></th>
                    </tr></thead>
                    <tbody id="scopeBody"></tbody>
                </table>
            </div>
            <button type="button" class="chip inv-addline" id="addScopeBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Row') }}</button>
        </div>

        {{-- Totals --}}
        <div class="row inv-bottom g-4">
            <div class="col-lg-6 order-2 order-lg-1">
                <a href="#" class="inv-link d-inline-block mb-3 {{ $invoice['discount'] > 0 ? 'is-hidden' : '' }}" id="addDiscountBtn">+ {{ __('Add Total Discount') }}</a>
                <div class="inv-totals">
                    <div class="tot-row {{ $invoice['discount'] > 0 ? '' : 'is-hidden' }}" id="discountRow">
                        <span>{{ __('Discount') }} (%) <button type="button" class="li-del" id="removeDiscountBtn" title="{{ __('Delete') }}"><i class="bi bi-x-lg"></i></button></span>
                        <span class="disc-cell">
                            <input type="number" min="0" max="100" step="0.01" id="discountPercent" class="form-control" style="width:90px; text-align:start;"> %
                            <span class="cell-muted" id="discountAmountLabel">0.00 {{ $cur }}</span>
                            <input type="hidden" name="discount" id="discountAmount" value="{{ old('discount', $invoice['discount']) }}">
                        </span>
                    </div>
                    <div class="tot-row grand"><span>{{ __('Total') }}</span><span><span id="sumTotal">0.00</span> {{ $cur }}</span></div>
                </div>
            </div>
        </div>

    </form>

    {{-- Quick-add: Customer --}}
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add new customer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">{{ __('Name') }} <span class="req">*</span></label><input type="text" class="form-control" id="ac-name"></div>
                    <div class="mb-3"><label class="form-label">{{ __('Phone') }}</label><input type="text" class="form-control" id="ac-phone"></div>
                    <div class="mb-3"><label class="form-label">{{ __('Email') }}</label><input type="email" class="form-control" id="ac-email"></div>
                    <div class="text-danger small" id="ac-err"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="chip" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn-brand" id="ac-save"><i class="bi bi-check2"></i>{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick-add: Exhibition --}}
    <div class="modal fade" id="addExhibitionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add new exhibition') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">{{ __('Exhibition Name') }} <span class="req">*</span></label><input type="text" class="form-control" id="ae-title"></div>
                    <div class="mb-3"><label class="form-label">{{ __('Location') }}</label><input type="text" class="form-control" id="ae-location"></div>
                    <div class="row g-2">
                        <div class="col-6 mb-1"><label class="form-label">{{ __('Start Date') }}</label><input type="date" class="form-control" id="ae-start"></div>
                        <div class="col-6 mb-1"><label class="form-label">{{ __('End Date') }}</label><input type="date" class="form-control" id="ae-end"></div>
                    </div>
                    <div class="text-danger small" id="ae-err"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="chip" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn-brand" id="ae-save"><i class="bi bi-check2"></i>{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
    .combo{position:relative}
    .combo-pop{position:absolute;inset-inline:0;top:calc(100% + 4px);z-index:1055;background:var(--panel,#fff);border:1px solid var(--line);border-radius:var(--radius-sm,9px);box-shadow:0 10px 28px rgba(20,20,40,.12);padding:8px;display:none}
    .combo.open .combo-pop{display:block}
    .combo-list{list-style:none;margin:0;padding:0;max-height:220px;overflow:auto}
    .combo-list li{padding:8px 10px;border-radius:8px;cursor:pointer;font-size:13px}
    .combo-list li:hover{background:var(--hover)}
    .combo-list li.empty{color:var(--muted);cursor:default}
    .combo-list li.empty:hover{background:transparent}
    .combo-add{width:100%;margin-top:8px;border:1px dashed var(--line);background:var(--brand-soft);color:var(--brand);border-radius:8px;padding:9px;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px}
    .combo-add:hover{filter:brightness(.97)}
    .combo-add i{font-size:14px}
    .disc-cell{display:inline-flex;align-items:center;gap:8px}
    .disc-cell #discountAmountLabel{font-size:12px}
    .period-sub{display:block;font-size:12px;color:var(--muted);margin-bottom:3px}
    .period-note{font-size:12.5px;color:var(--brand);margin-top:6px;min-height:16px}
    /* Plain document sections (Timeline / Scope) — no color, matches the quote layout */
    .doc-sec{margin-top:26px}
    .doc-sec-head{display:flex;align-items:center;gap:10px;margin-bottom:10px}
    .doc-sec-head .doc-sec-bar{width:4px;height:20px;background:var(--muted-2,#aab0ba);border-radius:2px}
    .doc-sec-head h3{font-size:16px;font-weight:700;color:var(--ink,#1f2a37);margin:0}
    .doc-sec-table{width:100%;border-collapse:collapse;border:1px solid var(--line);border-radius:10px;overflow:hidden}
    .doc-sec-table thead th{background:#f2f4f6;color:#1f2a37;font-weight:600;font-size:13px;padding:11px 14px;text-align:start}
    .doc-sec-table tbody td{padding:5px 8px;border-top:1px solid #eef0f3;vertical-align:middle}
    .doc-sec-table tbody tr:nth-child(even){background:#fafbfc}
    .doc-sec-table .form-control{border:1px solid transparent;background:transparent;box-shadow:none}
    .doc-sec-table .form-control:focus{border-color:var(--line);background:#fff}
    .doc-sec-table .li-actions{width:44px;text-align:center}
    .doc-sec-table .li-del{border:0;background:transparent;color:var(--muted);cursor:pointer}
    .doc-sec-table .li-del:hover{color:#d6455d}
    .doc-sec-table tfoot .doc-sec-grand td{background:#f2f4f6;font-weight:700;color:#1f2a37;padding:12px 14px;border-top:1px solid var(--line)}
    .doc-sec-table tfoot .doc-sec-grand td:first-child{text-align:start}
</style>
<script>
    window.invI18n = {
        required: @json(__('Required')),
        delete: @json(__('Delete')),
        currency: @json($cur),
        period: @json(__('Total Period')),
        days: @json(__('days')),
    };
    (function () {
        const t = window.invI18n;
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const esc = (s) => (s == null ? '' : String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])));

        /* ---------- Type-to-search combo boxes ---------- */
        function initCombo(root, onSelect) {
            const hidden = root.querySelector('input[type="hidden"]');
            const input = root.querySelector('.combo-input');
            const list = root.querySelector('.combo-list');
            const emptyMsg = root.dataset.empty || 'لا نتائج';
            let options = JSON.parse(root.dataset.options || '[]');

            function render(filter) {
                const f = (filter || '').trim().toLowerCase();
                const items = f ? options.filter(o => String(o.name).toLowerCase().includes(f)) : options;
                list.innerHTML = items.length
                    ? items.slice(0, 50).map(o => `<li data-id="${o.id}">${esc(o.name)}</li>`).join('')
                    : `<li class="empty">${esc(emptyMsg)}</li>`;
            }
            function open() { root.classList.add('open'); render(input.value); }
            function close() { root.classList.remove('open'); }
            function select(id, name, fire) { hidden.value = id; input.value = name; close(); if (fire !== false && onSelect) onSelect(id, name); }

            input.addEventListener('focus', open);
            input.addEventListener('input', () => {
                const exact = options.find(o => String(o.name).toLowerCase() === input.value.trim().toLowerCase());
                hidden.value = exact ? exact.id : '';
                open();
            });
            list.addEventListener('click', (e) => {
                const li = e.target.closest('li[data-id]');
                if (!li) return;
                select(li.dataset.id, li.textContent);
            });
            document.addEventListener('click', (e) => { if (!root.contains(e.target)) close(); });

            const sel = hidden.value ? options.find(o => String(o.id) === String(hidden.value)) : null;
            if (sel) input.value = sel.name;

            root._combo = {
                addOption(o) { options.push(o); select(o.id, o.name); },
                set(id) { const o = options.find(x => String(x.id) === String(id)); if (o) { select(o.id, o.name, false); return true; } return false; },
                typedText() { return input.value.trim(); },
            };
        }
        const clientCombo = document.getElementById('clientCombo');
        const exhibitionCombo = document.getElementById('exhibitionCombo');
        const contractCombo = document.getElementById('contractCombo');
        initCombo(clientCombo);
        initCombo(exhibitionCombo);
        initCombo(contractCombo, onContractSelect);

        /* ---------- Quick-add modals ---------- */
        document.querySelectorAll('.combo-add').forEach(btn => {
            btn.addEventListener('click', () => {
                const combo = btn.closest('.combo');
                combo.classList.remove('open');
                const typed = combo._combo.typedText();
                if (btn.dataset.modal === 'addClientModal') document.getElementById('ac-name').value = typed;
                if (btn.dataset.modal === 'addExhibitionModal') document.getElementById('ae-title').value = typed;
                bootstrap.Modal.getOrCreateInstance(document.getElementById(btn.dataset.modal)).show();
            });
        });
        async function postJson(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(Object.values(data.errors || { e: [data.message || 'خطأ'] }).flat()[0]);
            return data;
        }
        function wireQuickAdd(saveBtnId, errId, url, gather, combo, modalId) {
            const btn = document.getElementById(saveBtnId);
            const err = document.getElementById(errId);
            btn.addEventListener('click', async () => {
                err.textContent = '';
                const body = gather();
                if (!body.__ok) { err.textContent = t.required; return; }
                delete body.__ok;
                btn.disabled = true;
                try {
                    const created = await postJson(url, body);
                    combo._combo.addOption(created);
                    bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
                } catch (e) { err.textContent = e.message; }
                finally { btn.disabled = false; }
            });
        }
        wireQuickAdd('ac-save', 'ac-err', @json(route('invoices.quickClient')), () => {
            const name = document.getElementById('ac-name').value.trim();
            return { __ok: !!name, name, phone: document.getElementById('ac-phone').value.trim(), email: document.getElementById('ac-email').value.trim() };
        }, clientCombo, 'addClientModal');
        wireQuickAdd('ae-save', 'ae-err', @json(route('invoices.quickExhibition')), () => {
            const title = document.getElementById('ae-title').value.trim();
            return { __ok: !!title, title, location: document.getElementById('ae-location').value.trim(), start_date: document.getElementById('ae-start').value, end_date: document.getElementById('ae-end').value };
        }, exhibitionCombo, 'addExhibitionModal');
        ['addClientModal', 'addExhibitionModal'].forEach(id => {
            document.getElementById(id).addEventListener('hidden.bs.modal', function () {
                this.querySelectorAll('input').forEach(i => i.value = '');
                this.querySelectorAll('.text-danger').forEach(e => e.textContent = '');
            });
        });

        /* ---------- Totals (subtotal = sum of "بنود الفاتورة" totals) ---------- */
        const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const discRow = document.getElementById('discountRow');
        const discPercent = document.getElementById('discountPercent');
        const discAmount = document.getElementById('discountAmount');
        const discAmountLabel = document.getElementById('discountAmountLabel');

        function currentSubtotal() {
            let subtotal = 0;
            document.querySelectorAll('#scopeBody .scope-total').forEach((i) => {
                subtotal += parseFloat(String(i.value).replace(/[^\d.\-]/g, '')) || 0;
            });
            return subtotal;
        }
        function recalc() {
            const subtotal = currentSubtotal();
            let percent = 0;
            if (!discRow.classList.contains('is-hidden')) {
                percent = Math.min(100, Math.max(0, parseFloat(discPercent.value) || 0));
            }
            const discountValue = subtotal * percent / 100;
            discAmount.value = discountValue.toFixed(2);            // stored as amount
            discAmountLabel.textContent = `${fmt(discountValue)} ${t.currency}`;
            document.getElementById('sumTotal').textContent = fmt(Math.max(0, subtotal - discountValue));
        }

        const addDisc = document.getElementById('addDiscountBtn');
        addDisc.addEventListener('click', (e) => { e.preventDefault(); discRow.classList.remove('is-hidden'); addDisc.classList.add('is-hidden'); discPercent.focus(); recalc(); });
        discPercent.addEventListener('input', recalc);
        document.getElementById('removeDiscountBtn').addEventListener('click', (e) => { e.preventDefault(); discPercent.value = ''; discRow.classList.add('is-hidden'); addDisc.classList.remove('is-hidden'); recalc(); });

        /* ---------- Plain editable sections: Timeline + Scope ---------- */
        // cols: array of { key, type?, cls? }
        function initRowTable(bodyId, addBtnId, name, cols, placeholders, seed, onChange) {
            const body = document.getElementById(bodyId);
            let idx = 0;
            const rowHtml = (d, i) => {
                d = d || {};
                const cells = cols.map((c) => {
                    const cls = 'form-control' + (c.cls ? ' ' + c.cls : '');
                    return `<td><input type="${c.type || 'text'}" name="${name}[${i}][${c.key}]" class="${cls}" value="${esc(d[c.key])}" placeholder="${esc(placeholders[c.key] || '')}"></td>`;
                }).join('');
                return `<tr>${cells}<td class="li-actions"><button type="button" class="li-del" title="${t.delete}"><i class="bi bi-trash3"></i></button></td></tr>`;
            };
            const add = (d) => { body.insertAdjacentHTML('beforeend', rowHtml(d, idx)); idx++; if (onChange) onChange(); };
            (seed && seed.length ? seed : [{}]).forEach(add);
            document.getElementById(addBtnId).addEventListener('click', () => add());
            body.addEventListener('click', (e) => {
                const del = e.target.closest('.li-del');
                if (del) { e.preventDefault(); if (body.querySelectorAll('tr').length > 1) del.closest('tr').remove(); if (onChange) onChange(); }
            });
            if (onChange) body.addEventListener('input', onChange);
            return { add, reset(rows) { body.innerHTML = ''; idx = 0; (rows && rows.length ? rows : [{}]).forEach(add); if (onChange) onChange(); } };
        }

        const scopeTable = initRowTable('scopeBody', 'addScopeBtn', 'scope',
            [{ key: 'item' }, { key: 'qty' }, { key: 'price' }, { key: 'total', cls: 'scope-total' }],
            { item: @json(__('Item')), qty: @json(__('Quantity / Count')), price: @json(__('Price')), total: @json(__('Total')) },
            @json($scopeItems), recalc);

        /* ---------- Contract period (from → to) + link to a contract ---------- */
        const periodFrom = document.getElementById('periodFrom');
        const periodTo = document.getElementById('periodTo');
        const periodNote = document.getElementById('periodDuration');
        function periodDays() {
            const a = periodFrom.value ? new Date(periodFrom.value) : null;
            const b = periodTo.value ? new Date(periodTo.value) : null;
            if (a && b && b >= a) return Math.round((b - a) / 86400000) + 1;
            return 0;
        }
        function updatePeriodNote() {
            const d = periodDays();
            periodNote.textContent = d > 0 ? `${t.period}: ${d} ${t.days}` : '';
        }
        periodFrom.addEventListener('change', updatePeriodNote);
        periodTo.addEventListener('change', updatePeriodNote);
        updatePeriodNote();

        const contractsById = {};
        @json($contracts).forEach((c) => { contractsById[c.id] = c; });
        // Called when a contract is picked: fill the period, the customer, and
        // rebuild "بنود الفاتورة" from the contract's payment installments.
        function onContractSelect(id) {
            const c = contractsById[id];
            if (!c) return;
            if (c.start) periodFrom.value = c.start;
            if (c.end) periodTo.value = c.end;
            updatePeriodNote();
            if (c.clientId) clientCombo._combo.set(c.clientId);
            const rows = (c.installments || []).map((i) => {
                const amt = fmt(Number(i.amount) || 0);
                return { item: i.item, qty: '1', price: amt, total: amt };
            });
            scopeTable.reset(rows.length ? rows : [{}]);
        }

        // Derive the discount % from the stored amount (edit mode).
        const seedDiscountAmount = parseFloat(discAmount.value) || 0;
        if (seedDiscountAmount > 0) {
            const sub = currentSubtotal();
            discPercent.value = sub > 0 ? +(seedDiscountAmount / sub * 100).toFixed(2) : 0;
        }
        recalc();
    })();
</script>
@endpush
