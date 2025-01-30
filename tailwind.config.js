const defaultTheme=require('@tailwindcss/defaultTheme')
module.exports = {
  content: [
    "./resources/views/auth/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/**/*.css",
  ],
  theme: {
    extend: {
      fontFamily:{
        sans:['inter var',... defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
    
  ],
}

