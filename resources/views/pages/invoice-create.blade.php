@extends('layouts.app')
@section('title', $isEdit ? __('Edit Invoice') : __('Create Invoice'))

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
        <input type="hidden" name="vat_rate" value="{{ $invoice['vatRate'] }}">

        <div class="inv-head">
            <h2 class="inv-title">{{ __('Tax Invoice') }}</h2>
            <span class="section-link"><i class="bi bi-hash me-1"></i>{{ $invoice['number'] }}</span>
        </div>

        <div class="row g-4 inv-top">
            <div class="col-lg-7 order-2 order-lg-1">
                <div class="inv-field">
                    <label>{{ __('Customer') }} <span class="req">*</span></label>
                    <select name="client_id" class="form-select">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c['id'] }}" @selected($invoice['clientId'] == $c['id'])>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inv-field">
                    <label>{{ __('Invoice Number') }} <span class="req">*</span></label>
                    <input type="text" name="number" class="form-control" value="{{ old('number', $invoice['number']) }}">
                </div>
                <div class="inv-field">
                    <label>{{ __('Currency') }} <span class="req">*</span></label>
                    <select name="currency" class="form-select">
                        <option value="ر.س" @selected($invoice['currency'] === 'ر.س')>SAR ر.س</option>
                        <option value="$" @selected($invoice['currency'] === '$')>USD $</option>
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
                <div class="inv-field">
                    <label>{{ __('Purchase Order') }}</label>
                    <input type="text" name="po" class="form-control" value="{{ old('po', $invoice['po']) }}" placeholder="{{ __('Optional') }}">
                </div>
                <div class="inv-field">
                    <label>{{ __('Exhibition') }}</label>
                    <select name="exhibition_id" class="form-select">
                        <option value="">{{ __('Optional') }}</option>
                        @foreach ($exhibitions as $e)
                            <option value="{{ $e['id'] }}" @selected($invoice['exhibitionId'] == $e['id'])>{{ $e['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-5 order-1 order-lg-2">
                <div class="inv-company">
                    <span class="inv-logo">{{ $company['logo'] }}</span>
                    <div class="inv-company-info">
                        <strong>{{ $company['name'] }}</strong>
                        <div class="cell-muted">{{ $company['address'] }}</div>
                        <div class="cell-muted">{{ $company['country'] }}</div>
                        <div class="cell-muted">{{ __('VAT Reg. No.') }}: {{ $company['vat'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Line items --}}
        <div class="inv-items">
            <div class="inv-items-bar"><i class="bi bi-chevron-down"></i>{{ __('Prices exclusive of tax') }}</div>
            <div class="table-wrap">
                <table class="lineitems">
                    <thead>
                    <tr>
                        <th style="width:28px"></th>
                        <th>{{ __('Description') }} <span class="req">*</span></th>
                        <th style="width:90px">{{ __('Quantity') }} <span class="req">*</span></th>
                        <th style="width:160px">{{ __('Price') }} <span class="req">*</span></th>
                        <th style="width:140px">{{ __('Line Total') }}</th>
                        <th style="width:44px"></th>
                    </tr>
                    </thead>
                    <tbody id="lineItems"></tbody>
                </table>
            </div>
            <button type="button" class="chip inv-addline" id="addLineBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Line') }}</button>
        </div>

        {{-- Totals --}}
        <div class="row inv-bottom g-4">
            <div class="col-lg-6 order-2 order-lg-1">
                <a href="#" class="inv-link d-inline-block mb-3 {{ $invoice['discount'] > 0 ? 'is-hidden' : '' }}" id="addDiscountBtn">+ {{ __('Add Total Discount') }}</a>
                <div class="inv-totals">
                    <div class="tot-row {{ $invoice['discount'] > 0 ? '' : 'is-hidden' }}" id="discountRow">
                        <span>{{ __('Discount') }} <button type="button" class="li-del" id="removeDiscountBtn" title="{{ __('Delete') }}"><i class="bi bi-x-lg"></i></button></span>
                        <span><input type="number" min="0" name="discount" id="discountInput" class="form-control" style="width:130px; text-align:start;" value="{{ old('discount', $invoice['discount']) }}"> {{ __('SAR') }}</span>
                    </div>
                    <div class="tot-row"><span>{{ __('Subtotal') }}</span><span><span id="sumSubtotal">0.00</span> {{ __('SAR') }}</span></div>
                    <div class="tot-row"><span>{{ __('Total VAT') }}</span><span><span id="sumVat">0.00</span> {{ __('SAR') }}</span></div>
                    <div class="tot-row grand"><span>{{ __('Total') }}</span><span><span id="sumTotal">0.00</span> {{ __('SAR') }}</span></div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2">
                <div class="inv-qr">
                    <div class="qr-box"><i class="bi bi-qr-code"></i></div>
                    <p class="cell-muted">{{ __('This QR code is encoded as per ZATCA e-invoicing requirements') }}</p>
                </div>
            </div>
        </div>

        <hr class="inv-hr">

        {{-- Notes --}}
        <div class="inv-section">
            <label class="form-label">{{ __('Notes & transaction notes') }}</label>
            <textarea name="notes" class="form-control w-100" rows="2">{{ old('notes', $invoice['notes']) }}</textarea>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    window.invI18n = {
        required: @json(__('Required')),
        vat: @json(__('VAT on Sales')),
        delete: @json(__('Delete')),
        account: '402 مبيعات',
        currency: @json(__('SAR')),
    };
    (function () {
        const t = window.invI18n;
        const tbody = document.getElementById('lineItems');
        const VAT = (parseFloat(document.querySelector('input[name="vat_rate"]').value) || 15) / 100;
        const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const esc = (s) => (s == null ? '' : String(s).replace(/"/g, '&quot;'));
        let liIndex = 0;

        function rowHTML(d, idx) {
            d = d || {};
            return `<tr class="li-row">
                <td class="li-grip"><i class="bi bi-grip-vertical"></i></td>
                <td><input type="text" name="items[${idx}][description]" class="form-control li-desc" value="${esc(d.desc)}" placeholder="${t.required}"></td>
                <td><input type="text" name="items[${idx}][qty]" class="form-control li-qty" value="${d.qty != null ? d.qty : 1}"></td>
                <td><input type="text" name="items[${idx}][price]" class="form-control li-price" value="${d.price != null && d.price !== '' ? d.price : ''}" placeholder="${t.required}"><div class="inv-vat">${t.vat} (15.00%)</div></td>
                <td class="inv-linetotal"><span class="li-total">0.00</span> ${t.currency}</td>
                <td class="li-actions"><button type="button" class="li-del" title="${t.delete}"><i class="bi bi-trash3"></i></button></td>
            </tr>`;
        }

        function addLine(d) { tbody.insertAdjacentHTML('beforeend', rowHTML(d, liIndex++)); recalc(); }

        function recalc() {
            let subtotal = 0;
            tbody.querySelectorAll('.li-row').forEach((r) => {
                const qty = parseFloat(r.querySelector('.li-qty').value) || 0;
                const price = parseFloat(r.querySelector('.li-price').value) || 0;
                const amt = qty * price;
                subtotal += amt;
                r.querySelector('.li-total').textContent = fmt(amt * (1 + VAT));
            });
            const row = document.getElementById('discountRow');
            const di = document.getElementById('discountInput');
            const discount = (!row.classList.contains('is-hidden')) ? (parseFloat(di.value) || 0) : 0;
            const taxable = Math.max(0, subtotal - discount);
            const vat = taxable * VAT;
            document.getElementById('sumSubtotal').textContent = fmt(subtotal);
            document.getElementById('sumVat').textContent = fmt(vat);
            document.getElementById('sumTotal').textContent = fmt(taxable + vat);
        }

        // Recalculate on quantity / price edits
        tbody.addEventListener('input', (e) => { if (e.target.matches('.li-qty, .li-price')) recalc(); });

        // Delete a line (keep at least one)
        tbody.addEventListener('click', (e) => {
            const del = e.target.closest('.li-del');
            if (del) {
                e.preventDefault();
                if (tbody.querySelectorAll('.li-row').length > 1) { del.closest('.li-row').remove(); recalc(); }
            }
        });

        document.getElementById('addLineBtn').addEventListener('click', () => addLine());

        // Total discount show / hide
        const addDisc = document.getElementById('addDiscountBtn');
        const discRow = document.getElementById('discountRow');
        addDisc.addEventListener('click', (e) => { e.preventDefault(); discRow.classList.remove('is-hidden'); addDisc.classList.add('is-hidden'); document.getElementById('discountInput').focus(); recalc(); });
        document.getElementById('discountInput').addEventListener('input', recalc);
        document.getElementById('removeDiscountBtn').addEventListener('click', (e) => { e.preventDefault(); document.getElementById('discountInput').value = '0'; discRow.classList.add('is-hidden'); addDisc.classList.remove('is-hidden'); recalc(); });

        // Seed from server (existing items on edit, otherwise one empty row)
        const seed = @json($items);
        if (seed.length) { seed.forEach(addLine); } else { addLine(); }
    })();
</script>
@endpush
