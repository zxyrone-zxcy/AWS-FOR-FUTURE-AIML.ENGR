@extends('layouts.app')

@section('title', 'Practice: AWS MLA-C01 Reviewer')

@section('content')
    <div id="quiz-app" data-questions-url="{{ route('questions') }}" aria-live="polite">
        <noscript><p class="panel">This practice quiz needs JavaScript enabled.</p></noscript>
        <p class="muted">Loading questions…</p>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/progress.js') }}?v={{ @filemtime(public_path('js/progress.js')) }}"></script>
    <script src="{{ asset('js/quiz.js') }}?v={{ @filemtime(public_path('js/quiz.js')) }}"></script>
@endpush
