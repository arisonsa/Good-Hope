import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale } from '../tokens';

const styles = stylex.create({
    container: {
        padding: '2rem',
        textAlign: 'center',
        maxWidth: '600px',
        margin: '2rem auto',
        borderRadius: '8px',
        border: `1px solid ${M3SysColors.outline}`,
    },
    success: {
        backgroundColor: M3SysColors.primaryContainer,
        color: M3SysColors.onPrimaryContainer,
    },
    error: {
        backgroundColor: M3SysColors.errorContainer,
        color: M3SysColors.onErrorContainer,
    },
    heading: {
        ...M3TypeScale.headlineMedium,
        marginBottom: '1rem',
    },
    message: {
        ...M3TypeScale.bodyLarge,
    },
    loading: {
        ...M3TypeScale.bodyLarge,
        color: M3SysColors.onSurface,
    }
});

@customElement('donation-status')
export class DonationStatus extends LitElement {
    @state() private status: 'loading' | 'success' | 'error' = 'loading';
    @state() private message = 'Verifying your donation status...';

    connectedCallback() {
        super.connectedCallback();
        this.verifyPayment();
    }

    async verifyPayment() {
        const urlParams = new URLSearchParams(window.location.search);
        const paymentIntentId = urlParams.get('payment_intent');
        const clientSecret = urlParams.get('payment_intent_client_secret');

        if (!paymentIntentId || !clientSecret) {
            this.status = 'error';
            this.message = 'Could not find payment details in the URL.';
            return;
        }

        try {
            const response: any = await apiFetch({
                path: `/charitym3/v1/donations/status/${paymentIntentId}`,
                method: 'GET',
            });

            if (response.success) {
                this.status = 'success';
                this.message = response.message;
            } else {
                throw new Error(response.message || 'Failed to verify donation status.');
            }
        } catch (error: any) {
            this.status = 'error';
            this.message = error.message;
        }
    }

    render() {
        let containerClasses = styles.container;
        if (this.status === 'success') {
            containerClasses = stylex.props(styles.container, styles.success).className;
        } else if (this.status === 'error') {
            containerClasses = stylex.props(styles.container, styles.error).className;
        }

        return html`
            <div class="${containerClasses}">
                ${this.status === 'loading'
                    ? html`<p ${stylex.props(styles.loading)}>${this.message}</p>`
                    : html`
                        <h1 ${stylex.props(styles.heading)}>
                            ${this.status === 'success' ? 'Thank You!' : 'Donation Error'}
                        </h1>
                        <p ${stylex.props(styles.message)}>${this.message}</p>
                    `
                }
            </div>
        `;
    }
}

declare global {
    interface HTMLElementTagNameMap {
        'donation-status': DonationStatus;
    }
}
