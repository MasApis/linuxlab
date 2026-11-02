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
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                display: ['"Baloo 2"', 'Nunito', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Surface hierarchy (Pencil: surface #F7F7F7, raised #FFFFFF)
                surface: {
                    base: '#f7f7f7',
                    card: '#ffffff',
                    'card-hover': '#f5f5f5',
                    elevated: '#ffffff',
                    high: '#efefef',
                    highest: '#e5e5e5',
                },
                // Borders (Pencil: border #E5E5E5)
                border: {
                    subtle: '#eeeeee',
                    DEFAULT: '#e5e5e5',
                    focus: '#d4d4d4',
                },
                // Brand and semantic colors (Pencil Duolingo palette)
                brand: {
                    primary: {
                        DEFAULT: '#2f6e00',
                        hover: '#46a302',
                        light: '#2f6e00',
                        dark: '#46a302',
                    },
                    secondary: {
                        DEFAULT: '#ff9600',
                        hover: '#e08600',
                        light: '#a35c00',
                        dark: '#e08600',
                    },
                    tertiary: {
                        DEFAULT: '#1cb0f6',
                        hover: '#1899d6',
                        light: '#095f8f',
                        dark: '#1899d6',
                    },
                    danger: {
                        DEFAULT: '#ff4b4b',
                        hover: '#ea2b2b',
                        light: '#c42b2b',
                        dark: '#c42b2b',
                    },
                },
                // Text hierarchy (Pencil: #4B4B4B / #6B6B6B / #767676, headlines ink #100F3E)
                text: {
                    primary: '#4b4b4b',
                    secondary: '#6b6b6b',
                    muted: '#767676',
                    dim: '#9e9e9e',
                },
                ink: '#100f3e',
            },
            borderRadius: {
                card: '1rem',       // 16px
                button: '1rem',     // 16px
                input: '1rem',      // 16px
                badge: '9999px',
                panel: '1.5rem',    // 24px
            },
        },
    },

    plugins: [forms],
};
