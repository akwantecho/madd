@extends('layouts.app')
@section('title', __('Settings'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('Settings') }}</h1>
            <p class="subtitle">{{ __('General Settings') }}</p>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>{{ __('General Settings') }}</h2></div>
            <div class="card-body">
                <form onsubmit="return false">
                    <div class="mb-3">
                        <label class="form-label">{{ __('System Name') }}</label>
                        <input type="text" class="form-control" value="{{ config('app.name') }}">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Language') }}</label>
                            <select class="form-select">
                                <option @selected(app()->getLocale() === 'ar')>{{ __('Arabic') }}</option>
                                <option @selected(app()->getLocale() === 'en')>{{ __('English') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Currency') }}</label>
                            <select class="form-select">
                                <option>SAR — ر.س</option>
                                <option>USD — $</option>
                                <option>AED — د.إ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Timezone') }}</label>
                            <select class="form-select">
                                <option>Asia/Riyadh</option>
                                <option>Asia/Dubai</option>
                                <option>UTC</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn-brand mt-4"><i class="bi bi-check2"></i>{{ __('Save Changes') }}</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>{{ __('Profile Settings') }}</h2></div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <span class="avatar" style="width:72px;height:72px;border-radius:20px;font-size:24px;margin-inline:auto;">SA</span>
                </div>
                <form onsubmit="return false">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" class="form-control" value="Admin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control" value="admin@eventpuls.sa" dir="ltr">
                    </div>
                    <button class="btn-brand mt-2"><i class="bi bi-check2"></i>{{ __('Save Changes') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
