<?php

use App\Payments\CashOnDeliveryGateway;

return [
    /*
    | Checkout payment methods, in display order. Add card/installment gateways here (they implement
    | App\Payments\PaymentGateway).
    */
    'gateways' => [
        CashOnDeliveryGateway::class,
    ],
];
