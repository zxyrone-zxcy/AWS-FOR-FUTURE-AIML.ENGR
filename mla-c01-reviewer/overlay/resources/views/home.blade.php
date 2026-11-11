@extends('layouts.app')

@section('title', 'AWS MLA-C01 Reviewer: free practice questions')

@section('content')
    {{-- 1. Hero: one message, one primary action --}}
    <section class="hero">
        <h1>Pass the AWS <span class="hl">Machine Learning Engineer</span> Associate exam</h1>
        <p class="lead">Practice MLA-C01 questions where every answer choice is explained, so you learn
            why the right answer is right <em>and</em> why the others aren't.</p>
        <div class="cta">
            <a class="btn primary lg" href="{{ route('quiz') }}">Start practicing</a>
            <a class="btn ghost lg" href="{{ route('quiz') }}?mode=quick">Try a Quick 10</a>
        </div>
        <ul class="facts" aria-label="At a glance">
            <li><b>{{ $total }}</b><span>questions</span></li>
            <li><b>{{ count($topics) }}</b><span>topics</span></li>
            <li><b>100%</b><span>answers explained</span></li>
        </ul>
    </section>

    {{-- 2. Returning learners: their own progress, only when it exists --}}
    <section id="progress" class="progress-card" hidden>
        <div class="progress-head">
            <h2>Welcome back</h2>
            <span class="muted">Saved on this device</span>
        </div>
        <div class="stats">
            <div><b data-stat="answered">0</b><span>answered</span></div>
            <div><b data-stat="accuracy">0%</b><span>accuracy</span></div>
            <div><b data-stat="missed">0</b><span>to review</span></div>
        </div>
        <a class="btn primary" href="{{ route('quiz') }}?mode=missed">Review my missed questions</a>
    </section>

    {{-- 3. How it works --}}
    <section class="block">
        <h2 class="block-title">How it works</h2>
        <ol class="steps">
            <li><span class="step-n">1</span><div><b>Pick a topic</b><p>Focus on one area or shuffle everything.</p></div></li>
            <li><span class="step-n">2</span><div><b>Answer</b><p>Choose an option, or use the A–D keys.</p></div></li>
            <li><span class="step-n">3</span><div><b>Learn why</b><p>Read the explanation for every choice.</p></div></li>
        </ol>
    </section>

    {{-- 4. Topics --}}
    <section class="block">
        <h2 class="block-title">Browse by topic <span class="count">{{ count($topics) }}</span></h2>
        <div class="topic-grid">
            @foreach ($topics as $topic)
                <a class="topic-card" href="{{ route('quiz') }}?topic={{ $topic['slug'] }}"
                   data-topic="{{ $topic['slug'] }}">
                    <span class="topic-num">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <span class="topic-body">
                        <b>{{ $topic['name'] }}</b>
                        <span>{{ $topic['count'] }} {{ \Illuminate\Support\Str::plural('question', $topic['count']) }}</span>
                    </span>
                    <span class="badge" data-best="{{ $topic['slug'] }}"></span>
                </a>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/progress.js') }}?v={{ @filemtime(public_path('js/progress.js')) }}"></script>
    <script>MlaProgress.renderHome();</script>
@endpush
