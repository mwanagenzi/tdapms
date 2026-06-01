<?php

namespace App\Services\Mpesa;

class DarajaConfig
{
    public function baseUrl(): string
    {
        return config('tdaps.mpesa.env') === 'live'
            ? config('tdaps.mpesa.live_url')
            : config('tdaps.mpesa.sandbox_url');
    }

    public function consumerKey(): string
    {
        return config('tdaps.mpesa.consumer_key', '');
    }

    public function consumerSecret(): string
    {
        return config('tdaps.mpesa.consumer_secret', '');
    }

    public function shortcode(): string
    {
        return config('tdaps.mpesa.shortcode', '');
    }

    public function passkey(): string
    {
        return config('tdaps.mpesa.passkey', '');
    }

    public function stkCallbackUrl(): string
    {
        return config('tdaps.mpesa.stk_callback_url', '');
    }

    public function b2cShortcode(): string
    {
        return config('tdaps.mpesa.b2c_shortcode', '');
    }

    public function b2cInitiatorName(): string
    {
        return config('tdaps.mpesa.b2c_initiator_name', '');
    }

    public function b2cInitiatorPassword(): string
    {
        return config('tdaps.mpesa.b2c_initiator_password', '');
    }

    public function b2cResultUrl(): string
    {
        return config('tdaps.mpesa.b2c_result_url', '');
    }

    public function b2cTimeoutUrl(): string
    {
        return config('tdaps.mpesa.b2c_timeout_url', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->consumerKey())
            && ! empty($this->consumerSecret())
            && ! empty($this->shortcode());
    }
}
