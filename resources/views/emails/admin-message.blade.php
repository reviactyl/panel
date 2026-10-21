<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
    <style>
        body { margin: 0; padding: 24px; color: #202124; background: #ffffff; font: 16px/1.6 Arial, sans-serif; overflow-wrap: anywhere; }
        main { max-width: 640px; margin: 0 auto; }
        img { max-width: 100%; height: auto; }
        table { border-collapse: collapse; max-width: 100%; }
        th, td { border: 1px solid #cccccc; padding: 8px; }
        blockquote { margin-left: 16px; color: #555555; }
        a { color: #155eef; }
    </style>
</head>
<body>
    <main>
        <h1>{{ config('app.name') }}</h1>
        {!! $content !!}
    </main>
</body>
</html>
