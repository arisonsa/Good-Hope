/**
 * Material Design 3 Shape Tokens (as objects/values for StyleXJS)
 *
 * These define corner radius values.
 * See: https://m3.material.io/styles/shape/shape-scale-tokens
 */

export const M3SysShape = {
  corner: {
    none: '0px',
    extraSmall: '4px',
    extraSmallTop: { // For components like top app bar
      borderTopLeftRadius: '4px',
      borderTopRightRadius: '4px',
      borderBottomLeftRadius: '0px',
      borderBottomRightRadius: '0px',
    },
    small: '8px',
    medium: '12px', // Common for cards, dialogs
    large: '16px',
    largeEnd: { // For components like navigation drawers
        borderTopLeftRadius: '0px',
        borderTopRightRadius: '16px',
        borderBottomLeftRadius: '0px',
        borderBottomRightRadius: '16px',
    },
    largeTop: {
        borderTopLeftRadius: '16px',
        borderTopRightRadius: '16px',
        borderBottomLeftRadius: '0px',
        borderBottomRightRadius: '0px',
    },
    extraLarge: '28px',
    extraLargeTop: { // For components like bottom sheets
        borderTopLeftRadius: '28px',
        borderTopRightRadius: '28px',
        borderBottomLeftRadius: '0px',
        borderBottomRightRadius: '0px',
    },
    full: '9999px', // For pills, circular elements
  },
} as const;

export type M3SysShapeCornerKey = keyof typeof M3SysShape.corner;

/* Example usage with StyleXJS:
 * import * as stylex from '@stylexjs/stylex';
 * import { M3SysShape } from './m3-shape';
 * import { M3SysColors } from './m3-colors';
 *
 * const styles = stylex.create({
 *   card: {
 *     borderRadius: M3SysShape.corner.medium,
 *     backgroundColor: M3SysColors.surfaceContainerLow,
 *   },
 *   pillButton: {
 *     borderRadius: M3SysShape.corner.full,
 *     paddingTop: '8px',
 *     paddingBottom: '8px',
 *     paddingLeft: '16px',
 *     paddingRight: '16px',
 *   }
 * });
 */
