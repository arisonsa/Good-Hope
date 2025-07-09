import { LitElement, html, nothing } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale } from '../tokens';

// Assuming charity-button will be used. Keep md-icon if charity-button uses it.
import '@material/web/icon/icon.js'; // Keep if <md-icon> is used by <charity-button> or directly

// Define styles using StyleXJS
const ctaBannerStyles = stylex.create({
  base: {
    display: 'block', // Or 'flex' if content needs specific flex alignment
    position: 'relative',
    overflow: 'hidden',
    // Default text color can be set here or dynamically based on background
  },
  overlay: {
    position: 'absolute',
    inset: 0,
    backgroundColor: 'rgba(0,0,0,0.4)', // Default overlay for images
    zIndex: 0,
  },
  contentWrapper: {
    position: 'relative',
    zIndex: 1,
    width: '100%',
  },
  // Content width styles (similar to Hero)
  contentWidthContainer: { maxWidth: '1200px', marginLeft: 'auto', marginRight: 'auto', paddingLeft: '1rem', paddingRight: '1rem'},
  contentWidthNarrow: { maxWidth: '48rem', marginLeft: 'auto', marginRight: 'auto', paddingLeft: '1rem', paddingRight: '1rem'},
  contentWidthWide: { maxWidth: '80rem', marginLeft: 'auto', marginRight: 'auto', paddingLeft: '1rem', paddingRight: '1rem'},
  contentWidthFull: { paddingLeft: '1rem', paddingRight: '1rem'}, // Full width with padding
  contentWidthEdgeToEdge: {}, // No padding needed on wrapper

  title: {
    // Using M3 Headline Large for CTA title typically
    fontFamily: M3TypeScale.headlineLarge.fontFamily,
    fontSize: M3TypeScale.headlineLarge.fontSize,
    fontWeight: M3TypeScale.headlineLarge.fontWeight,
    lineHeight: M3TypeScale.headlineLarge.lineHeight,
    marginBottom: '0.75rem', // 12px
    // Responsive example
    '@media (min-width: 768px)': {
        fontFamily: M3TypeScale.displaySmall.fontFamily, // Larger on desktop
        fontSize: M3TypeScale.displaySmall.fontSize,
        fontWeight: M3TypeScale.displaySmall.fontWeight,
        lineHeight: M3TypeScale.displaySmall.lineHeight,
    },
  },
  text: {
    // Using M3 Body Large or Headline Small for text
    fontFamily: M3TypeScale.bodyLarge.fontFamily,
    fontSize: M3TypeScale.bodyLarge.fontSize,
    fontWeight: M3TypeScale.bodyLarge.fontWeight,
    lineHeight: M3TypeScale.bodyLarge.lineHeight,
    marginBottom: '1.5rem', // 24px
    maxWidth: '45em', // Max width for readability (prose)
    opacity: 0.9, // Slightly subdued if under a strong title
  },
  buttonsWrapper: {
    marginTop: '1.5rem', // 24px
    display: 'flex',
    flexWrap: 'wrap',
    gap: '1rem', // 16px
  },
  // Text alignment utilities
  textAlignLeft: { textAlign: 'left' },
  textAlignCenter: { textAlign: 'center' },
  textAlignRight: { textAlign: 'right' },
  // Justify content for text block and buttons based on text alignment
  contentJustifyLeft: { alignItems: 'flex-start', }, // For flex container if text is left
  contentJustifyCenter: { alignItems: 'center', },   // For flex container if text is center
  contentJustifyRight: { alignItems: 'flex-end', },  // For flex container if text is right
  buttonJustifyLeft: { justifyContent: 'flex-start'},
  buttonJustifyCenter: { justifyContent: 'center'},
  buttonJustifyRight: { justifyContent: 'flex-end'},
  // Max width for text when centered
  textCenterMaxWidth: {
    marginLeft: 'auto',
    marginRight: 'auto',
  }
});

// Type for buttons (same as Hero)
interface CtaButton {
  text: string;
  href: string;
  type?: 'filled' | 'outlined' | 'text' | 'elevated' | 'tonal';
  icon?: string;
  target?: string;
  rel?: string;
}

@customElement('charity-cta-banner')
export class CharityCtaBanner extends LitElement {

  @property({ type: String })
  title?: string;

  @property({ type: String })
  text?: string;

  @property({ type: Array })
  buttons: CtaButton[] = [];

  @property({ type: String, attribute: 'background-image' })
  backgroundImage?: string;

  @property({ type: String, attribute: 'background-color' })
  backgroundColor: string = M3SysColors.primaryContainer; // Default M3 token

  @property({ type: String, attribute: 'text-color' })
  textColor: string = M3SysColors.onPrimaryContainer; // Default M3 token

