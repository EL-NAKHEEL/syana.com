<?php

namespace App\Payments;

use App\Models\Order;

class CashOnDeliveryGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'cod';
    }

    public function label(): string
    {
        return 'الدفع عند الاستلام';
    }

    public function description(): string
    {
        return 'هنكلمك نأكد الطلب وميعاد التوصيل، وتدفع كاش لما التكييف يوصلك.';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function initiate(Order $order): ?string
    {
        return null;
    }
}
