@extends('layouts.app')
@section('title', __('Contacts'))

@php
    $dir = $directories[$active];
    $contactType = ['entities' => 'entity', 'clients' => 'client', 'organizers' => 'organizer', 'suppliers' => 'supplier'][$active];
    $singular = ['entities' => 'Entity', 'clients' => 'Client', 'organizers' => 'Organizer', 'suppliers' => 'Supplier'][$active];
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Contacts') }}</h1>
            <p class="subtitle">{{ __('Entities, clients, organizers and suppliers') }}</p>
        </div>
        <button class="btn-brand" id="addContactBtn"><i class="bi bi-plus-lg"></i>{{ __($dir['add']) }}</button>
    </div>

    <div class="toolbar full-bleed sheet-aligned" style="padding-block:0;">
        <div class="tabs">
            @foreach ($types as $key => $type)
                <a href="{{ route('contacts', ['type' => $key]) }}" class="tab {{ $active === $key ? 'active' : '' }}">
                    <i class="bi {{ $type['icon'] }}"></i>{{ __($type['label']) }}
                </a>
            @endforeach
        </div>
        <label class="search-input"><i class="bi bi-search"></i><input type="text" placeholder="{{ __('Search...') }}"></label>
    </div>

    <div class="full-bleed">
        <div class="sheet-frame">
            <div class="table-wrap">
                <table class="data sheet">
                    <thead>
                    <tr>
                        <th style="width:46px">#</th>
                        @foreach ($dir['columns'] as $col)
                            <th>{{ __($col) }}</th>
                        @endforeach
                        <th>{{ __('Status') }}</th>
                        <th style="width:100px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($dir['rows'] as $row)
                        <tr data-contact="{{ json_encode($row, JSON_UNESCAPED_UNICODE) }}">
                            <td class="cell-muted">{{ $loop->iteration }}</td>
                            <td class="cell-strong">{{ $row['name'] }}</td>
                            @if ($active === 'entities')
                                <td class="cell-muted" dir="ltr">{{ $row['phone'] }}</td>
                                <td class="cell-muted" dir="ltr">{{ $row['email'] }}</td>
                                <td class="cell-muted">{{ $row['rep'] }}</td>
                                <td class="cell-muted"><i class="bi bi-people me-1"></i>{{ $row['persons'] }}</td>
                            @elseif ($active === 'clients')
                                <td class="cell-muted" dir="ltr">{{ $row['phone'] }}</td>
                                <td class="cell-muted">{{ $row['entity'] }}</td>
                                <td class="cell-muted" dir="ltr">{{ $row['email'] }}</td>
                                <td class="cell-muted">{{ $row['bookings'] }}</td>
                            @elseif ($active === 'organizers')
                                <td class="cell-muted" dir="ltr">{{ $row['phone'] }}</td>
                                <td class="cell-muted" dir="ltr">{{ $row['email'] }}</td>
                                <td class="cell-muted">{{ $row['events'] }}</td>
                            @else
                                <td class="cell-muted" dir="ltr">{{ $row['phone'] }}</td>
                                <td class="cell-muted" dir="ltr">{{ $row['email'] }}</td>
                                <td class="cell-muted">{{ $row['category'] }}</td>
                            @endif
                            <td>@include('partials.status', ['status' => $row['status']])</td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="edit-contact" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('contacts.destroy', $row['id']) }}" onsubmit="return confirm('{{ __('Delete this record?') }}')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($dir['columns']) + 3 }}">
                            <div class="empty-state"><i class="bi bi-inbox"></i>{{ __('No records yet. Click “:add” to create one.', ['add' => __($dir['add'])]) }}</div>
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add / Edit contact modal --}}
    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:1px solid var(--line); border-radius:16px;">
                <form method="POST" id="contactForm" action="{{ route('contacts.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="contactMethod" value="POST">
                    <input type="hidden" name="type" value="{{ $contactType }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactModalTitle" style="font-size:16px; font-weight:700;">{{ __($dir['add']) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }} <span class="req">*</span></label>
                            <input type="text" name="name" id="cName" class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Phone') }}</label>
                                <input type="text" name="phone" id="cPhone" class="form-control" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" name="email" id="cEmail" class="form-control" dir="ltr">
                            </div>
                        </div>

                        @if ($active === 'entities')
                            <div class="row g-3 mt-0">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Representative') }}</label>
                                    <input type="text" name="representative" id="cRep" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Persons') }}</label>
                                    <input type="number" name="persons" id="cPersons" class="form-control" min="0">
                                </div>
                            </div>
                        @elseif ($active === 'clients')
                            <div class="row g-3 mt-0">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Entity') }}</label>
                                    <select name="entity_id" id="cEntity" class="form-select">
                                        <option value="">{{ __('None') }}</option>
                                        @foreach ($entities as $eid => $ename)
                                            <option value="{{ $eid }}">{{ $ename }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Representative') }}</label>
                                    <input type="text" name="representative" id="cRep" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('VAT Reg. No.') }}</label>
                                    <input type="text" name="vat_no" id="cVat" class="form-control" dir="ltr">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <input type="text" name="address" id="cAddress" class="form-control">
                                </div>
                            </div>
                        @elseif ($active === 'organizers')
                            <div class="mt-3">
                                <label class="form-label">{{ __('Events') }}</label>
                                <input type="number" name="events" id="cEvents" class="form-control" min="0">
                            </div>
                        @else
                            <div class="mt-3">
                                <label class="form-label">{{ __('Category') }}</label>
                                <input type="text" name="category" id="cCategory" class="form-control">
                            </div>
                        @endif

                        <div class="mt-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="cStatus" class="form-select">
                                @foreach ($contactStatuses as $s)
                                    <option value="{{ $s }}">{{ __($s) }}</option>
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
@endsection

@push('scripts')
<script>
    (function () {
        const modalEl = document.getElementById('contactModal');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('contactForm');
        const storeAction = @json(route('contacts.store'));
        const updateBase = @json(url('contacts'));
        const addTitle = @json(__($dir['add']));
        const editTitle = @json(__('Edit '.$singular));

        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = (v ?? ''); };

        function openCreate() {
            form.reset();
            form.action = storeAction;
            document.getElementById('contactMethod').value = 'POST';
            document.getElementById('contactModalTitle').textContent = addTitle;
            modal.show();
        }

        function openEdit(data) {
            form.reset();
            form.action = updateBase + '/' + data.id;
            document.getElementById('contactMethod').value = 'PUT';
            document.getElementById('contactModalTitle').textContent = editTitle;
            setVal('cName', data.name);
            setVal('cPhone', data.phone);
            setVal('cEmail', data.email);
            setVal('cRep', data.rep);
            setVal('cVat', data.vat_no);
            setVal('cAddress', data.address);
            setVal('cEntity', data.entity_id);
            setVal('cPersons', data.persons);
            setVal('cEvents', data.events);
            setVal('cCategory', data.category);
            setVal('cStatus', data.status);
            modal.show();
        }

        document.getElementById('addContactBtn').addEventListener('click', openCreate);
        document.querySelectorAll('.edit-contact').forEach((btn) => {
            btn.addEventListener('click', () => {
                const tr = btn.closest('tr');
                openEdit(JSON.parse(tr.dataset.contact));
            });
        });
    })();
</script>
@endpush
