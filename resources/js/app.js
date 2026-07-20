import './bootstrap';
import { animate, inView, stagger } from 'motion';
import AOS from 'aos';
import 'aos/dist/aos.css';
import Sortable from 'sortablejs';

window.Sortable = Sortable;

// Alpine: en paginas con Livewire (panel admin / editor visual) Livewire trae
// su propia instancia y dispara 'alpine:init' antes de iniciarla. En paginas
// publicas sin Livewire (home, convocatorias, etc.) Alpine no se carga solo,
// asi que lo importamos y arrancamos manualmente. La deteccion busca
// @livewireScripts (que define window.Livewire o inyecta un script con
// 'livewire' en la URL).
const hasLivewire = typeof window.Livewire !== 'undefined'
    || document.querySelector('script[src*="livewire"]') !== null;

if (!hasLivewire) {
    import('alpinejs').then(({ default: Alpine }) => {
        window.Alpine = Alpine;
        // Alpine.start() dispara alpine:init internamente antes de procesar
        // el DOM, asi nuestros document.addEventListener('alpine:init', ...)
        // de mas abajo ejecutaran y registraran stores/data a tiempo.
        Alpine.start();
    });
}

// ===========================================================================
//  Configuracion global de animaciones
// ===========================================================================
const root = document.documentElement;
const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const animEnabled = root.dataset.animEnabled === 'true' && !prefersReduced;
const animOnMobile = root.dataset.animMobile === 'true';
const isMobile = window.matchMedia('(max-width: 767px)').matches;
const speedMap = { slow: 1.4, normal: 1, fast: 0.65 };
const animSpeed = speedMap[root.dataset.animSpeed] || 1;

const ANIM_OK = animEnabled && (animOnMobile || !isMobile);

window.AAG_ANIM = { enabled: ANIM_OK, speed: animSpeed };

// ===========================================================================
//  AOS - fade/slide al hacer scroll
// ===========================================================================
if (ANIM_OK) {
    AOS.init({
        duration: Math.round(700 * animSpeed),
        easing: 'ease-out-cubic',
        once: true,
        offset: 60,
        disable: () => !animOnMobile && isMobile,
    });
}

// ===========================================================================
//  Stagger - usado en grids de cards (vuelos, accesos, valores)
// ===========================================================================
function staggerEntry(selector, opts = {}) {
    if (!ANIM_OK) return;
    const els = document.querySelectorAll(selector);
    if (!els.length) return;

    inView(selector, () => {
        animate(
            selector,
            { opacity: [0, 1], transform: ['translateY(16px)', 'translateY(0)'] },
            {
                duration: 0.55 * animSpeed,
                delay: stagger(opts.gap ?? 0.08, { start: opts.start ?? 0 }),
                easing: [0.22, 1, 0.36, 1],
            }
        );
    }, { amount: 0.15 });
}

// ===========================================================================
//  Counter - cuenta numeros animadamente cuando entra al viewport
//  Usa data-count-to="8200000" data-count-format="compact|integer|percent"
// ===========================================================================
function setupCounters() {
    if (!ANIM_OK) return;
    const els = document.querySelectorAll('[data-count-to]');
    els.forEach((el) => {
        const target = parseFloat(el.dataset.countTo);
        const format = el.dataset.countFormat || 'integer';
        const suffix = el.dataset.countSuffix || '';
        const prefix = el.dataset.countPrefix || '';
        const decimals = parseInt(el.dataset.countDecimals || '0', 10);

        const formatNumber = (n) => {
            if (format === 'compact') {
                if (n >= 1_000_000) return (n / 1_000_000).toFixed(decimals || 1) + 'M';
                if (n >= 1_000) return (n / 1_000).toFixed(decimals || 0) + 'K';
                return n.toFixed(decimals);
            }
            if (format === 'percent') return n.toFixed(decimals) + '%';
            return Math.round(n).toString();
        };

        el.textContent = prefix + formatNumber(0) + suffix;

        inView(el, () => {
            const startTime = performance.now();
            const duration = 1400 * animSpeed;
            const tick = (now) => {
                const t = Math.min(1, (now - startTime) / duration);
                const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
                el.textContent = prefix + formatNumber(target * eased) + suffix;
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }, { amount: 0.4 });
    });
}

// ===========================================================================
//  Hero h1 - animacion palabra por palabra (estilo editorial)
// ===========================================================================
function animateHeroTitle() {
    if (!ANIM_OK) return;
    const h1 = document.querySelector('[data-hero-title]');
    if (!h1) return;
    const words = h1.querySelectorAll('[data-word]');
    if (!words.length) return;

    animate(
        words,
        { opacity: [0, 1], transform: ['translateY(20px)', 'translateY(0)'] },
        {
            duration: 0.65 * animSpeed,
            delay: stagger(0.045, { start: 0.15 }),
            easing: [0.22, 1, 0.36, 1],
        }
    );
}

// ===========================================================================
//  Smooth scroll en anclas internas
// ===========================================================================
function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach((a) => {
        a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (id.length < 2) return;
            const target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
        });
    });
}

// ===========================================================================
//  Inicializacion al cargar el DOM
// ===========================================================================
document.addEventListener('DOMContentLoaded', () => {
    animateHeroTitle();
    setupCounters();
    setupSmoothScroll();
    // OJO: cada valor de data-stagger usado en una plantilla TIENE que estar
    // registrado aqui. La regla base de app.css deja esos elementos en
    // opacity:0 a la espera de la animacion, asi que uno sin registrar queda
    // invisible de forma permanente.
    staggerEntry('[data-stagger="quick-link"]', { gap: 0.06 });
    staggerEntry('[data-stagger="flight-row"]', { gap: 0.09, start: 0.2 });
    staggerEntry('[data-stagger="value-row"]', { gap: 0.1 });
    staggerEntry('[data-stagger="stat"]', { gap: 0.08 });
});

