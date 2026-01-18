@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'advanced'])

@section('title')
    @lang('admin/settings.advanced.title')
@endsection

@section('content-header')
    <h1>Advanced Settings<small>Configure advanced settings for Pterodactyl.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Settings</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <form action="" method="POST">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">CAPTCHA</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">Provider</label>
                                <div>
                                    <select class="form-control" name="captcha:provider" id="captcha_provider">
                                        <option value="disable" @if (old('captcha:provider', config('captcha.provider')) == 'disable') selected @endif>Disabled</option>
                                        <option value="recaptcha" @if (old('captcha:provider', config('captcha.provider')) == 'recaptcha') selected @endif>Google reCAPTCHA</option>
                                        <option value="turnstile" @if (old('captcha:provider', config('captcha.provider')) == 'turnstile') selected @endif>Cloudflare Turnstile</option>
                                    </select>
                                </div>
                            </div>

                            {{-- reCAPTCHA Settings (inline) --}}
                            <div class="form-group col-md-4" id="recaptcha_site_key" style="display: none;">
                                <label class="control-label">Site Key</label>
                                <div>
                                    <input type="text" class="form-control" name="captcha:recaptcha:website_key"
                                        value="{{ old('captcha:recaptcha:website_key', config('captcha.recaptcha.website_key')) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-4" id="recaptcha_secret_key" style="display: none;">
                                <label class="control-label">Secret Key</label>
                                <div>
                                    <input type="text" class="form-control" name="captcha:recaptcha:secret_key"
                                        value="{{ old('captcha:recaptcha:secret_key', config('captcha.recaptcha.secret_key')) }}">
                                </div>
                            </div>

                            {{-- Turnstile Settings (inline) --}}
                            <div class="form-group col-md-4" id="turnstile_site_key" style="display: none;">
                                <label class="control-label">Site Key</label>
                                <div>
                                    <input type="text" class="form-control" name="captcha:turnstile:site_key"
                                        value="{{ old('captcha:turnstile:site_key', config('captcha.turnstile.site_key')) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-4" id="turnstile_secret_key" style="display: none;">
                                <label class="control-label">Secret Key</label>
                                <div>
                                    <input type="text" class="form-control" name="captcha:turnstile:secret_key"
                                        value="{{ old('captcha:turnstile:secret_key', config('captcha.turnstile.secret_key')) }}">
                                </div>
                            </div>
                        </div>
                        @if ($showRecaptchaWarning)
                            <div class="row" id="recaptcha_warning" style="display: none;">
                                <div class="col-xs-12">
                                    <div class="alert alert-warning no-margin">
                                        {!! __('admin/settings.advanced.recaptcha-alert', ['a' => '<a href="https://www.google.com/recaptcha/admin">','aclose' => '</a>',]) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="row" id="turnstile_info" style="display: none;">
                            <div class="col-xs-12">
                                <div class="alert alert-info no-margin">
                                    <i class="fa fa-info-circle"></i> Get your Turnstile keys from the <a href="https://dash.cloudflare.com/" target="_blank">Cloudflare Dashboard</a> → Turnstile.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings.advanced.http-label')</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.advanced.timeout-label')</label>
                                <div>
                                    <input type="number" required class="form-control"
                                        name="pterodactyl:guzzle:connect_timeout"
                                        value="{{ old('pterodactyl:guzzle:connect_timeout', config('pterodactyl.guzzle.connect_timeout')) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.advanced.request-label')</label>
                                <div>
                                    <input type="number" required class="form-control" name="pterodactyl:guzzle:timeout"
                                        value="{{ old('pterodactyl:guzzle:timeout', config('pterodactyl.guzzle.timeout')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings.advanced.creation-title')</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced.status-label')</label>
                                <div>
                                    <select class="form-control" name="pterodactyl:client_features:allocations:enabled">
                                        <option value="false">@lang('admin/settings.advanced.disabled')</option>
                                        <option value="true" @if (old('pterodactyl:client_features:allocations:enabled', config('pterodactyl.client_features.allocations.enabled'))) selected @endif>@lang('admin/settings.advanced.enabled')
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced.starting-label')</label>
                                <div>
                                    <input type="number" class="form-control"
                                        name="pterodactyl:client_features:allocations:range_start"
                                        value="{{ old('pterodactyl:client_features:allocations:range_start', config('pterodactyl.client_features.allocations.range_start')) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.advanced.ending-label')</label>
                                <div>
                                    <input type="number" class="form-control"
                                        name="pterodactyl:client_features:allocations:range_end"
                                        value="{{ old('pterodactyl:client_features:allocations:range_end', config('pterodactyl.client_features.allocations.range_end')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-footer">
                        {{ csrf_field() }}
                        <button type="submit" name="_method" value="PATCH"
                            class="btn btn-sm btn-primary pull-right">@lang('admin/settings.advanced.save-btn')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const providerSelect = document.getElementById('captcha_provider');
            const recaptchaSiteKey = document.getElementById('recaptcha_site_key');
            const recaptchaSecretKey = document.getElementById('recaptcha_secret_key');
            const turnstileSiteKey = document.getElementById('turnstile_site_key');
            const turnstileSecretKey = document.getElementById('turnstile_secret_key');
            const recaptchaWarning = document.getElementById('recaptcha_warning');
            const turnstileInfo = document.getElementById('turnstile_info');

            function updateVisibility() {
                const provider = providerSelect.value;
                const isRecaptcha = provider === 'recaptcha';
                const isTurnstile = provider === 'turnstile';
                
                recaptchaSiteKey.style.display = isRecaptcha ? 'block' : 'none';
                recaptchaSecretKey.style.display = isRecaptcha ? 'block' : 'none';
                turnstileSiteKey.style.display = isTurnstile ? 'block' : 'none';
                turnstileSecretKey.style.display = isTurnstile ? 'block' : 'none';
                
                if (recaptchaWarning) {
                    recaptchaWarning.style.display = isRecaptcha ? 'flex' : 'none';
                }
                if (turnstileInfo) {
                    turnstileInfo.style.display = isTurnstile ? 'flex' : 'none';
                }
            }

            providerSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        });
    </script>
@endsection
