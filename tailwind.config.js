import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Surface hierarchy
                surface: {
                    base: '#0b1326',
                    card: '#131b2e',
                    'card-hover': '#1a233a',
                    elevated: '#171f33',
                    high: '#222a3d',
                    highest: '#2d3449',
                },
                // Borders
                border: {
                    subtle: '#1e293b',
                    DEFAULT: '#334155',
                    focus: '#475569',
                },
                // Brand and semantic colors
                brand: {
                    primary: {
                        DEFAULT: '#10b981',
                        hover: '#059669',
                        light: '#4edea3',
                        dark: '#003824',
                    },
                    secondary: {
                        DEFAULT: '#f59e0b',
                        hover: '#d97706',
                        light: '#ffb95f',
                        dark: '#472a00',
                    },
                    tertiary: {
                        DEFAULT: '#38bdf8',
                        hover: '#0284c7',
                        light: '#7bd0ff',
                        dark: '#00354a',
                    },
                    danger: {
                        DEFAULT: '#ef4444',
                        hover: '#dc2626',
                        light: '#ffb4ab',
                        dark: '#690005',
                    },
                },
                // Text hierarchy
                text: {
                    primary: '#f8fafc',
                    secondary: '#cbd5e1',
                    muted: '#94a3b8',
                    dim: '#64748b',
                },
            },
            borderRadius: {
                card: '0.75rem',   // 12px
                button: '0.5rem',  // 8px
                input: '0.5rem',   // 8px
                badge: '9999px',
                panel: '1rem',     // 16px
            },
        },
    },

    plugins: [forms],
};
