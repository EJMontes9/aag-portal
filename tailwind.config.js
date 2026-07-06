import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/View/Components/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--font-sans)', 'Inter', ...defaultTheme.fontFamily.sans],
                serif: ['var(--font-serif)', 'Fraunces', ...defaultTheme.fontFamily.serif],
                mono: ['var(--font-mono)', 'JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Tokens semanticos: leen del CSS variable inyectado desde BD
                bg: 'rgb(var(--color-bg) / <alpha-value>)',
                fg: 'rgb(var(--color-fg) / <alpha-value>)',
                muted: 'rgb(var(--color-muted) / <alpha-value>)',
                card: 'rgb(var(--color-card) / <alpha-value>)',
                border: 'rgb(var(--color-border) / <alpha-value>)',
                brand: {
                    navy: 'rgb(var(--color-navy) / <alpha-value>)',
                    primary: 'rgb(var(--color-primary) / <alpha-value>)',
                    accent: 'rgb(var(--color-accent) / <alpha-value>)',
                    soft: 'rgb(var(--color-soft) / <alpha-value>)',
                },
                on: {
                    navy: 'rgb(var(--color-on-navy) / <alpha-value>)',
                    primary: 'rgb(var(--color-on-primary) / <alpha-value>)',
                },
            },
            borderRadius: {
                // Tokens de tema: leen del CSS variable que fija cada tema activo
                // (institucional = redondeado, corporativo = cuadrado). Ver Theme.php.
                'pill': 'var(--radius-pill, 999px)',
                'card': 'var(--radius-card, 12px)',
                'hero': 'var(--radius-hero, 24px)',
            },
            fontSize: {
                'kicker': ['0.6875rem', { lineHeight: '1.2', letterSpacing: '0.14em', fontWeight: '600' }],
            },
            animation: {
                'pulse-soft': 'pulse 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'blink': 'blink 1.2s ease-in-out infinite',
                'pop-in': 'popIn 0.25s cubic-bezier(0.2, 1.2, 0.4, 1)',
            },
            keyframes: {
                blink: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.35' },
                },
                popIn: {
                    '0%': { opacity: '0', transform: 'scale(0.95) translateY(8px)' },
                    '100%': { opacity: '1', transform: 'scale(1) translateY(0)' },
                },
            },
        },
    },
    plugins: [forms, typography],
};
