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
    @state() private onBehalfOf = '';
    @state() private earmark = 'general_fund';
    @state() private earmarkOptions: { id: string; label: string }[] = [];
    @state() private isLoading = false;
    @state() private errorMessage = '';
    @state() private successMessage = '';

    // --- Stripe related state ---
    private stripe: Stripe | null = null;
    private cardElement?: StripeCardElement;
    @state() private stripePublicKey = '';
    @state() private clientSecret = '';

    // Stripe Elements instances
    private stripe: Stripe | null = null;
    private elements: any; // Stripe Elements instance

    constructor() {
        super();
        this.frequency = this.defaultFrequency;
    }

    connectedCallback() {
        super.connectedCallback();
        this.initializeComponent();
    }

    private async initializeComponent() {
        const urlParams = new URLSearchParams(window.location.search);
        const freqFromUrl = urlParams.get('frequency');
        if (freqFromUrl === 'monthly' || freqFromUrl === 'one-time') {
            this.frequency = freqFromUrl;
        }

        await this.fetchEarmarkOptions();

        this.stripePublicKey = window.charityM3?.stripePublicKey || '';
        if (!this.stripePublicKey) {
            this.errorMessage = 'Stripe is not configured correctly.';
            return;
        }
        this.stripe = await loadStripe(this.stripePublicKey);
    }

    async fetchEarmarkOptions() {
        try {
            const options = await apiFetch({ path: '/charitym3/v1/earmark-options' });
            if (Array.isArray(options)) {
                this.earmarkOptions = options;
            }
        } catch (error) {
            console.error('Failed to fetch earmark options:', error);
        }
    }

    private async updatePaymentIntent() {
        if (!this.amount || !this.email || !this.name) {
            this.clientSecret = '';
            this.elements = null;
            return;
        }

        try {
            const response: any = await apiFetch({
                path: '/charitym3/v1/donations/intent',
                method: 'POST',
                data: {
                    amount: this.amount,
                    currency: 'usd',
                    frequency: this.frequency,
                    name: this.name,
                    email: this.email,
                    on_behalf_of: this.onBehalfOf,
                    earmark: this.earmark,
                    campaign_id: this.campaignId,
                },
            });

            if (response.success && response.clientSecret) {
                this.clientSecret = response.clientSecret;
                this.initializePaymentElement();
            } else {
                throw new Error(response.message || 'Could not create payment intent.');
            }
        } catch (error: any) {
            this.errorMessage = error.message;
        }
    }

    private initializePaymentElement() {
        if (!this.stripe || !this.clientSecret) return;

        this.elements = this.stripe.elements({ clientSecret: this.clientSecret });
        const paymentElement = this.elements.create('payment', { /* layout options */ });

        const stripeContainer = this.shadowRoot?.getElementById('stripe-payment-element');
        if (stripeContainer) {
            paymentElement.mount(stripeContainer);
        }
    }

    private handleAmountClick(selectedAmount: number) {
        const newAmount = selectedAmount * 100;
        if (this.amount !== newAmount) {
            this.amount = newAmount;
            this.customAmountInput = '';
            this.updatePaymentIntent();
        }
    }

    private handleCustomAmountInput(e: Event) {
        const target = e.target as HTMLInputElement;
        this.customAmountInput = target.value;
        const parsedAmount = parseFloat(target.value) * 100;
        const newAmount = isNaN(parsedAmount) ? 0 : parsedAmount;
        if (this.amount !== newAmount) {
            this.amount = newAmount;
            this.updatePaymentIntent();
        }
    }

    private async handleSubmit(e: Event) {
        e.preventDefault();
        if (!this.stripe || !this.elements || this.isLoading || !this.amount) {
            return;
        }
        this.isLoading = true;
        this.errorMessage = '';

        // The logic to save data to our DB will now be handled by a webhook
        // after Stripe confirms the payment.
        // We just need to confirm the payment on the client side.
        const { error } = await this.stripe.confirmPayment({
            elements: this.elements,
            confirmParams: {
                // The return_url is where the user will be redirected after payment.
                // We will create a page for this.
                return_url: `${window.location.origin}/donation-confirmation/`,
                receipt_email: this.email,
            },
        });

        if (error.type === "card_error" || error.type === "validation_error") {
            this.errorMessage = error.message || 'An unknown error occurred.';
        } else {
            this.errorMessage = 'An unexpected error occurred. Please try again.';
        }

        this.isLoading = false;
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

                <!-- Fields that are needed for payment intent -->
                <md-outlined-text-field label="Full Name" type="text" .value=${this.name} @input=${(e: Event) => this.name = (e.target as HTMLInputElement).value} required></md-outlined-text-field>
                <md-outlined-text-field label="Email Address" type="email" .value=${this.email} @input=${(e: Event) => this.email = (e.target as HTMLInputElement).value} required></md-outlined-text-field>

                <!-- Optional Fields -->
                <div style="border-top: 1px solid ${M3SysColors.outlineVariant}; padding-top: 1rem; margin-top: 0.5rem;">
                    <md-outlined-text-field
                        label="On Behalf of (Optional)"
                        .value=${this.onBehalfOf}
                        @input=${(e: Event) => this.onBehalfOf = (e.target as HTMLInputElement).value}
                    ></md-outlined-text-field>
                </div>

                ${this.earmarkOptions.length > 1 ? html`
                    <div>
                        <label for="earmark-select">${__('Earmark Donation (Optional)', 'charity-m3')}</label>
                        <select
                            id="earmark-select"
                            .value=${this.earmark}
                            @change=${(e: Event) => this.earmark = (e.target as HTMLSelectElement).value}
                            style="width: 100%; padding: 1rem; border: 1px solid ${M3SysColors.outline}; border-radius: 4px; background-color: ${M3SysColors.surface}; color: ${M3SysColors.onSurface};"
                        >
                            ${this.earmarkOptions.map(opt => html`
                                <option value=${opt.id}>${opt.label}</option>
                            `)}
                        </select>
                    </div>
                ` : nothing}

                <!-- Stripe Payment Element will be mounted here, only when we have a client secret -->
                <div id="stripe-payment-element" ?hidden=${!this.clientSecret} ${stylex.props(styles.stripeElementContainer)}></div>

                <div id="card-errors" role="alert" ${stylex.props(styles.errorMessage)}>${this.errorMessage}</div>

                <charity-button type="submit" variant="filled" ?disabled=${this.isLoading || !this.clientSecret}>
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
