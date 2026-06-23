<?php

namespace App\Http\Controllers;

use App\Models\Biblioteca;
use App\Models\User;
use Illuminate\Http\Request;

class BibliotecasController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('nome');
        $bibliotecas = Biblioteca::where('nome', 'like', '%' . $busca . '%')->get();

        return view('bibliotecas.index', ['bibliotecas' => $bibliotecas]);
    }

    public function create()
    {
        $users = User::all();

        return view('bibliotecas.new', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'created_by' => 'required|exists:users,id',
            'nome' => 'required|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            Biblioteca::create($validated);
        } catch (\Exception $e) {
            return redirect()
                ->route('bibliotecas.create')
                ->with('error', 'Erro ao criar a biblioteca: verifique as informacoes enviadas.');
        }

        return redirect()->route('bibliotecas.index')->with('message', 'Biblioteca criada com sucesso');
    }

    public function edit(int $id)
    {
        $users = User::all();

        $biblioteca = Biblioteca::where('id', $id)->first();
        if (!$biblioteca) {
            return redirect()->route('bibliotecas.index')->with('error', 'Biblioteca nao encontrada');
        }

        return view('bibliotecas.edit', ['biblioteca' => $biblioteca, 'users' => $users]);
    }

    public function update(Request $request, int $id)
    {
        $biblioteca = Biblioteca::find($id);
        if (!$biblioteca) {
            return response()->json(['error' => 'Biblioteca nao encontrada'], 404);
        }

        $validated = $request->validate([
            'created_by' => 'sometimes|required|exists:users,id',
            'nome' => 'sometimes|required|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $biblioteca->update($validated);
        } catch (\Exception $e) {
            return redirect()
                ->route('bibliotecas.edit', ['id' => $id])
                ->with('error', 'Erro ao atualizar a biblioteca: verifique as informacoes enviadas.');
        }

        return redirect()->route('bibliotecas.index')->with('message', 'Biblioteca atualizada com sucesso');
    }

    public function destroy(int $id)
    {
        $biblioteca = Biblioteca::find($id);
        if (!$biblioteca) {
            return response()->json(['error' => 'Biblioteca nao encontrada'], 404);
        }

        try {
            $biblioteca->delete();
        } catch (\Exception $e) {
            return redirect()->route('bibliotecas.index')->with('message', 'Erro ao excluir a biblioteca: verifique o ID');
        }

        return redirect()->route('bibliotecas.index')->with('message', 'Biblioteca excluida com sucesso');
    }
}
