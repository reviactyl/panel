@extends('templates/wrapper', [
    'css' => [
        'body' => 'bg-gray-950',
        'style' => 'background-image: var(--background); background-size: cover; background-position: center; background-attachment: fixed;',
    ],
])

@section('container')
    <div id="modal-portal"></div>
    <div id="app"></div>
@endsection

@section('scripts')
    @parent
    <script>
        window.SocialLoginConfiguration = {
            google: {{ config('panel.auth.google_enabled') ? 'true' : 'false' }},
            discord: {{ config('panel.auth.discord_enabled') ? 'true' : 'false' }},
            github: {{ config('panel.auth.github_enabled') ? 'true' : 'false' }},
        };
    </script>
@endsection
