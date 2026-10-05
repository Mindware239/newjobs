/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/views/**/*.php",
    "./public/**/*.js",
    "./public/**/*.html",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Nunito Sans"', 'sans-serif'],
      },
      colors: {
        primary: {
          DEFAULT: '#f05537',
          50: '#fff1ed',
          100: '#ffd4c9',
          200: '#ffb9a6',
          300: '#ff966c',
          400: '#ff7b4d',
          500: '#f05537',
          600: '#e55a2d',
          700: '#cc4b20',
          800: '#b23d15',
          900: '#99310d',
        },
        secondary: {
          DEFAULT: '#FF6A3D',
          light: '#ff855d',
        }
      },
    },
  },
  plugins: [],
}

