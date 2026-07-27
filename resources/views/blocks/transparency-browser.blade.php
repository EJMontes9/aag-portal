@props(['block'])
@php
    $section = $block->get('section', 'lotaip');

    // Estructura cacheada: years[] con sus months[] y el CONTEO de documentos
    // de cada mes, no el listado completo.
    //
    // Antes esta cache guardaba, para cada mes de cada año, el HTML de cada
    // documento (enlace, icono, tamaño...), aunque el visitante solo puede ver
    // un mes a la vez: el resto queda oculto con x-show, pero igual viaja al
    // navegador. Con el archivo historico de LOTAIP (mas de mil documentos)
    // eso eran varios megabytes y quince segundos de carga en CADA visita a
    // esta pagina, la mayoria de los cuales el visitante ni siquiera llega a
    // ver.
    //
    // Ahora solo se guarda el conteo por mes (para el rotulo "N archivos") y
    // el listado real de un mes se pide aparte, solo cuando el visitante lo
    // abre: ver TransparencyController@documentos y el fetch() de mas abajo.
    $cacheKey = 'transparency_tree_'.$section;
    $tree = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($section) {
        return \App\Models\LotaipYear::forSection($section)
            ->orderByDesc('year')
            ->with(['activeMonths' => function ($q) {
                $q->orderBy('month');
            }, 'activeMonths.activeDocuments' => function ($q) {
                // Solo se necesitan estas columnas para contar cuantos
                // documentos quedan tras el filtro de extension: no hace
                // falta traer titulo, ruta ni tamaño de cada uno.
                $q->select('id', 'month_id', 'extension');
            }])
            ->get()
            ->map(function ($year) {
                return [
                    'id' => $year->id,
                    'year' => $year->year,
                    'months' => $year->activeMonths->map(function ($m) use ($year) {
                        $effExt = ! empty($m->allowed_extensions) ? $m->allowed_extensions : $year->allowed_extensions;
                        $docsCount = ! empty($effExt)
                            ? $m->activeDocuments->whereIn('extension', $effExt)->count()
                            : $m->activeDocuments->count();

                        return [
                            'id' => $m->id,
                            'month' => $m->month,
                            'name' => $m->name,
                            'mode' => $m->mode,
                            'redirect_url' => $m->redirect_url,
                            'redirect_label' => $m->redirect_label,
                            'documents_count' => $docsCount,
                        ];
                    })->all(),
                ];
            });
    });

    $kicker = $block->get('kicker');
    $title = $block->get('title');
    $intro = $block->get('intro');

    // Año que se abre al entrar: el mas reciente QUE TENGA DOCUMENTOS, no el
    // primero de la lista. El año en curso suele estar vacio en los primeros
    // meses (o hasta que se publica la informacion del periodo), y abrir en una
    // pantalla de "sin documentos" hace pensar que no hay nada publicado.
    $anioInicial = $tree->first(
        fn ($y) => collect($y['months'])->contains(fn ($m) => $m['documents_count'] > 0)
    ) ?? $tree->first();
@endphp

{{-- Navegador de transparencia (LOTAIP) -- Propuesta B.
     Rediseno visual: la logica de cache y de navegacion ano->mes->documentos es
     la misma. Todo pasa a caja blanca con borde marcado, esquinas de 4px y
     chips rectangulares; se elimina el radio "md" y las paletas rojo/verde
     ajenas al sistema. --}}
