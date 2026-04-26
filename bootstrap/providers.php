<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\VoltServiceProvider;
use Modules\FitnessChallenge\FitnessChallengeServiceProvider;
use Modules\Vendinha\VendinhaServiceProvider;

return [
    AppServiceProvider::class,
    FitnessChallengeServiceProvider::class,
    VendinhaServiceProvider::class,
    VoltServiceProvider::class,
    AuthServiceProvider::class,
];
