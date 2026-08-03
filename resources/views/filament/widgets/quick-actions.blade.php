<x-filament-widgets::widget>
    <div class="aag-quick-actions">
        {{-- Hero card de bienvenida --}}
        <div class="aag-welcome-card">
            <div class="aag-welcome-content">
                <div class="aag-welcome-text">
                    <p class="aag-welcome-kicker">PANEL DE ADMINISTRACIÓN</p>
                    <h2 class="aag-welcome-title">
                        Bienvenido/a, <span class="aag-welcome-name">{{ explode(' ', $userName)[0] }}</span>
                    </h2>
                    <p class="aag-welcome-subtitle">
                        Gestiona el portal institucional de la Autoridad Aeroportuaria de Guayaquil.
                    </p>
                </div>
                <div class="aag-welcome-illustration" aria-hidden="true">
                    <svg viewBox="0 0 120 120" fill="none">
                        <circle cx="60" cy="60" r="56" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 6" opacity="0.3"/>
                        <path d="M40 65 L60 30 L80 65 L70 60 L60 80 L50 60 Z" fill="currentColor" opacity="0.85"/>
                        <circle cx="60" cy="60" r="3" fill="currentColor"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Tarjetas de accesos rápidos --}}
        <div class="aag-actions-grid">
            @if($homePageId)
                <a href="{{ url('/admin/visual-editor/'.$homePageId) }}" class="aag-action-card aag-action-primary">
                    <div class="aag-action-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/>
                        </svg>
                    </div>
                    <div class="aag-action-body">
                        <h3>Editor visual del Home</h3>
                        <p>Modifica los bloques de la página de inicio</p>
                    </div>
                    <svg class="aag-action-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            @endif

            <a href="{{ url('/admin/pages') }}" class="aag-action-card">
                <div class="aag-action-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                </div>
                <div class="aag-action-body">
                    <h3>Páginas</h3>
                    <p>Gestionar contenido del sitio</p>
                </div>
                <svg class="aag-action-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>

            <a href="{{ url('/admin/convocatorias') }}" class="aag-action-card">
                <div class="aag-action-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.319-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73"/>
                    </svg>
                </div>
                <div class="aag-action-body">
                    <h3>Convocatorias</h3>
                    <p>Procesos de selección abiertos</p>
                </div>
                <svg class="aag-action-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>

            <a href="{{ url('/admin/site-settings-page') }}" class="aag-action-card">
                <div class="aag-action-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </div>
                <div class="aag-action-body">
                    <h3>Configuración del sitio</h3>
                    <p>Logo, colores, contacto, redes</p>
                </div>
                <svg class="aag-action-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>

    <style>
        .aag-quick-actions {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .aag-welcome-card {
            background: linear-gradient(135deg, rgb(30 58 138) 0%, rgb(46 95 169) 100%);
            border-radius: 1rem;
            padding: 2rem 2.25rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 24px -8px rgb(30 58 138 / 0.4);
        }
        .aag-welcome-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 100% 0%, rgb(91 143 217 / 0.4), transparent 50%);
            pointer-events: none;
        }
        .aag-welcome-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            position: relative;
        }
        .aag-welcome-text { flex: 1; }
        .aag-welcome-kicker {
            font-size: 0.7rem;
            letter-spacing: 0.16em;
            font-weight: 600;
            opacity: 0.7;
            margin: 0 0 0.5rem 0;
        }
        .aag-welcome-title {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            line-height: 1.2;
        }
        .aag-welcome-name { font-weight: 700; }
        .aag-welcome-subtitle {
            margin: 0;
            opacity: 0.85;
            font-size: 0.95rem;
            line-height: 1.5;
            max-width: 520px;
        }
        .aag-welcome-illustration {
            flex-shrink: 0;
            width: 100px;
            height: 100px;
            opacity: 0.9;
        }
        .aag-welcome-illustration svg {
            width: 100%;
            height: 100%;
        }
        @media (max-width: 768px) {
            .aag-welcome-illustration { display: none; }
            .aag-welcome-card { padding: 1.5rem; }
            .aag-welcome-title { font-size: 1.4rem; }
        }

        .aag-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        .aag-action-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: rgb(var(--gray-50));
            border: 1px solid rgb(var(--gray-200));
            border-radius: 0.75rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            position: relative;
        }
        .aag-action-card:hover {
            border-color: rgb(30 58 138);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -12px rgb(30 58 138 / 0.25);
            background: white;
        }
        .dark .aag-action-card {
            background: rgb(var(--gray-900));
            border-color: rgb(var(--gray-700));
        }
        .dark .aag-action-card:hover {
            background: rgb(var(--gray-800));
        }

        .aag-action-icon {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 0.625rem;
            background: rgb(30 58 138 / 0.1);
            color: rgb(30 58 138);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .aag-action-icon svg { width: 22px; height: 22px; }
        .dark .aag-action-icon {
            background: rgb(91 143 217 / 0.15);
            color: rgb(143 178 226);
        }
        .aag-action-card:hover .aag-action-icon {
            background: rgb(30 58 138);
            color: white;
        }

        .aag-action-primary .aag-action-icon {
            background: rgb(30 58 138);
            color: white;
        }
        .aag-action-primary {
            border-color: rgb(30 58 138 / 0.3);
            background: linear-gradient(135deg, rgb(240 245 252), rgb(255 255 255));
        }
        .dark .aag-action-primary {
            background: linear-gradient(135deg, rgb(18 36 86 / 0.4), rgb(var(--gray-900)));
            border-color: rgb(46 95 169 / 0.4);
        }

        .aag-action-body { flex: 1; min-width: 0; }
        .aag-action-body h3 {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0 0 0.2rem 0;
            color: rgb(var(--gray-900));
        }
        .dark .aag-action-body h3 { color: rgb(var(--gray-50)); }
        .aag-action-body p {
            font-size: 0.825rem;
            margin: 0;
            color: rgb(var(--gray-600));
            line-height: 1.4;
        }
        .dark .aag-action-body p { color: rgb(var(--gray-400)); }

        .aag-action-arrow {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            color: rgb(var(--gray-400));
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .aag-action-card:hover .aag-action-arrow {
            color: rgb(30 58 138);
            transform: translateX(3px);
        }
    </style>
</x-filament-widgets::widget>
