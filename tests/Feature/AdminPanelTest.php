<?php

use App\Models\Page;
use App\Models\User;
use Database\Seeders\LegacyContentSeeder;

function enrolledAdmin(): User
{
    $user = User::factory()->create();
    $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

    return $user;
}

it('renders every admin screen for an admin with MFA enrolled', function (string $path) {
    $this->seed(LegacyContentSeeder::class);

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
    '/admin/services',
    '/admin/services/create',
    '/admin/booking-requests',
    '/admin/areas',
    '/admin/areas/create',
    '/admin/price-guides',
    '/admin/price-guides/create',
    '/admin/brands',
    '/admin/products',
    '/admin/products/create',
    '/admin/facet-pages',
    '/admin/facet-pages/create',
    '/admin/orders',
    '/admin/manage-calculator',
    '/admin/people',
    '/admin/people/create',
    '/admin/post-categories',
    '/admin/post-categories/create',
    '/admin/posts',
    '/admin/posts/create',
    '/admin/projects',
    '/admin/projects/create',
    '/admin/reviews',
    '/admin/seo-health',
]);

it('opens the page editor with the SEO panel', function () {
    $this->seed(LegacyContentSeeder::class);
    $page = Page::query()->where('slug', 'home')->first();

    $this->actingAs(enrolledAdmin())
        ->get("/admin/pages/{$page->id}/edit")
        ->assertOk()
        ->assertSee('SEO')
        ->assertSee('الهيرو (الرئيسية)');
});
