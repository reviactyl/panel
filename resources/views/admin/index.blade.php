@extends('layouts.admin')

@section('title')
    @lang('admin/index.title')
@endsection

@section('content-header')
    <h1>Administrative Overview<small>A quick glance at your system.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Index</li>
    </ol>
@endsection

@section('content')
        <div class="row">
            <div class="col-xs-12">
                <div class="alert alert-danger">
                    <b>Depreciation Warning!</b><br />
                    You are currently viewing the legacy panel. This control panel should only be used when something on the new admin panel isn't working.
                </div>
            </div>
        </div>
    <div class="row">
        <div class="col-xs-12">
            <div
                class="box
            @if ($version->isLatestPanel()) box-success
            @else
                box-danger @endif
        ">
                <div class="box-header with-border">
                    <h3 class="box-title">@if ($version->isLatestPanel()) @lang('admin/index.uptodate-header') @else @lang('admin/index.notuptodate-header') @endif</h3>
                </div>
                <div class="box-body">
                    @if ($version->isLatestPanel())
                        {!! __('admin/index.uptodate-body', ['version' => config('app.version')]) !!}
                    @else
                        {!! __('admin/index.notuptodate-body', ['version' => config('app.version'), 'latest' => $version->getPanel()]) !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6 col-sm-3 text-center">
            <a href="{{ $version->getDiscord() }}"><button class="btn btn-warning" style="width:100%;"><i
                        class="fa fa-fw fa-support"></i> @lang('admin/index.help-btn') <small>(via Discord)</small></button></a>
        </div>
        <div class="col-xs-6 col-sm-3 text-center">
            <a href="https://reviactyl.dev/docs"><button class="btn btn-primary" style="width:100%;"><i
                        class="fa fa-fw fa-link"></i> @lang('admin/index.docs-btn')</button></a>
        </div>
        <div class="clearfix visible-xs-block">&nbsp;</div>
        <div class="col-xs-6 col-sm-3 text-center">
            <a href="https://github.com/reviactyl/panel"><button class="btn btn-primary" style="width:100%;"><i
                        class="fa fa-fw fa-github"></i> Github</button></a>
        </div>
        <div class="col-xs-6 col-sm-3 text-center">
            <a href="{{ $version->getDonations() }}"><button class="btn btn-success" style="width:100%;"><i
                        class="fa fa-fw fa-money"></i> @lang('admin/index.sponsor-btn')</button></a>
        </div>
    </div>


    <div class="row" style="margin-top: 40px;">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/index.feedback-header')</h3>
                </div>
                <div class="box-body">
                    <p class="box-text">
                        @lang('admin/index.feedback-body')
                    </p>
                    <a href="https://github.com/reviactyl/panel/issues" class="btn btn-primary"><i class="fa fa-fw fa-github"></i> @lang('admin/index.feedback-btn')</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/index.sponsor-header')</h3>
                </div>
                <div class="box-body">
                    <p class="box-text">
                        @lang('admin/index.sponsor-body')
                    </p>
                    <a href="{{ $version->getDonations() }}" class="btn btn-danger"><i class="fa fa-fw fa-money"></i> @lang('admin/index.sponsor-btn')</a>
                </div>
            </div>
        </div>
    </div>
@endsection
