<?php

namespace App\Http\Controllers;

use App\Seo\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ThankYouController extends Controller
{
    public function __invoke(Request $request, Seo $seo): View
    {
        $type = $request->query('type') === 'order' ? 'order' : 'booking';

        $seo->title('شكرًا لتواصلك مع النخيل كوول')
            ->description('وصلنا طلبك وهنكلمك في أقرب وقت على رقم الموبايل اللي كتبته. لو محتاج حاجة عاجلة اتصل بينا مباشرة على 01055207525 أو ابعت رسالة واتساب.')
            ->canonical(route('thank-you'))
            ->noindex()
            ->breadcrumbs([['name' => 'شكرًا', 'url' => route('thank-you')]]);

        // Order details come from the flashed session only (never from the URL).
        return view('pages.thank-you', ['type' => $type, 'order' => $type === 'order' ? $request->session()->get('order') : null]);
    }
}
