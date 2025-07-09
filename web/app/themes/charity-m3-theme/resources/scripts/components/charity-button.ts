import { LitElement, html, nothing, TemplateResult, CSSResultGroup, unsafeCSS } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale, M3SysShape } from '../tokens';

// Re-import MWC icon as it's a separate concern from buttons and generally useful
import '@material/web/icon/icon.js';

const buttonStyles = stylex.create({
  base: {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative', // For potential ripple or focus ring
    minHeight: '2.5rem', // 40px M3 spec
    paddingTop: '0.625rem', // 10px
    paddingBottom: '0.625rem',
    paddingLeft: '1.5rem', // 24px (icon only or text only might have different padding)
    paddingRight: '1.5rem',
    borderRadius: M3SysShape.corner.full, // M3 default for many buttons
    borderWidth: '1px',
    borderStyle: 'solid',
    borderColor: 'transparent', // Default, overridden by variants
    boxSizing: 'border-box',
    verticalAlign: 'middle',
    textDecoration: 'none',
    cursor: 'pointer',
    userSelect: 'none',
    overflow: 'hidden', // For ripple or contained states
    transitionProperty: 'background-color, border-color, box-shadow, color',
    transitionDuration: '0.15s', // M3 standard duration for state changes
    transitionTimingFunction: 'ease-out',
    ...M3TypeScale.labelLarge, // Default typography for buttons
    ':focus-visible': { // M3 focus state often uses outline or state layer
        outlineWidth: '2px',
        outlineStyle: 'solid',
        outlineColor: M3SysColors.primary, // Or a specific focus ring color
        outlineOffset: '2px',
    }
  },
  baseWithIcon: {
    paddingLeft: '1rem', // 16px if icon is leading
  },
  baseIconOnly: { // For icon buttons (circular)
    padding: '0.5rem', // 8px
    width: '2.5rem', // 40px
    height: '2.5rem',
  },
  label: {
    // No specific styles needed here if base handles typography
  },
  iconSlot: {
    display: 'inline-flex',
    alignItems: 'center',
    fontSize: '1.125rem', // 18px M3 spec for icons in buttons
  },
  iconLeading: {
    marginRight: '0.5rem', // 8px
  },
  iconTrailing: {
    marginLeft: '0.5rem', // 8px
  },
  // Variants
  filled: {
    backgroundColor: M3SysColors.primary,
    color: M3SysColors.onPrimary,
    borderColor: M3SysColors.primary,
    ':hover': {
        // M3 uses state layers for hover, or slightly darker/lighter background
        // For simplicity, direct color change. A state layer would be an overlay <div/>
        backgroundColor: `color-mix(in srgb, ${M3SysColors.onPrimary} 8%, ${M3SysColors.primary})`, // M3 hover state (8% onPrimary)
        boxShadow: `0px 1px 2px rgba(0,0,0,0.3), 0px 1px 3px 1px rgba(0,0,0,0.15)` // M3 elevation 1 on hover
    },
    ':active': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.onPrimary} 12%, ${M3SysColors.primary})`, // M3 pressed state (12% onPrimary)
        boxShadow: 'none',
    }
  },
  outlined: {
    backgroundColor: 'transparent', // Or M3SysColors.surface if on colored background
    color: M3SysColors.primary,
    borderColor: M3SysColors.outline,
    ':hover': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 8%, transparent)`, // M3 hover state (8% primary)
    },
     ':active': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 12%, transparent)`, // M3 pressed state (12% primary)
        borderColor: M3SysColors.primary, // Border often becomes primary on press
    }
  },
  text: {
    backgroundColor: 'transparent',
    color: M3SysColors.primary,
    paddingLeft: '0.75rem', // 12px for text buttons
    paddingRight: '0.75rem',
    borderColor: 'transparent',
     ':hover': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 8%, transparent)`,
    },
     ':active': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 12%, transparent)`,
    }
  },
  elevated: {
    backgroundColor: M3SysColors.surfaceContainerLow,
    color: M3SysColors.primary,
    boxShadow: '0px 1px 2px rgba(0,0,0,0.3), 0px 1px 3px 1px rgba(0,0,0,0.15)', // M3 Elevation 1
    ':hover': {
        boxShadow: '0px 2px 6px 2px rgba(0,0,0,0.15), 0px 1px 2px rgba(0,0,0,0.3)', // M3 Elevation 2
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 8%, ${M3SysColors.surfaceContainerLow})`,
    },
    ':active': {
        boxShadow: '0px 1px 2px rgba(0,0,0,0.3), 0px 1px 3px 1px rgba(0,0,0,0.15)', // M3 Elevation 1
        backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 12%, ${M3SysColors.surfaceContainerLow})`,
    }
  },
  tonal: { // Filled Tonal Button
    backgroundColor: M3SysColors.secondaryContainer,
    color: M3SysColors.onSecondaryContainer,
    borderColor: M3SysColors.secondaryContainer,
     ':hover': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.onSecondaryContainer} 8%, ${M3SysColors.secondaryContainer})`,
        boxShadow: '0px 1px 2px rgba(0,0,0,0.3), 0px 1px 3px 1px rgba(0,0,0,0.15)' // M3 elevation 1 on hover
    },
    ':active': {
        backgroundColor: `color-mix(in srgb, ${M3SysColors.onSecondaryContainer} 12%, ${M3SysColors.secondaryContainer})`,
        boxShadow: 'none',
    }
  },
  disabled: {
    opacity: 0.38, // M3 standard for disabled state
    cursor: 'not-allowed',
    boxShadow: 'none',
    // Specific background/color for disabled state if needed, M3 often uses on-surface with opacity
    // backgroundColor: `color-mix(in srgb, ${M3SysColors.onSurface} 12%, transparent)`,
    // color: `color-mix(in srgb, ${M3SysColors.onSurface} 38%, transparent)`,
    // borderColor: `color-mix(in srgb, ${M3SysColors.onSurface} 12%, transparent)`,
  },
});

