<?php

use App\Models\BookingRequest;
use App\Models\Service;
use App\Models\User;
use App\Notifications\NewBookingRequest;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Honeypot\Honeypot;

beforeEach(function () {
    publishCorePages();
    config(['honeypot.enabled' => false]);
});

it('stores a booking, normalizes the phone and notifies the team', function (string $phone) {
    Notification::fake();
    config(['site.notifications.email' => 'team@example.com']);
    $admin = User::factory()->create();
    [$service] = publishServices(1);

    $this->post('/bookings', [
        'name' => 'محمد',
        'phone' => $phone,
        'service_id' => $service->id,
        'ac_type' => 'split',
        'area_text' => 'مدينة نصر',
        'preferred_date' => now()->addDay()->toDateString(),
    ])->assertRedirect(route('thank-you', ['type' => 'booking']));

    $booking = BookingRequest::query()->sole();
    expect($booking->phone)->toBe('01055207525')->and($booking->status)->toBe('new');

    Notification::assertSentOnDemand(NewBookingRequest::class);
    Notification::assertSentTo($admin, DatabaseNotification::class);
})->with(['01055207525', '+20 105 520 7525', '٠١٠٥٥٢٠٧٥٢٥', '0020-1055207525']);

it('rejects invalid phones and past dates with Arabic messages', function () {
    $this->from('/contact')->post('/bookings', ['name' => 'محمد', 'phone' => '12345', 'preferred_date' => '2000-01-01'])
        ->assertRedirect('/contact')
        ->assertSessionHasErrors(['phone', 'preferred_date']);

    expect(BookingRequest::query()->count())->toBe(0);
});

it('rejects unpublished services', function () {
    $draft = Service::factory()->create();

    $this->post('/bookings', ['name' => 'محمد', 'phone' => '01055207525', 'service_id' => $draft->id])
        ->assertSessionHasErrors('service_id');
});

it('blocks honeypot spam', function () {
    config(['honeypot.enabled' => true]);
    $honeypot = app(Honeypot::class);

    $this->post('/bookings', [
        'name' => 'spam',
        'phone' => '01055207525',
        $honeypot->nameFieldName() => 'i am a bot',
        $honeypot->validFromFieldName() => $honeypot->encryptedValidFrom(),
    ]);

    expect(BookingRequest::query()->count())->toBe(0);
});

it('shows a noindex thank-you page that fires the lead event', function () {
    $doc = html((string) $this->get('/thank-you?type=booking')->assertOk()->getContent());

    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($doc->querySelector('[data-track-onload="generate_lead"]'))->not->toBeNull();
});
