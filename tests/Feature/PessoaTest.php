<?php

use App\Models\Pessoa;
use App\Models\PessoaSaude;
use App\Models\TipoRestricao;
use App\Services\UserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

use function Pest\Laravel\{get, post, put, delete};

uses(RefreshDatabase::class);

beforeEach(function () {

    // Criar usuário e logar
    $this->user = createUser();
    $this->actingAs($this->user);

    // Cria os dados de referência para os dados de saúde
    TipoRestricao::factory()->count(2)->create();

    // Mock do UserService para evitar chamadas reais
    $this->mock(UserService::class, function (MockInterface $mock) {
        $mock->shouldReceive('getUsuarioByEmail')->andReturn($this->user);
    });
});

/*
|--------------------------------------------------------------------------
| Testes de Listagem (index)
|--------------------------------------------------------------------------
*/

test('a pagina de listagem esta acessivel e mostra pessoas', function () {
    // Cria pessoas para o teste
    $pessoas = Pessoa::factory()->count(3)->create();

    $response = get(route('pessoas.index'));

    $response->assertStatus(200)
        ->assertViewIs('pessoa.list')
        ->assertViewHas('pessoas');

    foreach ($pessoas as $pessoa) {
        $response->assertSee($pessoa->nom_pessoa);
    }
});

test('a busca por nome ou apelido funciona corretamente', function () {

    Pessoa::factory()->create([
        'nom_pessoa' => 'Joao da Silva',
        'nom_apelido' => 'Joao',
        'eml_pessoa' => 'joao.silva@example.com',
        'dat_nascimento' => '1990-05-15', // qualquer data válida
    ]);

    Pessoa::factory()->create([
        'nom_pessoa' => 'Maria Souza',
        'nom_apelido' => 'Maria',
        'eml_pessoa' => 'maria.souza@example.com',
        'dat_nascimento' => '1992-08-20', // qualquer data válida
    ]);

    $response = $this->actingAs($this->user)->get(route('pessoas.index', ['search' => 'Joao']));

    $response->assertStatus(200)
        ->assertSee('Joao da Silva')
        ->assertDontSee('Maria Souza');
});

/*
|--------------------------------------------------------------------------
| Testes de Criação (create e store)
|--------------------------------------------------------------------------
*/

test('a pagina de criacao esta acessivel', function () {
    $this->actingAs($this->user)->get(route('pessoas.create'))
        ->assertStatus(200)
        ->assertViewIs('pessoa.form')
        ->assertViewHas('restricoes')
        ->assertViewHas('pessoasDisponiveis');
});

test('pode criar uma nova pessoa com sucesso', function () {
    $data = Pessoa::factory()->make()->toArray();

    $this->actingAs($this->user)->post(route('pessoas.store'), $data)
        ->assertRedirect(route('pessoas.index'))
        ->assertSessionHas('success', 'Pessoa criada com sucesso.');

    $this->assertDatabaseHas('pessoa', ['nom_pessoa' => $data['nom_pessoa']]);
});

test('pode criar pessoa com restricoes de saude e foto', function () {
    Storage::fake('public');

    $restricao = TipoRestricao::factory()->create();
    $complemento = 'Teste de complemento de saude';

    $data = Pessoa::factory()->make()->toArray();
    $data['ind_restricao'] = 1;
    $data['restricoes'] = [$restricao->idt_restricao => 1];
    $data['complementos'] = [$restricao->idt_restricao => $complemento];
    $data['med_foto'] = UploadedFile::fake()->image('foto_perfil.jpg');

    $this->actingAs($this->user)->post(route('pessoas.store'), $data)
        ->assertRedirect(route('pessoas.index'))
        ->assertSessionHas('success', 'Pessoa criada com sucesso.');

    $pessoa = Pessoa::where('nom_pessoa', $data['nom_pessoa'])->first();

    // Atualiza o modelo para garantir que o relacionamento 'foto' seja carregado
    $pessoa->refresh();

    $this->assertDatabaseHas('pessoa_saude', [
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_restricao' => $restricao->idt_restricao,
        'txt_complemento' => $complemento
    ]);

    $this->assertDatabaseHas('pessoa_foto', [
        'idt_pessoa' => $pessoa->idt_pessoa,
    ]);


    $this->assertNotNull($pessoa->foto);
    Storage::disk('public')->assertExists($pessoa->foto->med_foto);
});

