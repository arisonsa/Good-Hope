import { LitElement, html, nothing } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale, M3SysShape } from '../tokens';

// We need to load Stripe.js. This can be done by adding a <script> tag to the main document
// or by using the @stripe/stripe-js loader package. Let's assume the loader will be used.
// We'd add `@stripe/stripe-js` to package.json.
import { loadStripe, Stripe, StripeCardElement } from '@stripe/stripe-js';

// Re-use our custom button component
import './charity-button';
// MWC text field for name/email
import '@material/web/textfield/outlined-text-field.js';

const styles = stylex.create({
    form: {
        display: 'flex',
        flexDirection: 'column',
        gap: '1rem',
    },
    amountToggle: {
        display: 'flex',
        flexWrap: 'wrap',
        gap: '0.5rem',
        marginBottom: '0.5rem',
    },
    amountButton: {
        flex: '1 1 auto',
        // StyleXJS for button states
    },
    amountButtonSelected: {
        backgroundColor: M3SysColors.primary,
        color: M3SysColors.onPrimary,
    },
    customAmount: {
        // Style for the custom amount input field
    },
    frequencyToggle: {
        display: 'flex',
        borderRadius: M3SysShape.corner.full,
        borderWidth: '1px',
        borderStyle: 'solid',
        borderColor: M3SysColors.outline,
        overflow: 'hidden',
        width: 'fit-content', // Or '100%'
    },
    frequencyButton: {
        padding: '0.5rem 1rem',
        cursor: 'pointer',
        backgroundColor: M3SysColors.surface,
        color: M3SysColors.onSurface,
        border: 'none',
        ...M3TypeScale.labelLarge,
    },
    frequencyButtonSelected: {
        backgroundColor: M3SysColors.secondaryContainer,
        color: M3SysColors.onSecondaryContainer,
    },
    stripeElementContainer: {
        border: `1px solid ${M3SysColors.outline}`,
        borderRadius: M3SysShape.corner.extraSmall,
        padding: '1rem',
        backgroundColor: M3SysColors.surface,
        transition: 'border-color 0.2s',
        ':focus-within': {
            borderColor: M3SysColors.primary,
        }
    },
    errorMessage: {
        ...M3TypeScale.bodySmall,
        color: M3SysColors.error,
        marginTop: '0.5rem',
        minHeight: '1.2em', // Prevent layout shift
    },
    successMessage: {
        ...M3TypeScale.bodyLarge,
        color: M3SysColors.onPrimaryContainer,
        backgroundColor: M3SysColors.primaryContainer,
        padding: '1rem',
        borderRadius: M3SysShape.corner.small,
    }
});

@customElement('charity-donation-form')
export class CharityDonationForm extends LitElement {
    // --- Configurable Properties (from Gutenberg block attributes) ---
    @property({ type: Array, attribute: 'suggested-amounts' })
    suggestedAmounts: number[] = [25, 50, 100, 250];

    @property({ type: String, attribute: 'default-frequency' })
    defaultFrequency: 'one-time' | 'monthly' = 'one-time';

    @property({ type: String, attribute: 'campaign-id' })
    campaignId?: string;

    // --- State for the component ---
    @state() private frequency: 'one-time' | 'monthly';
    @state() private amount = 0; // in cents
    @state() private customAmountInput = ''; // string to handle decimal input
    @state() private name = '';
    @state() private email = '';
    @state() private isLoading = false;
    @state() private errorMessage = '';
    @state() private successMessage = '';

    // --- Stripe related state ---
    private stripe: Stripe | null = null;
    private cardElement?: StripeCardElement;
    @state() private stripePublicKey = ''; // Will be passed from server

    constructor() {
        super();
        this.frequency = this.defaultFrequency;
    }

    async firstUpdated() {
        // Fetch public key from a localized script or data attribute
        this.stripePublicKey = window.charityM3?.stripePublicKey || '';
        if (!this.stripePublicKey) {
            this.errorMessage = 'Stripe is not configured correctly.';
            return;
        }

        this.stripe = await loadStripe(this.stripePublicKey);
        if (!this.stripe) {
            this.errorMessage = 'Failed to load payment gateway.';
            return;
        }

        const elements = this.stripe.elements();
        this.cardElement = elements.create('card', {
            // M3 style options for Stripe Element
            style: {
                base: {
                    iconColor: M3SysColors.primary,
                    color: M3SysColors.onSurface,
                    fontFamily: 'Roboto, sans-serif',
                    fontSize: '16px',
                    '::placeholder': {
                        color: M3SysColors.onSurfaceVariant,
                    },
                },
                invalid: {
                    iconColor: M3SysColors.error,
                    color: M3SysColors.error,
                },
            },
        });

        const stripeContainer = this.shadowRoot?.getElementById('stripe-card-element');
        if (stripeContainer) {
            this.cardElement.mount(stripeContainer);
            this.cardElement.on('change', (event) => {
                this.errorMessage = event.error ? event.error.message : '';
            });
        }
    }

