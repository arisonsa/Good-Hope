/**
 * Main theme JavaScript file.
 */

// Import Material Web Components we plan to use globally or as examples
// Individual components are imported to enable tree-shaking.
import '@material/web/button/filled-button.js';
import '@material/web/button/outlined-button.js';
import '@material/web/checkbox/checkbox.js';
import '@material/web/textfield/outlined-text-field.js';
import '@material/web/iconbutton/icon-button.js';
import '@material/web/icon/icon.js'; // Required by icon-button and other components
// Add other MWC components as needed:
// import '@material/web/dialog/dialog.js';
// import '@material/web/menu/menu.js';
// import '@material/web/select/outlined-select.js';
// import '@material/web/tabs/tabs.js';
// import '@material/web/list/list-item.js';
// import '@material/web/list/list.js';

// Import Material Design 3 Typography styles
// These are adopted into document.adoptedStyleSheets
import { styles as typescaleStyles } from '@material/web/typography/md-typescale-styles.js';
import { styles as themeStyles } from '@material/web/theme/theme-styles.js'; // Base theme styles

// Apply global M3 styles
// Note: adoptedStyleSheets is not supported in all older browsers (e.g., IE11).
// Polyfills or alternative methods might be needed for broader compatibility if required.
if (document.adoptedStyleSheets) {
  document.adoptedStyleSheets = [...document.adoptedStyleSheets, themeStyles.styleSheet, typescaleStyles.styleSheet];
} else {
  // Fallback for browsers that don't support adoptedStyleSheets
  const style = document.createElement('style');
  style.textContent = themeStyles.cssText + typescaleStyles.cssText;
  document.head.appendChild(style);
}


// Example: Initialize or interact with components if needed
document.addEventListener('DOMContentLoaded', () => {
  console.log('Charity M3 Theme JS loaded with Material Web Components.');

  // Example: Attach event listener to a Material button
  const exampleButton = document.querySelector('#myExampleMwcButton');
  if (exampleButton) {
    exampleButton.addEventListener('click', () => {
      alert('Material Button Clicked!');
    });
  }
});

// Custom theme scripts can go here
// e.g., mobile navigation toggles, animations, etc.

// If using Hot Module Replacement (HMR) with Laravel Mix
if (import.meta.webpackHot) {
  import.meta.webpackHot.accept();
}