// ===========================================================================
//  Stores y componentes Alpine (registrados via alpine:init para usar
//  la instancia de Alpine que trae Livewire 3, no una segunda instancia).
// ===========================================================================
document.addEventListener('alpine:init', () => {
    // Tema claro/oscuro
    window.Alpine.store('theme', {
        current: 'light',
        allowed: false,

        init() {
            this.allowed = root.dataset.themeAllowed === 'true';
            const storedPreference = localStorage.getItem('aag-theme');
            const defaultTheme = root.dataset.themeDefault || 'light';

            if (!this.allowed) {
                this.apply('light');
                return;
            }

            this.current = storedPreference || defaultTheme;
            this.apply(this.current);
        },

        toggle() {
            if (!this.allowed) return;
            this.current = this.current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('aag-theme', this.current);
            this.apply(this.current);
        },

        apply(theme) {
            document.documentElement.classList.toggle('dark', theme === 'dark');
        },
    });

    // Reloj Guayaquil
    window.Alpine.data('gyeClock', () => ({
        time: '',
        init() {
            this.update();
            setInterval(() => this.update(), 60000);
        },
        update() {
            const now = new Date();
            this.time = now.toLocaleTimeString('es-EC', {
                timeZone: 'America/Guayaquil',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
        },
    }));

    // Countdown
    window.Alpine.data('countdown', (targetIso) => ({
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        flash: { days: false, hours: false, minutes: false, seconds: false },
        interval: null,
        start() {
            const tick = () => {
                const diff = new Date(targetIso) - new Date();
                if (diff <= 0) {
                    this.days = this.hours = this.minutes = this.seconds = 0;
                    clearInterval(this.interval);
                    return;
                }
                const newDays = Math.floor(diff / 86400000);
                const newHours = Math.floor((diff % 86400000) / 3600000);
                const newMinutes = Math.floor((diff % 3600000) / 60000);
                const newSeconds = Math.floor((diff % 60000) / 1000);

                ['days', 'hours', 'minutes', 'seconds'].forEach((k) => {
                    const newVal = { days: newDays, hours: newHours, minutes: newMinutes, seconds: newSeconds }[k];
                    if (this[k] !== newVal) {
                        this.flash[k] = true;
                        setTimeout(() => (this.flash[k] = false), 220);
                    }
                });

                this.days = newDays;
                this.hours = newHours;
                this.minutes = newMinutes;
                this.seconds = newSeconds;
            };
            tick();
            this.interval = setInterval(tick, 1000);
        },
    }));

    // Visor de PDF de las fichas de convocatoria.
    //
    // El foco se atrapa a mano en lugar de con x-trap porque el plugin
    // @alpinejs/focus no esta instalado y la CSP del sitio no deja cargarlo
    // desde un CDN. Por el mismo motivo el PDF va en un <iframe> al mismo
    // origen y no en un <object>, que la CSP bloquea con object-src 'none'.
    window.Alpine.data('pdfPreview', () => ({
        abierto: false,
        url: '',
        nombre: '',
        origen: null,

        ver(url, nombre) {
            this.url = url;
            this.nombre = nombre;
            // Se guarda quien abrio el visor para devolverle el foco al cerrar:
            // sin esto el teclado vuelve al inicio del documento y se pierde el
            // punto de lectura en una lista larga de documentos.
            this.origen = document.activeElement;
            this.abierto = true;
            document.body.classList.add('overflow-hidden');
            // x-show aplica la visibilidad despues de este tick; enfocar antes
            // seria enfocar un elemento aun oculto y el navegador lo ignora.
            this.$nextTick(() => this.enfocables()[0]?.focus());
        },

        cerrar() {
            if (! this.abierto) return;
            this.abierto = false;
            document.body.classList.remove('overflow-hidden');
            // Se vacia la URL para que el iframe suelte el PDF en vez de
            // mantenerlo cargado mientras el modal esta cerrado.
            this.url = '';
            this.origen?.focus();
        },

        enfocables() {
            if (! this.$refs.panel) return [];
            return Array.from(this.$refs.panel.querySelectorAll(
                'a[href], button:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])'
            )).filter((el) => el.offsetParent !== null);
        },

        // Ciclo manual de Tab / Shift+Tab: al llegar al ultimo elemento se
        // vuelve al primero, para que el foco no se escape al fondo de la
        // pagina mientras el modal esta abierto.
        atrapar(e) {
            const items = this.enfocables();
            if (! items.length) return;
            const primero = items[0];
            const ultimo = items[items.length - 1];

            if (e.shiftKey && document.activeElement === primero) {
                e.preventDefault();
                ultimo.focus();
            } else if (! e.shiftKey && document.activeElement === ultimo) {
                e.preventDefault();
                primero.focus();
            }
        },
    }));

    // Alerta de convocatoria
    window.Alpine.data('convocatoriaAlert', (id, mode, frequency) => ({
        show: false,
        mode,
        init() {
            const storageKey = `aag-conv-${id}`;
            const storage = frequency === 'always' ? null : (frequency === 'once' ? localStorage : sessionStorage);
            if (storage && storage.getItem(storageKey)) return;
            setTimeout(() => { this.show = true; }, 600);
            this.$watch('show', (v) => {
                if (!v && storage) storage.setItem(storageKey, '1');
            });
        },
        close() { this.show = false; },
    }));
});
