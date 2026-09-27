<?php

/*
| Price guide drafts. They hold NO prices: rows come live from the services table, so a guide can only go live
| once at least one service in it has a real starting price (PLAN.md Q9/Q14). {year} renders only while prices are
| fresh. Keyword owners: «تكلفة/سعر تركيب تكييف», «سعر صيانة تكييف» (PLAN.md §7).
*/

return [
    [
        'slug' => 'ac-installation-cost',
        'title' => 'تكلفة تركيب التكييف {year}: الأسعار بالتفصيل',
        'h1' => 'تكلفة تركيب التكييف {year}',
        'intro' => 'أسعار تركيب وتأسيس وفك ونقل التكييفات من جدول أسعار النخيل كوول الحالي، وإيه اللي بيأثر على التكلفة.',
        'body' => implode("\n", [
            '<h2>إيه اللي بيحدد تكلفة التركيب؟</h2>',
            '<ul><li>قدرة التكييف ونوعه.</li><li>طول مواسير النحاس المطلوب.</li><li>الدور ومكان الوحدة الخارجية.</li><li>لو المكان متأسس ولا محتاج تأسيس.</li></ul>',
            '<p>[TODO: هل التركيب داخل في سعر التكييف لو اتشرى من النخيل كوول؟ وسعر متر النحاس الزيادة]</p>',
        ]),
        'services' => ['ac-installation', 'ac-preparation', 'ac-relocation'],
        'sort' => 1,
    ],
    [
        'slug' => 'ac-maintenance-cost',
        'title' => 'سعر صيانة التكييف {year}: الصيانة والتنظيف والفريون',
        'h1' => 'سعر صيانة التكييف {year}',
        'intro' => 'أسعار صيانة وتنظيف التكييفات وشحن الفريون من جدول أسعار النخيل كوول الحالي، وإيه اللي بيأثر على التكلفة.',
        'body' => implode("\n", [
            '<h2>إيه اللي بيحدد تكلفة الصيانة؟</h2>',
            '<ul><li>سبب العطل وقطع الغيار المطلوبة.</li><li>نوع التكييف وقدرته.</li><li>محتاج شحن فريون ولا لأ.</li></ul>',
            '<p>[TODO: مراجعة الأسعار قبل النشر]</p>',
        ]),
        'services' => ['ac-maintenance', 'ac-cleaning', 'freon-recharge'],
        'sort' => 2,
    ],
];
