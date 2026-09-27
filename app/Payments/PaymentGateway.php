<?php

namespace App\Payments;

use App\Models\Order;

/**
 * Checkout payment method. Cash on delivery today; card/installment gateways (Paymob, Fawry, valU…) later
 * implement the same contract and register in config/payments.php.
 */
interface PaymentGateway
{
    public function key(): string;

    public function label(): string;

    public function description(): string;

    public function isAvailable(): bool;

    /**
     * Starts payment for a stored order. Returns a URL to redirect to (off-site payment page) or null when the
     * order is complete without a redirect (e.g. cash on delivery).
     */
    public function initiate(Order $order): ?string;
}
