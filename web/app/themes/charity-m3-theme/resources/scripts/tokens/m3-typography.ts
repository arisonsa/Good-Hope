/**
 * Material Design 3 Typography Tokens (as objects for StyleXJS)
 *
 * These definitions provide structured objects for different text styles.
 * They can reference CSS variables for font families (defined in main.scss)
 * and use specific values for size, weight, etc., as per M3 spec.
 *
 * See: https://m3.material.io/styles/typography/type-scale-tokens
 */

// References to global font family CSS variables (defined in main.scss)
const refTypefaceBrand = 'var(--md-ref-typeface-brand, Roboto, sans-serif)';
const refTypefacePlain = 'var(--md-ref-typeface-plain, Roboto, sans-serif)';

// Helper type for a single typescale role
export type M3TypeScaleRole = {
  fontFamily: string;
  fontWeight: string | number;
  fontSize: string; // e.g., '3.5rem', '16px'
  lineHeight: string; // e.g., '4rem', '1.5'
  letterSpacing?: string; // e.g., '-0.015625em', '0.5px'
};

export const M3TypeScale: Record<string, M3TypeScaleRole> = {
  // Display
  displayLarge: {
    fontFamily: refTypefaceBrand,
    fontWeight: '400',
    fontSize: '3.5625rem', // 57px
    lineHeight: '4rem',    // 64px
    letterSpacing: '-0.015625em', // -0.25px
  },
  displayMedium: {
    fontFamily: refTypefaceBrand,
    fontWeight: '400',
    fontSize: '2.8125rem', // 45px
    lineHeight: '3.25rem',   // 52px
  },
  displaySmall: {
    fontFamily: refTypefaceBrand,
    fontWeight: '400',
    fontSize: '2.25rem',   // 36px
    lineHeight: '2.75rem',  // 44px
  },
  // Headline
  headlineLarge: {
    fontFamily: refTypefaceBrand,
    fontWeight: '400',
    fontSize: '2rem',      // 32px
    lineHeight: '2.5rem',   // 40px
  },
  headlineMedium: {
    fontFamily: refTypefaceBrand,
    fontWeight: '400',
    fontSize: '1.75rem',   // 28px
    lineHeight: '2.25rem',  // 36px
  },
  headlineSmall: {
    fontFamily: refTypefaceBrand,
    fontWeight: '400',
    fontSize: '1.5rem',    // 24px
    lineHeight: '2rem',     // 32px
  },
  // Title
  titleLarge: {
    fontFamily: refTypefaceBrand, // Or plain, depending on M3 role usage
    fontWeight: '400', // M3 often uses 500 (Medium) for titles, but spec says 400 for some.
    fontSize: '1.375rem',  // 22px
    lineHeight: '1.75rem',  // 28px
  },
  titleMedium: {
    fontFamily: refTypefacePlain,
    fontWeight: '500', // Medium
    fontSize: '1rem',      // 16px
    lineHeight: '1.5rem',   // 24px
    letterSpacing: '0.009375em', // 0.15px
  },
  titleSmall: {
    fontFamily: refTypefacePlain,
    fontWeight: '500', // Medium
    fontSize: '0.875rem',  // 14px
    lineHeight: '1.25rem',  // 20px
    letterSpacing: '0.00714286em', // 0.1px
  },
  // Label
  labelLarge: {
    fontFamily: refTypefacePlain,
    fontWeight: '500', // Medium
    fontSize: '0.875rem',  // 14px
    lineHeight: '1.25rem',  // 20px
    letterSpacing: '0.00714286em', // 0.1px
  },
  labelMedium: {
    fontFamily: refTypefacePlain,
    fontWeight: '500', // Medium
    fontSize: '0.75rem',   // 12px
    lineHeight: '1rem',     // 16px
    letterSpacing: '0.04166667em', // 0.5px
  },
  labelSmall: {
    fontFamily: refTypefacePlain,
    fontWeight: '500', // Medium
    fontSize: '0.6875rem', // 11px
    lineHeight: '1rem',     // 16px
    letterSpacing: '0.04545455em', // 0.5px
  },
  // Body
  bodyLarge: {
    fontFamily: refTypefacePlain,
    fontWeight: '400',
    fontSize: '1rem',      // 16px
    lineHeight: '1.5rem',   // 24px
    letterSpacing: '0.03125em',  // 0.5px (Note: M3 spec varies, sometimes 0.009375em for Body Large)
  },
  bodyMedium: {
    fontFamily: refTypefacePlain,
    fontWeight: '400',
    fontSize: '0.875rem',  // 14px
    lineHeight: '1.25rem',  // 20px
    letterSpacing: '0.01785714em', // 0.25px
  },
  bodySmall: {
    fontFamily: refTypefacePlain,
    fontWeight: '400',
    fontSize: '0.75rem',   // 12px
    lineHeight: '1rem',     // 16px
    letterSpacing: '0.03333333em', // 0.4px
  },
} as const;

export type M3TypeScaleTokenKey = keyof typeof M3TypeScale;

/* Example usage with StyleXJS:
 * import * as stylex from '@stylexjs/stylex';
 * import { M3TypeScale } from './m3-typography';
 *
 * const styles = stylex.create({
 *   myHeadline: {
 *     ...M3TypeScale.headlineSmall, // Spread the properties
 *     color: M3SysColors.primary, // From m3-colors.ts
 *   },
 *   customBody: {
 *      fontFamily: M3TypeScale.bodyLarge.fontFamily,
 *      fontSize: M3TypeScale.bodyLarge.fontSize,
 *      // etc.
 *   }
 * });
 */
