/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],
    safelist: [
      // Ajouter ici les classes dynamiques qui pourraient ne pas être détectées
      'bg-3hcig-blue',
      'bg-3hcig-blue-dark',
      'text-3hcig-blue',
      'text-3hcig-green',
      'bg-3hcig-green',
      'border-3hcig-blue',
      'hover:bg-3hcig-blue',
      'hover:bg-3hcig-blue-light',
      'hover:bg-3hcig-green',
      'hover:bg-3hcig-green-light',
      'focus:outline-3hcig-blue',
      'focus:outline-3hcig-green',
      'focus:ring-offset-3hcig-blue',
      'bg-3hcig-blue/10',
      'bg-3hcig-green/10',
      'bg-3hcig-green-light/20',
      'focus:outline-3hcig-blue',
      'focus:outline-3hcig-green',
      'transition-shadow',
      'transition-colors',
      'transition-opacity',
      'duration-150',
      'duration-300'
    ],
    theme: {
      extend: {
        colors: {
          // Couleurs personnalisées basées sur le logo 3HCIG
          '3hcig-blue': {
            DEFAULT: '#1976D2',  // Bleu principal du logo
            'light': '#4791db',  // Version plus claire du bleu
            'dark': '#115293',   // Version plus foncée du bleu
          },
          '3hcig-green': {
            DEFAULT: '#10A54A',  // Vert principal du logo
            'light': '#4caf50',  // Version plus claire du vert
            'dark': '#087f38',   // Version plus foncée du vert
          },
          // Redéfinition des couleurs standard utilisées
          gray: {
            '100': '#f3f4f6',
            '200': '#e5e7eb',
            '300': '#d1d5db',
            '400': '#9ca3af',
            '500': '#6b7280',
            '600': '#4b5563',
            '700': '#374151',
            '800': '#1f2937',
            '900': '#111827'
          },
          white: '#ffffff',
          black: '#000000',
          red: {
            '600': '#dc2626'
          }
        },
        // Ajout de styles d'outline négatifs
        outlineOffset: {
          '-1': '-1px',
          '-2': '-2px'
        },
        // Ajout de shadow personnalisés
        boxShadow: {
          'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
          'DEFAULT': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
          'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
          'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        }
      },
    },
    plugins: [],
  }
