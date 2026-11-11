<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AWS MLA-C01 Reviewer')</title>
    <meta name="description" content="@yield('description', 'Free practice questions for the AWS Certified Machine Learning Engineer - Associate (MLA-C01) exam, with an explanation for every answer choice.')">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
<header class="site-header">
    <div class="wrap bar">
        <a class="brand" href="{{ route('home') }}">AWS MLA-C01 <span>Reviewer</span></a>
        <nav>
            <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Home</a>
            <a href="{{ route('quiz') }}" @class(['active' => request()->routeIs('quiz')])>Practice</a>
            <a href="{{ route('guide') }}" @class(['active' => request()->routeIs('guide')])>Guide</a>
            <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>About</a>
        </nav>
    </div>
</header>

<main class="wrap">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="wrap">
        <p>Unofficial study aid. Not affiliated with, endorsed by, or sponsored by Amazon Web Services.
            AWS and related service names are trademarks of Amazon.com, Inc. or its affiliates.</p>
        <p>Study notes by <a href="https://psychedelic-cuticle-e74.notion.site/AWS-Machine-Learning-Engineer-Associate-MLA-C01-19686c7395e780e1bab0eac37d0401a0" rel="noopener" target="_blank">Christian Greciano</a>. Your progress is saved only in this browser. <a href="{{ route('about') }}">About &amp; credits</a></p>
    </div>
</footer>

@stack('scripts')
</body>
</html>
