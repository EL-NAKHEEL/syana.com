<?php

namespace App\Notifications;

use App\Models\BookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly BookingRequest $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking;

        return (new MailMessage)
            ->subject('طلب معاينة جديد: '.$b->name.' — '.$b->phone)
            ->greeting('طلب معاينة جديد')
            ->line('الاسم: '.$b->name)
            ->line('الموبايل: '.$b->phone)
            ->line('الخدمة: '.($b->service->name ?? '—'))
            ->line('المنطقة: '.($b->area->name_ar ?? $b->area_text ?? '—'))
            ->line('نوع التكييف: '.(BookingRequest::AC_TYPES[$b->ac_type] ?? '—'))
            ->line('الميعاد المفضل: '.($b->preferred_date?->format('Y-m-d') ?? '—'))
            ->line('ملاحظات: '.($b->notes ?: '—'))
            ->action('افتح الطلب', url('/admin/booking-requests'));
    }
}