    private handleAmountClick(selectedAmount: number) {
        this.amount = selectedAmount * 100; // Convert to cents
        this.customAmountInput = ''; // Clear custom input
    }

    private handleCustomAmountInput(e: Event) {
        const target = e.target as HTMLInputElement;
        this.customAmountInput = target.value;
        const parsedAmount = parseFloat(target.value) * 100;
        this.amount = isNaN(parsedAmount) ? 0 : parsedAmount;
    }

    private async handleSubmit(e: Event) {
        e.preventDefault();
        if (!this.stripe || !this.cardElement || this.isLoading || !this.amount) {
            return;
        }
        this.isLoading = true;
        this.errorMessage = '';

        const { error, paymentMethod } = await this.stripe.createPaymentMethod({
            type: 'card',
            card: this.cardElement,
            billing_details: {
                name: this.name,
                email: this.email,
            },
        });

        if (error) {
            this.errorMessage = error.message || 'An unknown error occurred.';
            this.isLoading = false;
            return;
        }

        // Send paymentMethod.id and other data to our backend
        try {
            const nonce = window.wpApiSettings?.nonce || ''; // Nonce for REST API
            const response = await fetch('/wp-json/charitym3/v1/donations/charge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify({
                    paymentMethodId: paymentMethod.id,
                    amount: this.amount,
                    currency: 'usd', // This could be a prop
                    email: this.email,
                    name: this.name,
                    frequency: this.frequency,
                    campaignId: this.campaignId,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to process donation.');
            }

            this.successMessage = data.message;
            // Optionally clear form or redirect
            // this.resetForm();

        } catch (err: any) {
            this.errorMessage = err.message;
        } finally {
            this.isLoading = false;
        }
    }

    private getButtonText(): string {
        if (this.isLoading) {
            return 'Processing...';
        }
        const amountFormatted = `$${(this.amount / 100).toFixed(2)}`;
        if (this.frequency === 'monthly') {
            return `Donate ${amountFormatted} / month`;
        }
        return `Donate ${amountFormatted}`;
    }

    render() {
        if (this.successMessage) {
            return html`<div ${stylex.props(styles.successMessage)}>${this.successMessage}</div>`;
        }

        return html`
            <form ${stylex.props(styles.form)} @submit=${this.handleSubmit}>
                <div>
                    <label>${__('Donation Frequency', 'charity-m3')}</label>
                    <div ${stylex.props(styles.frequencyToggle)}>
                        <button type="button" @click=${() => this.frequency = 'one-time'} class=${stylex.props(styles.frequencyButton, this.frequency === 'one-time' && styles.frequencyButtonSelected).className}>One-time</button>
                        <button type="button" @click=${() => this.frequency = 'monthly'} class=${stylex.props(styles.frequencyButton, this.frequency === 'monthly' && styles.frequencyButtonSelected).className}>Monthly</button>
                    </div>
                </div>
                <div>
                    <label>${__('Select Amount (USD)', 'charity-m3')}</label>
                    <div ${stylex.props(styles.amountToggle)}>
                        ${this.suggestedAmounts.map(amt => html`
                            <charity-button
                                type="button"
                                variant=${this.amount === amt * 100 ? 'filled' : 'outlined'}
                                @click=${() => this.handleAmountClick(amt)}
                            >$${amt}</charity-button>
                        `)}
                    </div>
                    <md-outlined-text-field
                        label="Custom Amount"
                        type="number"
                        placeholder="Other"
                        .value=${this.customAmountInput}
                        @input=${this.handleCustomAmountInput}
                        class=${stylex.props(styles.customAmount).className}
                    ></md-outlined-text-field>
                </div>
                <md-outlined-text-field label="Full Name" type="text" .value=${this.name} @input=${(e: Event) => this.name = (e.target as HTMLInputElement).value} required></md-outlined-text-field>
                <md-outlined-text-field label="Email Address" type="email" .value=${this.email} @input=${(e: Event) => this.email = (e.target as HTMLInputElement).value} required></md-outlined-text-field>
                <div>
                    <label>${__('Card Details', 'charity-m3')}</label>
                    <div id="stripe-card-element" ${stylex.props(styles.stripeElementContainer)}>
                        <!-- Stripe Card Element will be mounted here -->
                    </div>
                </div>
                <div id="card-errors" role="alert" ${stylex.props(styles.errorMessage)}>${this.errorMessage}</div>
                <charity-button type="submit" variant="filled" ?disabled=${this.isLoading || !this.amount}>
                    ${this.getButtonText()}
                </charity-button>
            </form>
        `;
    }
}

declare global {
  interface Window {
    wpApiSettings?: { nonce: string; [key: string]: any; };
    charityM3?: { stripePublicKey: string; [key: string]: any; };
  }
  interface HTMLElementTagNameMap {
    'charity-donation-form': CharityDonationForm;
  }
}
