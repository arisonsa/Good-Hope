import { LitElement, html } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale } from '../tokens';

const styles = stylex.create({
    wrapper: {
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        textAlign: 'center',
        padding: '1rem',
    },
    stat: {
        ...M3TypeScale.displayMedium,
        color: M3SysColors.primary,
        fontWeight: '700', // Make the number bold
    },
    description: {
        ...M3TypeScale.titleMedium,
        color: M3SysColors.onSurfaceVariant,
        marginTop: '0.5rem',
    },
    icon: {
        fontSize: '2.5rem',
        color: M3SysColors.secondary,
        marginBottom: '0.5rem',
    }
});

@customElement('charity-counter')
export class CharityCounter extends LitElement {
    @property({ type: Number, attribute: 'target-value' })
    targetValue = 0;

    @property({ type: String })
    prefix = '';

    @property({ type: String })
    suffix = '';

    @property({ type: Number })
    duration = 2000; // 2 seconds

    @state() private currentValue = 0;
    private observer?: IntersectionObserver;
    private hasAnimated = false;

    // Use Light DOM for simple component
    createRenderRoot() {
        return this;
    }

    connectedCallback() {
        super.connectedCallback();
        this.observer = new IntersectionObserver(this.handleIntersection.bind(this), {
            threshold: 0.5, // Trigger when 50% of the element is visible
        });
        this.observer.observe(this);
    }

    disconnectedCallback() {
        super.disconnectedCallback();
        this.observer?.disconnect();
    }

    private handleIntersection(entries: IntersectionObserverEntry[]) {
        entries.forEach(entry => {
            if (entry.isIntersecting && !this.hasAnimated) {
                this.hasAnimated = true;
                this.animateValue();
                this.observer?.unobserve(this); // Stop observing after animation starts
            }
        });
    }

    private animateValue() {
        let startTimestamp: number | null = null;
        const step = (timestamp: number) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / this.duration, 1);

            // Ease-out function: progress * (2 - progress)
            const easedProgress = progress * (2 - progress);
            this.currentValue = Math.floor(easedProgress * this.targetValue);

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                this.currentValue = this.targetValue; // Ensure it ends on the exact value
            }
        };
        window.requestAnimationFrame(step);
    }

    render() {
        return html`
            <div ${stylex.props(styles.wrapper)}>
                <slot name="icon" ${stylex.props(styles.icon)}></slot>
                <div class="stat-value" ${stylex.props(styles.stat)}>
                    ${this.prefix}${Math.round(this.currentValue).toLocaleString()}${this.suffix}
                </div>
                <div class="stat-description" ${stylex.props(styles.description)}>
                    <slot></slot>
                </div>
            </div>
        `;
    }
}

declare global {
  interface HTMLElementTagNameMap {
    'charity-counter': CharityCounter;
  }
}