<section class="bg-bg">
    <div class="section-wrap">
        {{-- Encabezado --}}
        @if($kicker || $title || $intro)
            <header class="max-w-3xl" data-aos="fade-up">
                @if($kicker)
                    <span class="kicker">{{ $kicker }}</span>
                @endif
                @if($title)
                    <h2 class="font-serif text-section-title text-brand-navy mt-2">{{ $title }}</h2>
                @endif
                @if($intro)
                    <p class="mt-3 text-[15px] text-muted leading-relaxed whitespace-pre-line">{{ $intro }}</p>
                @endif
            </header>
        @endif

        @if($tree->isEmpty())
            <div class="mt-8 text-center py-16 card-surface border-dashed">
                <svg class="w-12 h-12 mx-auto text-border" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                </svg>
                <p class="mt-4 font-serif text-section-title text-brand-navy">Aun no hay informacion publicada</p>
                <p class="mt-2 text-[15px] text-muted">El administrador puede agregar años, meses y documentos desde el panel.</p>
            </div>
        @else
            <div class="mt-8 grid lg:grid-cols-[220px_1fr] gap-6"
                 x-data="{
                    yearId: {{ $anioInicial['id'] }},
                    monthId: null,
                    loading: {},
                    loaded: {},
                    setYear(id) { this.yearId = id; this.monthId = null; },
                    setMonth(id) {
                        this.monthId = this.monthId === id ? null : id;
                        // El detalle de cada mes se pide una sola vez y queda
                        // en memoria: volver a abrir el mismo mes no repite la
                        // peticion.
                        if (this.monthId === id && this.loaded[id] === undefined) {
                            this.loading[id] = true;
                            fetch(`/transparencia/mes/${id}/documentos`)
                                .then(r => r.ok ? r.text() : Promise.reject())
                                .then(html => { this.loaded[id] = html; })
                                .catch(() => { this.loaded[id] = '<div class=&quot;border-t border-border px-4 py-3.5 text-[14px] text-muted&quot;>No se pudo cargar el listado. Intenta de nuevo.</div>'; })
                                .finally(() => { this.loading[id] = false; });
                        }
                    },
                 }">

                {{-- Sidebar: lista de años --}}
                <aside>
                    <h3 class="font-sans text-[11px] tracking-[0.18em] uppercase text-muted font-bold mb-2.5">MENU</h3>
                    {{-- En movil los años van en fila envolvente (como un selector de
                         pestanas); apilados en vertical empujaban el contenido varias
                         pantallas hacia abajo. A partir de lg vuelve a ser columna. --}}
                    <nav class="flex flex-wrap lg:flex-col gap-1.5" aria-label="Seleccionar año">
                        @foreach($tree as $year)
                            {{-- El año activo se marca en navy solido (el color de
                                 seleccion de B); los inactivos son caja blanca con
                                 borde. Antes era al reves y el contraste enganaba. --}}
                            <button type="button"
                                    @click="setYear({{ $year['id'] }})"
                                    :class="yearId === {{ $year['id'] }} ? 'bg-brand-navy text-on-navy border-brand-navy' : 'bg-card text-brand-navy border-border hover:border-brand-primary hover:text-brand-primary'"
                                    class="flex-1 min-w-[76px] lg:flex-none lg:w-full text-center font-sans text-[13px] font-bold uppercase tracking-[0.07em] py-2.5 px-4 rounded-pill border transition-colors num-tabular focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                                {{ $year['year'] }}
                            </button>
                        @endforeach
                    </nav>
                </aside>

                {{-- Panel: meses + documentos --}}
                <div class="space-y-1.5">
                    @foreach($tree as $year)
                        <div x-show="yearId === {{ $year['id'] }}" x-cloak class="space-y-1.5">
                            @if(empty($year['months']))
                                <div class="text-center py-10 card-surface border-dashed">
                                    <p class="text-[15px] text-muted">No hay meses publicados para {{ $year['year'] }}.</p>
                                </div>
                            @endif

                            @foreach($year['months'] as $month)
                                @if($month['mode'] === 'redirect')
                                    {{-- Modo redireccion: enlace directo --}}
                                    <a href="{{ $month['redirect_url'] }}"
                                       target="_blank" rel="noopener"
                                       class="group flex items-center gap-3 px-4 py-3.5 card-surface hover:border-brand-primary hover:bg-brand-soft/40 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                                        <svg class="w-4 h-4 text-brand-primary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                                        </svg>
                                        <span class="flex-1 font-sans text-[14px] font-bold text-brand-navy">
                                            {{ $month['name'] }}
                                            @if($month['redirect_label'])
                                                <span class="text-muted font-normal">· {{ $month['redirect_label'] }}</span>
                                            @endif
                                        </span>
                                        <svg class="w-4 h-4 text-muted group-hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                        </svg>
                                    </a>
                                @else
                                    {{-- Modo archivos: acordeon --}}
                                    <div class="card-surface overflow-hidden"
                                         :class="monthId === {{ $month['id'] }} ? 'border-brand-primary' : ''">
                                        <button type="button"
                                                @click="setMonth({{ $month['id'] }})"
                                                :aria-expanded="monthId === {{ $month['id'] }}"
                                                class="w-full flex items-center justify-between gap-3 text-left px-4 py-3.5 hover:bg-brand-soft/40 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-primary">
                                            <span class="flex items-center gap-2.5 font-sans text-[14px] font-bold text-brand-navy">
                                                <svg class="w-4 h-4 transition-colors"
                                                     :class="monthId === {{ $month['id'] }} ? 'text-brand-primary' : 'text-brand-accent'"
                                                     fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 9h-15a4.483 4.483 0 0 0-3 1.146Z"/>
                                                </svg>
                                                {{ $month['name'] }}
                                            </span>
                                            <span class="shrink-0 font-sans text-[12px] text-muted uppercase tracking-[0.05em]">
                                                @if($month['documents_count'] > 0)
                                                    {{ $month['documents_count'] }} archivo{{ $month['documents_count'] !== 1 ? 's' : '' }}
                                                @else
                                                    Sin documentos
                                                @endif
                                            </span>
                                        </button>

                                        {{-- El listado real de este mes no esta en el HTML de la
                                             pagina: se pide a TransparencyController@documentos la
                                             primera vez que se abre (ver setMonth() mas arriba) y
                                             se pinta aqui con x-html. Antes esto llevaba, para CADA
                                             mes de CADA año, el HTML completo de sus documentos
                                             (aunque estuviera oculto); con el archivo historico de
                                             LOTAIP eso eran varios megabytes en cada visita. --}}
                                        <div x-show="monthId === {{ $month['id'] }}"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 -translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             style="display: none;">
                                            <template x-if="loading[{{ $month['id'] }}]">
                                                <div class="border-t border-border px-4 py-3.5 text-[14px] text-muted">
                                                    Cargando documentos…
                                                </div>
                                            </template>
                                            <div x-show="!loading[{{ $month['id'] }}]" x-html="loaded[{{ $month['id'] }}]"></div>
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
