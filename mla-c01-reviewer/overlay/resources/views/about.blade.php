@extends('layouts.app')

@section('title', 'About: AWS MLA-C01 Reviewer')

@section('content')
    <article class="prose">
        <h1>About this reviewer</h1>
        <p>This is a free, unofficial practice site for the <strong>AWS Certified Machine Learning Engineer –
            Associate (MLA-C01)</strong> exam. It has {{ $total }} multiple-choice questions grouped by topic.</p>

        <h2>How it works</h2>
        <ul>
            <li>Pick a topic, a quick 10-question round, or everything shuffled.</li>
            <li>Answer correctly and every other choice is explained, so you learn why the distractors are wrong.</li>
            <li>Answer incorrectly and the right answer is revealed with a full explanation.</li>
            <li>Questions you miss are remembered so you can replay only those.</li>
        </ul>

        <h2>Privacy</h2>
        <p>There are no accounts. Your scores and missed questions are stored in your own browser
            (localStorage) and never sent to a server. Clearing site data resets them.</p>

        <h2>Credits &amp; accuracy</h2>
        <p>Questions were written from the public MLA-C01 study notes by
            <a href="https://psychedelic-cuticle-e74.notion.site/AWS-Machine-Learning-Engineer-Associate-MLA-C01-19686c7395e780e1bab0eac37d0401a0" rel="noopener" target="_blank">Christian Greciano</a>.
            The generative-AI topics (Bedrock Guardrails, RAG &amp; Knowledge Bases, FM customization,
            Bedrock inference &amp; cost, AgentCore agents and FM evaluation) were written from AWS's public
            <a href="https://docs.aws.amazon.com/bedrock/" rel="noopener" target="_blank">Amazon Bedrock documentation</a>
            and <a href="https://aws.amazon.com/bedrock/faqs/" rel="noopener" target="_blank">FAQs</a>:
            Bedrock Guardrails docs, Knowledge Bases chunking, Bedrock FAQs, AgentCore FAQs, Bedrock
            Evaluations and Cross-Region inference.
            They are a study aid, not exam dumps, and may contain mistakes or go out of date as AWS
            changes. Always check the official
            <a href="https://aws.amazon.com/certification/certified-machine-learning-engineer-associate/"
               rel="noopener" target="_blank">exam guide</a> and AWS documentation.</p>

        <p><a class="btn primary" href="{{ route('quiz') }}">Start practicing</a></p>
    </article>
@endsection
