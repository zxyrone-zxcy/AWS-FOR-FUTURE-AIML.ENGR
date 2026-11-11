@extends('layouts.app')

@section('title', 'Study guide: Bedrock, RAG, Guardrails and AgentCore')
@section('description', 'Visual study notes for the Amazon Bedrock topics: Guardrails, Knowledge Bases and chunking, RAG vs fine-tuning vs continued pre-training vs distillation, inference options, AgentCore and model evaluation.')

@section('content')
    <div class="guide">
        <header class="page-head">
            <h1>Study guide: Bedrock &amp; generative AI</h1>
            <p>Short notes and diagrams for the newer generative-AI topics, each linked to its practice questions.
                Bedrock changes often, so confirm details in the
                <a href="https://docs.aws.amazon.com/bedrock/" rel="noopener" target="_blank">AWS docs</a>.</p>
        </header>

        <nav class="toc" aria-label="On this page">
            <a href="#big-picture">Big picture</a>
            <a href="#guardrails">1. Guardrails</a>
            <a href="#rag">2. Knowledge Bases &amp; chunking</a>
            <a href="#customization">3. RAG vs customization</a>
            <a href="#inference">4. Inference options</a>
            <a href="#agentcore">5. AgentCore</a>
            <a href="#evaluation">6. Evaluation</a>
        </nav>

        {{-- ============ Big picture ============ --}}
        <section id="big-picture" class="g-sec">
            <h2>The big picture</h2>
            <p>Building a generative-AI app on Bedrock is five decisions. Each topic below is one of them.</p>
            <figure class="diagram">
                <div class="flow">
                    <div class="node"><b>Choose / customize</b><span>Pick an FM, then RAG, fine-tune, pre-train or distill</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Ground it</b><span>Knowledge Bases (RAG) supply your own data</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Protect it</b><span>Guardrails filter inputs and outputs</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Run it</b><span>Pick an inference mode; AgentCore hosts agents</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Measure it</b><span>Evaluate quality before and after changes</span></div>
                </div>
                <figcaption>Order is a mental model, not a strict pipeline. Evaluation feeds back into every earlier step.</figcaption>
            </figure>
        </section>

        {{-- ============ 1. Guardrails ============ --}}
        <section id="guardrails" class="g-sec">
            <h2><span class="num">1</span> Bedrock Guardrails</h2>
            <p>A guardrail checks the <b>prompt going in</b> and the <b>response coming out</b>. If something violates a
                policy, it is blocked (or masked) and a message you configure is returned.</p>

            <figure class="diagram">
                <div class="flow">
                    <div class="node"><b>User prompt</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node guard"><b>Guardrail</b><span>checks input</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Foundation model</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node guard"><b>Guardrail</b><span>checks output</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Response</b></div>
                </div>
                <figcaption>Blocked at either step, the user gets your configured "blocked" message instead.</figcaption>
            </figure>

            <h3>Which policy do I pick?</h3>
            <div class="table-wrap"><table>
                <thead><tr><th>Policy</th><th>Use it to…</th><th>Remember</th></tr></thead>
                <tbody>
                <tr><td><b>Content filters</b></td><td>Block harmful categories: Hate, Insults, Sexual, Violence, Misconduct, Prompt Attack</td><td>Strength is configurable per category</td></tr>
                <tr><td><b>Denied topics</b></td><td>Refuse a whole <em>subject</em>, however it is phrased (e.g. illegal investment advice)</td><td>Defined in natural language; matches meaning</td></tr>
                <tr><td><b>Word filters</b></td><td>Block exact words or phrases (competitor names, profanity)</td><td>Exact match only, so it misses rephrasing</td></tr>
                <tr><td><b>Sensitive information filters</b></td><td>Detect PII (names, SSNs) and custom regex; <em>block or mask</em> it</td><td>Mask to anonymize in summaries</td></tr>
                <tr><td><b>Contextual grounding check</b></td><td>Catch hallucinations in RAG: is the answer <em>grounded</em> in the source and <em>relevant</em> to the query?</td><td>Higher threshold = more blocked answers</td></tr>
                <tr><td><b>Automated Reasoning checks</b></td><td>Validate answers against logical rules you define</td><td>Rule-based verification, not a blocklist</td></tr>
                </tbody>
            </table></div>

            <div class="callout"><b>ApplyGuardrail API</b> evaluates any text against a guardrail <em>without invoking a Bedrock model</em>,
                so it also works with custom or third-party models hosted elsewhere.</div>
            <div class="trap"><b>Exam trap:</b> theme or subject → <em>denied topic</em>; specific word → <em>word filter</em>; PII → <em>sensitive information filter</em>; made-up facts → <em>contextual grounding</em>.</div>
            <p><a class="btn" href="{{ route('quiz') }}?topic=bedrock-guardrails">Practice Guardrails →</a></p>
        </section>

        {{-- ============ 2. Knowledge Bases ============ --}}
        <section id="rag" class="g-sec">
            <h2><span class="num">2</span> Knowledge Bases &amp; chunking (RAG)</h2>
            <p><b>Retrieval-Augmented Generation</b> gives a model your own data at question time, without changing the
                model's weights. A Knowledge Base automates both halves.</p>

            <figure class="diagram">
                <div class="lane-title">A. Ingest (when you sync a data source)</div>
                <div class="flow">
                    <div class="node"><b>Documents</b><span>e.g. in S3</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Parse &amp; chunk</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Embeddings model</b><span>text → vectors</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Vector store</b><span>vectors + source link</span></div>
                </div>
                <div class="lane-title">B. Query (every request)</div>
                <div class="flow">
                    <div class="node"><b>Question</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Embed question</b><span>same model</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Retrieve</b><span>similar chunks</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Augment prompt</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>FM answers</b></div>
                </div>
                <figcaption>New or changed documents only need a re-sync, with no retraining.</figcaption>
            </figure>

            <h3>Chunking strategies</h3>
            <div class="table-wrap"><table>
                <thead><tr><th>Strategy</th><th>How it works</th><th>Choose it when</th></tr></thead>
                <tbody>
                <tr><td><b>Fixed-size</b></td><td>Splits by a token size you set, with optional overlap</td><td>You want simple, predictable chunks</td></tr>
                <tr><td><b>Hierarchical</b></td><td>Searches small <em>child</em> chunks, then returns the larger <em>parent</em> for context</td><td>Small chunks match well but the FM needs more context</td></tr>
                <tr><td><b>Semantic</b></td><td>Groups neighbouring sentences by meaning (buffer size, max tokens)</td><td>Narrative text with no clear section boundaries</td></tr>
                <tr><td><b>No chunking</b></td><td>Each file stays as one chunk</td><td>Documents are already split</td></tr>
                </tbody>
            </table></div>

            <figure class="diagram">
                <div class="flow">
                    <div class="node"><b>Query</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Matches a small child chunk</b><span>precise search</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Swapped for its parent</b><span>more context</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node"><b>Sent to the FM</b></div>
                </div>
                <figcaption>Hierarchical chunking. You can get fewer results than requested because several children share one parent.</figcaption>
            </figure>

            <h3>Choosing a vector store</h3>
            <ul>
                <li><b>Amazon OpenSearch Serverless</b>: supports <em>hybrid search</em> (keywords + vectors).</li>
                <li><b>Amazon S3 Vectors</b>: low-cost storage for large vector sets, best for pure semantic search.</li>
                <li>Athena and QuickSight are not vector stores.</li>
            </ul>
            <div class="trap"><b>Exam trap:</b> RAG does <em>not</em> change the model and is not free: you still pay for embeddings, vector storage and inference.</div>
            <p><a class="btn" href="{{ route('quiz') }}?topic=rag-knowledge-bases">Practice RAG &amp; Knowledge Bases →</a></p>
        </section>

        {{-- ============ 3. Customization ============ --}}
        <section id="customization" class="g-sec">
            <h2><span class="num">3</span> RAG vs fine-tuning vs continued pre-training vs distillation</h2>
            <p>Try <b>prompt engineering</b> first, since it is the cheapest. If that is not enough, match the problem to the technique:</p>

            <figure class="diagram">
                <div class="decide">
                    <div class="d-row"><span class="d-q">Knowledge changes often, or must come from your documents?</span><span class="d-a rag">RAG</span></div>
                    <div class="d-row"><span class="d-q">Need a specific style, format or task behaviour, and you have <em>labeled</em> prompt/completion examples?</span><span class="d-a ft">Fine-tuning</span></div>
                    <div class="d-row"><span class="d-q">Lots of <em>unlabeled</em> domain text and the model misreads your jargon?</span><span class="d-a cpt">Continued pre-training</span></div>
                    <div class="d-row"><span class="d-q">Proven quality with a big model, but you need it cheaper and faster?</span><span class="d-a dist">Distillation</span></div>
                    <div class="d-row"><span class="d-q">Already trained a model elsewhere (SageMaker, EC2)?</span><span class="d-a imp">Custom Model Import</span></div>
                </div>
                <figcaption>Read the left column as a symptom and the right column as the fix.</figcaption>
            </figure>

            <div class="table-wrap"><table>
                <thead><tr><th></th><th>Changes weights?</th><th>Data needed</th><th>Key point</th></tr></thead>
                <tbody>
                <tr><td><b>RAG</b></td><td>No</td><td>Your documents</td><td>Update by re-syncing; no training job</td></tr>
                <tr><td><b>Fine-tuning</b></td><td>Yes</td><td>Labeled prompt/completion pairs (JSONL in S3)</td><td>Teaches behaviour and format</td></tr>
                <tr><td><b>Continued pre-training</b></td><td>Yes</td><td>Unlabeled domain text</td><td>Builds domain knowledge; fine-tune after if needed</td></tr>
                <tr><td><b>Distillation</b></td><td>Trains a smaller student</td><td>Teacher model's responses</td><td>Smaller, faster, cheaper for one use case</td></tr>
                <tr><td><b>Custom Model Import</b></td><td>No (you bring it)</td><td>Model built elsewhere</td><td>Architecture must be supported (e.g. Llama, Mistral, Flan)</td></tr>
                </tbody>
            </table></div>

            <div class="callout"><b>Serving a customized model:</b> a fine-tuned or continued-pre-trained Bedrock model is invoked
                through <b>Provisioned Throughput</b> (you purchase model units and call it by ARN), not on-demand.</div>
            <div class="trap"><b>Exam trap:</b> "frequently changing documents" → <em>RAG</em>. "Unlabeled" → <em>continued pre-training</em>. "Labeled examples" → <em>fine-tuning</em>.</div>
            <p><a class="btn" href="{{ route('quiz') }}?topic=fm-customization-selection">Practice customization →</a></p>
        </section>

        {{-- ============ 4. Inference ============ --}}
        <section id="inference" class="g-sec">
            <h2><span class="num">4</span> Inference options</h2>
            <p>Pick the mode from the <b>traffic pattern</b> in the scenario.</p>

            <div class="table-wrap"><table>
                <thead><tr><th>Mode</th><th>Best for</th><th>Notes</th></tr></thead>
                <tbody>
                <tr><td><b>On-demand</b></td><td>Bursty or unpredictable interactive traffic</td><td>Pay per use, no commitment; can be throttled at the quota</td></tr>
                <tr><td><b>Cross-Region inference</b></td><td>Spikes that throttle on-demand calls</td><td>Higher throughput via an <em>inference profile</em>; no extra routing charge, you pay your source Region's price</td></tr>
                <tr><td><b>Provisioned Throughput</b></td><td>Steady, latency-sensitive production traffic; <em>required for custom models</em></td><td>Reserved model units, optional commitment term; not supported on inference profiles</td></tr>
                <tr><td><b>Batch inference</b></td><td>Large offline jobs with no latency requirement</td><td>Asynchronous: input in S3, results in S3; typically lower cost</td></tr>
                </tbody>
            </table></div>

            <figure class="diagram">
                <div class="decide">
                    <div class="d-row"><span class="d-q">Millions of documents overnight, no latency target</span><span class="d-a rag">Batch</span></div>
                    <div class="d-row"><span class="d-q">Steady, critical traffic or a custom model</span><span class="d-a ft">Provisioned Throughput</span></div>
                    <div class="d-row"><span class="d-q">Spiky interactive traffic, throttled</span><span class="d-a cpt">Cross-Region inference</span></div>
                    <div class="d-row"><span class="d-q">Unpredictable, light traffic</span><span class="d-a dist">On-demand</span></div>
                </div>
                <figcaption>Streaming responses improve <em>perceived</em> latency, not cost or capacity.</figcaption>
            </figure>

            <h3>Cross-Region inference profiles</h3>
            <ul>
                <li><b>Geographic profile</b> (e.g. EU): requests stay within that geography. Use it for <em>data residency</em>.</li>
                <li><b>Global profile</b>: can route to any supported commercial Region. Use it for <em>maximum throughput</em>.</li>
            </ul>
            <div class="trap"><b>Exam trap:</b> a larger <code>max_tokens</code> changes output length, not capacity. Batch is not for interactive spikes.</div>
            <p><a class="btn" href="{{ route('quiz') }}?topic=bedrock-inference-cost">Practice inference &amp; cost →</a></p>
        </section>

        {{-- ============ 5. AgentCore ============ --}}
        <section id="agentcore" class="g-sec">
            <h2><span class="num">5</span> Amazon Bedrock AgentCore</h2>
            <p>AgentCore is a set of building blocks for running agents in production, and it works with any framework
                and model. Pick the block that matches the need.</p>

            <figure class="diagram">
                <div class="hub">
                    <div class="hub-core"><b>Your agent</b><span>any framework, any model</span></div>
                    <div class="hub-item"><b>Runtime</b><span>Serverless hosting and scaling, with each session isolated</span></div>
                    <div class="hub-item"><b>Gateway</b><span>Turns REST APIs and Lambda into MCP-compatible tools</span></div>
                    <div class="hub-item"><b>Identity</b><span>Token vault and OAuth, so agents act for users</span></div>
                    <div class="hub-item"><b>Memory</b><span>Short-term and long-term context</span></div>
                    <div class="hub-item"><b>Observability</b><span>Traces, latency, tokens, errors; OpenTelemetry and CloudWatch</span></div>
                    <div class="hub-item"><b>Browser</b><span>Managed browser runtime for web tasks</span></div>
                    <div class="hub-item"><b>Code Interpreter</b><span>Sandbox for code the agent writes</span></div>
                </div>
                <figcaption>One building block per job: tools, access, context, hosting, tracing.</figcaption>
            </figure>

            <div class="trap"><b>Exam trap:</b> "expose my existing APIs as tools" → <em>Gateway</em>. "Call GitHub on a user's behalf with OAuth" → <em>Identity</em>.
                "Trace tool calls and token usage" → <em>Observability</em>. "Remember earlier conversations" → <em>Memory</em>.</div>
            <p><a class="btn" href="{{ route('quiz') }}?topic=agents-agentcore">Practice AgentCore →</a></p>
        </section>

        {{-- ============ 6. Evaluation ============ --}}
        <section id="evaluation" class="g-sec">
            <h2><span class="num">6</span> Model evaluation</h2>
            <p>Bedrock offers three ways to score outputs. Choose by <em>what</em> you are judging and <em>how many</em> responses you have.</p>

            <figure class="diagram">
                <div class="cols">
                    <div class="col"><h4>Programmatic metrics</h4><p>Compare output to a reference with a formula.</p><span class="chip">Fast · cheap · objective</span></div>
                    <div class="col"><h4>LLM-as-a-judge</h4><p>A judge model scores built-in or custom metrics.</p><span class="chip">Scalable · human-like</span></div>
                    <div class="col"><h4>Human evaluation</h4><p>Your own staff, or an AWS-managed team, rate responses.</p><span class="chip">Subjective · slower · costly</span></div>
                </div>
                <figcaption>Spot-check automated scores against a small human baseline.</figcaption>
            </figure>

            <div class="table-wrap"><table>
                <thead><tr><th>Metric</th><th>Use for</th><th>Idea</th></tr></thead>
                <tbody>
                <tr><td><b>ROUGE</b></td><td>Summarization</td><td>Recall-oriented n-gram overlap with a reference</td></tr>
                <tr><td><b>BLEU</b></td><td>Machine translation</td><td>Precision-oriented n-gram overlap</td></tr>
                <tr><td><b>BERTScore</b></td><td>Semantic similarity</td><td>Compares meaning, not just exact words</td></tr>
                </tbody>
            </table></div>

            <h3>LLM-as-a-judge and RAG evaluation</h3>
            <ul>
                <li>Built-in judge metrics include correctness, completeness, faithfulness, harmfulness and refusal; you can add custom ones.</li>
                <li><b>Subjective qualities</b> (brand voice, style) → human evaluation with custom metrics.</li>
            </ul>
            <figure class="diagram">
                <div class="flow">
                    <div class="node"><b>Question</b></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Retrieval</b><span>judge: context relevance &amp; coverage</span></div>
                    <span class="arr" aria-hidden="true">→</span>
                    <div class="node accent"><b>Generation</b><span>judge: faithfulness &amp; correctness</span></div>
                </div>
                <figcaption>Evaluate retrieval separately from generation to find which half of a RAG pipeline is failing.</figcaption>
            </figure>
            <div class="trap"><b>Exam trap:</b> "retrieved chunks look irrelevant" is a <em>retrieval</em> problem. Faithfulness only checks the answer against the context it was given.</div>
            <p><a class="btn" href="{{ route('quiz') }}?topic=fm-evaluation">Practice evaluation →</a></p>
        </section>

        <p class="muted g-foot">Written from AWS's public Bedrock documentation and FAQs. Study aid only; verify against the
            <a href="https://docs.aws.amazon.com/bedrock/" rel="noopener" target="_blank">official docs</a> before relying on any detail.</p>
    </div>
@endsection
