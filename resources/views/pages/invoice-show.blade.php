@extends('layouts.app')
@section('title', __('Invoice').' '.$invoice['number'])

@section('content')
    {{-- Top action bar --}}
    <div class="editor-bar full-bleed">
        <div class="editor-bar-start">
            <a href="{{ route('contracts', ['tab' => 'invoices']) }}" class="btn-icon" title="{{ __('Close') }}"><i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
            <strong class="js-number">{{ $invoice['number'] }}</strong>
            @include('partials.status', ['status' => $invoice['status']])
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
            {{-- Live total summary --}}
            <div class="doc-card dp-due-card">
                <span class="dp-due-label">{{ __('Amount Due') }}</span>
                <strong class="dp-due-amount" id="edDueAmount">0.00</strong>
                <div class="dp-due-meta"><i class="bi bi-pencil-square"></i>{{ __('Editing invoice content') }}</div>
            </div>

            {{-- Customer --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-person-vcard"></i>{{ __('Customer') }}</h3>
                <div class="ed-field">
                    <label>{{ __('Client Name') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-cust-name" value="{{ $customer['name'] }}">
                </div>
                <div class="ed-field">
                    <label>{{ __('Address') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-cust-address" value="{{ $customer['address'] }}">
                </div>
                <div class="ed-field">
                    <label>{{ __('Attn') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".pv-cust-contact" value="{{ $customer['contact'] }}">
                </div>
            </div>

            {{-- Invoice details --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-receipt"></i>{{ __('Invoice Details') }}</h3>
                <div class="ed-field">
                    <label>{{ __('Invoice Number') }}</label>
                    <input type="text" class="form-control form-control-sm" data-bind=".js-number" value="{{ $invoice['number'] }}">
                </div>
                <div class="ed-grid-2">
                    <div class="ed-field">
                        <label>{{ __('Issue Date') }}</label>
                        <input type="date" class="form-control form-control-sm" data-bind=".pv-issue" value="{{ $invoice['date'] }}">
                    </div>
                    <div class="ed-field">
                        <label>{{ __('Due Date') }}</label>
                        <input type="date" class="form-control form-control-sm" data-bind=".pv-due" value="{{ $invoice['due'] }}">
                    </div>
                </div>
                <div class="ed-field">
                    <label>{{ __('Currency') }}</label>
                    <select class="form-select form-select-sm" id="edCurrency">
                        <option value="ر.ع" selected>{{ __('Omani Rial') }} ر.ع</option>
                    </select>
                </div>
            </div>

            {{-- Line items editor --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-list-ul"></i>{{ __('Line Items') }}</h3>
                <div class="ed-items" id="edItems"></div>
                <button type="button" class="chip w-100 ed-add" id="edAddBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Line') }}</button>
            </div>

            {{-- Adjustments --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-sliders"></i>{{ __('Adjustments') }}</h3>
                <div class="ed-field">
                    <label>{{ __('Discount') }} (%)</label>
                    <input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm" id="edDiscount">
                </div>
            </div>

            {{-- Notes --}}
            <div class="doc-card">
                <h3 class="doc-card-title"><i class="bi bi-sticky"></i>{{ __('Notes') }}</h3>
                <textarea class="form-control form-control-sm" rows="3" data-bind=".pv-notes">{{ __('Thank you for your business. Payment is due within 15 days of the issue date.') }}</textarea>
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
                            @if (!empty($company['cr']))<div>{{ __('C.R. No.') }}: {{ $company['cr'] }}</div>@endif
                        </div>
                    </div>
                    <div class="dp-title-block">
                        <h1 class="dp-doctitle">{{ __('Invoice') }}</h1>
                        <div class="dp-doc-no js-number">{{ $invoice['number'] }}</div>
                        <span class="dp-status-pill">{{ __($invoice['status']) }}</span>
                    </div>
                </div>

                <div class="dp-rule"></div>

                {{-- Bill to + meta --}}
                <div class="dp-meta">
                    <div class="dp-billto">
                        <span class="dp-meta-label">{{ __('Bill To') }}</span>
                        <strong class="pv-cust-name">{{ $customer['name'] }}</strong>
                        <div class="pv-cust-address">{{ $customer['address'] }}</div>
                        <div>{{ $customer['country'] }}</div>
                        <div>{{ __('Attn') }}: <span class="pv-cust-contact">{{ $customer['contact'] }}</span></div>
                    </div>
                    <div class="dp-info">
                        <div class="dp-info-row"><span>{{ __('Invoice Number') }}</span><span class="js-number">{{ $invoice['number'] }}</span></div>
                        <div class="dp-info-row"><span>{{ __('Issue Date') }}</span><span class="pv-issue">{{ $invoice['date'] }}</span></div>
                        <div class="dp-info-row"><span>{{ __('Due Date') }}</span><span class="pv-due">{{ $invoice['due'] }}</span></div>
                    </div>
                </div>

                {{-- Line items --}}
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

                {{-- Totals --}}
                <div class="dp-bottom">
                    <div class="dp-totals" id="pvTotals"></div>
                </div>

                {{-- Footer --}}
                <div class="dp-foot">
                    <div class="dp-foot-block">
                        <span class="dp-meta-label">{{ __('Payment Details') }}</span>
                        <div>{{ __('Payment Method') }}: {{ __('Bank Transfer') }}</div>
                        @if (!empty($company['cr']))<div>{{ __('C.R. No.') }}: {{ $company['cr'] }}</div>@endif
                        <div>{{ $company['phone'] }}</div>
                    </div>
                    <div class="dp-foot-block">
                        <span class="dp-meta-label">{{ __('Notes') }}</span>
                        <div class="pv-notes">{{ __('Thank you for your business. Payment is due within 15 days of the issue date.') }}</div>
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
    window.invShow = {
        items: @json($items),
        currency: @json($invoice['currency']),
        vatRate: {{ $invoice['vatRate'] }},
        discount: {{ $invoice['discount'] }},
        i18n: {
            required: @json(__('Required')),
            delete: @json(__('Delete')),
            qty: @json(__('Quantity')),
            price: @json(__('Price')),
            subtotal: @json(__('Subtotal')),
            discount: @json(__('Discount')),
            vat: @json(__('Total VAT')),
            total: @json(__('Total')),
            paid: @json(__('Paid')),
            due: @json(__('Amount Due')),
        },
    };

    (function () {
        const cfg = window.invShow;
        const t = cfg.i18n;
        const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const pctFmt = (n) => (Math.round(n * 100) / 100).toString();
        const initItems = cfg.items.map((i) => ({ desc: i.desc || '', qty: +i.qty || 0, price: +i.price || 0 }));
        const initSubtotal = initItems.reduce((s, it) => s + it.qty * it.price, 0);
        const state = {
            items: initItems,
            currency: 'ر.ع',
            // discount is stored as a percentage; derived from the saved amount on load
            discountPct: initSubtotal > 0 ? ((+cfg.discount || 0) / initSubtotal * 100) : 0,
        };

        const edItems = document.getElementById('edItems');
        const pvItems = document.getElementById('pvItems');
        const pvTotals = document.getElementById('pvTotals');
        const edDue = document.getElementById('edDueAmount');

        /* ---- Editor rows (rebuilt only on add / delete) ---- */
        function renderEditor() {
            edItems.innerHTML = state.items.map((it, idx) => `
                <div class="ed-item" data-idx="${idx}">
                    <button type="button" class="ed-del" title="${t.delete}"><i class="bi bi-x-lg"></i></button>
                    <input type="text" class="form-control form-control-sm ed-desc" placeholder="${t.required}" value="${escapeHtml(it.desc)}">
                    <div class="ed-grid-2">
                        <label class="ed-mini"><small>${t.qty}</small>
                            <input type="number" min="0" class="form-control form-control-sm ed-qty" value="${it.qty}">
                        </label>
                        <label class="ed-mini"><small>${t.price}</small>
                            <input type="number" min="0" class="form-control form-control-sm ed-price" value="${it.price}">
                        </label>
                    </div>
                </div>`).join('');
        }

        /* ---- Preview rows + totals (rebuilt on every change) ---- */
        function renderPreview() {
            pvItems.innerHTML = state.items.map((it, idx) => `
                <tr>
                    <td class="dp-c-num">${idx + 1}</td>
                    <td>${escapeHtml(it.desc) || '<span class="dp-empty">—</span>'}</td>
                    <td class="dp-c-qty">${it.qty}</td>
                    <td class="dp-c-amt">${fmt(it.price)}</td>
                    <td class="dp-c-amt">${fmt(it.qty * it.price)}</td>
                </tr>`).join('');

            const subtotal = state.items.reduce((s, it) => s + it.qty * it.price, 0);
            const discount = subtotal * state.discountPct / 100;
            const total = Math.max(0, subtotal - discount);
            const cur = state.currency;

            pvTotals.innerHTML = `
                <div class="dp-tot-row"><span>${t.subtotal}</span><span>${fmt(subtotal)} ${cur}</span></div>
                ${state.discountPct > 0 ? `<div class="dp-tot-row"><span>${t.discount} (${pctFmt(state.discountPct)}%)</span><span>− ${fmt(discount)} ${cur}</span></div>` : ''}
                <div class="dp-tot-row grand"><span>${t.total}</span><span>${fmt(total)} ${cur}</span></div>
                <div class="dp-tot-row"><span>${t.paid}</span><span>${fmt(0)} ${cur}</span></div>
                <div class="dp-tot-row due"><span>${t.due}</span><span>${fmt(total)} ${cur}</span></div>`;

            edDue.textContent = fmt(total) + ' ' + cur;
        }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        /* ---- Read editor inputs back into state (no editor re-render) ---- */
        function syncFromEditor() {
            edItems.querySelectorAll('.ed-item').forEach((row, idx) => {
                if (!state.items[idx]) return;
                state.items[idx].desc = row.querySelector('.ed-desc').value;
                state.items[idx].qty = parseFloat(row.querySelector('.ed-qty').value) || 0;
                state.items[idx].price = parseFloat(row.querySelector('.ed-price').value) || 0;
            });
        }

        edItems.addEventListener('input', () => { syncFromEditor(); renderPreview(); });
        edItems.addEventListener('click', (e) => {
            const del = e.target.closest('.ed-del');
            if (!del) return;
            const idx = +del.closest('.ed-item').dataset.idx;
            state.items.splice(idx, 1);
            if (!state.items.length) state.items.push({ desc: '', qty: 1, price: 0 });
            renderEditor(); renderPreview();
        });
        document.getElementById('edAddBtn').addEventListener('click', () => {
            syncFromEditor();
            state.items.push({ desc: '', qty: 1, price: 0 });
            renderEditor(); renderPreview();
        });

        /* ---- Adjustments (discount as %) ---- */
        const edDiscount = document.getElementById('edDiscount');
        edDiscount.value = state.discountPct ? pctFmt(state.discountPct) : '';
        edDiscount.addEventListener('input', (e) => { state.discountPct = Math.min(100, Math.max(0, parseFloat(e.target.value) || 0)); renderPreview(); });

        /* ---- Direct text bindings (customer / meta / notes) ---- */
        document.querySelectorAll('[data-bind]').forEach((inp) => {
            inp.addEventListener('input', () => {
                document.querySelectorAll(inp.dataset.bind).forEach((el) => { el.textContent = inp.value; });
            });
        });

        renderEditor();
        renderPreview();
    })();
</script>
@endpush
