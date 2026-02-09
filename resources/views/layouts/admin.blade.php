<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - Control Panel</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="_token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/favicons/manifest.json">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
    <link rel="shortcut icon" href="/favicons/favicon.ico">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml">
    <meta name="theme-color" content="#0e4688">

    @include('layouts.scripts')

    @section('scripts')
        {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
        {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
        {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
        {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
        {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
        {!! Theme::css('vendor/revicons/solid.min.css?t={cache-version}') !!}
        {!! Theme::css('css/pterodactyl.css?t={cache-version}') !!}
        {!! Theme::css('css/reviactyl.css?t={cache-version}') !!}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu+Mono&display=swap" rel="stylesheet">
        <!--[if lt IE 9]>
                    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
                    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
                    <![endif]-->
    @show
</head>

<body class="hold-transition skin-blue fixed sidebar-mini">
    <div class="wrapper">
        <header class="main-header">
            <a href="{{ route('index') }}" class="logo">
                <span>Control Panel</span>
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="user-menu">
                            <a href="{{ route('account') }}">
                                <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160"
                                    class="user-image" alt="User Image">
                                <span class="hidden-xs">{{ Auth::user()->name_first }}
                                    {{ Auth::user()->name_last }}</span>
                            </a>
                        </li>
                        <li>
                        <li><a href="{{ route('index') }}" data-toggle="tooltip" data-placement="bottom"
                                title="Exit Admin Control"><i class="fa fa-server"></i></a></li>
                        </li>
                        <li>
                        <li><a href="{{ route('auth.logout') }}" id="logoutButton" data-toggle="tooltip"
                                data-placement="bottom" title="Logout"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.5em; height: 1.5em; vertical-align: middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg></a></li>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu">
                    <li class="header">@lang('admin/navigation.administration.title')</li>
                    <li class="{{ Route::currentRouteName() !== 'admin.index' ?: 'active' }}">
                        <a href="{{ route('admin.index') }}">
                            <i class="RiHome"></i> <span>@lang('admin/navigation.administration.dashboard')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.settings') ?: 'active' }}">
                        <a href="{{ route('admin.settings') }}">
                            <i class="RiWrench"></i> <span>@lang('admin/navigation.administration.settings')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.api') ?: 'active' }}">
                        <a href="{{ route('admin.api.index') }}">
                            <i class="RiCube"></i> <span>@lang('admin/navigation.administration.api')</span>
                        </a>
                    </li>
                    <li class="header">Reviactyl</li>
                    <li class="{{ Route::currentRouteName() !== 'admin.designify' ?: 'active' }}">
                        <a href="{{ route('admin.designify') }}">
                            <i class="RiPaintBrush"></i> <span>Designify</span>
                        </a>
                    </li>
                    <li class="header">@lang('admin/navigation.management.title')</li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.databases') ?: 'active' }}">
                        <a href="{{ route('admin.databases') }}">
                            <i class="RiCircleStack"></i> <span>@lang('admin/navigation.management.databases')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.locations') ?: 'active' }}">
                        <a href="{{ route('admin.locations') }}">
                            <i class="RiGlobeAlt"></i> <span>@lang('admin/navigation.management.locations')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.nodes') ?: 'active' }}">
                        <a href="{{ route('admin.nodes') }}">
                            <i class="RiRectangleGroup"></i> <span>@lang('admin/navigation.management.nodes')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.servers') ?: 'active' }}">
                        <a href="{{ route('admin.servers') }}">
                            <i class="RiServer"></i> <span>@lang('admin/navigation.management.servers')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.users') ?: 'active' }}">
                        <a href="{{ route('admin.users') }}">
                            <i class="RiUsers"></i> <span>@lang('admin/navigation.management.users')</span>
                        </a>
                    </li>
                    <li class="header">@lang('admin/navigation.service.title')</li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.mounts') ?: 'active' }}">
                        <a href="{{ route('admin.mounts') }}">
                            <i class="RiViewColumns"></i> <span>@lang('admin/navigation.service.mounts')</span>
                        </a>
                    </li>
                    <li class="{{ !starts_with(Route::currentRouteName(), 'admin.nests') ?: 'active' }}">
                        <a href="{{ route('admin.nests') }}">
                            <i class="RiLifebuoy"></i> <span>@lang('admin/navigation.service.nests')</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>
        <div class="content-wrapper">
            <section class="content-header">
                <h1 class="font-bold text-4xl">@yield('title')</h1>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                There was an error validating the data provided.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @foreach (Alert::getMessages() as $type => $messages)
                            @foreach ($messages as $message)
                                <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                    {{ $message }}
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                @yield('content')
            </section>
        </div>
        <footer class="main-footer">
            <div class="pull-right small text-gray" style="margin-right:10px;margin-top:-7px;">
                <strong><i class="fa fa-fw {{ $appIsGit ? 'fa-git-square' : 'fa-code-fork' }}"></i></strong>
                {{ $appVersion }}<br />
                <strong><i class="fa fa-fw fa-clock-o"></i></strong> {{ round(microtime(true) - LARAVEL_START, 3) }}s
            </div>
            Copyright &copy; {{ date('Y') }} <a href="https://reviactyl.dev/">Reviactyl</a>.
        </footer>
    </div>
    @section('footer-scripts')
        <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
        <script>
            keyboardeventKeyPolyfill.polyfill();
        </script>

        {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
        {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
        {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
        <script src="/js/autocomplete.js" type="application/javascript"></script>

        @if (Auth::user()->root_admin)
            <script>
                $('#logoutButton').on('click', function(event) {
                    event.preventDefault();

                    var that = this;
                    swal({
                        title: 'Do you want to log out?',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d9534f',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Log out'
                    }, function() {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('auth.logout') }}',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            complete: function() {
                                window.location.href = '{{ route('auth.login') }}';
                            }
                        });
                    });
                });
            </script>
        @endif

        <script>
            $(function() {
                $('[data-toggle="tooltip"]').tooltip();
            })
        </script>
    @show
</body>

</html>
