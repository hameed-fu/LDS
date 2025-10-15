@extends('site.layouts.app')

@section('pageTitle', "Run Code")

@section('content')
<head>
  <meta charset="UTF-8">
  <title>Code Runner</title>
  <style>
    body {
      background: radial-gradient(circle at 20% 20%, #10151f, #0c0f17);
      color: #e5e7eb;
      font-family: 'Inter', sans-serif;
    }

    h2 {
      font-weight: 700;
      color: #60a5fa;
      letter-spacing: -0.5px;
    }

    .card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      backdrop-filter: blur(20px);
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.25);
      padding: 2rem;
    }

 

    .form-select:focus {
      outline: none;
      border-color: #60a5fa;
      box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25);
    }

    #editor {
      height: 450px;
      border-radius: 12px;
      border: 1px solid #334155;
      overflow: hidden;
    }

    .btn-run {
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      border: none;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      padding: 10px 20px;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
    }

    .btn-run:hover {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
    }

    pre {
      border-radius: 10px;
      padding: 16px;
      font-size: 14px;
      white-space: pre-wrap;
      word-wrap: break-word;
      font-family: 'Fira Code', monospace;
    }

    #outputBox {
      background: #0f172a;
      color: #38bdf8;
      border-left: 4px solid #38bdf8;
    }

    #errorBox {
      background: #1e1b4b;
      color: #f87171;
      border-left: 4px solid #f87171;
    }

    #loader {
      display: none;
    }
  </style>
</head>

<section class="py-5">
  <div class="container">
    <div class="card mx-auto col-lg-10">
      <h2 class="text-center mb-4">Run Your Code Instantly</h2>

      <div class="row mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Language</label>
          <select class="form-select" id="languageSelect">
            <option value="python">Python</option>
            <option value="php">PHP</option>
            <option value="node">Node.js</option>
            <option value="java">Java</option>
            <option value="cpp">C++</option>
            <option value="html">HTML</option>
            <option value="tailwind">Tailwind CSS</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Code Editor</label>
        <div id="editor"></div>
      </div>

      <div class="text-center mb-4">
        <button id="runBtn" class="btn btn-run">▶ Run Code</button>
        <div id="loader" class="spinner-border text-info ms-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div class="mt-5">
        <h5 class="fw-bold text-info mb-2">Output</h5>
        <pre id="outputBox"></pre>

        <h5 class="fw-bold text-danger mt-4 mb-2">Error</h5>
        <pre id="errorBox"></pre>
      </div>
    </div>
  </div>

  {{-- Monaco Editor --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
  <script>
    let editor;

    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' } });

    require(["vs/editor/editor.main"], function() {
      editor = monaco.editor.create(document.getElementById('editor'), {
        value: "# Python example\nprint('Hello python!')",
        language: "python",
        theme: "vs-dark",
        fontSize: 15,
        minimap: { enabled: false },
        automaticLayout: true,
      });
    });

    document.getElementById('languageSelect').addEventListener('change', function() {
      const map = {
        python: "python",
        php: "php",
        node: "javascript",
        java: "java",
        cpp: "cpp",
        html: "html",
        tailwind: "html",
      };
      monaco.editor.setModelLanguage(editor.getModel(), map[this.value] || "plaintext");
    });

    document.getElementById('runBtn').addEventListener('click', function() {
      const code = editor.getValue();
      const language = document.getElementById('languageSelect').value;
      const loader = document.getElementById('loader');
      const outputBox = document.getElementById('outputBox');
      const errorBox = document.getElementById('errorBox');

      loader.style.display = "inline-block";
      outputBox.textContent = "";
      errorBox.textContent = "";

      if (language === "html" || language === "tailwind") {
        const html = `
          <!DOCTYPE html>
          <html><head>
          ${language === "tailwind" ? "<scr" + "ipt src='https://cdn.tailwindcss.com'></scr" + "ipt>" : ""}
          </head><body>${code}</body></html>`;
        const iframe = document.createElement("iframe");
        iframe.style = "width:100%;height:400px;border-radius:10px;border:1px solid #334155;background:white;";
        iframe.srcdoc = html;
        outputBox.innerHTML = "";
        outputBox.appendChild(iframe);
        loader.style.display = "none";
        return;
      }

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
        if (data.output && data.output.trim() !== "") {
          outputBox.textContent = data.output;
          errorBox.textContent = "";
        } else if (data.error && data.error.trim() !== "") {
          errorBox.textContent = data.error;
          outputBox.textContent = "";
        } else {
          outputBox.textContent = "No output.";
        }
      })
      .catch(err => {
        errorBox.textContent = "Error: " + err.message;
      })
      .finally(() => loader.style.display = "none");
    });
  </script>
</section>
@endsection
