/**
 * Material Design 3 Color System Tokens (as CSS Variable references for StyleXJS)
 *
 * These tokens represent references to the CSS Custom Properties defined in main.scss.
 * StyleXJS will use these variables in its generated styles.
 * The actual hex values are managed in SCSS and potentially overridden by WordPress Customizer.
 */

export const M3SysColors = {
  // Primary
  primary: 'var(--md-sys-color-primary)',
  onPrimary: 'var(--md-sys-color-on-primary)',
  primaryContainer: 'var(--md-sys-color-primary-container)',
  onPrimaryContainer: 'var(--md-sys-color-on-primary-container)',
  // Secondary
  secondary: 'var(--md-sys-color-secondary)',
  onSecondary: 'var(--md-sys-color-on-secondary)',
  secondaryContainer: 'var(--md-sys-color-secondary-container)',
  onSecondaryContainer: 'var(--md-sys-color-on-secondary-container)',
  // Tertiary
  tertiary: 'var(--md-sys-color-tertiary)',
  onTertiary: 'var(--md-sys-color-on-tertiary)',
  tertiaryContainer: 'var(--md-sys-color-tertiary-container)',
  onTertiaryContainer: 'var(--md-sys-color-on-tertiary-container)',
  // Error
  error: 'var(--md-sys-color-error)',
  onError: 'var(--md-sys-color-on-error)',
  errorContainer: 'var(--md-sys-color-error-container)',
  onErrorContainer: 'var(--md-sys-color-on-error-container)',
  // Neutral Surfaces
  background: 'var(--md-sys-color-background)',
  onBackground: 'var(--md-sys-color-on-background)',
  surface: 'var(--md-sys-color-surface)',
  onSurface: 'var(--md-sys-color-on-surface)',
  surfaceVariant: 'var(--md-sys-color-surface-variant)',
  onSurfaceVariant: 'var(--md-sys-color-on-surface-variant)',
  surfaceDim: 'var(--md-sys-color-surface-dim)',
  surfaceBright: 'var(--md-sys-color-surface-bright)',
  surfaceContainerLowest: 'var(--md-sys-color-surface-container-lowest)',
  surfaceContainerLow: 'var(--md-sys-color-surface-container-low)',
  surfaceContainer: 'var(--md-sys-color-surface-container)',
  surfaceContainerHigh: 'var(--md-sys-color-surface-container-high)',
  surfaceContainerHighest: 'var(--md-sys-color-surface-container-highest)',
  // Outline
  outline: 'var(--md-sys-color-outline)',
  outlineVariant: 'var(--md-sys-color-outline-variant)',
  // Inverse
  inversePrimary: 'var(--md-sys-color-inverse-primary)',
  inverseSurface: 'var(--md-sys-color-inverse-surface)',
  inverseOnSurface: 'var(--md-sys-color-inverse-on-surface)',
  // Shadow & Scrim
  shadow: 'var(--md-sys-color-shadow)',
  scrim: 'var(--md-sys-color-scrim)',
} as const; // `as const` makes it a readonly object with literal types

// Type helper for color token keys
export type M3SysColorKey = keyof typeof M3SysColors;

/*
 * Example of how these might be used with StyleXJS theming API (stylex.defineVars)
 * This would typically be in a separate theme definition file.
 *
 * import * as stylex from '@stylexjs/stylex';
 *
 * export const m3BaseTheme = stylex.defineVars(M3SysColors);
 *
 * // Then, to create specific light/dark themes if values were not CSS vars:
 * // export const m3LightActualColors = { primary: '#6750A4', ... };
 * // export const lightTheme = stylex.createTheme(m3BaseTheme, m3LightActualColors);
 *
 * Since M3SysColors already points to CSS vars which are themeable via SCSS (:root and .dark),
 * components can directly use M3SysColors values in stylex.create(), and they will
 * automatically pick up the correct light/dark mode values from the CSS variables.
 */
