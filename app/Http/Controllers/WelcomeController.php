<?php

namespace App\Http\Controllers;

use App\Modules\Billing\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    use TransformsPlansForFrontend;

    public function __invoke(): Response
    {
        $dbPlans = Plan::active()->ordered()->get();
        $plans = $this->transformPlans($dbPlans);

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'plans' => $plans,
        ]);
    }
}
