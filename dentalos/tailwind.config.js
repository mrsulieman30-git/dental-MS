/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#edf3f7',
          100: '#dbe7ef',
          200: '#b7cede',
          300: '#93b6cd',
          400: '#6f9dbd',
          500: '#1A3C5E', // Dental Blue
          600: '#15304b',
          700: '#102438',
          800: '#0b1825',
          900: '#050c12',
        },
        secondary: {
          500: '#2E6DA4',
        },
        accent: {
          500: '#3A8DBF',
        },
        success: {
          50: '#f0fdf4',
          500: '#22c55e',
          900: '#14532d',
        },
        warning: {
          50: '#fffbeb',
          500: '#f59e0b',
          900: '#78350f',
        },
        danger: {
          50: '#fef2f2',
          500: '#ef4444',
          900: '#7f1d1d',
        },
      },
      fontFamily: {
        sans: ['Inter', 'Outfit', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
