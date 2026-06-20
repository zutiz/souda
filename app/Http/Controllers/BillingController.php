<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\InvoiceService;
use App\Modules\Billing\Services\PaymentService;
use App\Modules\Billing\Services\PlanService;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        /** @var Tenant|null $tenant */
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'No tenant associated with your account.');
        }

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
            'trial_used' => (bool) $tenant->trial_used,
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:central.billing_plans,id'],
            'gateway' => ['required', 'string'],
            'billing_cycle' => ['nullable', 'string', 'in:daily,weekly,month,monthly,quarterly,year,yearly'],
        ]);

        /** @var Tenant|null $tenant */
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'No tenant associated with your account.');
        }

        $plan = $this->planService->findOrFail($validated['plan_id']);

        if (! $this->isGatewayConfigured($validated['gateway'])) {
            return response()->json([
                'error' => 'This payment method is not configured. Please contact support.',
            ], 422);
        }

        $billingCycleMap = [
            'month' => 'monthly',
            'year' => 'yearly',
        ];

        $billingCycleValue = $validated['billing_cycle'] ?? null;
        $normalizedCycle = $billingCycleMap[$billingCycleValue] ?? $billingCycleValue;

        try {
            $result = $this->subscriptionService->createSubscription(
                tenantId: $tenant->id,
                planId: $validated['plan_id'],
                gateway: $validated['gateway'],
                billingCycle: $normalizedCycle
                    ? BillingCycle::from($normalizedCycle)
                    : null,
                options: [
                    'success_url' => route('billing.success.sslcommerz'),
                    'cancel_url' => route('billing').'?checkout=cancelled',
                    'ipn_url' => route('billing.webhook.sslcommerz'),
                    'customer_name' => $tenant->name,
                    'customer_email' => $request->user()?->email,
                    'product_name' => $plan->name ?? 'Subscription',
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
        /** @var Tenant|null $tenant */
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'No tenant associated with your account.');
        }

        $subscription = $this->subscriptionService->getTenantSubscription($tenant->id);

        if (! $subscription) {
            return response()->json(['error' => 'No active subscription found.'], 404);
        }

        $this->subscriptionService->cancelSubscription($subscription);

        return response()->json(['message' => 'Subscription cancelled successfully.']);
    }

    public function invoices(Request $request): Response
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'No tenant associated with your account.');
        }

        $tenant->load('owner');

        $payments = $this->paymentService->getTenantPayments($tenant->id);

        $customerName = $tenant->owner?->name ?? $tenant->name ?? 'Valued Customer';
        $companyName = $tenant->name ?? 'Souda';

        $invoices = $payments->map(function ($payment) use ($customerName, $companyName) {
            $subscription = $payment->subscription;
            $periodStart = $subscription?->starts_at;
            $periodEnd = $subscription?->expires_at;

            return [
                'id' => $payment->id,
                'invoice_number' => $this->invoiceService->generateInvoiceNumber($payment),
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'gateway' => $payment->gateway,
                'status' => $payment->status->value,
                'paid_at' => $payment->paid_at?->toISOString(),
                'created_at' => $payment->created_at->toISOString(),
                'customer_name' => $customerName,
                'company_name' => $companyName,
                'app_name' => config('app.name', 'Souda'),
                'period_start' => $periodStart?->toISOString(),
                'period_end' => $periodEnd?->toISOString(),
                'billing_cycle' => $subscription?->billing_cycle?->value,
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

    public function sslcommerzSuccess(Request $request)
    {
        $transactionId = $request->input('tran_id');

        if ($transactionId) {
            $payment = $this->paymentService->findByTransactionId($transactionId);

            if ($payment && $payment->status !== PaymentStatus::Completed) {
                try {
                    $this->subscriptionService->verifyAndActivate(
                        transactionId: $transactionId,
                        gateway: 'sslcommerz',
                        payload: $request->all(),
                    );
                } catch (PaymentFailedException $e) {
                    Log::warning('SSLCommerz success fallback: verifying payment failed, completing locally', [
                        'tran_id' => $transactionId,
                        'error' => $e->getMessage(),
                    ]);

                    $payment = $this->paymentService->findByTransactionId($transactionId);

                    if ($payment && $payment->status !== PaymentStatus::Completed && $payment->subscription) {
                        try {
                            $payment->markAsCompleted($transactionId);
                            $this->subscriptionService->activateSubscription($payment->subscription);
                        } catch (\Throwable $activationError) {
                            Log::error('SSLCommerz success fallback: local completion failed', [
                                'tran_id' => $transactionId,
                                'error' => $activationError->getMessage(),
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('SSLCommerz success fallback failed, re-checking payment status', [
                        'tran_id' => $transactionId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('billing', ['checkout' => 'success']);
    }

    public function sslcommerzWebhook(Request $request)
    {
        $transactionId = $request->input('tran_id');

        if (! $transactionId) {
            return response('Missing transaction ID', 400);
        }

        try {
            $this->subscriptionService->verifyAndActivate(
                transactionId: $transactionId,
                gateway: 'sslcommerz',
                payload: $request->all(),
            );

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('SSLCommerz webhook verification failed', [
                'tran_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response('Verification failed', 400);
        }
    }

    private function getAvailableGateways(): array
    {
        $gateways = config('billing.gateways', []);
        $available = [];

        foreach ($gateways as $key => $config) {
            $isConfigured = $this->isGatewayConfigured($key, $config['config'] ?? []);

            if ($isConfigured) {
                $available[] = [
                    'id' => $key,
                    'label' => $config['label'] ?? ucfirst($key),
                ];
            }
        }

        return $available;
    }

    private function isGatewayConfigured(string $gateway, ?array $config = null): bool
    {
        $config ??= config("billing.gateways.{$gateway}.config", []);

        return match ($gateway) {
            'stripe' => filled($config['secretKey'] ?? $config['secret_key'] ?? null),
            'sslcommerz' => filled($config['storeId'] ?? null) && filled($config['storePassword'] ?? null),
            'bkash' => filled($config['appKey'] ?? null) && filled($config['appSecret'] ?? null),
            'nagad' => filled($config['merchantId'] ?? null) && filled($config['privateKey'] ?? null),
            'portwallet' => filled($config['apiKey'] ?? null) && filled($config['apiSecret'] ?? null),
            'manual' => true,
            default => false,
        };
    }
}
