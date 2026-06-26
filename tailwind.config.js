import forms from '@tailwindcss/forms';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['Archivo', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"Space Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Maritime / industrial palette (HSL via CSS vars, themeable)
                border: 'hsl(var(--border) / <alpha-value>)',
                input: 'hsl(var(--input) / <alpha-value>)',
                ring: 'hsl(var(--ring) / <alpha-value>)',
                background: 'hsl(var(--background) / <alpha-value>)',
                foreground: 'hsl(var(--foreground) / <alpha-value>)',
                primary: {
                    DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
                    foreground: 'hsl(var(--primary-foreground) / <alpha-value>)',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent) / <alpha-value>)',
                    foreground: 'hsl(var(--accent-foreground) / <alpha-value>)',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted) / <alpha-value>)',
                    foreground: 'hsl(var(--muted-foreground) / <alpha-value>)',
                },
                card: {
                    DEFAULT: 'hsl(var(--card) / <alpha-value>)',
                    foreground: 'hsl(var(--card-foreground) / <alpha-value>)',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive) / <alpha-value>)',
                    foreground: 'hsl(var(--destructive-foreground) / <alpha-value>)',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary) / <alpha-value>)',
                    foreground: 'hsl(var(--secondary-foreground) / <alpha-value>)',
                },
                positive: 'hsl(var(--positive) / <alpha-value>)',
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            keyframes: {
                'route-dash': { to: { 'stroke-dashoffset': '-14' } },
                'transit-pulse': { '0%,100%': { opacity: '1' }, '50%': { opacity: '0.3' } },
                shimmer: { '0%': { transform: 'translateX(-120%)' }, '100%': { transform: 'translateX(220%)' } },
                scanline: { '0%': { transform: 'translateY(-100%)', opacity: '0' }, '8%,92%': { opacity: '0.5' }, '100%': { transform: 'translateY(900%)', opacity: '0' } },
                'glow-pulse': { '0%,100%': { opacity: '0.35' }, '50%': { opacity: '0.7' } },
                'gradient-shift': { '0%,100%': { 'background-position': '0% 50%' }, '50%': { 'background-position': '100% 50%' } },
                reveal: { from: { opacity: '0', transform: 'translateY(24px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
            },
            animation: {
                'route-dash': 'route-dash 1.1s linear infinite',
                'transit-pulse': 'transit-pulse 1.8s ease-in-out infinite',
                shimmer: 'shimmer 1s ease',
                scanline: 'scanline 5s linear infinite',
                'glow-pulse': 'glow-pulse 6s ease-in-out infinite',
                'gradient-shift': 'gradient-shift 6s ease infinite',
                reveal: 'reveal 0.6s cubic-bezier(0.22,1,0.36,1) both',
            },
        },
    },
    plugins: [forms],
};
