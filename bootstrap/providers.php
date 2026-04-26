<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\VoltServiceProvider;
use Modules\FitnessChallenge\FitnessChallengeServiceProvider;

return [
    AppServiceProvider::class,
    FitnessChallengeServiceProvider::class,
    VoltServiceProvider::class,
    AuthServiceProvider::class,
];
