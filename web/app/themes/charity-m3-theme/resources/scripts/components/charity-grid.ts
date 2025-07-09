import { LitElement, html, unsafeStatic } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
// No specific M3 tokens needed for the grid itself, mostly layout.
// Spacing tokens could be used for gap if defined. For now, direct values.

const gridStyles = stylex.create({
  base: {
    display: 'grid',
    width: '100%',
  },
  // --- Column Styles ---
  // Simple columns
  cols1: { gridTemplateColumns: 'repeat(1, minmax(0, 1fr))' },
  cols2: { gridTemplateColumns: 'repeat(2, minmax(0, 1fr))' },
  cols3: { gridTemplateColumns: 'repeat(3, minmax(0, 1fr))' },
  cols4: { gridTemplateColumns: 'repeat(4, minmax(0, 1fr))' },
  // Responsive columns using StyleXJS media queries
  // Example: Default 1 col, 2 on medium screens, 3 on large screens
  colsResponsiveDefault: {
    gridTemplateColumns: 'repeat(1, minmax(0, 1fr))',
    '@media (min-width: 768px)': { // md breakpoint (adjust as needed)
      gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
    },
    '@media (min-width: 1024px)': { // lg breakpoint
      gridTemplateColumns: 'repeat(3, minmax(0, 1fr))',
    },
  },
  // --- Gap Styles ---
  // Using CSS variables for gaps for easier customization if needed, or direct values
  // These could also come from M3 spacing tokens if defined
  gap2: { gap: '0.5rem' },  // 8px
  gap4: { gap: '1rem' },   // 16px
  gap6: { gap: '1.5rem' }, // 24px
  gap8: { gap: '2rem' },   // 32px
  gapCustom: (value: string) => ({ gap: value }), // For custom gap values
});

@customElement('charity-grid')
export class CharityGrid extends LitElement {
  @property({ type: String })
  tag: string = 'div';

  // 'cols' can be a number (1-4 for predefined simple cols),
  // a keyword (e.g., 'responsive-default'),
  // or potentially a more complex string for custom StyleXJS parsing if needed.
  @property({ type: String })
  cols: string = 'responsive-default';

  // 'gap' can be a number (2, 4, 6, 8 for predefined gaps) or a CSS unit string (e.g., '1.25rem')
  @property({ type: String })
  gap: string = '6'; // Default to gap-6 (1.5rem)

  // Using Light DOM for simplicity as a layout container,
  // allowing global styles for slotted content if items are not web components.
  createRenderRoot() {
    return this;
  }

  private getColumnStyle() {
    switch (this.cols) {
      case '1': return gridStyles.cols1;
      case '2': return gridStyles.cols2;
      case '3': return gridStyles.cols3;
      case '4': return gridStyles.cols4;
      case 'responsive-default': return gridStyles.colsResponsiveDefault;
      default:
        // If 'cols' is something like '1_md:2_lg:4', parse and apply dynamically (more complex)
        // For now, default to responsive-default if not a simple number
        if (this.cols.match(/^\d{1}(_md:\d{1})?(_lg:\d{1})?$/)) {
            // Basic parsing for "1_md:2_lg:3" or "1_lg:2" etc.
            // This would require more dynamic StyleXJS style generation or predefined combinations.
            // For simplicity, this advanced parsing is omitted here.
            // Users wanting complex responsive grids beyond 'responsive-default'
            // might need to use multiple CharityGrid components or nest them with different 'cols'
            // at different container query breakpoints (if using container queries).
        }
        return gridStyles.colsResponsiveDefault;
    }
  }

  private getGapStyle() {
    switch (this.gap) {
      case '2': return gridStyles.gap2;
      case '4': return gridStyles.gap4;
      case '6': return gridStyles.gap6;
      case '8': return gridStyles.gap8;
      default:
        // Check if it's a valid CSS unit string (e.g., '1.25rem', '20px')
        if (this.gap.match(/^[0-9.]+([a-z%]+)$/i)) {
          return gridStyles.gapCustom(this.gap); // Dynamic style for custom gap
        }
        return gridStyles.gap6; // Default
    }
  }

  render() {
    const TagName = unsafeStatic(this.tag);
    const columnStyle = this.getColumnStyle();
    const gapStyle = this.getGapStyle();

    return html`
      <${TagName} ${stylex.props(gridStyles.base, columnStyle, gapStyle)}>
        <slot></slot>
      </${TagName}>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'charity-grid': CharityGrid;
  }
}