  @property({ type: String, attribute: 'text-alignment' })
  textAlignment: 'left' | 'center' | 'right' = 'center';

  @property({ type: String, attribute: 'content-width' })
  contentWidth: 'container' | 'narrow' | 'wide' | 'full' | 'edge-to-edge' = 'container';

  @property({ type: String })
  padding: string = '3rem 0'; // Default vertical padding, e.g., 'py-12 md:py-20' from Blade

  @property({ type: Boolean, attribute: 'show-overlay'})
  showOverlay = true; // Default to true if background image is used

  // Light DOM for simpler global style application if needed, or for slotted plain HTML.
  // createRenderRoot() { return this; }

  render() {
    const baseDynamicStyle = {
      padding: this.padding, // Apply padding prop directly
      backgroundColor: this.backgroundImage ? undefined : this.backgroundColor,
      color: this.textColor,
      backgroundImage: this.backgroundImage ? `url(${this.backgroundImage})` : undefined,
      backgroundSize: this.backgroundImage ? 'cover' : undefined,
      backgroundPosition: this.backgroundImage ? 'center' : undefined,
    };

    let contentWidthClass;
    switch(this.contentWidth) {
        case 'container': contentWidthClass = ctaBannerStyles.contentWidthContainer; break;
        case 'narrow': contentWidthClass = ctaBannerStyles.contentWidthNarrow; break;
        case 'wide': contentWidthClass = ctaBannerStyles.contentWidthWide; break;
        case 'full': contentWidthClass = ctaBannerStyles.contentWidthFull; break;
        case 'edge-to-edge': contentWidthClass = ctaBannerStyles.contentWidthEdgeToEdge; break;
        default: contentWidthClass = ctaBannerStyles.contentWidthContainer;
    }

    let textAlignStyle, contentJustifyStyle, buttonJustifyStyle, textBlockAlignmentClass;
    switch(this.textAlignment) {
        case 'left':
            textAlignStyle = ctaBannerStyles.textAlignLeft;
            contentJustifyStyle = ctaBannerStyles.contentJustifyLeft;
            buttonJustifyStyle = ctaBannerStyles.buttonJustifyLeft;
            textBlockAlignmentClass = undefined;
            break;
        case 'right':
            textAlignStyle = ctaBannerStyles.textAlignRight;
            contentJustifyStyle = ctaBannerStyles.contentJustifyRight;
            buttonJustifyStyle = ctaBannerStyles.buttonJustifyRight;
            textBlockAlignmentClass = undefined; // Text itself will align right due to parent
            break;
        default: // center
            textAlignStyle = ctaBannerStyles.textAlignCenter;
            contentJustifyStyle = ctaBannerStyles.contentJustifyCenter;
            buttonJustifyStyle = ctaBannerStyles.buttonJustifyCenter;
            textBlockAlignmentClass = ctaBannerStyles.textCenterMaxWidth;
            break;
    }

    // The main section needs display:flex and justify/align items if the contentWrapper is not full height.
    // For a banner that's usually a block, we apply text-align to the contentWrapper.
    const sectionClasses = stylex.props(ctaBannerStyles.base, textAlignStyle).className;

    return html`
      <section class=${sectionClasses} style=${this.buildStyleString(baseDynamicStyle)}>
        ${this.backgroundImage && this.showOverlay ? html`<div ${stylex.props(ctaBannerStyles.overlay)}></div>` : nothing}

        <div ${stylex.props(ctaBannerStyles.contentWrapper, contentWidthClass /*, contentJustifyStyle - if wrapper is flex */)}>
          ${this.title ? html`<h2 ${stylex.props(ctaBannerStyles.title)}>${this.title}</h2>` : nothing}

          ${this.text || this.querySelector(':not([slot])') /* check for default slot content */ ? html`
            <div ${stylex.props(ctaBannerStyles.text, textBlockAlignmentClass)}>
              ${this.text ? html`<p>${this.text}</p>` : nothing}
              <slot></slot> {{-- For more complex rich text content --}}
            </div>
          ` : nothing}

          ${this.buttons.length > 0 ? html`
            <div ${stylex.props(ctaBannerStyles.buttonsWrapper, buttonJustifyStyle)}>
              ${this.buttons.map(button => html`
                <charity-button
                  variant=${button.type || 'filled'}
                  href=${button.href || ''}
                  target=${button.target || nothing}
                  rel=${button.rel || nothing}
                  icon=${button.icon || nothing}
                >
                  ${button.text}
                </charity-button>
              `)}
            </div>
          ` : nothing}
        </div>
      </section>
    `;
  }

  private buildStyleString(styleObj: object): string {
    return Object.entries(styleObj)
      .filter(([, value]) => value !== undefined)
      .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}:${value}`)
      .join(';');
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'charity-cta-banner': CharityCtaBanner;
  }
}
