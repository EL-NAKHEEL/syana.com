<?php

namespace App\Payments;

class PaymentGateways
{
    /**
     * @return array<string, PaymentGateway>
     */
    public function available(): array
    {
        $gateways = [];

        foreach ((array) config('payments.gateways') as $class) {
            /** @var PaymentGateway $gateway */
            $gateway = app($class);
            if ($gateway->isAvailable()) {
                $gateways[$gateway->key()] = $gateway;
            }
        }

        return $gateways;
    }

    public function get(string $key): ?PaymentGateway
    {
        return $this->available()[$key] ?? null;
    }
}
