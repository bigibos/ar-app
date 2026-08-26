/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./index.{html,php}', './views/*.{html,php,js}', './public/*.{html,php,js}', './components/*.{html,php,js}', './pages/*.{html,php,js}'],
  theme: {
    extend: {
      fontFamily: { 
        main: ['Nunito Sans']
      },
      colors: {
        transparent: 'transparent',
        current: 'currentColor',
        main: {
          100: '#CBF2E1',
          200: '#AAE6CB',
          300: '#7ACCA8',
          400: '#4FB286',
          500: '#449973',
          600: '#398060',
          700: '#2D664D'
        },
        back: {
          100: '#FFFFFF',
          200: '#F4F4F9',
          300: '#E6E6F0',
          400: '#D8D8E6',
          500: '#CECEDB',
          600: '#C0C0D1',
          700: '#BABAC2'
        },
        front: {
          100: '#4F1D32',
          200: '#45192B',
          300: '#3B1625',
          400: '#30121F',
          500: '#260E18',
          600: '#1C0A12',
          700: '#13070C'
        }
      }
    },
  },
  plugins: [],
}