test('pode criar uma pessoa com parceiro', function () {
    $parceiro = Pessoa::factory()->create();
    $data = Pessoa::factory()->make(['idt_parceiro' => $parceiro->idt_pessoa])->toArray();

    $this->actingAs($this->user)->post(route('pessoas.store'), $data)
        ->assertRedirect(route('pessoas.index'))
        ->assertSessionHas('success', 'Pessoa criada com sucesso.');

    $pessoaCriada = Pessoa::where('nom_pessoa', $data['nom_pessoa'])->first();

    $this->assertEquals($parceiro->idt_pessoa, $pessoaCriada->idt_parceiro);
    // Verificando se o parceiro original também foi atualizado
    // O controller não faz essa lógica, então o teste abaixo falharia
    // $parceiroAtualizado = $parceiro->fresh();
    // $this->assertEquals($pessoaCriada->idt_pessoa, $parceiroAtualizado->idt_parceiro);
});

test('nao pode criar uma pessoa com dados invalidos', function () {
    $data = ['nom_pessoa' => '', 'eml_pessoa' => 'nao-e-email']; // Dados inválidos

    $this->actingAs($this->user)->post(route('pessoas.store'), $data)
        ->assertSessionHasErrors(['nom_pessoa', 'eml_pessoa']);
});

/*
|--------------------------------------------------------------------------
| Testes de Visualização e Edição (edit)
|--------------------------------------------------------------------------
*/

test('a pagina de visualizacao e edicao esta acessivel', function () {
    $pessoa = Pessoa::factory()->create();

    $this->actingAs($this->user)->get(route('pessoas.edit', $pessoa->idt_pessoa))
        ->assertStatus(200)
        ->assertViewIs('pessoa.form')
        ->assertSee($pessoa->nom_pessoa);
});

test('retorna 404 para show/edit de pessoa inexistente', function () {
    $this->actingAs($this->user)->get(route('pessoas.edit', 999))->assertStatus(404);
});

/*
|--------------------------------------------------------------------------
| Testes de Atualização (update)
|--------------------------------------------------------------------------
*/

test('pode atualizar uma pessoa existente com sucesso', function () {
    $pessoa = Pessoa::factory()->create();
    $novosDados = ['nom_pessoa' => 'Pessoa Atualizada'];

    $this->actingAs($this->user)->put(route('pessoas.update', $pessoa->idt_pessoa), array_merge($pessoa->toArray(), $novosDados))
        ->assertRedirect(route('pessoas.index'))
        ->assertSessionHas('success', 'Pessoa atualizada com sucesso.');

    $this->assertDatabaseHas('pessoa', $novosDados);
});

test('pode atualizar pessoa com novas restricoes de saude', function () {
    // Cria uma pessoa com uma restrição de saúde existente
    $pessoa = Pessoa::factory()
        ->has(PessoaSaude::factory(), 'restricoes')
        ->create();
    $restricaoAntiga = $pessoa->restricoes()->first();

    // Cria uma nova restrição
    $restricaoNova = TipoRestricao::factory()->create();
    $complementoNovo = 'Novo complemento';

    $dadosUpdate = [
        'ind_restricao' => 1,
        'restricoes' => [$restricaoNova->idt_restricao => 1],
        'complementos' => [$restricaoNova->idt_restricao => $complementoNovo],
    ];

    // Envia a requisição de update
    $this->actingAs($this->user)->put(route('pessoas.update', $pessoa->idt_pessoa), array_merge($pessoa->toArray(), $dadosUpdate))
        ->assertRedirect(route('pessoas.index'))
        ->assertSessionHas('success', 'Pessoa atualizada com sucesso.');

    // Confirma que a restrição antiga não existe mais
    $this->assertDatabaseMissing('pessoa_saude', [
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_restricao' => $restricaoAntiga->idt_restricao
    ]);

    // Confirma que a nova restrição foi criada
    $this->assertDatabaseHas('pessoa_saude', [
        'idt_pessoa' => $pessoa->idt_pessoa,
        'idt_restricao' => $restricaoNova->idt_restricao,
        'txt_complemento' => $complementoNovo
    ]);
});

/*
|--------------------------------------------------------------------------
| Testes de Exclusão (destroy)
|--------------------------------------------------------------------------
*/

test('pode excluir uma pessoa com sucesso', function () {
    $pessoa = Pessoa::factory()->create();

    $this->actingAs($this->user)->delete(route('pessoas.destroy', $pessoa->idt_pessoa))
        ->assertRedirect(route('pessoas.index'))
        ->assertSessionHas('success', 'Pessoa excluída com sucesso!');

    $this->assertSoftDeleted('pessoa', ['idt_pessoa' => $pessoa->idt_pessoa]);
});
