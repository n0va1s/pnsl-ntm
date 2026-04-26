<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\FitnessChallenge\Enums\ScoringType;
use Modules\FitnessChallenge\Models\FitnessChallenge;
use Modules\FitnessChallenge\Models\FitnessCheckIn;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['fitness-challenge.enabled' => true]);
    config(['fitness-challenge.media.require_manual_review' => true]);
    Storage::fake('local');
});

it('mantem check-ins pendentes fora do feed e do ranking ate aprovacao', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $participant = User::factory()->create(['role' => User::ROLE_USER]);
    $challenge = fitnessChallengeFor($admin, $participant);

    $response = $this->actingAs($participant)
        ->postJson(route('fitness.check-ins.store', $challenge), [
            'title' => 'Treino honesto',
            'media_path' => 'fitness/provas/treino-honesto.webp',
            'media_type' => 'image',
            'duration_minutes' => 45,
        ])
        ->assertCreated()
        ->assertJsonPath('data.moderation_status', 'pending')
        ->assertJsonPath('data.score', 0);

    $checkIn = FitnessCheckIn::findOrFail($response->json('data.id'));

    $this->actingAs($participant)
        ->getJson(route('fitness.check-ins.index', $challenge))
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->actingAs($participant)
        ->getJson(route('fitness.leaderboard.individual', $challenge))
        ->assertOk()
        ->assertJsonPath('data.0.total_score', 0);

    $this->actingAs($admin)
        ->postJson(route('fitness.moderation.check-ins.approve', $checkIn))
        ->assertOk()
        ->assertJsonPath('data.moderation_status', 'approved')
        ->assertJsonPath('data.score', 45);

    $this->actingAs($participant)
        ->getJson(route('fitness.check-ins.index', $challenge))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('bloqueia termos sexuais ou comprometedores antes de criar check-in', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $participant = User::factory()->create(['role' => User::ROLE_USER]);
    $challenge = fitnessChallengeFor($admin, $participant);

    $this->actingAs($participant)
        ->postJson(route('fitness.check-ins.store', $challenge), [
            'title' => 'foto nude pos treino',
            'media_path' => 'fitness/provas/nude.webp',
            'media_type' => 'image',
            'duration_minutes' => 30,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('media');

    expect(FitnessCheckIn::count())->toBe(0);
});

it('permite upload de imagem valida e salva em quarentena pendente', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $participant = User::factory()->create(['role' => User::ROLE_USER]);
    $challenge = fitnessChallengeFor($admin, $participant);

    $response = $this->actingAs($participant)
        ->postJson(route('fitness.check-ins.store', $challenge), [
            'title' => 'Musculacao concluida',
            'media' => UploadedFile::fake()->createWithContent(
                'prova.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
            'duration_minutes' => 50,
        ])
        ->assertCreated()
        ->assertJsonPath('data.media_type', 'image')
        ->assertJsonPath('data.moderation_status', 'pending');

    Storage::disk('local')->assertExists($response->json('data.media_path'));
});

it('impede usuario comum de moderar check-ins', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $participant = User::factory()->create(['role' => User::ROLE_USER]);
    $other = User::factory()->create(['role' => User::ROLE_USER]);
    $challenge = fitnessChallengeFor($admin, $participant);

    $response = $this->actingAs($participant)
        ->postJson(route('fitness.check-ins.store', $challenge), [
            'title' => 'Treino para revisar',
            'media_path' => 'fitness/provas/revisar.webp',
            'media_type' => 'image',
        ]);

    $checkIn = FitnessCheckIn::findOrFail($response->json('data.id'));

    $this->actingAs($other)
        ->postJson(route('fitness.moderation.check-ins.approve', $checkIn))
        ->assertForbidden();
});

function fitnessChallengeFor(User $admin, User $participant): FitnessChallenge
{
    $challenge = FitnessChallenge::create([
        'created_by' => $admin->id,
        'name' => 'Desafio moderado',
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addWeek()->toDateString(),
        'scoring_type' => ScoringType::TotalMinutes,
        'invite_code' => 'MOD'.str_pad((string) $participant->id, 5, '0', STR_PAD_LEFT),
        'status' => 'active',
    ]);

    $challenge->participants()->create([
        'user_id' => $participant->id,
        'joined_at' => now(),
    ]);

    return $challenge;
}
