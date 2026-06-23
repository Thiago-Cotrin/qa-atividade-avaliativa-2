<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Http\Request;

class LivroController extends Controller
{
    public function index()
    {
        $livros = Livro::all();

        return view('livros.index', compact('livros'));
    }

    public function show($id)
    {
        $livro = Livro::findOrFail($id);

        return view('livros.show', compact('livro'));
    }

    public function create()
    {
        $autores = Autor::all();

        return view('livros.create', compact(['autores']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'autor_id' => 'required|exists:autores,id',
            'titulo' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'data_publicacao' => 'required|date',
        ]);

        Livro::create($validated);

        return redirect()->route('livros.index')->with('message', 'Livro criado com sucesso');
    }

    public function edit(Request $request, $id)
    {
        $livro = Livro::findOrFail($id);
        $autores = Autor::all();

        return view('livros.edit', compact('livro', 'autores'));
    }

    public function update(Request $request, $id)
    {
        $livro = Livro::findOrFail($id);

        $validated = $request->validate([
            'autor_id' => 'required|exists:autores,id',
            'titulo' => 'required|string|max:255',
            'isbn' => 'required|string|max:255',
            'data_publicacao' => 'required|date',
        ]);

        $livro->update($validated);

        return redirect()->route('livros.index')->with('message', 'Livro atualizado com sucesso');
    }

    public function destroy($id)
    {
        $livro = Livro::findOrFail($id);
        $livro->delete();

        return redirect()->route('livros.index')->with('message', 'Livro excluido com sucesso');
    }
}
