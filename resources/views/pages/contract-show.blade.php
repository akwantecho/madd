@extends('layouts.app')
@section('title', __('Contract').' '.$contract['number'])

@section('content')
    {{-- Top action bar --}}
    <div class="editor-bar full-bleed">
        <div class="editor-bar-start">
            <a href="{{ route('contracts', ['tab' => 'contracts']) }}" class="btn-icon" title="{{ __('Close') }}"><i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
            <strong class="js-number">{{ $contract['number'] }}</strong>
            @include('partials.status', ['status' => $contract['status']])
        </div>
        <div class="editor-bar-end">
            <button class="chip" onclick="window.print()"><i class="bi bi-printer"></i>{{ __('Print') }}</button>
            <button class="chip"><i class="bi bi-download"></i>{{ __('Download PDF') }}</button>
            <button class="chip"><i class="bi bi-floppy"></i>{{ __('Save as Draft') }}</button>
            <button class="btn-brand"><i class="bi bi-check2"></i>{{ __('Save Changes') }}</button>
        </div>
    </div>

    {{-- Two-part layout: A4 live preview + content editor --}}
    <div class="doc-preview">

        {{-- Content editor (start side / right in RTL) --}}
        <aside class="doc-settings">
            {{-- Live value summary --}}
            <div class="doc-card dp-due-card">
                <span class="dp-due-label">{{ __('Contract Value') }}</span>
                <strong class="dp-due-amount" id="edValueAmount">0.00</strong>
                <div class="dp-due-meta"><i class="bi bi-pencil-square"></i>{{ __('Editing contract content') }}</div>
            </div>

            {{-- Parties --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-people"></i>{{ __('Parties') }}</h3>
                <div class="ed-field">
                    <label>{{ __('Client') }} ({{ __('Second Party') }})</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-client" value="{{ $contract['client'] }}">
                </div>
                <div class="ed-field">
                    <label>{{ __('VAT Reg. No.') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-client-vat" value="{{ $contract['clientVat'] }}">
                </div>
                <div class="ed-field">
                    <label>{{ __('Representative') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-client-rep" value="{{ $contract['clientRep'] }}">
                </div>
            </div>

            {{-- Contract details --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-file-earmark-text"></i>{{ __('Contract Details') }}</h3>
                <div class="ed-field">
                    <label>{{ __('Contract Number') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".js-number" value="{{ $contract['number'] }}">
                </div>
                <div class="ed-field">
                    <label>{{ __('Contract Type') }}</label>
                    <select class="form-select form-select-sm" data-bind=".pv-type">
                        <option @selected($contract['type'] === 'عقد خدمات')>عقد خدمات</option>
                        <option @selected($contract['type'] === 'عقد رعاية')>عقد رعاية</option>
                        <option @selected($contract['type'] === 'عقد تأجير جناح')>عقد تأجير جناح</option>
                        <option @selected($contract['type'] === 'عقد توريد')>عقد توريد</option>
                    </select>
                </div>
                <div class="ed-field">
                    <label>{{ __('Exhibition') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-exhibition" value="{{ $contract['exhibition'] }}">
                </div>
                <div class="ed-grid-2">
                    <div class="ed-field">
                        <label>{{ __('Start Date') }}</label>
                        <input type="date" class="form-control form-control-sm" data-bind=".pv-start" value="{{ $contract['start'] }}">
                    </div>
                    <div class="ed-field">
                        <label>{{ __('End Date') }}</label>
                        <input type="date" class="form-control form-control-sm" data-bind=".pv-end" value="{{ $contract['end'] }}">
                    </div>
                </div>
                <div class="ed-grid-2">
                    <div class="ed-field">
                        <label>{{ __('Currency') }}</label>
                        <select class="form-select form-select-sm" id="edCurrency">
                            <option value="ر.س" @selected($contract['currency'] === 'ر.س')>SAR {{ __('SAR') }}</option>
                            <option value="ر.ع" @selected($contract['currency'] === 'ر.ع')>OMR {{ __('OMR') }}</option>
                        </select>
                    </div>
                    <div class="ed-field">
                        <label>{{ __('VAT') }} %</label>
                        <input type="number" min="0" max="100" class="form-control form-control-sm" id="edVat" value="{{ $contract['vatRate'] }}">
                    </div>
                </div>
            </div>

            {{-- Contract items editor --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-list-ul"></i>{{ __('Contract Items') }}</h3>
                <div class="ed-items" id="edItems"></div>
                <button type="button" class="chip w-100 ed-add" id="edAddBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Line') }}</button>
            </div>

            {{-- Payment schedule editor --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-calendar2-check"></i>{{ __('Payment Schedule') }}</h3>
                <div class="ed-items" id="edSched"></div>
                <button type="button" class="chip w-100 ed-add" id="edAddSchedBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Installment') }}</button>
            </div>

            {{-- Terms editor --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-card-checklist"></i>{{ __('Terms & Conditions') }}</h3>
                <div class="ed-items" id="edTerms"></div>
                <button type="button" class="chip w-100 ed-add" id="edAddTermBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Clause') }}</button>
            </div>
        </aside>

        {{-- A4 document (live preview) --}}
        <div class="doc-canvas">
            <div class="doc-paper" id="docPaper">

                {{-- Header --}}
                <div class="dp-head">
                    <div class="dp-brand">
                        <span class="dp-logo">{{ $company['logo'] }}</span>
                        <div class="dp-brand-info">
                            <strong>{{ $company['name'] }}</strong>
                            <div>{{ $company['address'] }}</div>
                            <div>{{ $company['country'] }}</div>
                            <div>{{ __('VAT Reg. No.') }}: {{ $company['vat'] }}</div>
                        </div>
                    </div>
                    <div class="dp-title-block">
                        <h1 class="dp-doctitle pv-type">{{ $contract['type'] }}</h1>
                        <div class="dp-doc-no js-number">{{ $contract['number'] }}</div>
                        <span class="dp-status-pill">{{ __($contract['status']) }}</span>
                    </div>
                </div>

                <div class="dp-rule"></div>

                {{-- Parties + meta --}}
                <div class="dp-meta">
                    <div class="dp-billto">
                        <span class="dp-meta-label">{{ __('Second Party') }}</span>
                        <strong class="pv-client">{{ $contract['client'] }}</strong>
                        <div>{{ __('VAT Reg. No.') }}: <span class="pv-client-vat">{{ $contract['clientVat'] }}</span></div>
                        <div>{{ __('Representative') }}: <span class="pv-client-rep">{{ $contract['clientRep'] }}</span></div>
                    </div>
                    <div class="dp-info">
                        <div class="dp-info-row"><span>{{ __('Contract Number') }}</span><span class="js-number">{{ $contract['number'] }}</span></div>
                        <div class="dp-info-row"><span>{{ __('Exhibition') }}</span><span class="pv-exhibition">{{ $contract['exhibition'] }}</span></div>
                        <div class="dp-info-row"><span>{{ __('Start Date') }}</span><span class="pv-start">{{ $contract['start'] }}</span></div>
                        <div class="dp-info-row"><span>{{ __('End Date') }}</span><span class="pv-end">{{ $contract['end'] }}</span></div>
                    </div>
                </div>

                {{-- Contract items --}}
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th class="dp-c-num">#</th>
                            <th>{{ __('Description') }}</th>
                            <th class="dp-c-qty">{{ __('Quantity') }}</th>
                            <th class="dp-c-amt">{{ __('Price') }}</th>
                            <th class="dp-c-amt">{{ __('Line Total') }}</th>
                        </tr>
                    </thead>
                    <tbody id="pvItems"></tbody>
                </table>

                {{-- Contract value totals --}}
                <div class="dp-bottom">
                    <div></div>
                    <div class="dp-totals" id="pvTotals"></div>
                </div>

                {{-- Payment schedule --}}
                <div class="dp-section">
                    <h2 class="dp-sec-title">{{ __('Payment Schedule') }}</h2>
                    <table class="dp-table">
                        <thead>
                            <tr>
                                <th class="dp-c-num">#</th>
                                <th>{{ __('Installment') }}</th>
                                <th class="dp-c-qty">%</th>
                                <th class="dp-c-qty">{{ __('Due Date') }}</th>
                                <th class="dp-c-amt">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody id="pvSched"></tbody>
                        <tfoot id="pvSchedFoot"></tfoot>
                    </table>
                </div>

                {{-- Terms --}}
                <div class="dp-section">
                    <h2 class="dp-sec-title">{{ __('Terms & Conditions') }}</h2>
                    <ol class="dp-terms" id="pvTerms"></ol>
                </div>

                {{-- Signatures --}}
                <div class="dp-signs">
                    <div class="dp-sign">
                        <span class="dp-sign-line"></span>
                        <span>{{ __('First Party') }} — {{ $company['name'] }}</span>
                    </div>
                    <div class="dp-sign">
                        <span class="dp-sign-line"></span>
                        <span>{{ __('Second Party') }} — <span class="pv-client">{{ $contract['client'] }}</span></span>
                    </div>
                </div>

                <div class="dp-paper-foot">
                    <span>{{ $company['name'] }}</span>
                    <span>{{ $company['phone'] }} · {{ $company['email'] }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    window.ctShow = {
        items: @json($items),
        schedule: @json($schedule),
        terms: @json($terms),
        currency: @json($contract['currency']),
        vatRate: {{ $contract['vatRate'] }},
        i18n: {
            required: @json(__('Required')),
            delete: @json(__('Delete')),
            qty: @json(__('Quantity')),
            price: @json(__('Price')),
            installment: @json(__('Installment')),
            percent: @json(__('Percentage')),
            due: @json(__('Due Date')),
            clause: @json(__('Clause')),
            subtotal: @json(__('Subtotal')),
            vat: @json(__('Total VAT')),
            value: @json(__('Contract Value')),
            totalPct: @json(__('Total of percentages')),
        },
    };

    (function () {
        const cfg = window.ctShow;
        const t = cfg.i18n;
        const state = {
            items: cfg.items.map((i) => ({ desc: i.desc || '', qty: +i.qty || 0, price: +i.price || 0 })),
            schedule: cfg.schedule.map((s) => ({ desc: s.desc || '', percent: +s.percent || 0, due: s.due || '' })),
            terms: cfg.terms.slice(),
            currency: cfg.currency,
            vatRate: +cfg.vatRate || 0,
        };

        const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        const elItems = document.getElementById('edItems');
        const elSched = document.getElementById('edSched');
        const elTerms = document.getElementById('edTerms');
        const pvItems = document.getElementById('pvItems');
        const pvTotals = document.getElementById('pvTotals');
        const pvSched = document.getElementById('pvSched');
        const pvSchedFoot = document.getElementById('pvSchedFoot');
        const pvTerms = document.getElementById('pvTerms');
        const edValue = document.getElementById('edValueAmount');

        let contractValue = 0;

        /* ---- Editor renderers (rebuilt only on add / delete) ---- */
        function renderItemsEditor() {
            elItems.innerHTML = state.items.map((it, idx) => `
                <div class="ed-item" data-idx="${idx}">
                    <button type="button" class="ed-del" title="${t.delete}"><i class="bi bi-x-lg"></i></button>
                    <input type="text" class="form-control form-control-sm ed-desc" placeholder="${t.required}" value="${esc(it.desc)}">
                    <div class="ed-grid-2">
                        <label class="ed-mini"><small>${t.qty}</small><input type="number" min="0" class="form-control form-control-sm ed-qty" value="${it.qty}"></label>
                        <label class="ed-mini"><small>${t.price}</small><input type="number" min="0" class="form-control form-control-sm ed-price" value="${it.price}"></label>
                    </div>
                </div>`).join('');
        }
        function renderSchedEditor() {
            elSched.innerHTML = state.schedule.map((s, idx) => `
                <div class="ed-item" data-idx="${idx}">
                    <button type="button" class="ed-del" title="${t.delete}"><i class="bi bi-x-lg"></i></button>
                    <input type="text" class="form-control form-control-sm sc-desc" placeholder="${t.installment}" value="${esc(s.desc)}">
                    <div class="ed-grid-2">
                        <label class="ed-mini"><small>${t.percent} %</small><input type="number" min="0" max="100" class="form-control form-control-sm sc-pct" value="${s.percent}"></label>
                        <label class="ed-mini"><small>${t.due}</small><input type="date" class="form-control form-control-sm sc-due" value="${s.due}"></label>
                    </div>
                </div>`).join('');
        }
        function renderTermsEditor() {
            elTerms.innerHTML = state.terms.map((tx, idx) => `
                <div class="ed-item ed-term" data-idx="${idx}">
                    <button type="button" class="ed-del" title="${t.delete}"><i class="bi bi-x-lg"></i></button>
                    <textarea class="form-control form-control-sm tm-text" rows="2" placeholder="${t.clause}">${esc(tx)}</textarea>
                </div>`).join('');
        }

        /* ---- Preview renderer (rebuilt on every change) ---- */
        function renderPreview() {
            // Items + totals
            pvItems.innerHTML = state.items.map((it, idx) => `
                <tr>
                    <td class="dp-c-num">${idx + 1}</td>
                    <td>${esc(it.desc) || '<span class="dp-empty">—</span>'}</td>
                    <td class="dp-c-qty">${it.qty}</td>
                    <td class="dp-c-amt">${fmt(it.price)}</td>
                    <td class="dp-c-amt">${fmt(it.qty * it.price)}</td>
                </tr>`).join('');

            const subtotal = state.items.reduce((s, it) => s + it.qty * it.price, 0);
            const vat = subtotal * state.vatRate / 100;
            contractValue = subtotal + vat;
            const cur = state.currency;
            pvTotals.innerHTML = `
                <div class="dp-tot-row"><span>${t.subtotal}</span><span>${fmt(subtotal)} ${cur}</span></div>
                <div class="dp-tot-row"><span>${t.vat} (${state.vatRate}%)</span><span>${fmt(vat)} ${cur}</span></div>
                <div class="dp-tot-row grand"><span>${t.value}</span><span>${fmt(contractValue)} ${cur}</span></div>`;
            edValue.textContent = fmt(contractValue) + ' ' + cur;

            // Schedule
            let pctSum = 0, amtSum = 0;
            pvSched.innerHTML = state.schedule.map((s, idx) => {
                const amt = contractValue * s.percent / 100;
                pctSum += s.percent; amtSum += amt;
                return `<tr>
                    <td class="dp-c-num">${idx + 1}</td>
                    <td>${esc(s.desc) || '<span class="dp-empty">—</span>'}</td>
                    <td class="dp-c-qty">${s.percent}%</td>
                    <td class="dp-c-qty">${esc(s.due) || '—'}</td>
                    <td class="dp-c-amt">${fmt(amt)} ${cur}</td>
                </tr>`;
            }).join('');
            const ok = Math.abs(pctSum - 100) < 0.01;
            pvSchedFoot.innerHTML = `<tr class="dp-sched-foot">
                <td></td><td>${t.totalPct}</td>
                <td class="dp-c-qty"><span class="dp-pct ${ok ? 'ok' : 'bad'}">${Math.round(pctSum * 100) / 100}%</span></td>
                <td></td><td class="dp-c-amt">${fmt(amtSum)} ${cur}</td></tr>`;

            // Terms
            pvTerms.innerHTML = state.terms.map((tx) => `<li>${esc(tx) || '<span class="dp-empty">—</span>'}</li>`).join('');
        }

        /* ---- Sync editor inputs back into state ---- */
        function syncItems() {
            elItems.querySelectorAll('.ed-item').forEach((row, idx) => {
                if (!state.items[idx]) return;
                state.items[idx] = { desc: row.querySelector('.ed-desc').value, qty: parseFloat(row.querySelector('.ed-qty').value) || 0, price: parseFloat(row.querySelector('.ed-price').value) || 0 };
            });
        }
        function syncSched() {
            elSched.querySelectorAll('.ed-item').forEach((row, idx) => {
                if (!state.schedule[idx]) return;
                state.schedule[idx] = { desc: row.querySelector('.sc-desc').value, percent: parseFloat(row.querySelector('.sc-pct').value) || 0, due: row.querySelector('.sc-due').value };
            });
        }
        function syncTerms() {
            elTerms.querySelectorAll('.ed-item').forEach((row, idx) => { state.terms[idx] = row.querySelector('.tm-text').value; });
        }

        /* ---- Wiring: items ---- */
        elItems.addEventListener('input', () => { syncItems(); renderPreview(); });
        elItems.addEventListener('click', (e) => {
            if (!e.target.closest('.ed-del')) return;
            state.items.splice(+e.target.closest('.ed-item').dataset.idx, 1);
            if (!state.items.length) state.items.push({ desc: '', qty: 1, price: 0 });
            renderItemsEditor(); renderPreview();
        });
        document.getElementById('edAddBtn').addEventListener('click', () => { syncItems(); state.items.push({ desc: '', qty: 1, price: 0 }); renderItemsEditor(); renderPreview(); });

        /* ---- Wiring: schedule ---- */
        elSched.addEventListener('input', () => { syncSched(); renderPreview(); });
        elSched.addEventListener('click', (e) => {
            if (!e.target.closest('.ed-del')) return;
            state.schedule.splice(+e.target.closest('.ed-item').dataset.idx, 1);
            renderSchedEditor(); renderPreview();
        });
        document.getElementById('edAddSchedBtn').addEventListener('click', () => { syncSched(); state.schedule.push({ desc: '', percent: 0, due: '' }); renderSchedEditor(); renderPreview(); });

        /* ---- Wiring: terms ---- */
        elTerms.addEventListener('input', () => { syncTerms(); renderPreview(); });
        elTerms.addEventListener('click', (e) => {
            if (!e.target.closest('.ed-del')) return;
            state.terms.splice(+e.target.closest('.ed-item').dataset.idx, 1);
            renderTermsEditor(); renderPreview();
        });
        document.getElementById('edAddTermBtn').addEventListener('click', () => { syncTerms(); state.terms.push(''); renderTermsEditor(); renderPreview(); });

        /* ---- Adjustments + currency ---- */
        document.getElementById('edVat').addEventListener('input', (e) => { state.vatRate = parseFloat(e.target.value) || 0; renderPreview(); });
        document.getElementById('edCurrency').addEventListener('change', (e) => { state.currency = e.target.value; renderPreview(); });

        /* ---- Direct text bindings (parties / meta) ---- */
        document.querySelectorAll('[data-bind]').forEach((inp) => {
            inp.addEventListener('input', () => { document.querySelectorAll(inp.dataset.bind).forEach((el) => { el.textContent = inp.value; }); });
        });

        renderItemsEditor(); renderSchedEditor(); renderTermsEditor();
        renderPreview();
    })();
</script>
@endpush
