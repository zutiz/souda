<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\InvoiceService;
use App\Modules\Billing\Services\PaymentService;
use App\Modules\Billing\Services\PlanService;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    use TransformsPlansForFrontend;

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PlanService $planService,
        private readonly InvoiceService $invoiceService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request): Response
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $subscription = $this->subscriptionService->getTenantSubscription($tenant->id);

        $latestSubscription = Subscription::forTenant($tenant->id)
            ->latest('id')
            ->first();

        $displaySubscription = $subscription ?? $latestSubscription;

        $dbPlans = $this->planService->getActivePlans();
        $plans = $this->transformPlans($dbPlans);

        $subscriptionData = null;
        if ($displaySubscription) {
            $plan = $displaySubscription->plan;
            $status = $displaySubscription->status;

            // Map to values the frontend subscription type expects
            $subscriptionData = [
                'stripe_status' => $status->value,
                'stripe_price' => $plan ? 'plan_'.$plan->id : null,
                'plan_name' => $plan?->name,
                'on_trial' => $displaySubscription->onTrial(),
                'trial_ends_at' => $displaySubscription->trial_ends_at?->toISOString(),
                'on_grace_period' => $status === SubscriptionStatus::Grace,
                'ends_at' => $displaySubscription->expires_at?->toISOString(),
                'active' => $status === SubscriptionStatus::Active,
                'cancelled' => $status === SubscriptionStatus::Cancelled,
                'current_price' => $plan ? [
                    'unit_amount' => $displaySubscription->amount,
                    'currency' => $displaySubscription->currency,
                    'interval' => $displaySubscription->billing_cycle->value,
                ] : null,
                'current_period_start' => $displaySubscription->starts_at?->toISOString(),
                'current_period_end' => $displaySubscription->expires_at?->toISOString(),
                'next_billing_at' => $displaySubscription->next_billing_at?->toISOString(),
                'created_at' => $displaySubscription->created_at->toISOString(),
                'features' => $plan?->features ?? [],
                'limits' => $plan?->limits ?? [],
            ];
        }

        return Inertia::render('billing/index', [
            'plans' => $plans,
            'subscription' => $subscriptionData,
            'on_generic_trial' => $tenant->trial_ends_at !== null && $tenant->trial_ends_at->isFuture(),
            'generic_trial_ends_at' => $tenant->trial_ends_at?->toISOString(),
            'available_gateways' => $this->getAvailableGateways(),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:billing_plans,id'],
            'gateway' => ['required', 'string'],
            'billing_cycle' => ['nullable', 'string', 'in:daily,weekly,monthly,quarterly,yearly'],
        ]);

        /** @var Tenant $tenant */
        $tenant = tenant();

        try {
            $result = $this->subscriptionService->createSubscription(
                tenantId: $tenant->id,
                planId: $validated['plan_id'],
                gateway: $validated['gateway'],
                billingCycle: isset($validated['billing_cycle'])
                    ? BillingCycle::from($validated['billing_cycle'])
                    : null,
                options: [
                    'success_url' => route('billing').'?checkout=success',
                    'cancel_url' => route('billing').'?checkout=cancelled',
                    'customer_name' => $tenant->name,
                    'customer_email' => $request->user()?->email,
                    'product_name' => $result['subscription']->plan?->name ?? 'Subscription',
                    'metadata' => [
                        'tenant_id' => $tenant->id,
                    ],
                ],
            );

            return response()->json([
                'checkout_url' => $result['checkoutUrl'],
                'subscription_id' => $result['subscription']->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $subscription = $this->subscriptionService->getTenantSubscription($tenant->id);

        if (! $subscription) {
            return response()->json(['error' => 'No active subscription found.'], 404);
        }

        $this->subscriptionService->cancelSubscription($subscription);

        return response()->json(['message' => 'Subscription cancelled successfully.']);
    }

    public function invoices(Request $request): Response
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $payments = $this->paymentService->getTenantPayments($tenant->id);

        $invoices = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'invoice_number' => $this->invoiceService->generateInvoiceNumber($payment),
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'gateway' => $payment->gateway,
                'status' => $payment->status->value,
                'paid_at' => $payment->paid_at?->toISOString(),
                'created_at' => $payment->created_at->toISOString(),
            ];
        });

        return Inertia::render('billing/invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function callback(Request $request, string $gateway): JsonResponse
    {
        $transactionId = $request->input('transaction_id') ?? $request->input('tran_id');

        if (! $transactionId) {
            return response()->json(['error' => 'Missing transaction ID.'], 400);
        }

        try {
            $subscription = $this->subscriptionService->verifyAndActivate(
                transactionId: $transactionId,
                gateway: $gateway,
                payload: $request->all(),
            );

            return response()->json([
                'message' => 'Payment verified successfully.',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    private function getAvailableGateways(): array
    {
        $gateways = config('billing.gateways', []);
        $available = [];

        foreach ($gateways as $key => $config) {
            $available[] = [
                'id' => $key,
                'label' => $config['label'] ?? ucfirst($key),
            ];
        }

        return $available;
    }
}
