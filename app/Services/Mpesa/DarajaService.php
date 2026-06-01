<?php

namespace App\Services\Mpesa;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DarajaService
{
    public function __construct(protected DarajaConfig $config) {}

    // -------------------------------------------------------
    // Access Token
    // -------------------------------------------------------

    public function getAccessToken(): ?string
    {
        return Cache::remember('mpesa_access_token', now()->addMinutes(55), function () {
            if (! $this->config->isConfigured()) {
                Log::warning('MPESA Daraja API is not configured. Set MPESA_CONSUMER_KEY/SECRET in .env.');
                return null;
            }

            $response = Http::withBasicAuth(
                $this->config->consumerKey(),
                $this->config->consumerSecret()
            )->get("{$this->config->baseUrl()}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('MPESA token request failed', ['response' => $response->body()]);
            return null;
        });
    }

    // -------------------------------------------------------
    // STK Push — C2B deposit collection
    // -------------------------------------------------------

    /**
     * Initiate an STK Push prompt to the tenant's phone.
     *
     * @param  string  $phone   Tenant's phone in the format 2547XXXXXXXX
     * @param  float   $amount  Amount to collect (rounded up to whole number)
     * @param  string  $accountRef  Reference shown on the tenant's phone (e.g. unit number)
     * @param  string  $description Internal description
     * @return array{success: bool, checkout_request_id: ?string, merchant_request_id: ?string, message: string}
     */
    public function stkPush(string $phone, float $amount, string $accountRef, string $description = 'Deposit Payment'): array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return $this->notConfiguredResult();
        }

        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password  = base64_encode($this->config->shortcode() . $this->config->passkey() . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->config->shortcode(),
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) ceil($amount),
            'PartyA'            => $this->normalizePhone($phone),
            'PartyB'            => $this->config->shortcode(),
            'PhoneNumber'       => $this->normalizePhone($phone),
            'CallBackURL'       => $this->config->stkCallbackUrl(),
            'AccountReference'  => $accountRef,
            'TransactionDesc'   => $description,
        ];

        $response = Http::withToken($token)
            ->post("{$this->config->baseUrl()}/mpesa/stkpush/v1/processrequest", $payload);

        Log::info('MPESA STK Push', ['payload' => $payload, 'response' => $response->json()]);

        if ($response->successful() && $response->json('ResponseCode') === '0') {
            return [
                'success'              => true,
                'checkout_request_id'  => $response->json('CheckoutRequestID'),
                'merchant_request_id'  => $response->json('MerchantRequestID'),
                'message'              => $response->json('CustomerMessage', 'STK push sent.'),
            ];
        }

        return [
            'success'             => false,
            'checkout_request_id' => null,
            'merchant_request_id' => null,
            'message'             => $response->json('errorMessage', 'STK Push failed. Please try again.'),
        ];
    }

    // -------------------------------------------------------
    // B2C — Deposit refund disbursement
    // -------------------------------------------------------

    /**
     * Send a B2C payment (deposit refund) to the tenant.
     *
     * @param  string  $phone   Tenant's phone in the format 2547XXXXXXXX
     * @param  float   $amount  Amount to disburse
     * @param  string  $remarks Internal remarks
     * @return array{success: bool, conversation_id: ?string, originator_id: ?string, message: string}
     */
    public function b2cPayment(string $phone, float $amount, string $remarks = 'Deposit Refund'): array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return $this->notConfiguredResult();
        }

        $securityCredential = $this->generateSecurityCredential();

        $payload = [
            'InitiatorName'      => $this->config->b2cInitiatorName(),
            'SecurityCredential' => $securityCredential,
            'CommandID'          => 'BusinessPayment',
            'Amount'             => (int) floor($amount),
            'PartyA'             => $this->config->b2cShortcode(),
            'PartyB'             => $this->normalizePhone($phone),
            'Remarks'            => $remarks,
            'QueueTimeOutURL'    => $this->config->b2cTimeoutUrl(),
            'ResultURL'          => $this->config->b2cResultUrl(),
            'Occassion'          => 'Deposit Refund',
        ];

        $response = Http::withToken($token)
            ->post("{$this->config->baseUrl()}/mpesa/b2c/v3/paymentrequest", $payload);

        Log::info('MPESA B2C Payment', ['response' => $response->json()]);

        if ($response->successful() && $response->json('ResponseCode') === '0') {
            return [
                'success'         => true,
                'conversation_id' => $response->json('ConversationID'),
                'originator_id'   => $response->json('OriginatorConversationID'),
                'message'         => $response->json('ResponseDescription', 'B2C payment queued.'),
            ];
        }

        return [
            'success'         => false,
            'conversation_id' => null,
            'originator_id'   => null,
            'message'         => $response->json('errorMessage', 'B2C payment failed. Please try again.'),
        ];
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Normalize a Kenyan phone number to 2547XXXXXXXX format.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        }

        return $phone;
    }

    private function generateSecurityCredential(): string
    {
        $cert = $this->config->baseUrl() === config('tdaps.mpesa.live_url')
            ? storage_path('mpesa/ProductionCertificate.cer')
            : storage_path('mpesa/SandboxCertificate.cer');

        $publicKey = '';
        if (file_exists($cert)) {
            $publicKey = file_get_contents($cert);
        }

        if (empty($publicKey)) {
            return base64_encode($this->config->b2cInitiatorPassword());
        }

        openssl_public_encrypt(
            $this->config->b2cInitiatorPassword(),
            $encrypted,
            $publicKey,
            OPENSSL_PKCS1_PADDING
        );

        return base64_encode($encrypted);
    }

    private function notConfiguredResult(): array
    {
        return [
            'success'              => false,
            'checkout_request_id'  => null,
            'merchant_request_id'  => null,
            'conversation_id'      => null,
            'originator_id'        => null,
            'message'              => 'MPESA is not configured. Add your Daraja API credentials to .env.',
        ];
    }
}
