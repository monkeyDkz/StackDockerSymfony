
/** @type {import('tailwindcss').Config} */


export default {
  content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
  theme: {
    extend: {},
  },
  plugins: [
    require('daisyui'),

  ],
}

