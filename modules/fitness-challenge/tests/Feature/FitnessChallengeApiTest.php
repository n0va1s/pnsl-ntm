<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['fitness-challenge.enabled' => true]);
});

it('cria desafio com participante criador e permite entrada por convite', function () {
    $creator = User::factory()->create();
    $guest = User::factory()->create();

    $response = $this->actingAs($creator)
        ->postJson(route('fitness.challenges.store'), [
            'name' => '30 dias em movimento',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(30)->toDateString(),
            'scoring_type' => ScoringType::TotalMinutes->value,
            'is_team_challenge' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', '30 dias em movimento');

    $challenge = FitnessChallenge::findOrFail($response->json('data.id'));

    expect($challenge->participants()->where('user_id', $creator->id)->exists())->toBeTrue()
        ->and($challenge->invite_code)->not->toBeEmpty();

    $this->actingAs($guest)
        ->postJson(route('fitness.challenges.join', $challenge->invite_code))
        ->assertCreated()
        ->assertJsonPath('data.user_id', $guest->id);
});

it('registra check-in calcula score atualiza ranking e permite interacoes sociais', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();

    $challenge = FitnessChallenge::create([
        'created_by' => $creator->id,
        'name' => 'Corrida da semana',
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addWeek()->toDateString(),
        'scoring_type' => ScoringType::TotalDistance,
        'invite_code' => 'RUN12345',
        'status' => 'active',
    ]);

    $challenge->participants()->create([
        'user_id' => $participant->id,
        'joined_at' => now(),
    ]);

    $checkInResponse = $this->actingAs($participant)
        ->postJson(route('fitness.check-ins.store', $challenge), [
            'title' => 'Treino leve',
            'media_path' => 'fitness/provas/treino-leve.webp',
            'media_type' => 'image',
            'distance_km' => 6.4,
            'activity_type' => 'corrida',
        ])
        ->assertCreated()
        ->assertJsonPath('data.score', 6.4);

    $checkIn = FitnessCheckIn::findOrFail($checkInResponse->json('data.id'));

    expect((float) $challenge->participants()->where('user_id', $participant->id)->first()->total_score)->toBe(6.4);

    $this->actingAs($creator)
        ->postJson(route('fitness.challenges.join', $challenge->invite_code))
        ->assertCreated();

    $this->actingAs($creator)
        ->postJson(route('fitness.check-ins.like', $checkIn))
        ->assertOk()
        ->assertJsonPath('liked', true)
        ->assertJsonPath('likes_count', 1);

    $this->actingAs($creator)
        ->postJson(route('fitness.check-ins.comments.store', $checkIn), ['body' => 'Boa!'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Boa!');

    $this->actingAs($participant)
        ->getJson(route('fitness.leaderboard.individual', $challenge))
        ->assertOk()
        ->assertJsonPath('data.0.user_id', $participant->id)
        ->assertJsonPath('data.0.total_score', 6.4);
});

it('cria time e atualiza ranking por equipes', function () {
    $creator = User::factory()->create();

    $challenge = FitnessChallenge::create([
        'created_by' => $creator->id,
        'name' => 'Times ativos',
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addWeek()->toDateString(),
        'scoring_type' => ScoringType::TotalWorkouts,
        'invite_code' => 'TEAM1234',
        'status' => 'active',
        'is_team_challenge' => true,
    ]);

    $challenge->participants()->create([
        'user_id' => $creator->id,
        'joined_at' => now(),
    ]);

    $teamResponse = $this->actingAs($creator)
        ->postJson(route('fitness.teams.store', $challenge), ['name' => 'Manha forte'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Manha forte');

    $this->postJson(route('fitness.teams.join', [$challenge, $teamResponse->json('data.id')]))
        ->assertOk()
        ->assertJsonPath('data.fitness_team_id', $teamResponse->json('data.id'));

    $this->postJson(route('fitness.check-ins.store', $challenge), [
        'title' => 'Sessao concluida',
        'media_path' => 'fitness/provas/sessao.webp',
        'media_type' => 'image',
    ])->assertCreated();

    $this->getJson(route('fitness.leaderboard.teams', $challenge))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Manha forte')
        ->assertJsonPath('data.0.total_score', 1);
});
