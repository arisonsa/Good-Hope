import { LitElement, html, nothing, TemplateResult } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale, M3SysShape } from '../tokens';

const cardStyles = stylex.create({
  base: {
    display: 'block', // Or 'flex' with flexDirection: 'column' for consistent height cards in a row
    borderRadius: M3SysShape.corner.medium, // 12px
    overflow: 'hidden',
    transitionProperty: 'box-shadow, transform', // Added transform for potential hover effect
    transitionDuration: '0.25s',
    transitionTimingFunction: 'ease-out',
    position: 'relative', // For absolute positioning of link overlay if interactive
  },
  elevated: {
    backgroundColor: M3SysColors.surfaceContainerLow, // M3 Elevation Light Level 1
    // M3 Elevation is complex: specific shadow values for different levels.
    // StyleXJS can define these as tokens. For now, a simpler shadow.
    // True M3 elevation uses multiple layered shadows.
    // Example for level 1 (simplified):
    boxShadow: '0px 1px 2px rgba(0,0,0,0.3), 0px 1px 3px 1px rgba(0,0,0,0.15)',
    ':hover': {
        // Example for level 3 (simplified):
        boxShadow: '0px 2px 6px 2px rgba(0,0,0,0.15), 0px 1px 2px rgba(0,0,0,0.3)',
        transform: 'translateY(-2px)', // Optional subtle hover lift
    }
  },
  filled: {
    backgroundColor: M3SysColors.surfaceContainerHighest,
    // M3 Filled cards are elevation level 0, so no shadow by default
    boxShadow: 'none',
     ':hover': { // Optional: slight elevation or indication on hover
        backgroundColor: M3SysColors.surfaceContainerHigh, // Lighten slightly
    }
  },
  outlined: {
    backgroundColor: M3SysColors.surface,
    borderColor: M3SysColors.outline,
    borderWidth: '1px',
    borderStyle: 'solid',
    // M3 Outlined cards are elevation level 0
    boxShadow: 'none',
    ':hover': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 5%, ${M3SysColors.surface})`, // Subtle primary hover state
    }
  },
  interactiveLinkOverlay: { // For making the whole card clickable
    position: 'absolute',
    inset: 0,
    zIndex: 1, // Above content but below explicit actions
  },
  media: {
    width: '100%',
    // height: '12.5rem', // 200px, M3 spec often suggests 1:1, 16:9, or 3:2 aspect ratios for media
    aspectRatio: '16/9', // Common aspect ratio
    objectFit: 'cover',
    display: 'block',
  },
  header: {
    paddingTop: '1rem', // 16px
    paddingBottom: '1rem',
    paddingLeft: '1rem',
    paddingRight: '1rem',
  },
  title: {
    ...M3TypeScale.titleMedium, // Apply M3 typescale
    color: M3SysColors.onSurface, // Or onSurfaceVariant if subtitle is primary info
    marginBottom: '0.25rem', // If subtitle exists
  },
  subtitle: {
    ...M3TypeScale.bodyMedium,
    color: M3SysColors.onSurfaceVariant,
  },
  content: {
    ...M3TypeScale.bodyMedium,
    color: M3SysColors.onSurfaceVariant, // Or onSurface for primary content text
    paddingTop: '0rem', // Assumes header has padding-bottom
    paddingBottom: '1rem',
    paddingLeft: '1rem',
    paddingRight: '1rem',
  },
  actions: {
    paddingTop: '0.5rem', // 8px
    paddingBottom: '0.5rem',
    paddingLeft: '0.5rem', // Actions often have less padding on sides
    paddingRight: '0.5rem',
    display: 'flex',
    flexWrap: 'wrap',
    gap: '0.5rem', // 8px
    // Buttons inside actions might need specific alignment if not full width
  }
});

@customElement('charity-card')
export class CharityCard extends LitElement {
  @property({ type: String })
  href?: string;

  @property({ type: String, attribute: 'image-url' })
  imageUrl?: string;

  @property({ type: String, attribute: 'image-alt' })
  imageAlt = '';

  @property({ type: String })
  title?: string;

  @property({ type: String })
  subtitle?: string;

  @property({ type: String })
  variant: 'elevated' | 'filled' | 'outlined' = 'elevated';

  @property({ type: Boolean, reflect: true }) // Reflect for potential CSS targeting if needed
  interactive = false;

  // For StyleXJS with Lit, we apply classes in render.
  // If using Shadow DOM (default), ensure StyleXJS generated CSS is loaded into it.
  // For this example, we assume StyleXJS classes are globally available for simplicity.
  // To encapsulate styles, you would import the generated CSS for this component
  // and adopt it in `static styles` or use constructable stylesheets.
  // static styles = [unsafeCSS(styleStringFromStyleXBuild)]; // Example

  private renderCardContent() {
    return html`
      ${this.imageUrl ?
        html`<img class="card-media" src=${this.imageUrl} alt=${this.imageAlt || this.title || ''} ${stylex.props(cardStyles.media)} />` :
        nothing
      }
      ${(this.title || this.subtitle) ?
        html`
          <div class="card-header" ${stylex.props(cardStyles.header)}>
            ${this.title ? html`<h3 class="card-title" ${stylex.props(cardStyles.title)}>${this.title}</h3>` : nothing}
            ${this.subtitle ? html`<p class="card-subtitle" ${stylex.props(cardStyles.subtitle)}>${this.subtitle}</p>` : nothing}
          </div>
        ` : nothing
      }

      <div class="card-content-slot" ${stylex.props(cardStyles.content)}>
        <slot></slot> {{-- Default slot for main content --}}
      </div>

      <div class="card-actions-slot" ${stylex.props(cardStyles.actions)}>
        <slot name="actions"></slot> {{-- Slot for action buttons/links --}}
      </div>
    `;
  }

  render() {
    const variantStyle = this.variant === 'filled' ? cardStyles.filled :
                         this.variant === 'outlined' ? cardStyles.outlined :
                         cardStyles.elevated; // Default

    if (this.interactive && this.href) {
      return html`
        <a
          ${stylex.props(cardStyles.base, variantStyle)}
          href=${this.href}
          aria-label=${this.title || 'Card link'}
        >
          ${this.renderCardContent()}
        </a>
      `;
    } else {
      return html`
        <div ${stylex.props(cardStyles.base, variantStyle)}>
          ${this.renderCardContent()}
          ${this.href ? html`<a href=${this.href} ${stylex.props(cardStyles.interactiveLinkOverlay)} aria-label=${this.title || 'Card link'}></a>` : nothing}
        </div>
      `;
    }
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'charity-card': CharityCard;
  }
}
