@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'basic'])

@section('title')
    @lang('admin/settings.overview.title')
@endsection

@section('content-header')
    <h1>Panel Settings<small>Configure Pterodactyl to your liking.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Settings</li>
    </ol>
@endsection

@section('content')
    @php
        $settings = app(\App\Contracts\Repository\SettingsRepositoryInterface::class);
        $globalLocale = $settings->get('settings::app:locale', config('app.locale', 'en'));
    @endphp
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/settings.overview.general-title')</h3>
                </div>
                <form action="{{ route('admin.settings') }}" method="POST">
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">@lang('admin/settings.overview.app-name')<sup class="required">*</sup></label>
                                <div>
                                    <input type="text" class="form-control" name="app:name"
                                        value="{{ old('app:name', config('app.name')) }}" />
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">@lang('admin/settings.overview.app-logo')<sup class="required">*</sup></label>
                                <div>
                                    <input type="text" class="form-control" name="app:logo"
                                        value="{{ old('app:logo', config('app.logo')) }}" />
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">@lang('admin/settings.overview.app-icon')<sup class="required">*</sup></label>
                                <div>
                                    <input type="text" class="form-control" name="app:icon"
                                        value="{{ old('app:icon', config('app.icon')) }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.overview.default-language')</label>
                                <div>
                                    <select name="app:locale" class="form-control">
                                        @foreach($languages as $key => $value)
                                            <option value="{{ $key }}" @if($globalLocale === $key) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted"><small>The default language to use when rendering the Panel for users who have not selected a custom language.</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-2">
                                <label class="control-label">@lang('admin/settings.overview.debug-mode')</label>
                                <div>
                                <label class="toggle-switch">
                                    <input type="hidden" name="app:debug" value="false">
                                    <input type="checkbox" name="app:debug" value="true" @checked(old('app:debug', config('app.debug')) == true)>
                                    <span class="slider"></span>
                                </label>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">@lang('admin/settings.overview.2fa')</label>
                                <div>
                                    <div class="btn-group btn-group-sm" data-toggle="buttons">
                                        @php
                                            $level = old(
                                                'pterodactyl:auth:2fa_required',
                                                config('pterodactyl.auth.2fa_required'),
                                            );
                                        @endphp
                                        <label class="btn btn-primary @if ($level == 0) active @endif">
                                            <input type="radio" name="pterodactyl:auth:2fa_required" autocomplete="off"
                                                value="0" @if ($level == 0) checked @endif> @lang('admin/settings.overview.not-required')
                                        </label>
                                        <label class="btn btn-primary @if ($level == 1) active @endif">
                                            <input type="radio" name="pterodactyl:auth:2fa_required" autocomplete="off"
                                                value="1" @if ($level == 1) checked @endif> @lang('admin/settings.overview.admin-only')
                                        </label>
                                        <label class="btn btn-primary @if ($level == 2) active @endif">
                                            <input type="radio" name="pterodactyl:auth:2fa_required" autocomplete="off"
                                                value="2" @if ($level == 2) checked @endif> @lang('admin/settings.overview.all-users')
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Progressive Web App</label>
                                <div>
                                    <select class="form-control" name="app:pwa">
                                        <option value="true">@lang('admin/settings.overview.enabled')</option>
                                        <option value="false" @if (old('app:pwa', config('app.pwa')) == '0') selected @endif>@lang('admin/settings.overview.disabled')
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">@lang('admin/settings.overview.provider')</label>
                                <div>
                                    <select class="form-control" name="app:avatar">
                                        <option value="gravatar">Gravatar</option>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <button type="submit" name="_method" value="PATCH"
                            class="btn btn-sm btn-primary pull-right">@lang('admin/settings.overview.save-btn')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
