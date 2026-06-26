<?php

use App\Http\Controllers\Admin\AppSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', EnsureAdmin::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('admin.dashboard');

        Route::resource('users', UserController::class)
            ->only(['index', 'show', 'destroy']);

        Route::post('users/{user}/restore', [UserController::class, 'restore'])
            ->name('users.restore');

        Route::delete('users/{user}/force', [UserController::class, 'forceDestroy'])
            ->name('users.force-destroy');

        // Pricing routes — keep /admin/pricing/* path for frontend compatibility.
        Route::resource('pricing', PlanController::class)
            ->parameters(['pricing' => 'plan'])
            ->names([
                'index' => 'pricing.index',
                'create' => 'pricing.create',
                'store' => 'pricing.store',
                'show' => 'pricing.show',
                'edit' => 'pricing.edit',
                'update' => 'pricing.update',
                'destroy' => 'pricing.destroy',
            ]);

        Route::post('pricing/reorder', [PlanController::class, 'reorder'])
            ->name('pricing.reorder');

        Route::post('pricing/{plan}/features', [PlanController::class, 'updateFeatures'])
            ->name('pricing.features.update');

        Route::post('pricing/{plan}/prices', [PlanController::class, 'storePrice'])
            ->name('pricing.prices.store');

        Route::put('prices/{price}', [PlanController::class, 'updatePrice'])
            ->name('prices.update');

        Route::delete('prices/{price}', [PlanController::class, 'destroyPrice'])
            ->name('prices.destroy');

        Route::redirect('settings', '/admin/settings/general');
        Route::get('settings/general', [AppSettingsController::class, 'edit'])->name('admin.settings.general');
        Route::post('settings/general', [AppSettingsController::class, 'update'])->name('admin.settings.update');
        Route::get('settings/emails', [AppSettingsController::class, 'editEmails'])->name('admin.settings.emails');
        Route::post('settings/emails', [AppSettingsController::class, 'updateEmails'])->name('admin.settings.emails.update');
        Route::get('settings/social-auth', [AppSettingsController::class, 'editSocialAuth'])->name('admin.settings.social-auth');
        Route::post('settings/social-auth', [AppSettingsController::class, 'updateSocialAuth'])->name('admin.settings.social-auth.update');
    });
