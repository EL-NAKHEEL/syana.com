<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\BookingRequest;
use App\Models\User;
use App\Notifications\NewBookingRequest;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = BookingRequest::query()->create([
            ...$request->validated(),
            'source_url' => mb_substr((string) $request->headers->get('referer'), 0, 250),
            'utm' => array_filter($request->only(['utm_source', 'utm_medium', 'utm_campaign', 'gclid'])) ?: null,
        ]);
        $booking->load(['service', 'area']);

        FilamentNotification::make()
            ->title('طلب معاينة جديد من '.$booking->name)
            ->body($booking->phone.' — '.($booking->service->name ?? 'بدون خدمة محددة'))
            ->sendToDatabase(User::all());

        if ($email = config('site.notifications.email')) {
            Notification::route('mail', $email)->notify(new NewBookingRequest($booking));
        }

        return redirect()->route('thank-you', ['type' => 'booking']);
    }
}
