import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                xs: ['0.75rem', { lineHeight: '1.125rem', letterSpacing: '0.025em' }],
                sm: ['0.875rem', { lineHeight: '1.25rem', letterSpacing: '0.0125em' }],
                base: ['1rem', { lineHeight: '1.5rem', letterSpacing: '0' }],
                lg: ['1.125rem', { lineHeight: '1.75rem', letterSpacing: '-0.0125em' }],
                xl: ['1.25rem', { lineHeight: '1.875rem', letterSpacing: '-0.025em' }],
                '2xl': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.025em' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem', letterSpacing: '-0.025em' }],
                '4xl': ['2.25rem', { lineHeight: '2.75rem', letterSpacing: '-0.05em' }],
                '5xl': ['3rem', { lineHeight: '3.5rem', letterSpacing: '-0.05em' }],
            },
            fontWeight: {
                thin: '200',
                extralight: '300',
                light: '300',
                normal: '400',
                medium: '500',
                semibold: '600',
                bold: '700',
                extrabold: '800',
            },
        },
    },

    plugins: [forms],
};
