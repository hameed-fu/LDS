<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class CodeRunController extends Controller
{
    public function index()
    {
        return view('site.editor');
    }

    public function execute(Request $request)
    {
        $request->validate([
            'language' => 'required|string',
            'code' => 'required|string',
        ]);

        $language = $request->language;
        $code = $request->code;

        // language config: image, container name and run template (use {file} placeholder)
        $languages = [
            // --- Interpreted ---
            'python' => [
                'image' => 'python:3.11',
                'container' => 'python_runner',
                'run' => 'python {file}'
            ],
            'php' => [
                'image' => 'php:8.2-cli',
                'container' => 'php_runner',
                'run' => 'php {file}'
            ],
            'node' => [
                'image' => 'node:20',
                'container' => 'node_runner',
                'run' => 'node {file}'
            ],
            'ruby' => [
                'image' => 'ruby:3.2',
                'container' => 'ruby_runner',
                'run' => 'ruby {file}'
            ],
            'perl' => [
                'image' => 'perl:latest',
                'container' => 'perl_runner',
                'run' => 'perl {file}'
            ],
            'bash' => [
                'image' => 'bash:latest',
                'container' => 'bash_runner',
                'run' => 'bash {file}'
            ],
            'r' => [
                'image' => 'r-base:latest',
                'container' => 'r_runner',
                'run' => 'Rscript {file}'
            ],
            'lua' => [
                'image' => 'lua:5.4',
                'container' => 'lua_runner',
                'run' => 'lua {file}'
            ],

            // --- Compiled ---
            'c' => [
                'image' => 'gcc:13',
                'container' => 'c_runner',
                'run' => 'sh -c "gcc {file} -o /tmp/a.out && /tmp/a.out"'
            ],
            'cpp' => [
                'image' => 'gcc:13',
                'container' => 'cpp_runner',
                'run' => 'sh -c "g++ {file} -o /tmp/a.out && /tmp/a.out"'
            ],
            'java' => [
                'image' => 'openjdk:21-slim',
                'container' => 'java_runner',
                'run' => 'sh -c "javac {file} && java Main"'
            ],
            'go' => [
                'image' => 'golang:1.21',
                'container' => 'go_runner',
                'run' => 'go run {file}'
            ],
            'rust' => [
                'image' => 'rust:latest',
                'container' => 'rust_runner',
                'run' => 'sh -c "rustc {file} -o /tmp/a.out && /tmp/a.out"'
            ],
            'kotlin' => [
                'image' => 'zenika/kotlin',
                'container' => 'kotlin_runner',
                'run' => 'sh -c "kotlinc {file} -include-runtime -d /tmp/code.jar && java -jar /tmp/code.jar"'
            ],
            'swift' => [
                'image' => 'swift:latest',
                'container' => 'swift_runner',
                'run' => 'swift {file}'
            ],

            // --- Functional ---
            'haskell' => [
                'image' => 'haskell:latest',
                'container' => 'haskell_runner',
                'run' => 'runghc {file}'
            ],
            'elixir' => [
                'image' => 'elixir:latest',
                'container' => 'elixir_runner',
                'run' => 'elixir {file}'
            ],
            'erlang' => [
                'image' => 'erlang:latest',
                'container' => 'erlang_runner',
                'run' => 'escript {file}'
            ],
            'scala' => [
                'image' => 'hseeberger/scala-sbt:11.0.16_1.8.0_2.13.10',
                'container' => 'scala_runner',
                'run' => 'scala {file}'
            ],
            'clojure' => [
                'image' => 'clojure:temurin-21-tools-deps',
                'container' => 'clojure_runner',
                'run' => 'clojure -M {file}'
            ],
            'julia' => [
                'image' => 'julia:1.9',
                'container' => 'julia_runner',
                'run' => 'julia {file}'
            ],
            'octave' => [
                'image' => 'gnuoctave/octave:latest',
                'container' => 'octave_runner',
                'run' => 'octave {file}'
            ],
            'fortran' => [
                'image' => 'gcc:13',
                'container' => 'fortran_runner',
                'run' => 'sh -c "gfortran {file} -o /tmp/a.out && /tmp/a.out"'
            ],

            // --- .NET ---
            'csharp' => [
                'image' => 'mcr.microsoft.com/dotnet/sdk:8.0',
                'container' => 'csharp_runner',
                'run' => 'sh -c "dotnet new console -o /tmp/app -n App && cp {file} /tmp/app/Program.cs && cd /tmp/app && dotnet run --no-restore"'
            ],
            'fsharp' => [
                'image' => 'mcr.microsoft.com/dotnet/sdk:8.0',
                'container' => 'fsharp_runner',
                'run' => 'dotnet fsi {file}'
            ],
        ];



        if (!isset($languages[$language])) {
            return response()->json(['error' => 'Language not supported'], 422);
        }

        $config = $languages[$language];

        // Choose filename & extension (java must be Main.java)
        $uniq = uniqid('code_');
        switch ($language) {
            // Interpreted
            case 'python':
                $filename = $uniq . '.py';
                break;
            case 'php':
                $filename = $uniq . '.php';
                break;
            case 'node':
                $filename = $uniq . '.js';
                break;
            case 'ruby':
                $filename = $uniq . '.rb';
                break;
            case 'perl':
                $filename = $uniq . '.pl';
                break;
            case 'bash':
                $filename = $uniq . '.sh';
                break;
            case 'r':
                $filename = $uniq . '.R';
                break;
            case 'lua':
                $filename = $uniq . '.lua';
                break;

            // Compiled
            case 'c':
                $filename = $uniq . '.c';
                break;
            case 'cpp':
                $filename = $uniq . '.cpp';
                break;
            case 'java':
                $filename = 'Main.java';
                break; // must be Main.java
            case 'go':
                $filename = $uniq . '.go';
                break;
            case 'rust':
                $filename = $uniq . '.rs';
                break;
            case 'kotlin':
                $filename = $uniq . '.kt';
                break;
            case 'swift':
                $filename = $uniq . '.swift';
                break;

            // Functional / Others
            case 'haskell':
                $filename = $uniq . '.hs';
                break;
            case 'elixir':
                $filename = $uniq . '.exs';
                break;
            case 'erlang':
                $filename = $uniq . '.erl';
                break;
            case 'scala':
                $filename = $uniq . '.scala';
                break;
            case 'clojure':
                $filename = $uniq . '.clj';
                break;
            case 'julia':
                $filename = $uniq . '.jl';
                break;
            case 'octave':
                $filename = $uniq . '.m';
                break;
            case 'fortran':
                $filename = $uniq . '.f90';
                break;

            // .NET ecosystem
            case 'csharp':
                $filename = 'Program.cs';
                break;
            case 'fsharp':
                $filename = $uniq . '.fsx';
                break;

            default:
                $filename = $uniq . '.txt';
        }


        // ensure container running (start background container if missing)
        try {
            $this->ensureContainerRunning($config['container'], $config['image']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to start container: ' . $e->getMessage()], 500);
        }

        // Write temporary file
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($tmpPath, $code);

        // docker cp into container
        $cp = new Process(['docker', 'cp', $tmpPath, "{$config['container']}:/{$filename}"]);
        $cp->setTimeout(20);
        try {
            $cp->run();
        } catch (ProcessTimedOutException $e) {
            @unlink($tmpPath);
            return response()->json(['error' => 'Copy to container timed out'], 500);
        } catch (\Throwable $e) {
            @unlink($tmpPath);
            return response()->json(['error' => 'Error copying file to container: ' . $e->getMessage()], 500);
        }

        // build run command; put timeout inside container so long runs get killed
        $runCommand = str_replace('{file}', "/{$filename}", $config['run']);
        // prepend timeout (many images include timeout)
        $runCommand = "timeout 8s " . $runCommand;

        // execute inside container
        $exec = new Process(['docker', 'exec', '-i', $config['container'], 'sh', '-c', $runCommand]);
        $exec->setTimeout(20);

        try {
            $exec->run();
            $stdout = $exec->getOutput();
            $stderr = $exec->getErrorOutput();
            $exit = $exec->getExitCode();
        } catch (ProcessTimedOutException $e) {
            $stdout = $exec->getOutput();
            $stderr = $exec->getErrorOutput() . "\nExecution timed out.";
            $exit = 124;
        } catch (\Throwable $e) {
            @unlink($tmpPath);
            return response()->json(['error' => 'Execution error: ' . $e->getMessage()], 500);
        }

        // cleanup temp file
        @unlink($tmpPath);

        return response()->json([
            'output' => $stdout,
            'error'  => $stderr,
            'exit'   => $exit,
        ]);
    }

    private function ensureContainerRunning(string $container, string $image)
    {
        // If running -> return
        $check = new Process(['docker', 'ps', '-q', '-f', "name={$container}"]);
        $check->setTimeout(10);
        $check->run();
        if (trim($check->getOutput()) !== '') {
            return;
        }

        // If exists but stopped -> remove it (force)
        $exists = new Process(['docker', 'ps', '-aq', '-f', "name={$container}"]);
        $exists->setTimeout(10);
        $exists->run();
        if (trim($exists->getOutput()) !== '') {
            (new Process(['docker', 'rm', '-f', $container]))->run();
        }

        // Start background container
        // Using tail keeps container alive; remove --rm so container stays
        $start = new Process(['docker', 'run', '-dit', '--name', $container, $image, 'tail', '-f', '/dev/null']);
        $start->setTimeout(60); // pulling image may take time first run
        $start->run();
        if ($start->getExitCode() !== 0) {
            throw new \RuntimeException("Failed to start container: " . $start->getErrorOutput());
        }
    }
}
