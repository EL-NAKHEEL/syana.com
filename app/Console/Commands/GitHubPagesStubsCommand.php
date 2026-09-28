<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Builds the files that replace the old static site on GitHub Pages at launch (PLAN.md §5, Q11): one stub per old
 * HTML page with a canonical, an instant meta refresh and a visible link to its new URL, plus a 404 page that sends
 * anything else to the new home page. Run it with the production APP_URL.
 */
#[Signature('app:github-pages-stubs {--output=deploy/github-pages-redirects : Directory to write the stubs to}')]
#[Description('Generate GitHub Pages redirect stubs for the old static site')]
class GitHubPagesStubsCommand extends Command
{
    public function handle(): int
    {
        $base = rtrim((string) config('app.url'), '/');
        if (! str_starts_with($base, 'https://') || str_contains($base, 'localhost')) {
            $this->error('Set APP_URL to the production https:// domain first (currently: '.$base.').');

            return self::FAILURE;
        }

        $output = base_path((string) $this->option('output'));
        File::ensureDirectoryExists($output);

        $pages = Redirect::query()->where('source', 'legacy')->where('status_code', 301)
            ->where('from_path', 'like', '%.html')->orderBy('from_path')->get();

        foreach ($pages as $redirect) {
            $file = ltrim((string) $redirect->from_path, '/');
            File::put($output.'/'.$file, $this->stub($base.$redirect->to_url, false));
            $this->line("  {$file} → {$base}{$redirect->to_url}");
        }

        File::put($output.'/404.html', $this->stub($base.'/', true));
        File::put($output.'/.nojekyll', '');

        $this->info($pages->count().' stubs + 404.html written to '.$this->option('output'));

        return self::SUCCESS;
    }

    private function stub(string $target, bool $notFound): string
    {
        $url = e($target);
        $robots = $notFound ? "\n    <meta name=\"robots\" content=\"noindex\">" : '';
        $canonical = $notFound ? '' : "\n    <link rel=\"canonical\" href=\"{$url}\">";

        return <<<HTML
            <!doctype html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="utf-8">
                <title>النخيل كوول</title>{$canonical}{$robots}
                <meta http-equiv="refresh" content="0; url={$url}">
                <meta name="viewport" content="width=device-width, initial-scale=1">
            </head>
            <body>
                <p>موقع النخيل كوول اتنقل. <a href="{$url}">اضغط هنا لو الصفحة ما اتفتحتش لوحدها</a>.</p>
            </body>
            </html>

            HTML;
    }
}
