<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSLCommerzDriver implements BillingGatewayInterface
{
    public function __construct(
        private readonly string $storeId,
        private readonly string $storePassword,
        private readonly bool $isSandbox = true,
    ) {}

    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        // @TODO: Implement SSLCommerz payment initiation.
        // Reference: https://developer.sslcommerz.com/registration/
        //
        // $baseUrl = $this->isSandbox
        //     ? 'https://sandbox.sslcommerz.com'
        //     : 'https://securepay.sslcommerz.com';
        //
        // $response = Http::post($baseUrl . '/gwprocess/v4/api.php', [
        //     'store_id' => $this->storeId,
        //     'store_passwd' => $this->storePassword,
        //     'total_amount' => $subscription->amount / 100,
        //     'currency' => $subscription->currency,
        //     'tran_id' => $options['transaction_id'],
        //     'success_url' => $options['success_url'] ?? '',
        //     'fail_url' => $options['fail_url'] ?? '',
        //     'cancel_url' => $options['cancel_url'] ?? '',
        //     'cus_name' => $options['customer_name'] ?? '',
        //     'cus_email' => $options['customer_email'] ?? '',
        //     'product_name' => $options['product_name'] ?? 'Subscription',
        //     'product_category' => 'Subscription',
        //     'product_profile' => 'general',
        // ]);

        Log::info('[SSLCommerzDriver] createPayment called', [
            'tenant_id' => $subscription->tenantId,
            'amount' => $subscription->amount,
        ]);

        throw new PaymentFailedException(
            message: 'SSLCommerz driver — createPayment not yet implemented.',
            gateway: 'sslcommerz',
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        // @TODO: Validate SSLCommerz IPN/webhook or transaction query.

        Log::info('[SSLCommerzDriver] verifyPayment called', [
            'transaction_id' => $transactionId,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'sslcommerz',
            'status' => 'completed',
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        // SSLCommerz does not manage recurring subscriptions natively.
        // Cancellation is handled locally by marking the subscription as cancelled.
        Log::info('[SSLCommerzDriver] cancelSubscription called - no remote action needed');

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        // @TODO: SSLCommerz refund via Refund Pipeline API.
        // https://developer.sslcommerz.com/refund/

        Log::info('[SSLCommerzDriver] refundPayment called', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'sslcommerz',
            'status' => 'refunded',
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        return $payment->checkoutUrl ?? '';
    }
}
