@extends('layouts.install')

@section('content')
<main class="relative isolate min-h-screen overflow-hidden flex items-center justify-center">
    <div class="absolute inset-0 -z-10 opacity-30 [background-image:linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] [background-size:24px_24px]"></div>

    <section class="w-full max-w-2xl rounded-xl border border-white/10 bg-zinc-900 p-5 sm:p-8">
        @livewire('installer')
    </section>
</main>
@endsection
