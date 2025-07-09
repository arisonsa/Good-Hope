/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/**/*.php',
    './resources/**/*.php',
    './resources/**/*.vue', // If using Vue
    './resources/**/*.js',
  ],
  theme: {
    extend: {
      // M3 Color System Integration with Tailwind
      // These colors should correspond to the CSS custom properties defined in main.scss
      // Example: :root { --md-sys-color-primary: #6750A4; }
      // Then here: primary: 'var(--md-sys-color-primary)',
      colors: {
        // Core Roles
        'primary': 'var(--md-sys-color-primary)',
        'on-primary': 'var(--md-sys-color-on-primary)',
        'primary-container': 'var(--md-sys-color-primary-container)',
        'on-primary-container': 'var(--md-sys-color-on-primary-container)',
        'secondary': 'var(--md-sys-color-secondary)',
        'on-secondary': 'var(--md-sys-color-on-secondary)',
        'secondary-container': 'var(--md-sys-color-secondary-container)',
        'on-secondary-container': 'var(--md-sys-color-on-secondary-container)',
        'tertiary': 'var(--md-sys-color-tertiary)',
        'on-tertiary': 'var(--md-sys-color-on-tertiary)',
        'tertiary-container': 'var(--md-sys-color-tertiary-container)',
        'on-tertiary-container': 'var(--md-sys-color-on-tertiary-container)',
        'error': 'var(--md-sys-color-error)',
        'on-error': 'var(--md-sys-color-on-error)',
        'error-container': 'var(--md-sys-color-error-container)',
        'on-error-container': 'var(--md-sys-color-on-error-container)',

        // Surface Roles
        'background': 'var(--md-sys-color-background)',
        'on-background': 'var(--md-sys-color-on-background)',
        'surface': 'var(--md-sys-color-surface)',
        'on-surface': 'var(--md-sys-color-on-surface)',
        'surface-variant': 'var(--md-sys-color-surface-variant)',
        'on-surface-variant': 'var(--md-sys-color-on-surface-variant)',
        'outline': 'var(--md-sys-color-outline)',
        'outline-variant': 'var(--md-sys-color-outline-variant)', // Added for completeness
        'shadow': 'var(--md-sys-color-shadow)',
        'scrim': 'var(--md-sys-color-scrim)', // Added for completeness

        // Inverse Roles
        'inverse-surface': 'var(--md-sys-color-inverse-surface)',
        'inverse-on-surface': 'var(--md-sys-color-inverse-on-surface)',
        'inverse-primary': 'var(--md-sys-color-inverse-primary)',

        // Surface Container Roles (Important for cards, dialogs, etc.)
        'surface-dim': 'var(--md-sys-color-surface-dim)', // Darker surfaces
        'surface-bright': 'var(--md-sys-color-surface-bright)', // Lighter surfaces
        'surface-container-lowest': 'var(--md-sys-color-surface-container-lowest)',
        'surface-container-low': 'var(--md-sys-color-surface-container-low)',
        'surface-container': 'var(--md-sys-color-surface-container)',
        'surface-container-high': 'var(--md-sys-color-surface-container-high)',
        'surface-container-highest': 'var(--md-sys-color-surface-container-highest)',
      },
      // M3 Typography Integration (if needed beyond md-typescale-* classes from MWC)
      // fontFamily: {
      //   sans: ['Roboto', ...defaultTheme.fontFamily.sans],
      // },
      // fontSize, fontWeight, letterSpacing, lineHeight for M3 typescale roles
      // can be defined here if not relying solely on MWC's adopted stylesheets.

      // M3 Shape Integration (Border Radius)
      // borderRadius: {
      //   'none': '0',
      //   'xs': 'var(--md-sys-shape-corner-extra-small, 4px)',
      //   'sm': 'var(--md-sys-shape-corner-small, 8px)',
      //   'md': 'var(--md-sys-shape-corner-medium, 12px)', // Default for cards
      //   'lg': 'var(--md-sys-shape-corner-large, 16px)',
      //   'xl': 'var(--md-sys-shape-corner-extra-large, 28px)',
      //   'full': 'var(--md-sys-shape-corner-full, 9999px)',
      // }
    },
  },
  plugins: [
    // require('@tailwindcss/typography'), // If using prose classes
  ],
};
