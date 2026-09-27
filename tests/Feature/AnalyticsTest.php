<?php

use App\Settings\AnalyticsSettings;

beforeEach(function () {
    publishCorePages();
});

it('keeps the existing Google Ads call-conversion setup', function () {
    $settings = app(AnalyticsSettings::class);

    expect($settings->google_ads_id)->toBe('AW-11415013969')
        ->and($settings->google_ads_call_label)->toBe('7C79CO6q5sscENGUjcMq');
});

it('loads tracking only in production', function () {
    expect((string) $this->get('/')->getContent())->not->toContain('nk-ads');

    app()->detectEnvironment(fn () => 'production');
    $html = (string) $this->get('/')->getContent();

    expect($html)->toContain('<meta name="nk-ads" content="AW-11415013969">')
        ->toContain('<meta name="nk-ads-call" content="AW-11415013969/7C79CO6q5sscENGUjcMq">');
});

it('marks every call link for conversion tracking', function () {
    $doc = html((string) $this->get('/')->getContent());

    foreach ($doc->querySelectorAll('a[href^="tel:"]') as $link) {
        expect($link->getAttribute('href'))->toBe('tel:+201055207525')
            ->and($link->getAttribute('data-track'))->toBe('click_call');
    }
});
