import { LitElement, html, css, unsafeCSS } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale, M3SysShape } from '../tokens'; // Adjusted path if necessary

// Import MWC button if we want to use it directly for actions, or build custom with StyleXJS
// For this example, let's assume we might use MWC button for simplicity of its built-in features.
// If not, button styling would also be done with StyleXJS.
import '@material/web/button/filled-button.js';
import '@material/web/button/outlined-button.js';
import '@material/web/icon/icon.js'; // If buttons have icons

// Define styles using StyleXJS
const heroStyles = stylex.create({
  base: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
    paddingTop: '3rem', // Example padding, adjust as needed (or use spacing tokens)
    paddingBottom: '3rem',
    paddingLeft: '1rem',
    paddingRight: '1rem',
    overflow: 'hidden', // Ensure background image doesn't cause overflow
    // Default text color, can be overridden by theme or props
    color: M3SysColors.onSurface,
  },
  // Variants for background color could be defined here if not passed via style prop
  // e.g., primaryContainerBg: { backgroundColor: M3SysColors.primaryContainer }
  overlay: {
    position: 'absolute',
    inset: 0,
    backgroundColor: 'rgba(0,0,0,0.4)', // Default overlay, can be themed
    zIndex: 0,
  },
  contentWrapper: {
    position: 'relative',
    zIndex: 1,
    textAlign: 'center', // Default, can be overridden by prop
    width: '100%',
  },
  // Content width styles
  contentWidthContainer: {
    maxWidth: '1200px', // Corresponds to .container
    marginLeft: 'auto',
    marginRight: 'auto',
  },
  contentWidthNarrow: {
    maxWidth: '48rem', // max-w-3xl
    marginLeft: 'auto',
    marginRight: 'auto',
  },
  contentWidthWide: {
    maxWidth: '80rem', // max-w-7xl
    marginLeft: 'auto',
    marginRight: 'auto',
  },
  contentWidthFull: { // Full width with padding (padding is on base)
    // No specific max-width needed if base has padding
  },
  contentWidthEdgeToEdge: { // Full width no padding (base padding would need to be removed)
    // This would require base to have no horizontal padding
  },
  title: {
    // Apply M3 typescale for displayMedium or displayLarge
    fontFamily: M3TypeScale.displayMedium.fontFamily,
    fontSize: M3TypeScale.displayMedium.fontSize,
    fontWeight: M3TypeScale.displayMedium.fontWeight,
    lineHeight: M3TypeScale.displayMedium.lineHeight,
    letterSpacing: M3TypeScale.displayMedium.letterSpacing ?? 'normal',
    marginBottom: '1rem', // Example spacing
    // Responsive font size example (using media queries with StyleXJS)
    '@media (min-width: 768px)': {
        fontFamily: M3TypeScale.displayLarge.fontFamily,
        fontSize: M3TypeScale.displayLarge.fontSize,
        fontWeight: M3TypeScale.displayLarge.fontWeight,
        lineHeight: M3TypeScale.displayLarge.lineHeight,
        letterSpacing: M3TypeScale.displayLarge.letterSpacing ?? 'normal',
    },
  },
  subtitle: {
    fontFamily: M3TypeScale.headlineSmall.fontFamily,
    fontSize: M3TypeScale.headlineSmall.fontSize,
    fontWeight: M3TypeScale.headlineSmall.fontWeight,
    lineHeight: M3TypeScale.headlineSmall.lineHeight,
    marginBottom: '2rem', // Example spacing
    opacity: 0.9,
     '@media (min-width: 768px)': {
        fontFamily: M3TypeScale.headlineMedium.fontFamily,
        fontSize: M3TypeScale.headlineMedium.fontSize,
        fontWeight: M3TypeScale.headlineMedium.fontWeight,
        lineHeight: M3TypeScale.headlineMedium.lineHeight,
    },
  },
  buttonsWrapper: {
    marginTop: '2rem',
    display: 'flex',
    flexWrap: 'wrap',
    gap: '1rem', // Example spacing
    justifyContent: 'center', // Default, can be overridden
  },
  // Text alignment utilities
  textAlignLeft: { textAlign: 'left' },
  textAlignRight: { textAlign: 'right' },
  buttonJustifyLeft: { justifyContent: 'flex-start'},
  buttonJustifyRight: { justifyContent: 'flex-end'},
});

// Define the type for button objects
interface HeroButton {
  text: string;
  href: string;
  type?: 'filled' | 'outlined' | 'text' | 'elevated' | 'tonal'; // MWC button types
  icon?: string;
  target?: string; // _blank, etc.
  rel?: string;
}

@customElement('charity-hero')
export class CharityHero extends LitElement {
  // Use StyleXJS for component's static styles.
  // For dynamic styles based on props, we'll compute them in render or use inline styles.
  // Note: LitElement's `static styles` property expects `CSSResultGroup`.
  // StyleXJS generates class names. We apply these class names in the `render` method.
  // Global styles or component-level CSS from StyleXJS are typically handled by the build process
  // and linked via <link> or imported into the JS module that defines the component.

  @property({ type: String })
  title = '';

  @property({ type: String })
  subtitle = '';

  @property({ type: String, attribute: 'background-image' })
  backgroundImage?: string;

