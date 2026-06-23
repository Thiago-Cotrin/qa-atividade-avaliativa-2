<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class TestRunnerController extends Controller
{
    public function index()
    {
        return view('testes.index');
    }

    public function run(Request $request)
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        $command = [PHP_BINARY, base_path('artisan'), 'test'];

        if ($request->boolean('coverage')) {
            $command[] = '--coverage';
            $command[] = '--min=0';
        }

        $startedAt = microtime(true);

        $process = new Process($command, base_path(), [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ]);

        $process->setTimeout(180);
        $process->run();

        return view('testes.index', [
            'command' => implode(' ', $command),
            'duration' => round(microtime(true) - $startedAt, 2),
            'exitCode' => $process->getExitCode(),
            'output' => trim($process->getOutput() . PHP_EOL . $process->getErrorOutput()),
        ]);
    }
}
