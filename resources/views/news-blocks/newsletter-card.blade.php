@props(['data'])
@php
    $kicker = $data['kicker'] ?? 'BOLETIN';
    $title = $data['title'] ?? 'Recibe nuestras noticias';
    $subtitle = $data['subtitle'] ?? null;
    $buttonLabel = $data['button_label'] ?? 'Suscribirme';
    $placeholder = $data['placeholder'] ?? 'tu@correo.com';
@endphp

<div class="rounded-card bg-brand-navy text-on-navy p-7 space-y-4"
     x-data="{
        email: '',
        loading: false,
        message: '',
        success: false,
        async submit() {
            if (!this.email || this.loading) return;
            this.loading = true;
            this.message = '';
            try {
                const res = await fetch('{{ route('subscribe.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: this.email,
                        source: 'news_sidebar',
                        website: '',
                    }),
                });
                const data = await res.json();
                this.success = !!data.ok;
                this.message = data.message;
                if (data.ok) this.email = '';
            } catch (e) {
                this.success = false;
                this.message = 'No se pudo conectar con el servidor.';
            } finally {
                this.loading = false;
            }
        }
     }">
    <p class="text-[10px] tracking-[0.18em] uppercase text-on-navy/60 font-semibold">
        {{ $kicker }}
    </p>

    <h3 class="font-serif text-[22px] leading-tight" style="font-weight:500;">
        {{ $title }}
    </h3>

    @if($subtitle)
        <p class="text-[13px] text-on-navy/70 leading-relaxed">
            {{ $subtitle }}
        </p>
    @endif

    <form @submit.prevent="submit()" class="space-y-3 pt-1" novalidate>
        {{-- Honeypot anti-bot --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off"
               class="absolute -left-[9999px] opacity-0 pointer-events-none" aria-hidden="true">

        <input type="email"
               x-model="email"
               required
               placeholder="{{ $placeholder }}"
               class="w-full px-4 py-2.5 rounded-pill bg-card text-fg text-sm placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-accent"
               :disabled="loading">

        <button type="submit"
                :disabled="loading || !email"
                {{-- Amarillo institucional = color de accion sobre fondo oscuro, con texto oscuro (token on-accent) --}}
                class="w-full px-4 py-2.5 rounded-pill bg-brand-accent text-on-accent font-semibold text-sm hover:bg-brand-accent/90 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            <span x-show="!loading">{{ $buttonLabel }}</span>
            <span x-show="loading" x-cloak>Enviando...</span>
        </button>

        <p x-show="message"
           x-cloak
           x-transition.opacity
           {{-- Sobre navy: tinte celeste de marca para el exito, rojo apagado legible para el error --}}
           :class="success ? 'text-brand-soft' : 'text-[#F2B8B5]'"
           class="text-xs leading-relaxed"
           x-text="message"></p>
    </form>
</div>
