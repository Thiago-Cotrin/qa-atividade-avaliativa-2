@extends('layouts.app')

@section('content')
<div class="section">
    <div class="row">
        <div class="col s12 m10 l8 offset-m1 offset-l2">
            <div class="card blue-grey darken-1">
                <div class="card-content white-text">
                    <span class="card-title">Execucao dos Testes</span>
                    <p>Use esta tela para executar a suite de testes de integracao da atividade.</p>
                </div>
                <div class="card-action">
                    <form action="{{ route('testes.run') }}" method="POST" style="display:inline-block; margin-right: 0.5rem;">
                        @csrf
                        <button type="submit" class="btn amber darken-2">Rodar testes</button>
                    </form>

                    <form action="{{ route('testes.run') }}" method="POST" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="coverage" value="1">
                        <button type="submit" class="btn amber darken-4">Rodar com cobertura</button>
                    </form>
                </div>
            </div>

            @isset($exitCode)
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">
                            Resultado:
                            @if ($exitCode === 0)
                                <span class="green-text text-darken-2">sucesso</span>
                            @else
                                <span class="red-text text-darken-2">falha</span>
                            @endif
                        </span>

                        <p><strong>Comando:</strong> {{ $command }}</p>
                        <p><strong>Codigo de saida:</strong> {{ $exitCode }}</p>
                        <p><strong>Tempo:</strong> {{ $duration }}s</p>
                    </div>
                </div>

                <div class="card black">
                    <div class="card-content white-text">
                        <span class="card-title">Saida do terminal</span>
                        <pre style="white-space: pre-wrap; overflow-x: auto;">{{ $output ?: 'Nenhuma saida retornada.' }}</pre>
                    </div>
                </div>
            @endisset
        </div>
    </div>
</div>
@endsection
