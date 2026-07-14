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
        <input type="hidden" name="vat_rate" value="{{ $contract['vatRate'] }}">

        <div class="inv-head">
            <h2 class="inv-title">{{ __('Service Contract') }}</h2>
            <span class="section-link"><i class="bi bi-hash me-1"></i>{{ $contract['number'] }}</span>
        </div>

        <div class="row g-4 inv-top">
            <div class="col-lg-7 order-2 order-lg-1">
                <div class="inv-field">
                    <label>{{ __('Client') }} <span class="req">*</span></label>
                    <select name="client_id" class="form-select">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c['id'] }}" @selected($contract['clientId'] == $c['id'])>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inv-field">
                    <label>{{ __('Contract Number') }} <span class="req">*</span></label>
                    <input type="text" name="number" class="form-control" value="{{ old('number', $contract['number']) }}">
                </div>
                <div class="inv-field">
                    <label>{{ __('Contract Type') }} <span class="req">*</span></label>
                    <select name="type" class="form-select">
                        @foreach (['عقد خدمات', 'عقد رعاية', 'عقد تأجير جناح', 'عقد توريد'] as $type)
                            <option value="{{ $type }}" @selected($contract['type'] === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inv-field">
                    <label>{{ __('Exhibition') }}</label>
                    <select name="exhibition_id" class="form-select">
                        <option value="">{{ __('Optional') }}</option>
                        @foreach ($exhibitions as $e)
                            <option value="{{ $e['id'] }}" @selected($contract['exhibitionId'] == $e['id'])>{{ $e['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inv-field">
                    <label>{{ __('Currency') }} <span class="req">*</span></label>
                    <select name="currency" class="form-select">
                        <option value="ر.س" @selected($contract['currency'] === 'ر.س')>SAR ر.س</option>
                        <option value="$" @selected($contract['currency'] === '$')>USD $</option>
                    </select>
                </div>
                <div class="inv-field">
                    <label>{{ __('Start Date') }} <span class="req">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $contract['start']) }}">
                </div>
                <div class="inv-field">
                    <label>{{ __('End Date') }} <span class="req">*</span></label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $contract['end']) }}">
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

        {{-- Contract items --}}
        <div class="inv-items">
            <div class="inv-items-bar"><i class="bi bi-list-task"></i>{{ __('Contract Items') }}</div>
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

        {{-- Contract value totals --}}
        <div class="row inv-bottom g-4">
            <div class="col-lg-6">
                <div class="inv-totals">
                    <div class="tot-row"><span>{{ __('Subtotal') }}</span><span><span id="sumSubtotal">0.00</span> {{ __('SAR') }}</span></div>
                    <div class="tot-row"><span>{{ __('Total VAT') }}</span><span><span id="sumVat">0.00</span> {{ __('SAR') }}</span></div>
                    <div class="tot-row grand"><span>{{ __('Contract Value') }}</span><span><span id="sumTotal">0.00</span> {{ __('SAR') }}</span></div>
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
                        <td class="inv-linetotal"><span id="schedAmtTotal">0.00</span> {{ __('SAR') }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="chip inv-addline" id="addSchedBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Installment') }}</button>
        </div>

        <hr class="inv-hr">

        {{-- Terms & conditions --}}
        <div class="inv-section">
            <h3 class="inv-sec-title">{{ __('Terms & Conditions') }}</h3>
            <ol class="terms-list" id="termsList"></ol>
            <button type="button" class="chip" id="addTermBtn"><i class="bi bi-plus-lg"></i>{{ __('Add Clause') }}</button>
        </div>

        {{-- Notes --}}
        <div class="inv-section">
            <label class="form-label">{{ __('Notes & transaction notes') }}</label>
            <textarea name="notes" class="form-control w-100" rows="2" placeholder="{{ __('Optional') }}">{{ old('notes', $contract['notes']) }}</textarea>
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
@endsection

@push('scripts')
<script>
    window.ctI18n = {
        required: @json(__('Required')),
        delete: @json(__('Delete')),
        currency: @json(__('SAR')),
        installment: @json(__('Installment')),
    };
    (function () {
        const t = window.ctI18n;
        const VAT = (parseFloat(document.querySelector('input[name="vat_rate"]').value) || 15) / 100;
        const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const esc = (s) => (s == null ? '' : String(s).replace(/"/g, '&quot;'));

        /* ---- Contract items ---- */
        const items = document.getElementById('lineItems');
        let liIndex = 0;
        function itemRow(d, idx) {
            d = d || {};
            return `<tr class="li-row">
                <td class="li-grip"><i class="bi bi-grip-vertical"></i></td>
                <td><input type="text" name="items[${idx}][description]" class="form-control li-desc" value="${esc(d.desc)}" placeholder="${t.required}"></td>
                <td><input type="text" name="items[${idx}][qty]" class="form-control li-qty" value="${d.qty != null ? d.qty : 1}"></td>
                <td><input type="text" name="items[${idx}][price]" class="form-control li-price" value="${d.price != null && d.price !== '' ? d.price : ''}" placeholder="${t.required}"></td>
                <td class="inv-linetotal"><span class="li-total">0.00</span> ${t.currency}</td>
                <td class="li-actions"><button type="button" class="li-del" title="${t.delete}"><i class="bi bi-trash3"></i></button></td>
            </tr>`;
        }
        function addItem(d) { items.insertAdjacentHTML('beforeend', itemRow(d, liIndex++)); recalc(); }

        let contractTotal = 0;
        function recalc() {
            let subtotal = 0;
            items.querySelectorAll('.li-row').forEach((r) => {
                const qty = parseFloat(r.querySelector('.li-qty').value) || 0;
                const price = parseFloat(r.querySelector('.li-price').value) || 0;
                const amt = qty * price;
                subtotal += amt;
                r.querySelector('.li-total').textContent = fmt(amt * (1 + VAT));
            });
            const vat = subtotal * VAT;
            contractTotal = subtotal + vat;
            document.getElementById('sumSubtotal').textContent = fmt(subtotal);
            document.getElementById('sumVat').textContent = fmt(vat);
            document.getElementById('sumTotal').textContent = fmt(contractTotal);
            recalcSchedule();
        }
        items.addEventListener('input', (e) => { if (e.target.matches('.li-qty, .li-price')) recalc(); });
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

        const seedTerms = @json($terms);
        (seedTerms.length ? seedTerms : ['']).forEach(addTerm);
    })();
</script>
@endpush
