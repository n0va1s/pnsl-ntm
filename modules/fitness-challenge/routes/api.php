<?php

use Illuminate\Support\Facades\Route;
use Modules\FitnessChallenge\Http\Controllers\ChallengeController;
use Modules\FitnessChallenge\Http\Controllers\CheckInController;
use Modules\FitnessChallenge\Http\Controllers\LeaderboardController;
use Modules\FitnessChallenge\Http\Controllers\TeamController;

Route::get('challenges', [ChallengeController::class, 'index'])->name('challenges.index');
Route::post('challenges', [ChallengeController::class, 'store'])->name('challenges.store');
Route::post('challenges/join/{inviteCode}', [ChallengeController::class, 'join'])->name('challenges.join');
Route::get('challenges/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
Route::put('challenges/{challenge}', [ChallengeController::class, 'update'])->name('challenges.update');
Route::delete('challenges/{challenge}', [ChallengeController::class, 'destroy'])->name('challenges.destroy');
Route::delete('challenges/{challenge}/leave', [ChallengeController::class, 'leave'])->name('challenges.leave');

Route::get('challenges/{challenge}/check-ins', [CheckInController::class, 'index'])->name('check-ins.index');
Route::post('challenges/{challenge}/check-ins', [CheckInController::class, 'store'])->name('check-ins.store');
Route::delete('check-ins/{checkIn}', [CheckInController::class, 'destroy'])->name('check-ins.destroy');
Route::post('check-ins/{checkIn}/like', [CheckInController::class, 'like'])->name('check-ins.like');
Route::post('check-ins/{checkIn}/comments', [CheckInController::class, 'comment'])->name('check-ins.comments.store');

Route::get('challenges/{challenge}/leaderboard', [LeaderboardController::class, 'individual'])->name('leaderboard.individual');
Route::get('challenges/{challenge}/leaderboard/teams', [LeaderboardController::class, 'teams'])->name('leaderboard.teams');
Route::post('challenges/{challenge}/teams', [TeamController::class, 'store'])->name('teams.store');
Route::post('challenges/{challenge}/teams/{team}/join', [TeamController::class, 'join'])->name('teams.join');
