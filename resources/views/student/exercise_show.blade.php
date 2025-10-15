@extends('site.layouts.app')

@section('pageTitle', $exercise->title)

@section('content')
<section class="py-5" style="background: radial-gradient(circle at top left, #0f172a, #020617); min-height: 100vh;">
    <div class="container">

        {{-- 🧠 Header --}}
        <div class="text-center mb-5">
            <h2 class="fw-bold text-light mb-2">{{ $exercise->title }}</h2>
            <p class="text-secondary">
                Lesson:
                <a href="{{ route('student.lesson.show', $exercise->lesson->id) }}" class="text-info text-decoration-none fw-semibold">
                    {{ $exercise->lesson->title }}
                </a>
            </p>
        </div>

        <div class="row g-4">
            {{-- LEFT: Description --}}
            <div class="col-lg-5">
                <div class="card bg-dark border-0 shadow-lg rounded-4 p-4 h-100 text-light">
                    <h5 class="fw-semibold text-info mb-3">
                        <i class="bi bi-lightbulb me-2"></i> Problem Description
                    </h5>
                    <p class="text-light opacity-75">{{ $exercise->description ?? 'No description available.' }}</p>

                    {{-- 💡 Starter Code --}}
                    @if($exercise->sample_code)
                        <div class="mt-4">
                            <h6 class="fw-semibold text-warning mb-2">
                                <i class="bi bi-code-slash me-2"></i> Starter Code
                            </h6>
                            <pre class="bg-black text-success rounded-4 p-3 shadow-sm"><code>{{ $exercise->sample_code }}</code></pre>
                        </div>
                    @endif

                      {{-- 💎 Solution Toggle --}}
                    @if($exercise->solution_code)
                        <div class="mt-4">
                            <details class="bg-black text-light rounded-4 p-3 border border-secondary">
                                <summary class="fw-semibold text-info cursor-pointer">
                                    <i class="bi bi-eye me-2"></i> View Solution
                                </summary>
                                <pre class="bg-dark text-success rounded-4 p-3 mt-3"><code>{{ $exercise->solution_code }}</code></pre>
                            </details>
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Editor --}}
            <div class="col-lg-7">
                <div class="card bg-dark border-0 shadow-lg rounded-4 p-4 text-light">
                    @php
                        $course = $exercise->lesson->course ?? null;
                        $selectedLang = strtolower(optional($course->language)->name ?? '');
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-gradient fs-6 py-2 px-3 shadow" style="background: linear-gradient(135deg, #3b82f6, #06b6d4);">
                            <i class="bi bi-terminal me-2"></i>{{ strtoupper($selectedLang ?: 'PLAINTEXT') }}
                        </span>
                        <button id="runBtn" class="btn btn-gradient px-4 py-2 rounded-pill">
                            <i class="bi bi-play-fill me-2"></i>Run
                        </button>
                    </div>

                    {{-- 🖥️ Monaco Editor --}}
                    <div id="editor" class="rounded-4 border border-secondary" style="height: 400px;"></div>

                    {{-- Loader --}}
                    <div class="text-center mt-3">
                        <div id="loader" class="spinner-border text-info" role="status" style="display:none;">
                            <span class="visually-hidden">Running...</span>
                        </div>
                    </div>

                    {{-- ⚙️ Output Panels --}}
                    <div id="resultSection" class="mt-4" style="display: none;">
                        <h6 class="text-info fw-semibold mb-2">Result</h6>
                        <pre id="outputBox" class="bg-black text-success rounded-4 p-3 mb-3 shadow-sm" style="min-height:100px;"></pre>
                    </div>

                    <div id="errorSection" class="mt-4" style="display: none;">
                        <h6 class="text-danger fw-semibold mb-2">Error</h6>
                        <pre id="errorBox" class="bg-black text-danger rounded-4 p-3 mb-3 shadow-sm" style="min-height:100px;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #0f172a;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(6,182,212,0.6);
        }
        pre code, #outputBox, #errorBox {
            font-family: "Fira Code", monospace;
            font-size: 0.9rem;
        }
    </style>
</section>

{{-- 🧠 Monaco Editor --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>

<script>
let editor;
const selectedLang = "{{ strtolower(optional($exercise->lesson->course->language)->name ?? 'plaintext') }}";
const starterCode = @json($exercise->sample_code ?? '# Write your code here');

require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' } });

require(['vs/editor/editor.main'], function () {
    const langMap = {
        "python": "python",
        "php": "php",
        "node": "javascript",
        "javascript": "javascript",
        "c": "c",
        "cpp": "cpp",
        "java": "java",
        "html": "html",
        "tailwind": "html"
    };

    editor = monaco.editor.create(document.getElementById('editor'), {
        value: starterCode,
        language: langMap[selectedLang] || "plaintext",
        theme: "vs-dark",
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: false }
    });
});

document.getElementById('runBtn').addEventListener('click', () => {
    const code = editor.getValue();
    const language = selectedLang;
    const loader = document.getElementById('loader');
    const resultSection = document.getElementById('resultSection');
    const errorSection = document.getElementById('errorSection');
    const outputBox = document.getElementById('outputBox');
    const errorBox = document.getElementById('errorBox');

    loader.style.display = "inline-block";
    resultSection.style.display = "none";
    errorSection.style.display = "none";

    fetch("{{ route('run.execute') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ language, code })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            // Show Error Section
            resultSection.style.display = "none";
            errorSection.style.display = "block";
            errorBox.textContent = data.error;
        } else {
            // Show Result Section
            errorSection.style.display = "none";
            resultSection.style.display = "block";
            outputBox.textContent = data.output || "✅ Success";
        }
    })
    .catch(err => {
        resultSection.style.display = "none";
        errorSection.style.display = "block";
        errorBox.textContent = "Error: " + err.message;
    })
    .finally(() => loader.style.display = "none");
});
</script>
@endsection
