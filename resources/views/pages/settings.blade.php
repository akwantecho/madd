@extends('layouts.app')
@section('title', __('Settings'))

@php
    $active = request('tab', 'general');
    if (! in_array($active, ['general', 'users', 'finance'], true)) {
        $active = 'general';
    }
    $labels = ['general' => 'General Settings', 'users' => 'Roles & Users', 'finance' => 'Finance Settings'];
@endphp

@section('content')
    <div class="page-head head-bar full-bleed sheet-aligned">
        <div>
            <h1>{{ __('Settings') }}</h1>
            <p class="subtitle">{{ __($labels[$active]) }}</p>
        </div>
    </div>

    <div class="toolbar full-bleed sheet-aligned" style="padding-block:0;">
        <div class="tabs" style="border:0; margin:0;">
            <a href="{{ route('settings', ['tab' => 'general']) }}" class="tab {{ $active === 'general' ? 'active' : '' }}">
                <i class="bi bi-sliders"></i>{{ __('General Settings') }}
            </a>
            <a href="{{ route('settings', ['tab' => 'users']) }}" class="tab {{ $active === 'users' ? 'active' : '' }}">
                <i class="bi bi-people"></i>{{ __('Roles & Users') }}
            </a>
            <a href="{{ route('settings', ['tab' => 'finance']) }}" class="tab {{ $active === 'finance' ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i>{{ __('Finance Settings') }}
            </a>
        </div>
    </div>

    @if ($active === 'general')
        <div class="full-bleed sheet-aligned" style="padding-block:16px;">
            <div class="grid-2">
                <div class="card">
                    <div class="card-head"><h2>{{ __('General Settings') }}</h2></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.save') }}">
                            @csrf
                            <input type="hidden" name="tab" value="general">
                            <div class="mb-3">
                                <label class="form-label">{{ __('System Name') }}</label>
                                <input type="text" name="system_name" class="form-control w-100" value="{{ $settings['system_name'] }}">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Language') }}</label>
                                    <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="chip" style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;"><i class="bi bi-translate"></i>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</a>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Currency') }}</label>
                                    <select name="currency" class="form-select w-100">
                                        @foreach (['SAR — ر.س', 'USD — $', 'AED — د.إ'] as $cur)
                                            <option @selected($settings['currency'] === $cur)>{{ $cur }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Timezone') }}</label>
                                    <select name="timezone" class="form-select w-100">
                                        @foreach (['Asia/Riyadh', 'Asia/Dubai', 'UTC'] as $tz)
                                            <option @selected($settings['timezone'] === $tz)>{{ $tz }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-brand mt-4"><i class="bi bi-check2"></i>{{ __('Save Changes') }}</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>{{ __('Profile Settings') }}</h2></div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <span class="avatar" style="width:72px;height:72px;border-radius:20px;font-size:24px;margin-inline:auto;">SA</span>
                        </div>
                        <form method="POST" action="{{ route('settings.save') }}">
                            @csrf
                            <input type="hidden" name="tab" value="general">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Full Name') }}</label>
                                <input type="text" name="profile_name" class="form-control w-100" value="{{ $settings['profile_name'] }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Email') }}</label>
                                <input type="email" name="profile_email" class="form-control w-100" value="{{ $settings['profile_email'] }}" dir="ltr">
                            </div>
                            <button type="submit" class="btn-brand mt-2"><i class="bi bi-check2"></i>{{ __('Save Changes') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($active === 'users')
        <div class="toolbar full-bleed sheet-aligned">
            <strong>{{ __('Roles & Users') }}</strong>
            <button type="button" class="btn-brand" id="addUserBtn"><i class="bi bi-person-plus"></i>{{ __('Add User') }}</button>
        </div>
        <div class="full-bleed">
            <div class="sheet-frame">
                <div class="table-wrap">
                    <table class="data sheet">
                        <thead><tr>
                            <th style="width:46px">#</th>
                            <th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Role') }}</th>
                            <th>{{ __('Status') }}</th><th style="width:90px">{{ __('Actions') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($users as $u)
                            <tr>
                                <td class="cell-muted">{{ $loop->iteration }}</td>
                                <td class="cell-strong">{{ $u['name'] }}</td>
                                <td class="cell-muted" dir="ltr">{{ $u['email'] }}</td>
                                <td>
                                    <form method="POST" action="{{ route('users.update', $u['id']) }}" style="display:inline">
                                        @csrf @method('PUT')
                                        <select name="role" class="form-select form-select-sm ep-w-auto" onchange="this.form.submit()">
                                            @foreach ($roles as $role)
                                                <option @selected($role === $u['role'])>{{ __($role) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>@include('partials.status', ['status' => $u['status']])</td>
                                <td><div class="row-actions">
                                    <button type="button" class="edit-user" title="{{ __('Edit') }}"
                                        data-id="{{ $u['id'] }}" data-name="{{ $u['name'] }}" data-email="{{ $u['email'] }}" data-role="{{ $u['role'] }}" data-status="{{ $u['status'] }}"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="{{ route('users.destroy', $u['id']) }}" onsubmit="return confirm('{{ __('Delete this user?') }}')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="cell-muted" style="text-align:center; padding:2rem;">{{ __('No users yet') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Add / Edit user modal --}}
        <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="userForm" method="POST" action="{{ route('users.store') }}">
                        @csrf
                        <input type="hidden" name="_method" id="userMethod" value="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userModalTitle">{{ __('Add User') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Name') }} <span class="req">*</span></label>
                                <input type="text" name="name" id="uName" class="form-control w-100" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Email') }} <span class="req">*</span></label>
                                <input type="email" name="email" id="uEmail" class="form-control w-100" dir="ltr" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Password') }} <span class="req" id="uPassReq">*</span></label>
                                <input type="password" name="password" id="uPassword" class="form-control w-100" dir="ltr" autocomplete="new-password">
                                <small class="cell-muted" id="uPassHint" style="display:none">{{ __('Leave blank to keep the current password.') }}</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Role') }}</label>
                                    <select name="role" id="uRole" class="form-select w-100">
                                        @foreach ($roles as $role)<option value="{{ $role }}">{{ __($role) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <select name="status" id="uStatus" class="form-select w-100">
                                        <option value="Active">{{ __('Active') }}</option>
                                        <option value="Cancelled">{{ __('Cancelled') }}</option>
                                    </select>
                                </div>
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
    @endif

    @push('scripts')
    <script>
        (function () {
            const modalEl = document.getElementById('userModal');
            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('userForm');
            const STORE = @json(route('users.store'));
            const UPDATE = @json(url('users'));

            function openAdd() {
                form.action = STORE;
                document.getElementById('userMethod').value = 'POST';
                document.getElementById('userModalTitle').textContent = @json(__('Add User'));
                form.reset();
                document.getElementById('uRole').value = 'Operator';
                document.getElementById('uStatus').value = 'Active';
                document.getElementById('uPassword').required = true;
                document.getElementById('uPassReq').style.display = '';
                document.getElementById('uPassHint').style.display = 'none';
                modal.show();
            }

            function openEdit(btn) {
                const d = btn.dataset;
                form.action = UPDATE + '/' + d.id;
                document.getElementById('userMethod').value = 'PUT';
                document.getElementById('userModalTitle').textContent = @json(__('Edit User'));
                document.getElementById('uName').value = d.name || '';
                document.getElementById('uEmail').value = d.email || '';
                document.getElementById('uPassword').value = '';
                document.getElementById('uPassword').required = false;
                document.getElementById('uPassReq').style.display = 'none';
                document.getElementById('uPassHint').style.display = '';
                document.getElementById('uRole').value = d.role || 'Operator';
                document.getElementById('uStatus').value = d.status || 'Active';
                modal.show();
            }

            document.getElementById('addUserBtn')?.addEventListener('click', openAdd);
            document.querySelectorAll('.edit-user').forEach((b) => b.addEventListener('click', () => openEdit(b)));
        })();
    </script>
    @endpush

    @if ($active === 'finance')
        <div class="full-bleed sheet-aligned" style="padding-block:16px;">
            <div class="card">
                <div class="card-head"><h2>{{ __('Finance Settings') }}</h2></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.save') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="tab" value="finance">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Default Currency') }}</label>
                            <select name="default_currency" class="form-select w-100">
                                @foreach (['SAR — ر.س', 'USD — $', 'AED — د.إ'] as $cur)
                                    <option @selected($settings['default_currency'] === $cur)>{{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('VAT (%)') }}</label>
                            <input type="number" name="vat_rate" class="form-control w-100" value="{{ $settings['vat_rate'] }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Invoice Prefix') }}</label>
                            <input type="text" name="invoice_prefix" class="form-control w-100" value="{{ $settings['invoice_prefix'] }}" dir="ltr">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-brand mt-2"><i class="bi bi-check2"></i>{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
