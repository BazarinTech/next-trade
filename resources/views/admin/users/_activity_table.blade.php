<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $title }}</p>
    </div>
    @if($items->isEmpty())
    <p class="px-5 py-6 text-xs text-gray-500 text-center">None.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    @foreach($cols as $col)
                    <th class="px-5 py-2 text-left font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @foreach($items as $item)
                <tr>
                    @foreach($cols as $col)
                    <td class="px-5 py-2.5" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $col['value']($item) }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
