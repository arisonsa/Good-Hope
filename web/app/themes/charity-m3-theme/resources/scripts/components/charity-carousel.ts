import { LitElement, html, nothing } from 'lit';
import { customElement, property, query } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3SysShape } from '../tokens';

// Import Swiper and required modules
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade } from 'swiper/modules';

// Re-use our custom button
import './charity-button';

const styles = stylex.create({
    wrapper: {
        position: 'relative',
        width: '100%',
        // The swiper-container will be the direct child and will be initialized by Swiper
    },
    // Custom styles for navigation buttons
    navButton: {
        position: 'absolute',
        top: '50%',
        transform: 'translateY(-50%)',
        zIndex: 10,
        color: M3SysColors.onPrimary, // White icon on dark overlay
        backgroundColor: `color-mix(in srgb, ${M3SysColors.scrim} 30%, transparent)`,
        '::before': { // StyleXJS way to style pseudo-elements or parts if needed
             // Example: content: '""'
        },
        ':hover': {
            backgroundColor: `color-mix(in srgb, ${M3SysColors.scrim} 50%, transparent)`,
        },
        // We will use charity-button, so we can pass variants instead of direct styling here
    },
    navButtonPrev: {
        left: '0.5rem',
    },
    navButtonNext: {
        right: '0.5rem',
    },
    // Custom styles for pagination bullets
    pagination: {
        position: 'absolute',
        bottom: '1rem',
        left: '50%',
        transform: 'translateX(-50%)',
        zIndex: 10,
        // The 'swiper-pagination-bullet' and 'swiper-pagination-bullet-active' classes
        // are added by Swiper. We target them in global CSS or with ::part if Swiper supported it.
        // For now, we'll rely on the default Swiper CSS imported in main.scss,
        // but we can override it with more specific selectors if needed.
        // For example, in main.scss:
        // .charity-carousel-pagination .swiper-pagination-bullet-active { background-color: var(--md-sys-color-primary); }
    },
});

@customElement('charity-carousel')
export class CharityCarousel extends LitElement {
    // A single options object to pass to Swiper
    @property({ type: Object })
    options: object = {};

    // Simple props for common controls, which will construct the final options object
    @property({ type: Boolean })
    navigation = false;

    @property({ type: Boolean })
    pagination = false;

    @query('.swiper-container')
    private swiperContainer!: HTMLElement;

    @query('.swiper-button-prev')
    private prevButton!: HTMLElement;

    @query('.swiper-button-next')
    private nextButton!: HTMLElement;

    @query('.swiper-pagination')
    private paginationEl!: HTMLElement;

    private swiperInstance?: Swiper;

    // Use Light DOM to make it easier for Swiper to find and style elements,
    // and for slotted content to be found.
    createRenderRoot() {
        return this;
    }

    firstUpdated() {
        if (!this.swiperContainer) return;

        const defaultOptions = {
            modules: [Navigation, Pagination, Autoplay, EffectFade],
            slidesPerView: 1,
            spaceBetween: 16,
            loop: false,
            effect: 'slide',
            navigation: this.navigation ? {
                nextEl: this.nextButton,
                prevEl: this.prevButton,
            } : false,
            pagination: this.pagination ? {
                el: this.paginationEl,
                clickable: true,
            } : false,
        };

        const finalOptions = { ...defaultOptions, ...this.options };

        // Initialize Swiper
        this.swiperInstance = new Swiper(this.swiperContainer, finalOptions);
    }

    disconnectedCallback() {
        super.disconnectedCallback();
        // Destroy Swiper instance to prevent memory leaks
        this.swiperInstance?.destroy(true, true);
    }

    render() {
        // We need to manually wrap slotted children in 'swiper-slide' divs
        // because Swiper needs this structure.
        const slides = Array.from(this.children).map(child => html`
            <div class="swiper-slide">${child}</div>
        `);

        return html`
            <div ${stylex.props(styles.wrapper)}>
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        ${slides}
                    </div>

                    ${this.pagination ? html`<div class="swiper-pagination charity-carousel-pagination"></div>` : nothing}
                </div>

                ${this.navigation ? html`
                    <charity-button
                        class="swiper-button-prev"
                        variant="icon"
                        icon="arrow_back"
                        label="Previous slide"
                        ${stylex.props(styles.navButton, styles.navButtonPrev)}
                    ></charity-button>
                    <charity-button
                        class="swiper-button-next"
                        variant="icon"
                        icon="arrow_forward"
                        label="Next slide"
                        ${stylex.props(styles.navButton, styles.navButtonNext)}
                    ></charity-button>
                ` : nothing}
            </div>
        `;
    }
}

declare global {
  interface HTMLElementTagNameMap {
    'charity-carousel': CharityCarousel;
  }
}
