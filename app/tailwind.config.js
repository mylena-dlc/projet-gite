/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    './node_modules/fullcalendar/**/*.js',
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        gray1: "#504f4b",
        gray2: "#adadad",
        green1: "#a9b4a4",
        green2: "#e6f3e0",
        terracota1: "#6E5340",
        terracota2: "#85634d",
        terracota3: "#ecdcd3",
        terracota4: "#b58869",
        white1: "#fcf7f1"
      },
      fontFamily: {
        sans: ['system-ui', 'sans-serif'],
        heading: ['"Segoe UI"', 'Roboto', 'Helvetica', 'Arial', 'sans-serif'],
      }
    },
  },
  plugins: [
    // require('tailwind-scrollbar-hide') // Plugin pour cacher la scrollbar

  ],
}

