<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    //
    protected $fillable = [
        'nome',
        'nacionalidade',
        'data_nascimento',
    ];

    protected $table = 'autores';

    public function livros()
    {
        return $this->hasMany(Livro::class, 'autor_id');
    }
}
