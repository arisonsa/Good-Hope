import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';

import './charity-button'; // Using our M3 button for the toggle

@customElement('mobile-nav-toggle')
export class MobileNavToggle extends LitElement {
    @state() private isMenuOpen = false;

    private menuTargetSelector = '#mobile-menu-container'; // The ID of the menu to toggle

    connectedCallback() {
        super.connectedCallback();
        document.addEventListener('keydown', this.handleKeydown.bind(this));
    }

    disconnectedCallback() {
        super.disconnectedCallback();
        document.removeEventListener('keydown', this.handleKeydown.bind(this));
    }

    private handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape' && this.isMenuOpen) {
            this.toggleMenu();
        }
    }

    private toggleMenu() {
        this.isMenuOpen = !this.isMenuOpen;
        const menuContainer = document.querySelector(this.menuTargetSelector);
        if (menuContainer) {
            menuContainer.classList.toggle('is-open', this.isMenuOpen);
            document.body.classList.toggle('overflow-hidden', this.isMenuOpen); // Prevent body scroll when menu is open
        }
        this.setAttribute('aria-expanded', String(this.isMenuOpen));
    }

    // Using Light DOM for easier global class application
    createRenderRoot() {
        return this;
    }

    render() {
        return html`
            <charity-button
                variant="icon"
                label=${this.isMenuOpen ? 'Close navigation menu' : 'Open navigation menu'}
                icon=${this.isMenuOpen ? 'close' : 'menu'}
                @click=${this.toggleMenu}
            ></charity-button>
        `;
    }
}

declare global {
    interface HTMLElementTagNameMap {
        'mobile-nav-toggle': MobileNavToggle;
    }
}
