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
      'duration-300',
      // Classes dynamiques Pharaon
      'bg-pharaoh-gold',
      'bg-pharaoh-gold-light',
      'bg-pharaoh-gold-bright',
      'bg-pharaoh-bronze',
      'bg-pharaoh-bronze-dark',
      'bg-pharaoh-black',
      'text-pharaoh-gold',
      'text-pharaoh-gold-light',
      'text-pharaoh-bronze',
      'border-pharaoh-gold',
      'hover:bg-pharaoh-gold',
      'hover:bg-pharaoh-gold-light',
      'hover:text-pharaoh-gold'
    ],
    theme: {
      extend: {
        colors: {
          // Charte « Le Pharaon » — noir & or
          pharaoh: {
            black: '#080808',
            gold: '#D39B23',
            'gold-light': '#E9B533',
            'gold-bright': '#FACE4A',
            bronze: '#B77F1D',
            'bronze-dark': '#885910',
          },
          background: '#F8F8F8',
          surface: '#FFFFFF',
          'surface-dark': '#111111',
          // Compatibilité : les anciennes classes 3HCIG sont remappées
          // sur la nouvelle identité visuelle (or pour l'action, vert pour le succès)
          '3hcig-blue': {
            DEFAULT: '#D39B23',   // or principal (ex-boutons bleus -> or)
            'light': '#E9B533',   // or clair
            'dark': '#885910',    // bronze foncé (ex-sidebar bleu foncé -> noir/or)
          },
          '3hcig-green': {
            DEFAULT: '#2E8B57',   // vert succès de la charte
            'light': '#3aa86e',   // vert clair
            'dark': '#1f6e43',    // vert foncé
          },
          // Redéfinition des couleurs standard utilisées
          gray: {
            '50': '#fafafa',
            '100': '#f5f5f5',
            '200': '#e7e7e7',
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
            '600': '#d64545'
          }
        },
        fontFamily: {
          sans: ['"Inter"', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
        },
        // Ajout de styles d'outline négatifs
        outlineOffset: {
          '-1': '-1px',
          '-2': '-2px'
        },
        // Ajout de shadow personnalisés (modernes, plus profonds et doux)
        boxShadow: {
          'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.04)',
          'DEFAULT': '0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.04)',
          'md': '0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -2px rgba(0, 0, 0, 0.04)',
          'lg': '0 10px 20px -4px rgba(0, 0, 0, 0.08), 0 4px 8px -4px rgba(0, 0, 0, 0.04)',
          'card': '0 1px 3px rgba(0,0,0,0.04), 0 8px 24px -8px rgba(0,0,0,0.08)',
          'gold': '0 8px 24px -8px rgba(211, 155, 35, 0.35)',
        },
        borderRadius: {
          '2xl': '1rem',
          '3xl': '1.5rem',
        }
      },
    },
    plugins: [],
  }
