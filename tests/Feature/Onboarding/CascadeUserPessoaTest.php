<?php

use App\Mail\BoasVindasMail;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('criar user cria pessoa vinculada sem enviar boas vindas duplicadas', function () {
    Mail::fake();

    $user = User::factory()->create([
        'name' => 'Maria Onboarding',
        'email' => 'maria.onboarding@example.com',
        'role' => User::ROLE_USER,
    ]);

    $pessoa = Pessoa::where('idt_usuario', $user->id)->first();

    expect($pessoa)->not->toBeNull()
        ->and($pessoa->nom_pessoa)->toBe('Maria Onboarding')
        ->and($pessoa->eml_pessoa)->toBe('maria.onboarding@example.com')
        ->and($pessoa->dat_nascimento->format('Y-m-d'))->toBe('1900-01-01');

    Mail::assertNothingSent();
});

it('criar pessoa sem usuario cria user vinculado com senha por nascimento e boas vindas', function () {
    Mail::fake();

    $pessoa = Pessoa::create([
        'nom_pessoa' => 'Joao Cascade',
        'eml_pessoa' => 'joao.cascade@example.com',
        'dat_nascimento' => '1990-01-31',
        'tel_pessoa' => '(61) 99999-9999',
    ]);

    $pessoa->refresh();
    $user = User::where('email', 'joao.cascade@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($pessoa->idt_usuario)->toBe($user->id)
        ->and($user->name)->toBe('Joao Cascade')
        ->and($user->role)->toBe(User::ROLE_USER)
        ->and(Hash::check('19900131', $user->password))->toBeTrue();

    Mail::assertSent(BoasVindasMail::class, function (BoasVindasMail $mail) use ($user) {
        return $mail->user->is($user)
            && $mail->senhaTemporaria === '19900131';
    });
});
