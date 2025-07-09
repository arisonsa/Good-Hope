import { LitElement, html } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale, M3SysShape } from '../tokens';

// Assuming MWC text-field and button are still desired for their built-in M3 functionality
// Otherwise, these would be custom-built with StyleXJS too.
import '@material/web/textfield/outlined-text-field.js';
import '@material/web/button/filled-button.js';

const styles = stylex.create({
  form: {
    display: 'flex',
    flexDirection: 'column',
    gap: '1rem', // Spacing token could be used
    maxWidth: '400px', // Or make this a prop
  },
  textField: { // StyleXJS can style slotted MWC components if needed, or MWC components style themselves
    width: '100%',
  },
  messageBase: {
    padding: '0.75rem 1rem', // 12px 16px
    borderWidth: '1px',
    borderStyle: 'solid',
    borderRadius: M3SysShape.corner.small, // 8px
    fontFamily: M3TypeScale.bodyMedium.fontFamily,
    fontSize: M3TypeScale.bodyMedium.fontSize,
    lineHeight: M3TypeScale.bodyMedium.lineHeight,
  },
  messageSuccess: {
    borderColor: M3SysColors.primary, // Example, or a success color token
    backgroundColor: M3SysColors.primaryContainer,
    color: M3SysColors.onPrimaryContainer,
  },
  messageError: {
    borderColor: M3SysColors.error,
    backgroundColor: M3SysColors.errorContainer,
    color: M3SysColors.onErrorContainer,
  },
  messageInfo: {
    borderColor: M3SysColors.secondary, // Example
    backgroundColor: M3SysColors.secondaryContainer,
    color: M3SysColors.onSecondaryContainer,
  }
});

@customElement('newsletter-signup-form')
export class NewsletterSignupForm extends LitElement {
  @property({ type: String, attribute: 'email-placeholder' })
  emailPlaceholder = 'Enter your email';

  @property({ type: String, attribute: 'name-placeholder' })
  namePlaceholder = 'Your Name';

  @property({ type: String, attribute: 'button-text' })
  buttonText = 'Subscribe';

  @property({ type: Boolean, attribute: 'show-name-field' })
  showNameField = false;

  @property({ type: String, attribute: 'form-action' })
  formAction = ''; // URL to post to

  @property({ type: String, attribute: 'nonce-value' })
  nonceValue = '';

  @property({ type: String, attribute: 'nonce-name' })
  nonceName = '_wpnonce';

  @property({ type: String, attribute: 'form-id' })
  formId = '';

  // Properties to display submission messages
  @property({ type: String, attribute: 'submission-message' })
  submissionMessage = '';

  @property({ type: String, attribute: 'message-type' }) // 'success', 'error', 'info'
  messageType = '';


  // LitElement does not use Shadow DOM by default if createRenderRoot() returns `this`.
  // However, to use StyleXJS effectively with Lit's default Shadow DOM,
  // styles need to be adopted or linked.
  // For simplicity of this example, IF StyleXJS outputs global CSS that this component can use,
  // then Shadow DOM can be kept. If not, Light DOM might be easier for StyleXJS classes.
  // Let's assume global CSS output for now, or that MWC components handle their own styling well.
  // createRenderRoot() { return this; } // To use Light DOM if StyleXJS classes are global

  render() {
    let messageClasses = styles.messageBase;
    if (this.messageType === 'success') {
        messageClasses = stylex.props(styles.messageBase, styles.messageSuccess).className;
    } else if (this.messageType === 'error') {
        messageClasses = stylex.props(styles.messageBase, styles.messageError).className;
    } else if (this.messageType === 'info') {
        messageClasses = stylex.props(styles.messageBase, styles.messageInfo).className;
    }

    return html`
      ${this.submissionMessage && this.messageType ?
        html`<div class="${messageClasses || ''}">${this.submissionMessage}</div>` : ''
      }
      <form
        id=${this.formId || 'newsletter-form-wc'}
        class=${stylex.props(styles.form).className}
        method="post"
        action=${this.formAction || window.location.href}
      >
        <input type="hidden" name="action" value="charity_m3_subscribe" />
        <input type="hidden" name=${this.nonceName} value=${this.nonceValue} />

        ${this.showNameField ?
          html`
            <md-outlined-text-field
              label=${this.namePlaceholder}
              type="text"
              name="newsletter_name"
              class=${stylex.props(styles.textField).className}
            ></md-outlined-text-field>
          ` : ''
        }
        <md-outlined-text-field
          label=${this.emailPlaceholder}
          type="email"
          name="newsletter_email"
          required
          class=${stylex.props(styles.textField).className}
        ></md-outlined-text-field>
        <md-filled-button type="submit">
          ${this.buttonText}
        </md-filled-button>
      </form>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'newsletter-signup-form': NewsletterSignupForm;
  }
}
