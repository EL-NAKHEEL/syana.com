<x-filament-panels::page>
    @foreach ($report as $key => $section)
        <x-filament::section :heading="$section['label'].' ('.$section['rows']->count().')'" collapsible :collapsed="$section['rows']->isEmpty()">
            @if ($section['rows']->isEmpty())
                <p>✓ مفيش مشاكل.</p>
            @else
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($section['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                <td class="py-2 pe-4 font-medium">{{ $row['label'] }}</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ $row['detail'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
