<div class="space-y-4 p-2">
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div><span class="font-semibold text-gray-500">Evento:</span> {{ $activity->event ?? '—' }}</div>
        <div><span class="font-semibold text-gray-500">Fecha:</span> {{ $activity->created_at->format('d/m/Y H:i:s') }}</div>
        <div><span class="font-semibold text-gray-500">Usuario:</span> {{ $activity->causer?->name ?? 'Sistema / Visitante' }}</div>
        <div><span class="font-semibold text-gray-500">Email:</span> {{ $activity->causer?->email ?? '—' }}</div>
        <div class="col-span-2"><span class="font-semibold text-gray-500">Descripción:</span> {{ $activity->description }}</div>
        @if($activity->subject_type)
        <div class="col-span-2"><span class="font-semibold text-gray-500">Objeto:</span> {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</div>
        @endif
    </div>
    @php $props = $activity->properties->toArray(); @endphp
    @if(!empty($props))
    <div class="mt-4">
        <p class="font-semibold text-gray-500 text-sm mb-2">Propiedades:</p>
        @if(isset($props['attributes']) && isset($props['old']))
            <table class="w-full text-xs border-collapse">
                <thead><tr class="bg-gray-100"><th class="p-2 text-left border">Campo</th><th class="p-2 text-left border">Antes</th><th class="p-2 text-left border">Después</th></tr></thead>
                <tbody>
                @foreach($props['attributes'] as $key => $new)
                    @php $old = $props['old'][$key] ?? null; @endphp
                    @if($old !== $new)
                    <tr class="border-b">
                        <td class="p-2 border font-mono">{{ $key }}</td>
                        <td class="p-2 border text-red-600">{{ is_array($old) ? json_encode($old) : $old }}</td>
                        <td class="p-2 border text-green-600">{{ is_array($new) ? json_encode($new) : $new }}</td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        @else
            <pre class="bg-gray-50 rounded p-3 text-xs overflow-auto max-h-64">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
    </div>
    @endif
</div>
