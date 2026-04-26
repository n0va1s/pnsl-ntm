<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['fitness-challenge.enabled' => true]);
});

it('renderiza dashboard frontend quando addon esta ativo', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $challenge = frontendChallengeFor($user);

    $this->actingAs($user)
        ->get(route('fitness.index'))
        ->assertOk()
        ->assertSee('Desafios Fitness')
        ->assertSee($challenge->name);
});

it('retorna 404 silencioso no frontend quando addon esta desativado', function () {
    config(['fitness-challenge.enabled' => false]);

    $user = User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($user)
        ->get(route('fitness.index'))
        ->assertNotFound();
});

it('renderiza telas principais do desafio fitness', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $challenge = frontendChallengeFor($user);

    $this->actingAs($user)
        ->get(route('fitness.app.challenges.create'))
        ->assertOk()
        ->assertSee('Novo desafio fitness');

    $this->actingAs($user)
        ->get(route('fitness.app.challenges.show', $challenge))
        ->assertOk()
        ->assertSee($challenge->name)
        ->assertSee('Registrar treino');

    $this->actingAs($user)
        ->get(route('fitness.app.check-ins.create', $challenge))
        ->assertOk()
        ->assertSee('Foto ou video da prova');

    $this->actingAs($user)
        ->get(route('fitness.app.ranking', $challenge))
        ->assertOk()
        ->assertSee('Ranking');

    $this->actingAs($user)
        ->get(route('fitness.app.profile'))
        ->assertOk()
        ->assertSee('Historico fitness');
});

function frontendChallengeFor(User $user): FitnessChallenge
{
    $challenge = FitnessChallenge::create([
        'created_by' => $user->id,
        'name' => 'Desafio da manha',
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addWeek()->toDateString(),
        'scoring_type' => ScoringType::TotalWorkouts,
        'invite_code' => 'FRONT123',
        'status' => 'active',
    ]);

    $challenge->participants()->create([
        'user_id' => $user->id,
        'joined_at' => now(),
    ]);

    return $challenge;
}
