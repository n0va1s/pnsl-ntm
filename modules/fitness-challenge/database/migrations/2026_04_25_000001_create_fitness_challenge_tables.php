<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitness_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('scoring_type', 32);
            $table->json('hustle_points')->nullable();
            $table->boolean('is_team_challenge')->default(false);
            $table->string('cover_image_path')->nullable();
            $table->string('invite_code', 16)->unique();
            $table->string('status', 24)->default('upcoming');
            $table->unsignedInteger('max_participants')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fitness_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fitness_challenge_id')->constrained('fitness_challenges')->cascadeOnDelete();
            $table->string('name');
            $table->string('avatar_path')->nullable();
            $table->decimal('total_score', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['fitness_challenge_id', 'name']);
        });

        Schema::create('fitness_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fitness_challenge_id')->constrained('fitness_challenges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fitness_team_id')->nullable()->constrained('fitness_teams')->nullOnDelete();
            $table->decimal('total_score', 12, 2)->default(0);
            $table->unsignedInteger('total_check_ins')->default(0);
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['fitness_challenge_id', 'user_id']);
        });

        Schema::create('fitness_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fitness_challenge_id')->constrained('fitness_challenges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fitness_team_id')->nullable()->constrained('fitness_teams')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('media_path');
            $table->string('media_type', 16);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->unsignedInteger('steps')->nullable();
            $table->string('activity_type')->nullable();
            $table->decimal('score', 12, 2)->default(0);
            $table->string('moderation_status', 24)->default('pending');
            $table->text('moderation_reason')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamp('score_awarded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fitness_challenge_id', 'created_at']);
            $table->index('moderation_status');
        });

        Schema::create('fitness_check_in_likes', function (Blueprint $table) {
            $table->foreignId('fitness_check_in_id')->constrained('fitness_check_ins')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['fitness_check_in_id', 'user_id']);
        });

        Schema::create('fitness_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fitness_check_in_id')->constrained('fitness_check_ins')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_comments');
        Schema::dropIfExists('fitness_check_in_likes');
        Schema::dropIfExists('fitness_check_ins');
        Schema::dropIfExists('fitness_participants');
        Schema::dropIfExists('fitness_teams');
        Schema::dropIfExists('fitness_challenges');
    }
};
