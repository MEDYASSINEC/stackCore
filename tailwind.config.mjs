/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        ecoGreen: '#2E7D32',
        deepBlue: '#1E3A5F'
      }
    }
  },
  plugins: []
};
