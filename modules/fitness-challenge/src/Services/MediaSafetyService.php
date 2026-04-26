<?php

namespace Modules\FitnessChallenge\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\FitnessChallenge\Enums\ModerationStatus;

class MediaSafetyService
{
    /**
     * @param  array<string, mixed>  $validated
     * @return array{media_path: string, media_type: string, moderation_status: string, moderation_reason: string|null}
     */
    public function prepare(array $validated, int $userId, ?UploadedFile $media = null): array
    {
        $this->rejectBlockedTerms($validated);

        $status = config('fitness-challenge.media.require_manual_review', true)
            ? ModerationStatus::Pending
            : ModerationStatus::Approved;

        if (! $media) {
            return [
                'media_path' => (string) $validated['media_path'],
                'media_type' => (string) $validated['media_type'],
                'moderation_status' => $status->value,
                'moderation_reason' => $status === ModerationStatus::Pending ? 'Aguardando revisão manual da prova.' : null,
            ];
        }

        $mime = (string) $media->getMimeType();
        $mediaType = $this->detectMediaType($mime);
        $this->validateSize($media, $mediaType);

        $directory = $status === ModerationStatus::Pending ? 'fitness-challenge/pending' : 'fitness-challenge/approved';
        $extension = $media->extension() ?: ($mediaType === 'image' ? 'jpg' : 'mp4');
        $filename = sprintf('%s/%d/%s.%s', $directory, $userId, (string) Str::uuid(), $extension);

        $disk = config('fitness-challenge.media.disk');
        Storage::disk($disk)->put($filename, $media->getContent());

        return [
            'media_path' => $filename,
            'media_type' => $mediaType,
            'moderation_status' => $status->value,
            'moderation_reason' => $status === ModerationStatus::Pending ? 'Aguardando revisão manual da prova.' : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function rejectBlockedTerms(array $payload): void
    {
        $text = Str::lower(implode(' ', array_filter([
            $payload['title'] ?? null,
            $payload['description'] ?? null,
            $payload['activity_type'] ?? null,
            $payload['media_path'] ?? null,
        ], fn ($value) => is_string($value) && $value !== '')));

        foreach (config('fitness-challenge.media.blocked_terms', []) as $term) {
            if ($term !== '' && Str::contains($text, Str::lower($term))) {
                throw ValidationException::withMessages([
                    'media' => 'A prova enviada precisa passar por uma revisão: evite conteúdo íntimo, sexual ou comprometedor.',
                ]);
            }
        }
    }

    private function detectMediaType(string $mime): string
    {
        if (in_array($mime, config('fitness-challenge.media.allowed_image_mimes', []), true)) {
            return 'image';
        }

        if (in_array($mime, config('fitness-challenge.media.allowed_video_mimes', []), true)) {
            return 'video';
        }

        throw ValidationException::withMessages([
            'media' => 'Formato de prova inválido. Use JPEG, PNG, WEBP ou MP4.',
        ]);
    }

    private function validateSize(UploadedFile $media, string $mediaType): void
    {
        $maxKb = $mediaType === 'image'
            ? (int) config('fitness-challenge.media.max_image_kb', 10240)
            : (int) config('fitness-challenge.media.max_video_kb', 51200);

        if (($media->getSize() / 1024) > $maxKb) {
            throw ValidationException::withMessages([
                'media' => "A prova excede o limite de {$maxKb}KB.",
            ]);
        }
    }
}
