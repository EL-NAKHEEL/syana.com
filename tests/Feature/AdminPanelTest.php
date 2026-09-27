<?php

use App\Models\Page;
use App\Models\User;

function enrolledAdmin(): User
{
    $user = User::factory()->create();
    $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

    return $user;
}

it('renders every admin screen for an admin with MFA enrolled', function (string $path) {
    $this->seed(Database\Seeders\LegacyContentSeeder::class);

    $this->actingAs(enrolledAdmin())->get($path)->assertOk();
})->with([
    '/admin',
    '/admin/pages',
    '/admin/redirects',
    '/admin/redirects/create',
    '/admin/not-found-logs',
    '/admin/manage-business',
    '/admin/manage-seo',
    '/admin/manage-analytics',
]);

it('opens the page editor with the SEO panel', function () {
    $this->seed(Database\Seeders\LegacyContentSeeder::class);
    $page = Page::query()->where('slug', 'home')->first();

    $this->actingAs(enrolledAdmin())
        ->get("/admin/pages/{$page->id}/edit")
        ->assertOk()
        ->assertSee('SEO')
        ->assertSee('الهيرو (الرئيسية)');
});
