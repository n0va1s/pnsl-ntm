<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['fitness-challenge.enabled' => true]);
    config(['fitness-challenge.media.require_manual_review' => true]);
});

it('cria desafio com participante criador e permite entrada por convite', function () {
    $creator = User::factory()->create();
    $guest = User::factory()->create();

    $response = $this->actingAs($creator)
        ->postJson(route('desafios.challenges.store'), [
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
        ->postJson(route('desafios.challenges.join', $challenge->invite_code))
        ->assertCreated()
        ->assertJsonPath('data.user_id', $guest->id);
});

it('registra cumprimento de desafio, exige moderacao antes de pontuar e permite interacoes sociais apos aprovacao', function () {
    $creator = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $participant = User::factory()->create();

    $challenge = FitnessChallenge::create([
        'created_by' => $creator->id,
        'name' => 'Terco diario por 60 dias',
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addWeek()->toDateString(),
        'scoring_type' => ScoringType::TotalWorkouts,
        'invite_code' => 'TERCO123',
        'status' => 'active',
    ]);

    $challenge->participants()->create([
        'user_id' => $participant->id,
        'joined_at' => now(),
    ]);

    $checkInResponse = $this->actingAs($participant)
        ->postJson(route('desafios.check-ins.store', $challenge), [
            'title' => 'Dia 1 do terco',
            'media_path' => 'desafios/provas/terco-dia-1.webp',
            'media_type' => 'image',
            'duration_minutes' => 25,
            'activity_type' => 'terco',
        ])
        ->assertCreated()
        ->assertJsonPath('data.score', 0)
        ->assertJsonPath('data.moderation_status', 'pending');

    $checkIn = FitnessCheckIn::findOrFail($checkInResponse->json('data.id'));

    expect((float) $challenge->participants()->where('user_id', $participant->id)->first()->total_score)->toBe(0.0);

    $this->actingAs($creator)
        ->postJson(route('desafios.challenges.join', $challenge->invite_code))
        ->assertCreated();

    $this->actingAs($creator)
        ->postJson(route('desafios.moderation.check-ins.approve', $checkIn))
        ->assertOk()
        ->assertJsonPath('data.score', 1)
        ->assertJsonPath('data.moderation_status', 'approved');

    $checkIn->refresh();

    $this->actingAs($creator)
        ->postJson(route('desafios.check-ins.like', $checkIn))
        ->assertOk()
        ->assertJsonPath('liked', true)
        ->assertJsonPath('likes_count', 1);

    $this->actingAs($creator)
        ->postJson(route('desafios.check-ins.comments.store', $checkIn), ['body' => 'Boa!'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Boa!');

    $this->actingAs($participant)
        ->getJson(route('desafios.leaderboard.individual', $challenge))
        ->assertOk()
        ->assertJsonPath('data.0.user_id', $participant->id)
        ->assertJsonPath('data.0.total_score', 1);
});

it('cria time e atualiza ranking por equipes', function () {
    $creator = User::factory()->create(['role' => User::ROLE_ADMIN]);

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
        ->postJson(route('desafios.teams.store', $challenge), ['name' => 'Manha forte'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Manha forte');

    $this->postJson(route('desafios.teams.join', [$challenge, $teamResponse->json('data.id')]))
        ->assertOk()
        ->assertJsonPath('data.fitness_team_id', $teamResponse->json('data.id'));

    $checkInResponse = $this->postJson(route('desafios.check-ins.store', $challenge), [
        'title' => 'Sessao concluida',
        'media_path' => 'desafios/provas/sessao.webp',
        'media_type' => 'image',
    ])->assertCreated();

    $checkIn = FitnessCheckIn::findOrFail($checkInResponse->json('data.id'));

    $this->postJson(route('desafios.moderation.check-ins.approve', $checkIn))
        ->assertOk();

    $this->getJson(route('desafios.leaderboard.teams', $challenge))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Manha forte')
        ->assertJsonPath('data.0.total_score', 1);
});
