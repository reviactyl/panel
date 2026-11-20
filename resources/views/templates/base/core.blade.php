@extends('templates/wrapper', [
    'css' => ['body' => 'bg-gray-950'],
])

@section('container')
    <div id="modal-portal"></div>
    <div id="app">@include('templates.partials.boot-indicator')</div>
@endsection
