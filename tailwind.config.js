// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Esto es vital para que funcione con la clase .dark
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                darkBg: '#FF7F50',
                darkText: '#ffffff',
                lightBg: '#ffffff',
                lightText: '#1a202c',
                success: '#16A34A',
                danger: '#FF0000',
                warning: '#FFFF00',
                info: '#0000FF',
            },
        },
    },
    plugins: [forms],
};