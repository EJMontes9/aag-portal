@props(['block'])
@php
    $section = $block->get('section', 'lotaip');

    // Estructura cacheada: years[] con sus months[] y documents[] resueltos
    $cacheKey = 'transparency_tree_'.$section;
    $tree = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($section) {
        return \App\Models\LotaipYear::forSection($section)
            ->orderByDesc('year')
            ->with(['activeMonths' => function ($q) {
                $q->orderBy('month');
            }, 'activeMonths.activeDocuments' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->get()
            ->map(function ($year) {
                return [
                    'id' => $year->id,
                    'year' => $year->year,
                    'allowed_extensions' => $year->allowed_extensions,
                    'months' => $year->activeMonths->map(function ($m) use ($year) {
                        $effExt = ! empty($m->allowed_extensions) ? $m->allowed_extensions : $year->allowed_extensions;
                        $docs = $m->activeDocuments;
                        if (! empty($effExt)) {
                            $docs = $docs->filter(fn ($d) => in_array($d->extension, $effExt))->values();
                        }
                        return [
                            'id' => $m->id,
                            'month' => $m->month,
                            'name' => $m->name,
                            'mode' => $m->mode,
                            'redirect_url' => $m->redirect_url,
                            'redirect_label' => $m->redirect_label,
                            'documents' => $docs->map(fn ($d) => [
                                'id' => $d->id,
                                'title' => $d->title,
                                'url' => $d->url,
                                'extension' => $d->extension,
                                'size_human' => $d->size_human,
                                'icon' => $d->icon,
                            ])->all(),
                        ];
                    })->all(),
                ];
            });
    });

    $kicker = $block->get('kicker');
    $title = $block->get('title');
    $intro = $block->get('intro');
@endphp

<section class="bg-bg">
    <div class="section-wrap">
        {{-- Encabezado --}}
        @if($kicker || $title || $intro)
            <header class="max-w-3xl" data-aos="fade-up">
                @if($kicker)
                    <span class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-semibold">{{ $kicker }}</span>
                @endif
                @if($title)
                    <h2 class="font-serif text-section-title text-fg mt-3">{{ $title }}</h2>
                @endif
                @if($intro)
                    <p class="mt-4 text-muted leading-[1.65] whitespace-pre-line">{{ $intro }}</p>
                @endif
            </header>
        @endif

        @if($tree->isEmpty())
            <div class="mt-12 text-center py-20 border-2 border-dashed border-border rounded-hero">
                <svg class="w-12 h-12 mx-auto text-muted/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                </svg>
                <p class="mt-4 font-serif text-2xl text-fg" style="font-weight:400;">Aun no hay informacion publicada</p>
                <p class="mt-2 text-muted">El administrador puede agregar años, meses y documentos desde el panel.</p>
            </div>
        @else
            <div class="mt-10 grid lg:grid-cols-[260px_1fr] gap-8"
                 x-data="{
                    yearId: {{ $tree->first()['id'] }},
                    monthId: null,
                    setYear(id) { this.yearId = id; this.monthId = null; },
                    setMonth(id) { this.monthId = this.monthId === id ? null : id; },
                 }">

                {{-- Sidebar: lista de años --}}
                <aside>
                    <h3 class="font-sans text-[10px] tracking-[0.18em] uppercase text-muted font-semibold mb-3">MENU</h3>
                    <nav class="flex flex-col gap-2" aria-label="Seleccionar año">
                        @foreach($tree as $year)
                            <button type="button"
                                    @click="setYear({{ $year['id'] }})"
                                    :class="yearId === {{ $year['id'] }} ? 'bg-gray-200 text-fg border-gray-300' : 'bg-brand-navy text-on-navy hover:bg-brand-primary'"
                                    class="w-full text-center font-semibold py-2.5 px-4 rounded-md border border-transparent transition-colors">
                                {{ $year['year'] }}
                            </button>
                        @endforeach
                    </nav>
                </aside>

                {{-- Panel: meses + documentos --}}
                <div class="space-y-2">
                    @foreach($tree as $year)
                        <div x-show="yearId === {{ $year['id'] }}" x-cloak class="space-y-2">
                            @if(empty($year['months']))
                                <div class="text-center py-12 border-2 border-dashed border-border rounded-card">
                                    <p class="text-muted">No hay meses publicados para {{ $year['year'] }}.</p>
                                </div>
                            @endif

                            @foreach($year['months'] as $month)
                                @if($month['mode'] === 'redirect')
                                    {{-- Modo redireccion: enlace directo --}}
                                    <a href="{{ $month['redirect_url'] }}"
                                       target="_blank" rel="noopener"
                                       class="group flex items-center gap-3 px-4 py-3 bg-card border border-border rounded-md hover:border-brand-primary/40 hover:bg-brand-soft/20 transition-colors">
                                        <svg class="w-5 h-5 text-brand-primary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                                        </svg>
                                        <span class="flex-1 font-medium text-fg">
                                            <span class="text-brand-primary font-semibold">{{ $month['name'] }}</span>
                                            @if($month['redirect_label'])
                                                · <span class="text-muted font-normal">{{ $month['redirect_label'] }}</span>
                                            @endif
                                        </span>
                                        <svg class="w-4 h-4 text-muted group-hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                        </svg>
                                    </a>
                                @else
                                    {{-- Modo archivos: acordeon --}}
                                    <div class="border border-border rounded-md overflow-hidden bg-card"
                                         :class="monthId === {{ $month['id'] }} ? 'border-brand-primary/40' : ''">
                                        <button type="button"
                                                @click="setMonth({{ $month['id'] }})"
                                                :aria-expanded="monthId === {{ $month['id'] }}"
                                                class="w-full flex items-center justify-between gap-3 text-left px-4 py-3 hover:bg-brand-soft/20 transition-colors">
                                            <span class="flex items-center gap-2 font-medium text-fg">
                                                <svg class="w-5 h-5 transition-colors"
                                                     :class="monthId === {{ $month['id'] }} ? 'text-brand-primary' : 'text-amber-500'"
                                                     fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 9h-15a4.483 4.483 0 0 0-3 1.146Z"/>
                                                </svg>
                                                {{ $month['name'] }}
                                            </span>
                                            <span class="text-xs text-muted">
                                                @if(count($month['documents']) > 0)
                                                    {{ count($month['documents']) }} archivo{{ count($month['documents']) !== 1 ? 's' : '' }}
                                                @else
                                                    Sin documentos
                                                @endif
                                            </span>
                                        </button>

                                        <div x-show="monthId === {{ $month['id'] }}"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 -translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             style="display: none;">
                                            @if(count($month['documents']) > 0)
                                                <div class="border-t border-border">
                                                    <table class="w-full text-sm">
                                                        <thead class="bg-brand-soft/20">
                                                            <tr class="text-left text-[10px] tracking-[0.14em] uppercase text-muted font-semibold">
                                                                <th class="px-4 py-2.5">Archivo</th>
                                                                <th class="px-4 py-2.5 text-right">Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($month['documents'] as $doc)
                                                                <tr class="border-t border-border hover:bg-brand-soft/10 transition-colors">
                                                                    <td class="px-4 py-3">
                                                                        <div class="flex items-center gap-3">
                                                                            <span class="inline-flex w-8 h-8 items-center justify-center rounded {{ match($doc['extension']) {
                                                                                'pdf' => 'bg-red-50 text-red-600',
                                                                                'csv', 'xlsx' => 'bg-emerald-50 text-emerald-600',
                                                                                default => 'bg-gray-100 text-gray-600',
                                                                            } }}">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                                                                </svg>
                                                                            </span>
                                                                            <div>
                                                                                <p class="text-fg font-medium leading-tight">{{ $doc['title'] }}</p>
                                                                                <p class="text-xs text-muted mt-0.5 font-mono">
                                                                                    {{ strtoupper($doc['extension']) }}
                                                                                    @if($doc['size_human']) · {{ $doc['size_human'] }} @endif
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-4 py-3 text-right">
                                                                        <a href="{{ $doc['url'] }}"
                                                                           target="_blank" rel="noopener"
                                                                           download
                                                                           class="inline-flex items-center justify-center w-9 h-9 rounded bg-brand-primary text-white hover:bg-brand-navy transition-colors"
                                                                           aria-label="Descargar {{ $doc['title'] }}">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                                                            </svg>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="border-t border-border px-4 py-4 text-sm text-muted">
                                                    No hay documentos publicados para este mes.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
