<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Old static-site URLs (PLAN.md §5). Targets are paths; they are resolved against APP_URL at request time.
 * Old stock images get 410 Gone by exact path only (owner decision C3: no wildcard).
 * /services, /reviews targets go live in later phases; until then those URLs return the new site's 404.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = collect([
            ['/index.html', '/', 301],
            ['/about.html', '/about', 301],
            ['/service.html', '/services', 301],
            ['/syana.html', '/services/ac-maintenance', 301],
            ['/tarkeeb.html', '/services/ac-installation', 301],
            ['/tagheez.html', '/services/ac-preparation', 301],
            ['/contact.html', '/contact', 301],
            ['/feature.html', '/about', 301],
            ['/testimonial.html', '/reviews', 301],
            ['/tarkeeb.html.html', '/services/ac-installation', 301],
            ['/tagheez.html.html.html', '/services/ac-preparation', 301],
            ['/img/2306.q891.030.S.m004.c10.air conditioner split system realistic.jpg', null, 410],
            ['/img/carousel-1.jpg', null, 410],
            ['/img/carousel-2.jpg', null, 410],
            ['/img/engineer-assembling-hvac-unit-manometers.jpg', null, 410],
            ['/img/expert-repairman-doing-condenser-investigations-filter-replacements-necessary-fixes-prevent-major-breakdowns-proficient-worker-checking-up-hvac-system-writing-findings-clipboard.jpg', null, 410],
            ['/img/feature.jpg', null, 410],
            ['/img/full-shot-couple-dog-home.jpg', null, 410],
            ['/img/full-shot-mean-cleaning-air.jpg', null, 410],
            ['/img/hvac-technician-working-capacitor-part-condensing-unit.jpg', null, 410],
            ['/img/project-6.jpg', null, 410],
            ['/img/technician-working-air-conditioner.jpg', null, 410],
            ['/img/testimonial-1.jpg', null, 410],
            ['/img/testimonial-2.jpg', null, 410],
            ['/img/testimonial-3.jpg', null, 410],
            ['/img/wall-city-estate-background-office.jpg', null, 410],
            ['/img/woman-holding-remote-start-heater.jpg', null, 410],
            ['/img/young-woman-using-home-technology.jpg', null, 410],
        ])->map(fn (array $row) => [
            'from_path' => mb_strtolower($row[0]),
            'to_url' => $row[1],
            'status_code' => $row[2],
            'source' => 'legacy',
            'is_active' => true,
            'hits' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('redirects')->upsert($rows->all(), ['from_path'], ['to_url', 'status_code', 'source', 'is_active', 'updated_at']);
    }

    public function down(): void
    {
        DB::table('redirects')->where('source', 'legacy')->delete();
    }
};
