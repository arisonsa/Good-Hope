import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3SysShape, M3TypeScale } from '../tokens';

import './charity-button';

const styles = stylex.create({
    wrapper: {
        position: 'relative',
        display: 'inline-block',
    },
    dropdown: {
        position: 'absolute',
        top: 'calc(100% + 0.5rem)', // Position below the button
        right: 0,
        backgroundColor: M3SysColors.surfaceContainer,
        borderRadius: M3SysShape.corner.medium,
        boxShadow: '0px 4px 8px 3px rgba(0,0,0,0.15), 0px 1px 3px rgba(0,0,0,0.3)', // M3 Elevation 2
        zIndex: 50,
        width: 'max-content', // Fit content
        overflow: 'hidden',
        // For transition
        opacity: 0,
        transform: 'translateY(-10px)',
        visibility: 'hidden',
        transitionProperty: 'transform, opacity, visibility',
        transitionDuration: '0.2s',
        transitionTimingFunction: 'ease-out',
    },
    dropdownOpen: {
        opacity: 1,
        transform: 'translateY(0)',
        visibility: 'visible',
    },
    dropdownItem: {
        display: 'block',
        padding: '0.75rem 1.5rem',
        ...M3TypeScale.labelLarge,
        color: M3SysColors.onSurface,
        textDecoration: 'none',
        textAlign: 'left',
        ':hover': {
            backgroundColor: `color-mix(in srgb, ${M3SysColors.primary} 8%, ${M3SysColors.surfaceContainer})`,
        }
    }
});

@customElement('donate-dropdown-button')
export class DonateDropdownButton extends LitElement {
    @state() private isOpen = false;

    // Use Light DOM for easier event handling and positioning
    createRenderRoot() {
        return this;
    }

    connectedCallback() {
        super.connectedCallback();
        window.addEventListener('click', this.handleGlobalClick);
        window.addEventListener('keydown', this.handleKeydown);
    }

    disconnectedCallback() {
        super.disconnectedCallback();
        window.removeEventListener('click', this.handleGlobalClick);
        window.removeEventListener('keydown', this.handleKeydown);
    }

    private handleGlobalClick = (e: MouseEvent) => {
        if (this.isOpen && !this.contains(e.target as Node)) {
            this.isOpen = false;
        }
    }

    private handleKeydown = (e: KeyboardEvent) => {
        if (e.key === 'Escape' && this.isOpen) {
            this.isOpen = false;
        }
    }

    private toggleDropdown(e: Event) {
        e.stopPropagation(); // Prevent global click handler from closing it immediately
        this.isOpen = !this.isOpen;
    }

    render() {
        return html`
            <div ${stylex.props(styles.wrapper)}>
                <charity-button
                    variant="filled"
                    @click=${this.toggleDropdown}
                    aria-haspopup="true"
                    aria-expanded=${this.isOpen}
                >
                    ${__('Donate Now', 'charity-m3')}
                </charity-button>
                <div class=${stylex.props(styles.dropdown, this.isOpen && styles.dropdownOpen).className}>
                    <a href="/donate?frequency=one-time" ${stylex.props(styles.dropdownItem)}>
                        ${__('One-time Gift', 'charity-m3')}
                    </a>
                    <a href="/donate?frequency=monthly" ${stylex.props(styles.dropdownItem)}>
                        ${__('Monthly Gift', 'charity-m3')}
                    </a>
                </div>
            </div>
        `;
    }
}

declare global {
    interface HTMLElementTagNameMap {
        'donate-dropdown-button': DonateDropdownButton;
    }
}
