/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./app/**/*.php",
    "./_sidebar.php"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('daisyui'),
  ],
  // Optional: DaisyUI configuration
  daisyui: {
    themes: ["light", "dark", "cupcake"], // example themes, can be customized
    darkTheme: "dark", // name of one of the included themes for dark mode
    base: true, // applies background color and foreground color for root element by default
    styled: true, // include daisyUI colors and design decisions for all components
    utils: true, // adds responsive and modifier utility classes
    rtl: false, // rotate style direction from left-to-right to right-to-left. You also need to add dir="rtl" to your html tag and install `tailwindcss-flip` plugin for Tailwind CSS.
    prefix: "", // prefix for daisyUI classnames (components, modifiers and responsive class names. Not colors)
    logs: true, // Show info about daisyUI version and used config in the console when building your CSS
  },
}
