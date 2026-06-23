# Relatorio da Atividade Avaliativa 2 - Testes de Integracao

## Objetivo

Foram implementados testes de integracao para validar os principais fluxos HTTP do sistema de biblioteca em Laravel, cobrindo cadastro, listagem, atualizacao, exclusao, validacoes de entrada, respostas de erro e regras de relacionamento.

## O que foi testado

- Bibliotecas:
  - criacao com usuario responsavel valido;
  - listagem com filtro por nome;
  - atualizacao;
  - exclusao;
  - validacao de usuario inexistente, nome obrigatorio e e-mail invalido;
  - retorno 404 ao tentar atualizar ou excluir biblioteca inexistente.

- Pessoas:
  - criacao com senha e confirmacao;
  - atualizacao de dados;
  - exclusao;
  - bloqueio de e-mail duplicado;
  - bloqueio de senha com confirmacao divergente.

- Autores:
  - validacao de nome obrigatorio e data de nascimento invalida;
  - criacao;
  - atualizacao;
  - exclusao;
  - confirmacao de exclusao em cascata dos livros do autor.

- Livros:
  - validacao de autor inexistente, titulo obrigatorio, ISBN obrigatorio e data invalida;
  - criacao;
  - atualizacao;
  - exclusao;
  - retorno 404 ao buscar livro inexistente.

- Associacao biblioteca-pessoa:
  - associacao de uma pessoa a uma biblioteca;
  - bloqueio de associacao duplicada.

## Problemas encontrados

- O projeto nao possuia rotas em `routes/api.php`; os testes foram feitos sobre as rotas HTTP existentes em `routes/web.php`.
- Alguns controllers nao validavam entrada antes de persistir dados.
- `PessoaController::destroy` estava vazio, impossibilitando testar o ciclo CRUD completo de pessoas.
- `AutorController` tinha rota de exclusao pelo resource, mas nao possuia metodo `destroy`.
- O model `Autor` nao permitia preencher `data_nascimento` via `create/update`.
- O model `Livro` nao tinha `autor_id` e `data_publicacao` no `$fillable`, apesar desses campos serem usados pelo controller.
- Algumas mensagens originais tinham problemas de codificacao; as mensagens novas foram mantidas sem acentos para evitar inconsistencias.

## Arquivos alterados

- `app/Http/Controllers/BibliotecasController.php`
- `app/Http/Controllers/PessoaController.php`
- `app/Http/Controllers/LivroController.php`
- `app/Http/Controllers/AutorController.php`
- `app/Models/Autor.php`
- `app/Models/Livro.php`
- `tests/Feature/LibraryIntegrationTest.php`
- `.github/workflows/tests.yml`

## Como executar localmente

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan test
```

Para cobertura de codigo:

```bash
php artisan test --coverage --min=0
```

## Automacao

Foi criado o workflow `.github/workflows/tests.yml`, que executa os testes automaticamente em `pull_request` e em `push` nas branches `master` e `develop`.
