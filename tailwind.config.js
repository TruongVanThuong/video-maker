import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            colors: {
                'on-surface': '#131b2e',
                'surface-variant': '#dae2fd',
                'on-background': '#131b2e',
                'on-secondary-fixed': '#2a1700',

                'primary-container': '#5b21b6',
                'secondary-container': '#fea619',

                primary: '#420093',
                secondary: '#855300',

                surface: '#faf8ff',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f2f3ff',
                'surface-container': '#eaedff',
                'surface-container-high': '#e2e7ff',
                'surface-container-highest': '#dae2fd',

                'on-surface-variant': '#4a4453',
                outline: '#7b7485',
                'outline-variant': '#ccc3d6',

                error: '#ba1a1a',
                'error-container': '#ffdad6',

                'tertiary-container': '#005438',
                'tertiary-fixed': '#6ffbbe',

                'primary-fixed': '#ebddff',
                'primary-fixed-dim': '#d3bbff',

                'secondary-fixed': '#ffddb8',
                'secondary-fixed-dim': '#ffb95f',

                'inverse-surface': '#283044',
                'inverse-on-surface': '#eef0ff',
            },

            spacing: {
                'space-xs': '0.25rem',
                'space-sm': '0.5rem',
                'space-md': '0.75rem',
                'space-lg': '1.25rem',
                'space-xl': '2rem',
                'margin-mobile': '1rem',
            },

            borderRadius: {
                DEFAULT: '0.25rem',
                lg: '0.5rem',
                xl: '0.75rem',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                jakarta: ['Plus Jakarta Sans', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
