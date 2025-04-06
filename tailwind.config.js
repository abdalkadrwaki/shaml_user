import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },




            colors: {
                'custom-gray2': '#eff3f9', // اللون المخصص
                'bak-gray': '#162f6e',
                'bak-gray2': '#162f8e',
                'red-1': '#ef4444',
                'Lime': '#65a30d',
                'custom-gray': '#eff3f5', // اللون الرمادي المستخدم في الخلفية
                'form-error': '#dc2626' // لون نصوص الأخطاء
            },
        },
    },

    plugins: [forms, typography],
};
