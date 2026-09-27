<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $o = $this->order;
        $mail = (new MailMessage)
            ->subject('طلب جديد '.$o->number.': '.number_format((float) $o->total).' جنيه')
            ->greeting('طلب جديد '.$o->number)
            ->line('الاسم: '.$o->name.' — '.$o->phone)
            ->line('العنوان: '.($o->area->name_ar ?? $o->area_text ?? '').' — '.$o->address);

        foreach ($o->items as $item) {
            $mail->line($item->qty.' × '.$item->name.' ('.$item->model_number.') = '.number_format((float) $item->line_total).' جنيه');
        }

        return $mail->line('الإجمالي: '.number_format((float) $o->total).' جنيه — '.$o->payment_method)
            ->action('افتح الطلب', url('/admin/orders'));
    }
}
