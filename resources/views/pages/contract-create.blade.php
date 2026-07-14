@extends('layouts.app')
@section('title', $isEdit ? __('Edit Contract') : __('Create Contract'))

@section('content')
    {{-- Editor action bar --}}
    <div class="editor-bar full-bleed">
        <div class="editor-bar-start">
            <a href="{{ route('contracts') }}" class="btn-icon" title="{{ __('Close') }}"><i class="bi bi-x-lg"></i></a>
            <strong>{{ $isEdit ? __('Edit Contract') : __('Create Contract') }}</strong>
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

    {{-- Contract document --}}
    <form id="docForm" method="POST" action="{{ $action }}" class="invoice-doc">
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <input type="hidden" name="vat_rate" value="0">

        <div class="inv-head">
            <h2 class="inv-title">{{ __('Service Contract') }}</h2>
            <span class="section-link"><i class="bi bi-hash me-1"></i>{{ $contract['number'] }}</span>
        </div>

        <div class="row g-4 inv-top">
            <div class="col-lg-7 order-2 order-lg-1">
                {{-- Client: type-to-search + quick add --}}
                <div class="inv-field">
                    <label>{{ __('Client') }} <span class="req">*</span></label>
                    <div class="combo" id="clientCombo"
                         data-options='@json($clients)'
                         data-empty="{{ __('No matching customers') }}">
                        <input type="hidden" name="client_id" value="{{ $contract['clientId'] }}">
                        <input type="text" class="form-control combo-input" autocomplete="off" placeholder="{{ __('Type customer name to search…') }}">
                        <div class="combo-pop">
                            <ul class="combo-list"></ul>
                            <button type="button" class="combo-add" data-modal="addClientModal"><i class="bi bi-plus-lg"></i>{{ __('Add new customer') }}</button>
                        </div>
                    </div>
                </div>
                <div class="inv-field">
                    <label>{{ __('Contract Number') }} <span class="req">*</span></label>
                    <input type="text" name="number" class="form-control" value="{{ old('number', $contract['number']) }}">
                </div>
                <div class="inv-field">
                    <label>{{ __('Contract Type') }} <span class="req">*</span></label>
                    <input type="text" name="type" class="form-control" value="{{ old('type', $contract['type']) }}" placeholder="{{ __('e.g. Service Contract') }}">
                </div>
                {{-- Exhibition: type-to-search + quick add --}}
                <div class="inv-field">
                    <label>{{ __('Exhibition') }}</label>
                    <div class="combo" id="exhibitionCombo"
                         data-options='@json($exhibitions)'
                         data-empty="{{ __('No matching exhibitions') }}">
                        <input type="hidden" name="exhibition_id" value="{{ $contract['exhibitionId'] }}">
                        <input type="text" class="form-control combo-input" autocomplete="off" placeholder="{{ __('Type exhibition name to search…') }}">
                        <div class="combo-pop">
                            <ul class="combo-list"></ul>
                            <button type="button" class="combo-add" data-modal="addExhibitionModal"><i class="bi bi-plus-lg"></i>{{ __('Add new exhibition') }}</button>
                        </div>
                    </div>
                </div>
                <div class="inv-field">
                    <label>{{ __('Currency') }} <span class="req">*</span></label>
                    <select name="currency" class="form-select">
                        <option value="ر.ع" selected>{{ __('Omani Rial') }} ر.ع</option>
                    </select>
                </div>
                <div class="inv-field">
                    <label>{{ __('Contract Period') }} <span class="req">*</span></label>
                    <div class="row g-2 period-grid">
                        <div class="col-6">
                            <span class="period-sub">{{ __('From') }}</span>
                            <input type="date" name="start_date" id="periodFrom" class="form-control" value="{{ old('start_date', $contract['start']) }}">
                        </div>
                        <div class="col-6">
                            <span class="period-sub">{{ __('To') }}</span>
                            <input type="date" name="end_date" id="periodTo" class="form-control" value="{{ old('end_date', $contract['end']) }}">
                        </div>
                    </div>
                    <div class="period-note" id="periodDuration"></div>
                </div>
            </div>
            <div class="col-lg-5 order-1 order-lg-2">
                <div class="inv-company">
                    <span class="inv-logo">{{ $company['logo'] }}</span>
                    <div class="inv-company-info">
                        <strong>{{ $company['name'] }}</strong>
                        <div class="cell-muted">{{ $company['address'] }}</div>
                        <div class="cell-muted">{{ $company['country'] }}</div>
                        @if (!empty($company['cr']))<div class="cell-muted">{{ __('C.R. No.') }}: {{ $company['cr'] }}</div>@endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Contract items (linked to stock) --}}
        <div class="inv-items">
            <div class="inv-items-bar"><i class="bi bi-box-seam"></i>{{ __('Contract Items') }}</div>
            <div class="table-wrap items-wrap">
                <table class="lineitems">
                    <thead>
                    <tr>
                        <th style="width:28px"></th>
                        <th>{{ __('Item (from stock)') }} <span class="req">*</span></th>
                        <th style="width:80px">{{ __('Quantity') }} <span class="req">*</span></th>
                        <th style="width:150px">{{ __('Price') }} <span class="req">*</span></th>
                        <th style="width:90px">{{ __('Days') }}</th>
                        <th style="width:140px">{{ __('Line Total') }}</th>
                        <th style="width:44px"></th>
                    </tr>
                    </thead>
                    <tbody id="lineItems"></tbody>
                </table>
            </div>
            <button type="button" class="chip inv-addline" id="addLineBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Line') }}</button>
        </div>

        {{-- Contract value totals --}}
        <div class="row inv-bottom g-4">
            <div class="col-lg-6">
                <div class="inv-totals">
                    <div class="tot-row grand"><span>{{ __('Contract Value') }}</span><span><span id="sumTotal">0.00</span> {{ __('OMR') }}</span></div>
                </div>
            </div>
        </div>

        <hr class="inv-hr">

        {{-- Payment schedule --}}
        <div class="inv-items">
            <div class="inv-items-bar"><i class="bi bi-calendar2-check"></i>{{ __('Payment Schedule') }}</div>
            <div class="table-wrap">
                <table class="lineitems">
                    <thead>
                    <tr>
                        <th style="width:28px"></th>
                        <th>{{ __('Installment') }} <span class="req">*</span></th>
                        <th style="width:130px">{{ __('Percentage') }} %</th>
                        <th style="width:170px">{{ __('Due Date') }}</th>
                        <th style="width:150px">{{ __('Amount') }}</th>
                        <th style="width:44px"></th>
                    </tr>
                    </thead>
                    <tbody id="schedItems"></tbody>
                    <tfoot>
                    <tr class="sched-foot">
                        <td></td>
                        <td class="cell-strong">{{ __('Total of percentages') }}</td>
                        <td><span id="schedPctTotal" class="badge-soft green">0%</span></td>
                        <td></td>
                        <td class="inv-linetotal"><span id="schedAmtTotal">0.00</span> {{ __('OMR') }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="chip inv-addline" id="addSchedBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Installment') }}</button>
        </div>

        <hr class="inv-hr">

        {{-- Terms & notices — pick from the library (options) managed in Stock --}}
        <div class="inv-section">
            <h3 class="inv-sec-title">{{ __('Terms & Conditions') }}</h3>
            @if (!empty($notices))
                <div class="notice-options" id="noticeOptions">
                    @foreach ($notices as $n)
                        <label class="notice-opt">
                            <input type="checkbox" name="terms[]" value="{{ $n }}" class="notice-check">
                            <span>{{ $n }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
            <ol class="terms-list" id="termsList"></ol>
            <button type="button" class="chip" id="addTermBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Clause') }}</button>
        </div>

        {{-- Signatures --}}
        <div class="row g-4 inv-section">
            <div class="col-md-6">
                <div class="sign-box"><span class="cell-muted">{{ __('First Party') }} ({{ $company['name'] }})</span></div>
            </div>
            <div class="col-md-6">
                <div class="sign-box"><span class="cell-muted">{{ __('Second Party') }}</span></div>
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

    {{-- Quick-add: Stock item --}}
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add new stock item') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">{{ __('Name') }} <span class="req">*</span></label><input type="text" class="form-control" id="as-name"></div>
                    <div class="mb-3"><label class="form-label">{{ __('Price') }} ({{ __('OMR') }})</label><input type="number" min="0" step="0.001" class="form-control" id="as-price"></div>
                    <div class="text-danger small" id="as-err"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="chip" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn-brand" id="as-save"><i class="bi bi-check2"></i>{{ __('Save') }}</button>
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
    .period-sub{display:block;font-size:12px;color:var(--muted);margin-bottom:3px}
    .period-note{font-size:12.5px;color:var(--brand);margin-top:6px;min-height:16px}
    /* Let each item's inline stock dropdown escape the items table instead of being clipped */
    .items-wrap{overflow:visible}
    .item-combo .combo-pop{min-width:220px}
    .notice-options{display:flex;flex-direction:column;gap:8px;margin-bottom:12px}
    .notice-opt{display:flex;align-items:flex-start;gap:9px;padding:10px 12px;border:1px solid var(--line);border-radius:9px;cursor:pointer;font-size:13.5px;line-height:1.5}
    .notice-opt:hover{background:var(--hover)}
    .notice-opt input{margin-top:3px;flex:0 0 auto}
    .notice-opt:has(input:checked){border-color:var(--brand);background:var(--brand-soft)}
</style>
<script>
    window.ctI18n = {
        required: @json(__('Required')),
        delete: @json(__('Delete')),
        currency: @json(__('OMR')),
        installment: @json(__('Installment')),
        days: @json(__('days')),
        period: @json(__('Total Period')),
        noStock: @json(__('No matching items')),
        addStock: @json(__('Add new stock item')),
    };
    (function () {
        const t = window.ctI18n;
        const VAT = (parseFloat(document.querySelector('input[name="vat_rate"]').value) || 5) / 100;
        const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const esc = (s) => (s == null ? '' : String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])));
        const token = document.querySelector('meta[name="csrf-token"]').content;

        /* ---- Type-to-search combos (client / exhibition) + quick add ---- */
        function initCombo(root) {
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
            function select(id, name) { hidden.value = id; input.value = name; close(); }
            input.addEventListener('focus', open);
            input.addEventListener('input', () => {
                const exact = options.find(o => String(o.name).toLowerCase() === input.value.trim().toLowerCase());
                hidden.value = exact ? exact.id : '';
                open();
            });
            list.addEventListener('click', (e) => {
                const li = e.target.closest('li[data-id]');
                if (li) select(li.dataset.id, li.textContent);
            });
            document.addEventListener('click', (e) => { if (!root.contains(e.target)) close(); });
            const sel = hidden.value ? options.find(o => String(o.id) === String(hidden.value)) : null;
            if (sel) input.value = sel.name;
            root._combo = { addOption(o) { options.push(o); select(o.id, o.name); }, typedText() { return input.value.trim(); } };
        }
        const clientCombo = document.getElementById('clientCombo');
        const exhibitionCombo = document.getElementById('exhibitionCombo');
        initCombo(clientCombo);
        initCombo(exhibitionCombo);

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

        /* ---- Contract period (from → to) — drives item duration ---- */
        const pFrom = document.getElementById('periodFrom');
        const pTo = document.getElementById('periodTo');
        const pNote = document.getElementById('periodDuration');
        // Number of days spanned by the contract period (inclusive); 1 if unset/invalid.
        function periodDays() {
            const a = pFrom.value ? new Date(pFrom.value) : null;
            const b = pTo.value ? new Date(pTo.value) : null;
            if (a && b && b >= a) return Math.round((b - a) / 86400000) + 1;
            return 1;
        }
        function updatePeriod() {
            const days = periodDays();
            pNote.textContent = (pFrom.value && pTo.value) ? `${t.period}: ${days} ${t.days}` : '';
            // Sync every item's days to the contract period unless it was edited by hand.
            document.querySelectorAll('.li-days').forEach((inp) => {
                if (inp.dataset.manual !== '1') { inp.value = days; }
            });
            if (typeof recalc === 'function') recalc();
        }
        pFrom.addEventListener('change', updatePeriod);
        pTo.addEventListener('change', updatePeriod);

        /* ---- Contract items ---- */
        const items = document.getElementById('lineItems');
        let liIndex = 0;
        function itemRow(d, idx) {
            d = d || {};
            return `<tr class="li-row">
                <td class="li-grip"><i class="bi bi-grip-vertical"></i></td>
                <td>
                    <div class="combo item-combo">
                        <input type="text" name="items[${idx}][description]" class="form-control combo-input li-desc" autocomplete="off" value="${esc(d.desc)}" placeholder="${t.required}">
                        <div class="combo-pop">
                            <ul class="combo-list"></ul>
                            <button type="button" class="combo-add stock-add"><i class="bi bi-plus-lg"></i>${t.addStock}</button>
                        </div>
                    </div>
                </td>
                <td><input type="number" min="1" step="1" name="items[${idx}][qty]" class="form-control li-qty" value="${d.qty != null ? d.qty : 1}"></td>
                <td><input type="text" name="items[${idx}][price]" class="form-control li-price" value="${d.price != null && d.price !== '' ? d.price : ''}" placeholder="${t.required}"></td>
                <td><input type="number" min="1" step="1" name="items[${idx}][days]" class="form-control li-days" data-manual="${d.days != null && d.days !== '' ? '1' : ''}" value="${d.days != null && d.days !== '' ? d.days : periodDays()}"></td>
                <td class="inv-linetotal"><span class="li-total">0.00</span> ${t.currency}</td>
                <td class="li-actions"><button type="button" class="li-del" title="${t.delete}"><i class="bi bi-trash3"></i></button></td>
            </tr>`;
        }
        function addItem(d) { items.insertAdjacentHTML('beforeend', itemRow(d, liIndex++)); initItemCombo(items.lastElementChild.querySelector('.item-combo')); recalc(); }

        let contractTotal = 0;
        function recalc() {
            let subtotal = 0;
            items.querySelectorAll('.li-row').forEach((r) => {
                const qty = parseFloat(r.querySelector('.li-qty').value) || 0;
                const price = parseFloat(r.querySelector('.li-price').value) || 0;
                const days = parseFloat(r.querySelector('.li-days').value) || 1;
                const amt = qty * price * days;
                subtotal += amt;
                r.querySelector('.li-total').textContent = fmt(amt);
            });
            contractTotal = subtotal;
            document.getElementById('sumTotal').textContent = fmt(contractTotal);
            recalcSchedule();
        }
        // Stock price lookup: name -> price (auto-fill price when a stock item is picked)
        const stockPrices = {};
        @json($stock).forEach((s) => { stockPrices[s.name] = s.price; });
        const stockOptions = @json($stock); // [{name, price}]
        function applyStockPrice(descInput) {
            const p = stockPrices[descInput.value.trim()];
            if (p != null) {
                const priceInput = descInput.closest('.li-row').querySelector('.li-price');
                if (priceInput && (priceInput.value === '' || priceInput.dataset.auto === '1')) {
                    priceInput.value = p; priceInput.dataset.auto = '1'; recalc();
                }
            }
        }

        /* ---- Stock search dropdown, inline under each item (same UX as the customer search) ---- */
        let stockTarget = null; // item input to fill after a quick-add
        function renderStockList(listEl, filter) {
            const f = (filter || '').trim().toLowerCase();
            const list = f ? stockOptions.filter(o => String(o.name).toLowerCase().includes(f)) : stockOptions;
            listEl.innerHTML = list.length
                ? list.slice(0, 50).map(o => `<li data-name="${esc(o.name)}">${esc(o.name)}</li>`).join('')
                : `<li class="empty">${esc(t.noStock)}</li>`;
        }
        function initItemCombo(root) {
            const input = root.querySelector('.li-desc');
            const list = root.querySelector('.combo-list');
            const open = () => { root.classList.add('open'); renderStockList(list, input.value); };
            const close = () => root.classList.remove('open');
            input.addEventListener('focus', open);
            input.addEventListener('input', () => { open(); applyStockPrice(input); });
            list.addEventListener('mousedown', (e) => {
                const li = e.target.closest('li[data-name]');
                if (!li) return;
                e.preventDefault();
                input.value = li.dataset.name;
                applyStockPrice(input);
                close();
            });
            root.querySelector('.stock-add').addEventListener('mousedown', (e) => {
                e.preventDefault();
                stockTarget = input;
                document.getElementById('as-name').value = input.value.trim();
                document.getElementById('as-price').value = '';
                close();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addStockModal')).show();
            });
            root._closeCombo = close;
        }
        document.addEventListener('click', (e) => {
            document.querySelectorAll('.item-combo.open').forEach((c) => { if (!c.contains(e.target)) c._closeCombo(); });
        });
        document.getElementById('as-save').addEventListener('click', async () => {
            const err = document.getElementById('as-err');
            err.textContent = '';
            const name = document.getElementById('as-name').value.trim();
            if (!name) { err.textContent = t.required; return; }
            const price = document.getElementById('as-price').value;
            const btn = document.getElementById('as-save');
            btn.disabled = true;
            try {
                const created = await postJson(@json(route('invoices.quickStock')), { name, price: price === '' ? 0 : price });
                stockOptions.push({ name: created.name, price: created.price });
                stockPrices[created.name] = created.price;
                if (stockTarget) { stockTarget.value = created.name; applyStockPrice(stockTarget); }
                bootstrap.Modal.getInstance(document.getElementById('addStockModal')).hide();
            } catch (e) { err.textContent = e.message; }
            finally { btn.disabled = false; }
        });
        document.getElementById('addStockModal').addEventListener('hidden.bs.modal', function () {
            this.querySelectorAll('input').forEach(i => i.value = '');
            document.getElementById('as-err').textContent = '';
        });

        items.addEventListener('input', (e) => {
            if (e.target.matches('.li-days')) e.target.dataset.manual = '1';
            if (e.target.matches('.li-qty, .li-price, .li-days')) {
                if (e.target.matches('.li-price')) e.target.dataset.auto = '';
                recalc();
            }
        });
        items.addEventListener('click', (e) => { const del = e.target.closest('.li-del'); if (del) { e.preventDefault(); if (items.querySelectorAll('.li-row').length > 1) { del.closest('.li-row').remove(); recalc(); } } });
        document.getElementById('addLineBtn').addEventListener('click', () => addItem());

        /* ---- Payment schedule ---- */
        const sched = document.getElementById('schedItems');
        let scIndex = 0;
        function schedRow(d, idx) {
            d = d || {};
            return `<tr class="li-row">
                <td class="li-grip"><i class="bi bi-grip-vertical"></i></td>
                <td><input type="text" name="schedule[${idx}][description]" class="form-control sc-desc" value="${esc(d.desc)}" placeholder="${t.installment}"></td>
                <td><input type="number" min="0" max="100" name="schedule[${idx}][percent]" class="form-control sc-pct" value="${d.percent != null && d.percent !== '' ? d.percent : ''}"></td>
                <td><input type="date" name="schedule[${idx}][due_date]" class="form-control sc-due" value="${d.due || ''}"></td>
                <td class="inv-linetotal"><span class="sc-amt">0.00</span> ${t.currency}</td>
                <td class="li-actions"><button type="button" class="li-del" title="${t.delete}"><i class="bi bi-trash3"></i></button></td>
            </tr>`;
        }
        function addSched(d) { sched.insertAdjacentHTML('beforeend', schedRow(d, scIndex++)); recalcSchedule(); }
        function recalcSchedule() {
            let pctSum = 0, amtSum = 0;
            sched.querySelectorAll('.li-row').forEach((r) => {
                const pct = parseFloat(r.querySelector('.sc-pct').value) || 0;
                const amt = contractTotal * pct / 100;
                pctSum += pct; amtSum += amt;
                r.querySelector('.sc-amt').textContent = fmt(amt);
            });
            const badge = document.getElementById('schedPctTotal');
            badge.textContent = (Math.round(pctSum * 100) / 100) + '%';
            badge.className = 'badge-soft ' + (Math.abs(pctSum - 100) < 0.01 ? 'green' : 'red');
            document.getElementById('schedAmtTotal').textContent = fmt(amtSum);
        }
        sched.addEventListener('input', (e) => { if (e.target.matches('.sc-pct')) recalcSchedule(); });
        sched.addEventListener('click', (e) => { const del = e.target.closest('.li-del'); if (del) { e.preventDefault(); if (sched.querySelectorAll('.li-row').length > 1) { del.closest('.li-row').remove(); recalcSchedule(); } } });
        document.getElementById('addSchedBtn').addEventListener('click', () => addSched());

        /* ---- Terms & conditions ---- */
        const terms = document.getElementById('termsList');
        function termRow(text) {
            return `<li class="term-item"><textarea name="terms[]" class="form-control" rows="1">${text || ''}</textarea><button type="button" class="li-del" title="${t.delete}"><i class="bi bi-trash3"></i></button></li>`;
        }
        function addTerm(text) { terms.insertAdjacentHTML('beforeend', termRow(text)); }
        terms.addEventListener('click', (e) => { const del = e.target.closest('.li-del'); if (del) { e.preventDefault(); if (terms.querySelectorAll('.term-item').length > 1) del.closest('.term-item').remove(); } });
        document.getElementById('addTermBtn').addEventListener('click', () => addTerm());

        /* ---- Seed from server (existing data on edit, otherwise blank rows) ---- */
        const seedItems = @json($items);
        (seedItems.length ? seedItems : [{}]).forEach(addItem);

        const seedSched = @json($schedule);
        (seedSched.length ? seedSched : [{}]).forEach(addSched);

        // Seed terms: tick matching library options, keep the rest as custom clauses.
        const noticeChecks = Array.from(document.querySelectorAll('.notice-check'));
        const seedTerms = @json($terms);
        const customTerms = [];
        seedTerms.forEach((tt) => {
            const box = noticeChecks.find((c) => c.value === tt);
            if (box) { box.checked = true; } else { customTerms.push(tt); }
        });
        (customTerms.length ? customTerms : (noticeChecks.length ? [] : [''])).forEach(addTerm);

        updatePeriod();
    })();
</script>
@endpush
