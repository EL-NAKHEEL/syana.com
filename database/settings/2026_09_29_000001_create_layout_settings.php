<?php

use App\Support\Navigation;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('layout.topbar_label', 'اتصل بنا :');
        $this->migrator->add('layout.header_cta_visible', true);
        $this->migrator->add('layout.header_cta_label', 'احصل على عرض أسعار');
        $this->migrator->add('layout.header_cta_message', 'السلام عليكم، عايز عرض سعر');
        $this->migrator->add('layout.menu', array_map(
            fn (string $key, array $item) => ['key' => $key, 'label' => $item['label'], 'visible' => $item['visible'] ?? true],
            array_keys(Navigation::ITEMS),
            Navigation::ITEMS,
        ));
        $this->migrator->add('layout.footer_about', 'بيع وتركيب وصيانة وتأسيس التكييفات للبيوت والشركات.');
        $this->migrator->add('layout.footer_show_links', true);
        $this->migrator->add('layout.footer_show_areas', true);
        $this->migrator->add('layout.float_whatsapp', true);
        $this->migrator->add('layout.float_call', true);
    }
};
