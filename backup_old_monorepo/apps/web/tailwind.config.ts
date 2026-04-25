import type { Config } from 'tailwindcss';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        '../../packages/ui/src/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                dental: {
                    primary: '#0ea5e9',
                    secondary: '#6366f1',
                    accent: '#f43f5e',
                },
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
} satisfies Config;
