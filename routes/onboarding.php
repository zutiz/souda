<?php

declare(strict_types=1);

use App\Modules\Onboarding\Http\Controllers\BusinessTypeController;
use App\Modules\Onboarding\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'start'])->name('onboarding.start');
    Route::post('/onboarding/select-type', [OnboardingController::class, 'selectType'])->name('onboarding.select-type');
    Route::get('/onboarding/provision', [OnboardingController::class, 'provision'])->name('onboarding.provision');
    Route::post('/onboarding/run', [OnboardingController::class, 'run'])->name('onboarding.run');
    Route::get('/onboarding/{tenant}/progress', [OnboardingController::class, 'progress'])->name('onboarding.progress');

    Route::get('/api/business-types', [BusinessTypeController::class, 'index'])->name('api.business-types.index');
});
