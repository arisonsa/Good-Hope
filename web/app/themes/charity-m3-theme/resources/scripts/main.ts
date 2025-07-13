/**
 * Main theme TypeScript entry point.
 */

// Import global MWC base styles or StyleXJS global styles if any
// Example: If StyleXJS generates a global stylesheet for tokens/resets, import it here.
// import './global.stylex.css'; // If StyleXJS is set up to output a predictable global file

// Import and register Lit Web Components
import './components/charity-hero'; // Registers <charity-hero>

import './components/newsletter-form'; // Registers <newsletter-signup-form>
import './components/charity-card'; // Registers <charity-card>
import './components/charity-grid'; // Registers <charity-grid>
import './components/charity-cta-banner'; // Registers <charity-cta-banner>
import './components/charity-button'; // Registers <charity-button>
import './components/charity-donation-form'; // Registers <charity-donation-form>
import './components/charity-carousel'; // Registers <charity-carousel>
import './components/charity-counter'; // Registers <charity-counter>
import './components/charity-testimonial'; // Registers <charity-testimonial>
import './components/mobile-nav-toggle'; // Registers <mobile-nav-toggle>
import './components/analytics-dashboard'; // Registers <analytics-dashboard>

// Example:

// Example: Newsletter Signup Form (if refactored to Lit/StyleXJS)
// import './components/NewsletterForm';


// Import MWC components that are still used directly (if any)
// e.g., for form elements if not yet replaced by custom Lit/StyleXJS versions.
// import '@material/web/button/filled-button.js';
// import '@material/web/textfield/outlined-text-field.js';
// import '@material/web/icon/icon.js';

// Import M3 Typography styles (if still using MWC's global typescale classes approach)
// Note: With StyleXJS, typography would ideally be handled via typed tokens and applied in components.
// If MWC's md-typescale-styles.js is still needed for some parts:
/*
import { styles as typescaleStyles } from '@material/web/typography/md-typescale-styles.js';
import { styles as themeStyles } from '@material/web/theme/theme-styles.js'; // Base theme styles

if (document.adoptedStyleSheets) {
  document.adoptedStyleSheets = [...document.adoptedStyleSheets, themeStyles.styleSheet, typescaleStyles.styleSheet];
} else {
  const style = document.createElement('style');
  style.textContent = themeStyles.cssText + typescaleStyles.cssText;
  document.head.appendChild(style);
}
*/


console.log('Charity M3 Theme TypeScript initialized. StyleXJS and Lit components will be managed here.');

// HMR (Hot Module Replacement)
if (import.meta.webpackHot) {
  import.meta.webpackHot.accept();
}
