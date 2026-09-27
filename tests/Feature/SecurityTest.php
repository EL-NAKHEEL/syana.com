<?php

use App\Models\User;
use App\Support\InlineScripts;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    publishCorePages();
});

it('sends the baseline security headers on public pages', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy');
});

it('ships CSP in report-only mode by default and enforces it on request', function () {
    $this->get('/')->assertHeader('Content-Security-Policy-Report-Only')->assertHeaderMissing('Content-Security-Policy');

    config(['site.security.csp_enforce' => true]);
    $this->get('/')->assertHeader('Content-Security-Policy')->assertHeaderMissing('Content-Security-Policy-Report-Only');
});

it('allow-lists the only inline script by hash (cache-safe, no nonce)', function () {
    $response = $this->get('/');
    $doc = html((string) $response->getContent());

    $inline = collect($doc->querySelectorAll('script'))
        ->filter(fn ($s) => ! $s->hasAttribute('src') && $s->getAttribute('type') !== 'application/ld+json');

    expect($inline)->toHaveCount(1)
        ->and($inline->first()->textContent)->toBe(InlineScripts::HEAD);

    $hash = "'sha256-".base64_encode(hash('sha256', $inline->first()->textContent, true))."'";
    expect($response->headers->get('Content-Security-Policy-Report-Only'))->toContain($hash)
        ->not->toContain('unsafe-eval')
        ->toContain("object-src 'none'")
        ->toContain("frame-ancestors 'self'");
});

it('adds HSTS only when enabled and served over HTTPS', function () {
    $this->get('https://localhost/')->assertHeaderMissing('Strict-Transport-Security');

    config(['site.security.hsts' => true]);
    $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
    $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('accepts CSP violation reports without a CSRF token', function () {
    $this->postJson('/csp-report', ['csp-report' => ['blocked-uri' => 'inline']])->assertNoContent();
});

it('requires multi-factor authentication for every admin', function () {
    $panel = Filament::getPanel('admin');

    expect($panel->isMultiFactorAuthenticationRequired())->toBeTrue()
        ->and($panel->getMultiFactorAuthenticationProviders())->not->toBeEmpty();

    // A signed-in admin without MFA is sent to set it up before reaching the dashboard.
    $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect();
});

it('keeps the admin out of search engines and outside the public CSP', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/admin/login')
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeaderMissing('Content-Security-Policy-Report-Only');
});

it('throttles admin login attempts', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    foreach (range(1, 5) as $attempt) {
        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'wrong-password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);
    }

    Livewire::test(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'wrong-password'])
        ->call('authenticate')
        ->assertNotified();
});

it('never tracks the .env file in git', function () {
    if (! is_dir(base_path('.git'))) {
        $this->markTestSkipped('Not a git checkout.');
    }

    exec('git -C '.escapeshellarg(base_path()).' ls-files --error-unmatch .env 2>/dev/null', $output, $code);

    expect($code)->not->toBe(0);
});
