<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PessoaController extends Controller
{
    public function index()
    {
        $pessoas = Pessoa::all();

        return view('pessoas.index', compact('pessoas'));
    }

    public function create()
    {
        return view('pessoas.new');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pessoas,email',
            'telefone' => 'nullable|string|max:255',
            'matricula' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|same:confirmPassword',
            'confirmPassword' => 'required|string|min:6',
        ]);

        Pessoa::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telefone' => $validated['telefone'] ?? null,
            'matricula' => $validated['matricula'] ?? null,
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('pessoas.index')->with('message', 'Pessoa criada com sucesso!');
    }

    public function edit($id)
    {
        $pessoa = Pessoa::find($id);
        if (!$pessoa) {
            return redirect()->route('pessoas.index')->with('error', 'Pessoa nao encontrada');
        }

        return view('pessoas.edit', compact('pessoa'));
    }

    public function update(Request $request, $id)
    {
        $pessoa = Pessoa::find($id);
        if (!$pessoa) {
            return response()->json(['error' => 'Pessoa nao encontrada'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pessoas', 'email')->ignore($pessoa->id),
            ],
            'telefone' => 'nullable|string|max:255',
            'matricula' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|same:confirmPassword',
            'confirmPassword' => 'nullable|string|min:6',
        ]);

        $pessoa->name = $validated['name'];
        $pessoa->email = $validated['email'];
        $pessoa->telefone = $validated['telefone'] ?? null;
        $pessoa->matricula = $validated['matricula'] ?? null;

        if (!empty($validated['password'])) {
            $pessoa->password = bcrypt($validated['password']);
        }

        $pessoa->save();

        return redirect()->route('pessoas.index')->with('message', 'Pessoa atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $pessoa = Pessoa::find($id);
        if (!$pessoa) {
            return response()->json(['error' => 'Pessoa nao encontrada'], 404);
        }

        $pessoa->delete();

        return redirect()->route('pessoas.index')->with('message', 'Pessoa excluida com sucesso!');
    }
}
