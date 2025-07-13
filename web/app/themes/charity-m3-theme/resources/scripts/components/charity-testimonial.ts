import { LitElement, html, nothing } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import { M3SysColors, M3TypeScale, M3SysShape } from '../tokens';

const styles = stylex.create({
    wrapper: {
        backgroundColor: M3SysColors.surfaceContainer,
        padding: '2rem',
        borderRadius: M3SysShape.corner.medium,
        textAlign: 'center',
    },
    quote: {
        ...M3TypeScale.headlineSmall,
        fontStyle: 'italic',
        color: M3SysColors.onSurface,
        position: 'relative',
        margin: '0',
        padding: '0 1.5rem', // Space for quote marks
        '::before': {
            content: '"“"',
            position: 'absolute',
            left: '-0.5rem',
            top: '-1rem',
            fontSize: '4rem',
            color: M3SysColors.primary,
            opacity: 0.5,
            lineHeight: '1',
        },
        '::after': {
            content: '"”"',
            position: 'absolute',
            right: '-0.5rem',
            bottom: '-2.5rem',
            fontSize: '4rem',
            color: M3SysColors.primary,
            opacity: 0.5,
            lineHeight: '1',
        },
    },
    attribution: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: '2rem',
        gap: '1rem',
    },
    authorImage: {
        width: '3.5rem', // 56px
        height: '3.5rem',
        borderRadius: M3SysShape.corner.full,
        objectFit: 'cover',
    },
    authorInfo: {
        textAlign: 'left',
    },
    authorName: {
        ...M3TypeScale.titleMedium,
        color: M3SysColors.onSurface,
        margin: '0',
    },
    authorTitle: {
        ...M3TypeScale.bodyMedium,
        color: M3SysColors.onSurfaceVariant,
        margin: '0',
    },
});

@customElement('charity-testimonial')
export class CharityTestimonial extends LitElement {
    @property({ type: String })
    quote = '';

    @property({ type: String, attribute: 'author-name' })
    authorName = '';

    @property({ type: String, attribute: 'author-title' })
    authorTitle = '';

    @property({ type: String, attribute: 'author-image-url' })
    authorImageUrl?: string;

    // Use Light DOM for simpler styling context
    createRenderRoot() {
        return this;
    }

    render() {
        return html`
            <figure ${stylex.props(styles.wrapper)}>
                <blockquote ${stylex.props(styles.quote)}>
                    <slot>${this.quote}</slot>
                </blockquote>
                ${this.authorName ? html`
                    <figcaption ${stylex.props(styles.attribution)}>
                        ${this.authorImageUrl ? html`
                            <img src=${this.authorImageUrl} alt=${this.authorName} ${stylex.props(styles.authorImage)} />
                        ` : nothing}
                        <div ${stylex.props(styles.authorInfo)}>
                            <div class="author-name" ${stylex.props(styles.authorName)}>${this.authorName}</div>
                            ${this.authorTitle ? html`<div class="author-title" ${stylex.props(styles.authorTitle)}>${this.authorTitle}</div>` : nothing}
                        </div>
                    </figcaption>
                ` : nothing}
            </figure>
        `;
    }
}

declare global {
    interface HTMLElementTagNameMap {
        'charity-testimonial': CharityTestimonial;
    }
}
