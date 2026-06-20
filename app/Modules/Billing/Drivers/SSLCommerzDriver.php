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
    private readonly string $baseUrl;

    public function __construct(
        private readonly string $storeId,
        private readonly string $storePassword,
        private readonly bool $isSandbox = true,
    ) {
        $this->baseUrl = $isSandbox
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        $tranId = 'SSLC'.uniqid().time();

        $response = Http::asForm()->post($this->baseUrl.'/gwprocess/v4/api.php', [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => $subscription->amount / 100,
            'currency' => strtoupper($subscription->currency),
            'tran_id' => $tranId,
            'success_url' => $options['success_url'] ?? '',
            'fail_url' => $options['cancel_url'] ?? '',
            'cancel_url' => $options['cancel_url'] ?? '',
            'ipn_url' => $options['ipn_url'] ?? '',
            'cus_name' => $options['customer_name'] ?? '',
            'cus_email' => $options['customer_email'] ?? '',
            'cus_phone' => $options['customer_phone'] ?? '',
            'cus_add1' => $options['customer_address'] ?? '',
            'cus_city' => $options['customer_city'] ?? '',
            'cus_country' => $options['customer_country'] ?? 'Bangladesh',
            'product_name' => $options['product_name'] ?? 'Subscription',
            'product_category' => 'Subscription',
            'product_profile' => 'general',
            'shipping_method' => 'NO',
            'num_of_item' => 1,
            'multi_card_name' => '',
            'allowed_bin' => '',
            'value_a' => $subscription->tenantId,
            'value_b' => $tranId,
        ]);

        if (! $response->successful()) {
            Log::error('[SSLCommerzDriver] API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PaymentFailedException(
                message: 'Failed to connect to SSLCommerz.',
                gateway: 'sslcommerz',
            );
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'SUCCESS') {
            $message = $data['failedreason'] ?? 'SSLCommerz session creation failed.';

            Log::error('[SSLCommerzDriver] Session creation failed', [
                'response' => $data,
            ]);

            throw new PaymentFailedException(
                message: $message,
                gateway: 'sslcommerz',
            );
        }

        $checkoutUrl = $data['GatewayPageURL'] ?? '';

        if (! $checkoutUrl) {
            throw new PaymentFailedException(
                message: 'No checkout URL returned by SSLCommerz.',
                gateway: 'sslcommerz',
            );
        }

        Log::info('[SSLCommerzDriver] Payment session created', [
            'tran_id' => $tranId,
            'amount' => $subscription->amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $tranId,
            'gateway' => 'sslcommerz',
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'status' => 'pending',
            'checkout_url' => $checkoutUrl,
            'payload' => $data,
        ]);
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        $validationData = [
            'val_id' => $payload['val_id'] ?? '',
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
        ];

        $response = Http::asForm()->post($this->baseUrl.'/validator/api/validationserverAPI.php', $validationData);

        if (! $response->successful()) {
            Log::error('[SSLCommerzDriver] Validation request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return PaymentDTO::fromArray([
                'transaction_id' => $transactionId,
                'gateway' => 'sslcommerz',
                'status' => 'failed',
                'payload' => $payload,
            ]);
        }

        $data = $response->json();
        $status = strtolower($data['status'] ?? '');
        $isValid = $status === 'valid' || $status === 'valid_a' || $status === 'valid_b';

        if (! $isValid) {
            Log::warning('[SSLCommerzDriver] Payment validation failed', [
                'tran_id' => $transactionId,
                'response' => $data,
            ]);

            return PaymentDTO::fromArray([
                'transaction_id' => $transactionId,
                'gateway' => 'sslcommerz',
                'status' => 'failed',
                'payload' => $data,
            ]);
        }

        Log::info('[SSLCommerzDriver] Payment verified', [
            'tran_id' => $transactionId,
            'amount' => $data['amount'] ?? null,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $data['tran_id'] ?? $transactionId,
            'gateway' => 'sslcommerz',
            'amount' => isset($data['amount']) ? (int) ($data['amount'] * 100) : 0,
            'currency' => $data['currency'] ?? 'BDT',
            'status' => 'completed',
            'payload' => $data,
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        Log::info('[SSLCommerzDriver] cancelSubscription called - no remote action needed');

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        $refundData = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'refund_amount' => $amount ? $amount / 100 : 0,
            'refund_remarks' => 'Refund for subscription',
            'bank_tran_id' => $transactionId,
            'ref_id' => 'REF_'.$transactionId,
        ];

        $response = Http::asForm()->post($this->baseUrl.'/validator/api/merchantTransIDapi.php', $refundData);

        if (! $response->successful()) {
            Log::error('[SSLCommerzDriver] Refund request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PaymentFailedException(
                message: 'Refund request failed.',
                gateway: 'sslcommerz',
            );
        }

        $data = $response->json();

        Log::info('[SSLCommerzDriver] Refund processed', [
            'transaction_id' => $transactionId,
            'response' => $data,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'sslcommerz',
            'status' => $data['status'] === 'Success' ? 'refunded' : 'failed',
            'payload' => $data,
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        return $payment->checkoutUrl ?? '';
    }
}
