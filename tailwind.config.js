/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './src/**/*.php',
    ],
    darkMode: 'class', // Add this for dark mode support
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