  @property({ type: String, attribute: 'background-color' })
  backgroundColor: string = M3SysColors.surfaceVariant; // Default, references CSS var

  @property({ type: String, attribute: 'text-color' })
  textColor: string = M3SysColors.onSurfaceVariant; // Default, references CSS var

  @property({ type: String, attribute: 'content-width' })
  contentWidth: 'container' | 'narrow' | 'wide' | 'full' | 'edge-to-edge' = 'container';

  @property({ type: String, attribute: 'text-alignment' })
  textAlignment: 'left' | 'center' | 'right' = 'center';

  @property({ type: Array })
  buttons: HeroButton[] = [];

  @property({ type: String, attribute: 'min-height' })
  minHeight = '60vh'; // e.g., '500px', '70vh'

  @property({ type: Boolean, attribute: 'show-overlay'})
  showOverlay = false;


  // This method is crucial: it tells Lit NOT to use Shadow DOM.
  // StyleXJS works best with global CSS and doesn't inherently pierce Shadow DOM.
  // If Shadow DOM is desired, a different approach to applying StyleXJS themes/styles
  // within the shadow root would be needed (e.g., adopting stylesheets or linking).
  // For simplicity with global M3 tokens as CSS vars, light DOM is easier.
  // createRenderRoot() {
  //   return this;
  // }
  // UPDATE: StyleXJS CAN work with Shadow DOM if styles are constructed and injected properly.
  // For this example, let's assume styles are global for simplicity of token application.
  // If using Shadow DOM (default for Lit), ensure your StyleXJS build outputs CSS that
  // can be linked or adopted by each component instance, or use constructable stylesheets with StyleXJS.
  // For now, we'll proceed as if StyleXJS classes are globally available or injected.

  render() {
    const baseStyle = {
      minHeight: this.minHeight,
      backgroundColor: this.backgroundImage ? undefined : this.backgroundColor, // Only apply if no image
      color: this.textColor,
      backgroundImage: this.backgroundImage ? `url(${this.backgroundImage})` : undefined,
      backgroundSize: this.backgroundImage ? 'cover' : undefined,
      backgroundPosition: this.backgroundImage ? 'center' : undefined,
    };

    let contentWidthClass;
    switch(this.contentWidth) {
        case 'container': contentWidthClass = heroStyles.contentWidthContainer; break;
        case 'narrow': contentWidthClass = heroStyles.contentWidthNarrow; break;
        case 'wide': contentWidthClass = heroStyles.contentWidthWide; break;
        case 'full': contentWidthClass = heroStyles.contentWidthFull; break;
        case 'edge-to-edge': contentWidthClass = heroStyles.contentWidthEdgeToEdge; break;
        default: contentWidthClass = heroStyles.contentWidthContainer;
    }

    let textAlignStyle;
    let buttonJustifyStyle;
    switch(this.textAlignment) {
        case 'left':
            textAlignStyle = heroStyles.textAlignLeft;
            buttonJustifyStyle = heroStyles.buttonJustifyLeft;
            break;
        case 'right':
            textAlignStyle = heroStyles.textAlignRight;
            buttonJustifyStyle = heroStyles.buttonJustifyRight;
            break;
        default: // center
            textAlignStyle = undefined; // Default is center in contentWrapper
            buttonJustifyStyle = undefined; // Default is center in buttonsWrapper
    }

    return html`
      <section ${stylex.props(heroStyles.base)} style=${this.buildStyleString(baseStyle)}>
        ${this.backgroundImage && this.showOverlay ? html`<div ${stylex.props(heroStyles.overlay)}></div>` : ''}
        <div ${stylex.props(heroStyles.contentWrapper, contentWidthClass, textAlignStyle)}>
          ${this.title ? html`<h1 ${stylex.props(heroStyles.title)}>${this.title}</h1>` : ''}
          ${this.subtitle ? html`<p ${stylex.props(heroStyles.subtitle)}>${this.subtitle}</p>` : ''}

          ${this.buttons.length > 0 ? html`
            <div ${stylex.props(heroStyles.buttonsWrapper, buttonJustifyStyle)}>
              ${this.buttons.map(button => {
                const buttonTag = button.type === 'filled' ? html`md-filled-button` :
                                  button.type === 'outlined' ? html`md-outlined-button` :
                                  // Add other MWC button types if needed (text, elevated, tonal)
                                  html`md-filled-button`; // Default
                return html`
                  <${buttonTag}
                    href=${button.href}
                    target=${button.target || ''}
                    rel=${button.rel || ''}
                  >
                    ${button.icon ? html`<md-icon slot="icon">${button.icon}</md-icon>` : ''}
                    ${button.text}
                  </${buttonTag}>`;
              })}
            </div>
          ` : ''}
          <slot></slot> {{-- For additional content --}}
        </div>
      </section>
    `;
  }

  // Helper to convert object to style string, filtering undefined
  private buildStyleString(styleObj: object): string {
    return Object.entries(styleObj)
      .filter(([, value]) => value !== undefined)
      .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}:${value}`)
      .join(';');
  }
}

// Make TypeScript aware of the new custom element
declare global {
  interface HTMLElementTagNameMap {
    'charity-hero': CharityHero;
  }
}
