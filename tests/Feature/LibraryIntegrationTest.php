<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Biblioteca;
use App\Models\Livro;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_biblioteca_can_be_created_updated_listed_and_deleted(): void
    {
        $user = User::factory()->create();

        $createResponse = $this->post(route('bibliotecas.store'), [
            'created_by' => $user->id,
            'nome' => 'Biblioteca Central',
            'endereco' => 'Rua das Letras, 100',
            'email' => 'central@example.com',
        ]);

        $createResponse->assertRedirect(route('bibliotecas.index'));
        $this->assertDatabaseHas('bibliotecas', [
            'created_by' => $user->id,
            'nome' => 'Biblioteca Central',
            'endereco' => 'Rua das Letras, 100',
            'email' => 'central@example.com',
        ]);

        $biblioteca = Biblioteca::where('nome', 'Biblioteca Central')->firstOrFail();

        $this->get(route('bibliotecas.index', ['nome' => 'Central']))
            ->assertOk()
            ->assertSee('Biblioteca Central');

        $updateResponse = $this->put(route('bibliotecas.update', ['id' => $biblioteca->id]), [
            'created_by' => $user->id,
            'nome' => 'Biblioteca Comunitaria',
            'endereco' => 'Avenida Principal, 200',
            'email' => 'comunitaria@example.com',
        ]);

        $updateResponse->assertRedirect(route('bibliotecas.index'));
        $this->assertDatabaseHas('bibliotecas', [
            'id' => $biblioteca->id,
            'nome' => 'Biblioteca Comunitaria',
            'endereco' => 'Avenida Principal, 200',
            'email' => 'comunitaria@example.com',
        ]);

        $deleteResponse = $this->delete(route('bibliotecas.destroy', ['id' => $biblioteca->id]));

        $deleteResponse->assertRedirect(route('bibliotecas.index'));
        $this->assertDatabaseMissing('bibliotecas', ['id' => $biblioteca->id]);
    }

    public function test_biblioteca_validation_blocks_invalid_payload(): void
    {
        $response = $this->from(route('bibliotecas.create'))->post(route('bibliotecas.store'), [
            'created_by' => 999,
            'nome' => '',
            'email' => 'email-invalido',
        ]);

        $response->assertRedirect(route('bibliotecas.create'));
        $response->assertSessionHasErrors(['created_by', 'nome', 'email']);
        $this->assertDatabaseCount('bibliotecas', 0);
    }

    public function test_missing_biblioteca_returns_404_on_update_and_delete(): void
    {
        $this->put(route('bibliotecas.update', ['id' => 999]), [
            'nome' => 'Nao existe',
        ])->assertNotFound();

        $this->delete(route('bibliotecas.destroy', ['id' => 999]))
            ->assertNotFound();
    }

    public function test_pessoa_can_be_created_updated_and_deleted(): void
    {
        $createResponse = $this->post(route('pessoas.store'), [
            'name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'telefone' => '11999999999',
            'matricula' => '20260001',
            'password' => 'secret123',
            'confirmPassword' => 'secret123',
        ]);

        $createResponse->assertRedirect(route('pessoas.index'));
        $this->assertDatabaseHas('pessoas', [
            'name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'matricula' => '20260001',
        ]);

        $pessoa = Pessoa::where('email', 'ana@example.com')->firstOrFail();

        $updateResponse = $this->put(route('pessoas.update', ['pessoa' => $pessoa->id]), [
            'name' => 'Ana Maria Souza',
            'email' => 'ana.maria@example.com',
            'telefone' => '11888888888',
            'matricula' => '20260002',
        ]);

        $updateResponse->assertRedirect(route('pessoas.index'));
        $this->assertDatabaseHas('pessoas', [
            'id' => $pessoa->id,
            'name' => 'Ana Maria Souza',
            'email' => 'ana.maria@example.com',
        ]);

        $deleteResponse = $this->delete(route('pessoas.destroy', ['pessoa' => $pessoa->id]));

        $deleteResponse->assertRedirect(route('pessoas.index'));
        $this->assertDatabaseMissing('pessoas', ['id' => $pessoa->id]);
    }

    public function test_pessoa_password_confirmation_and_duplicate_email_are_validated(): void
    {
        Pessoa::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->from(route('pessoas.create'))->post(route('pessoas.store'), [
            'name' => 'Pessoa Invalida',
            'email' => 'duplicado@example.com',
            'password' => 'secret123',
            'confirmPassword' => 'diferente',
        ]);

        $response->assertRedirect(route('pessoas.create'));
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertDatabaseCount('pessoas', 1);
    }

    public function test_autor_validation_crud_and_deleting_cascades_books(): void
    {
        $invalidResponse = $this->from(route('autores.create'))->post(route('autores.store'), [
            'nome' => '',
            'data_nascimento' => 'data-invalida',
        ]);

        $invalidResponse->assertRedirect(route('autores.create'));
        $invalidResponse->assertSessionHasErrors(['nome', 'data_nascimento']);
        $this->assertDatabaseCount('autores', 0);

        $createResponse = $this->post(route('autores.store'), [
            'nome' => 'Machado de Assis',
            'nacionalidade' => 'Brasileira',
            'data_nascimento' => '1839-06-21',
        ]);

        $createResponse->assertRedirect(route('autores.index'));
        $autor = Autor::where('nome', 'Machado de Assis')->firstOrFail();

        $this->put(route('autores.update', ['id' => $autor->id]), [
            'nome' => 'Joaquim Maria Machado de Assis',
            'nacionalidade' => 'Brasileira',
            'data_nascimento' => '1839-06-21',
        ])->assertRedirect(route('autores.index'));

        $this->assertDatabaseHas('autores', [
            'id' => $autor->id,
            'nome' => 'Joaquim Maria Machado de Assis',
        ]);

        Livro::create([
            'autor_id' => $autor->id,
            'titulo' => 'Dom Casmurro',
            'isbn' => '9788535910663',
            'data_publicacao' => '1899-01-01',
        ]);

        $this->delete(route('autores.destroy', ['autore' => $autor->id]))
            ->assertRedirect(route('autores.index'));

        $this->assertDatabaseMissing('autores', ['id' => $autor->id]);
        $this->assertDatabaseMissing('livros', ['titulo' => 'Dom Casmurro']);
    }

    public function test_livro_validation_crud_and_not_found(): void
    {
        $autor = Autor::create([
            'nome' => 'Clarice Lispector',
            'nacionalidade' => 'Brasileira',
            'data_nascimento' => '1920-12-10',
        ]);

        $invalidResponse = $this->from(route('livros.create'))->post(route('livros.store'), [
            'autor_id' => 999,
            'titulo' => '',
            'isbn' => '',
            'data_publicacao' => 'data-invalida',
        ]);

        $invalidResponse->assertRedirect(route('livros.create'));
        $invalidResponse->assertSessionHasErrors(['autor_id', 'titulo', 'isbn', 'data_publicacao']);
        $this->assertDatabaseCount('livros', 0);

        $createResponse = $this->post(route('livros.store'), [
            'autor_id' => $autor->id,
            'titulo' => 'A Hora da Estrela',
            'isbn' => '9788532508122',
            'data_publicacao' => '1977-01-01',
        ]);

        $createResponse->assertRedirect(route('livros.index'));
        $livro = Livro::where('titulo', 'A Hora da Estrela')->firstOrFail();

        $this->put(route('livros.update', ['id' => $livro->id]), [
            'autor_id' => $autor->id,
            'titulo' => 'A Hora da Estrela - Edicao Revista',
            'isbn' => '9788532508122',
            'data_publicacao' => '1977-01-01',
        ])->assertRedirect(route('livros.index'));

        $this->assertDatabaseHas('livros', [
            'id' => $livro->id,
            'titulo' => 'A Hora da Estrela - Edicao Revista',
        ]);

        $this->delete(route('livros.destroy', ['livro' => $livro->id]))
            ->assertRedirect(route('livros.index'));

        $this->assertDatabaseMissing('livros', ['id' => $livro->id]);

        $this->get(route('livros.show', ['livro' => 999]))
            ->assertNotFound();
    }

    public function test_pessoa_can_be_attached_to_biblioteca_once(): void
    {
        $user = User::factory()->create();
        $biblioteca = Biblioteca::create([
            'created_by' => $user->id,
            'nome' => 'Biblioteca de Testes',
        ]);
        $pessoa = Pessoa::factory()->create();

        $firstResponse = $this->post(route('bibliotecas.pessoas.store', ['biblioteca' => $biblioteca->id]), [
            'pessoa_id' => $pessoa->id,
        ]);

        $firstResponse->assertRedirect(route('bibliotecas.edit', ['id' => $biblioteca->id]));
        $this->assertDatabaseHas('biblioteca_pessoa', [
            'biblioteca_id' => $biblioteca->id,
            'pessoa_id' => $pessoa->id,
        ]);

        $secondResponse = $this->post(route('bibliotecas.pessoas.store', ['biblioteca' => $biblioteca->id]), [
            'pessoa_id' => $pessoa->id,
        ]);

        $secondResponse->assertRedirect(route('bibliotecas.pessoas.create', ['biblioteca' => $biblioteca->id]));
        $secondResponse->assertSessionHas('error');
        $this->assertDatabaseCount('biblioteca_pessoa', 1);
    }
}