@customElement('charity-button')
export class CharityButton extends LitElement {
  @property({ type: String })
  variant: 'filled' | 'outlined' | 'text' | 'elevated' | 'tonal' = 'filled';

  @property({ type: String })
  href?: string;

  @property({ type: Boolean, reflect: true })
  disabled = false;

  @property({ type: String })
  icon?: string;

  @property({ type: Boolean, attribute: 'trailing-icon' })
  trailingIcon = false;

  @property({ type: String })
  label?: string; // For aria-label if no text content

  @property({ type: String })
  type: 'button' | 'submit' | 'reset' = 'button'; // For form buttons

  // createRenderRoot() { return this; } // Consider Light DOM if global StyleXJS classes are preferred

  private getVariantStyle() {
    switch (this.variant) {
      case 'filled': return buttonStyles.filled;
      case 'outlined': return buttonStyles.outlined;
      case 'text': return buttonStyles.text;
      case 'elevated': return buttonStyles.elevated;
      case 'tonal': return buttonStyles.tonal;
      default: return buttonStyles.filled;
    }
  }

  render() {
    const isIconOnly = !this.querySelector(':not([slot="icon"])') && !this.textContent?.trim() && !!this.icon && !this.label;

    const baseDynamicStyles = [
        buttonStyles.base,
        this.getVariantStyle(),
        this.disabled ? buttonStyles.disabled : null,
        this.icon && !this.trailingIcon && !isIconOnly ? buttonStyles.baseWithIcon : null,
        isIconOnly ? buttonStyles.baseIconOnly : null,
    ];

    const iconTemplate = this.icon ? html`
      <md-icon slot="icon" ${stylex.props(buttonStyles.iconSlot, this.trailingIcon ? buttonStyles.iconTrailing : buttonStyles.iconLeading)}>
        ${this.icon}
      </md-icon>
    ` : nothing;

    const content = html`
      ${!this.trailingIcon ? iconTemplate : nothing}
      <span ${stylex.props(buttonStyles.label)}><slot>${this.label || ''}</slot></span>
      ${this.trailingIcon ? iconTemplate : nothing}
    `;

    if (this.href && !this.disabled) {
      return html`
        <a
          ${stylex.props(...baseDynamicStyles)}
          href=${this.href}
          role="button"
          aria-disabled=${this.disabled ? 'true' : 'false'}
          aria-label=${this.label || nothing}
        >
          ${content}
        </a>
      `;
    }

    return html`
      <button
        ${stylex.props(...baseDynamicStyles)}
        ?disabled=${this.disabled}
        type=${this.type}
        aria-label=${this.label || nothing}
      >
        ${content}
      </button>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'charity-button': CharityButton;
  }
}
