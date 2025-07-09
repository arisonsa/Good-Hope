/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    RichText,
    MediaUpload,
    MediaUploadCheck,
    URLInput, // For href attribute
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    Button,
    SelectControl,
    TextareaControl, // For text attribute if not using RichText in preview for it
} from '@wordpress/components';

import metadata from './block.json';

export default function Edit({ attributes, setAttributes, clientId, isSelected }) {
    const {
        title,
        subtitle,
        text, // Main content for the card
        imageId,
        imageUrl,
        imageAlt,
        href,
        variant,
        interactive,
        button1Text, button1Href, button1Type, button1Icon,
        button2Text, button2Href, button2Type, button2Icon,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-card-item--editor-preview',
        // Add some basic styling to make it look like a card in the editor
        style: {
            border: '1px dashed #ccc',
            padding: '1rem',
            marginBottom: '1rem', // Space between card items in editor if parent doesn't provide it
            backgroundColor: variant === 'filled' ? '#f0f0f0' : (variant === 'outlined' ? '#fff' : '#f9f9f9')
        }
    });

    const onSelectMedia = (media) => {
        setAttributes({
            imageId: media.id,
            imageUrl: media.url,
            imageAlt: media.alt || media.title,
        });
    };
    const onRemoveMedia = () => setAttributes({ imageId: 0, imageUrl: '', imageAlt: '' });

    const buttonTypeOptions = [
        { label: 'Text', value: 'text' },
        { label: 'Filled', value: 'filled' },
        { label: 'Outlined', value: 'outlined' },
        { label: 'Elevated', value: 'elevated' },
        { label: 'Tonal', value: 'tonal' },
    ];

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Card Content', 'charity-m3')}>
                    <TextControl
                        label={__('Title', 'charity-m3')}
                        value={title}
                        onChange={(val) => setAttributes({ title: val })}
                    />
                    <TextControl
                        label={__('Subtitle', 'charity-m3')}
                        value={subtitle}
                        onChange={(val) => setAttributes({ subtitle: val })}
                    />
                    {/* Main text can be a TextareaControl in inspector or RichText in preview */}
                    <TextareaControl
                        label={__('Main Text/Content', 'charity-m3')}
                        value={text}
                        onChange={(val) => setAttributes({ text: val })}
                        help={__('This content will go into the main body of the card. Alternatively, edit directly in the card preview if enabled.', 'charity-m3')}
                        rows={4}
                    />
                </PanelBody>

                <PanelBody title={__('Card Media & Link', 'charity-m3')}>
                    <MediaUploadCheck fallback={<div>{__('Enable media uploads.', 'charity-m3')}</div>}>
                        <MediaUpload
                            onSelect={onSelectMedia}
                            allowedTypes={['image']}
                            value={imageId}
                            render={({ open }) => (
                                <Button onClick={open} isPrimary>
                                    {!imageId ? __('Set Image', 'charity-m3') : __('Replace Image', 'charity-m3')}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    {imageId !== 0 && (
                        <Button onClick={onRemoveMedia} isLink isDestructive style={{ marginTop: '10px', display: 'block' }}>
                            {__('Remove Image', 'charity-m3')}
                        </Button>
                    )}
                    <TextControl
                        label={__('Image Alt Text', 'charity-m3')}
                        value={imageAlt}
                        onChange={(val) => setAttributes({ imageAlt: val })}
                        disabled={!imageUrl}
                    />
                    <p>{__('Card Link (HREF)', 'charity-m3')}</p>
                    <URLInput
                        label={__('Card URL', 'charity-m3')}
                        value={href}
                        onChange={(val) => setAttributes({ href: val })}
                    />
                    <ToggleControl
                        label={__('Make entire card interactive (if URL is set)', 'charity-m3')}
                        checked={interactive}
                        onChange={(val) => setAttributes({ interactive: val })}
                        disabled={!href}
                    />
                </PanelBody>

                <PanelBody title={__('Card Style & Actions', 'charity-m3')}>
                    <SelectControl
                        label={__('Card Variant', 'charity-m3')}
                        value={variant}
                        options={[
                            { label: 'Elevated', value: 'elevated' },
                            { label: 'Filled', value: 'filled' },
                            { label: 'Outlined', value: 'outlined' },
                        ]}
                        onChange={(val) => setAttributes({ variant: val })}
                    />
                </PanelBody>

                {/* Simplified Button Controls - up to 2 buttons */}
                <PanelBody title={__('Button 1 (Optional)', 'charity-m3')} initialOpen={false}>
                    <TextControl label={__('Text', 'charity-m3')} value={button1Text} onChange={(val) => setAttributes({ button1Text: val })} />
                    <URLInput label={__('Link URL', 'charity-m3')} value={button1Href} onChange={(val) => setAttributes({ button1Href: val })} />
                    <SelectControl label={__('Type', 'charity-m3')} value={button1Type} options={buttonTypeOptions} onChange={(val) => setAttributes({ button1Type: val })} />
                    <TextControl label={__('Icon', 'charity-m3')} value={button1Icon} onChange={(val) => setAttributes({ button1Icon: val })} />
                </PanelBody>

                {(!!button1Text || !!button1Href) && ( // Show Button 2 only if Button 1 has some data
                    <PanelBody title={__('Button 2 (Optional)', 'charity-m3')} initialOpen={false}>
                        <TextControl label={__('Text', 'charity-m3')} value={button2Text} onChange={(val) => setAttributes({ button2Text: val })} />
                        <URLInput label={__('Link URL', 'charity-m3')} value={button2Href} onChange={(val) => setAttributes({ button2Href: val })} />
                        <SelectControl label={__('Type', 'charity-m3')} value={button2Type} options={buttonTypeOptions} onChange={(val) => setAttributes({ button2Type: val })} />
                        <TextControl label={__('Icon', 'charity-m3')} value={button2Icon} onChange={(val) => setAttributes({ button2Icon: val })} />
                    </PanelBody>
                )}


            </InspectorControls>

            {/* Editor Preview for the Card Item */}
            <div {...blockProps}>
                {imageUrl && (
                    <img src={imageUrl} alt={imageAlt || title || ''} style={{ maxWidth: '100%', height: 'auto', marginBottom: '0.5rem' }} />
                )}
                <RichText
                    tagName="h4" // Semantic for item title within editor
                    className="wp-block-charity-m3-card-item__title" // For attribute source
                    value={title}
                    onChange={(val) => setAttributes({ title: val })}
                    placeholder={__('Card Title...', 'charity-m3')}
                    style={{ fontSize: '1.25rem', fontWeight: 'bold', margin: '0 0 0.25rem 0' }}
                    allowedFormats={['core/bold', 'core/italic']}
                />
                {subtitle && (
                     <RichText
                        tagName="p"
                        className="wp-block-charity-m3-card-item__subtitle"
                        value={subtitle}
                        onChange={(val) => setAttributes({ subtitle: val })}
                        placeholder={__('Card Subtitle...', 'charity-m3')}
                        style={{ fontSize: '0.9rem', color: '#555', margin: '0 0 0.5rem 0' }}
                        allowedFormats={['core/bold', 'core/italic']}
                    />
                )}
                <RichText
                    tagName="p"
                    className="wp-block-charity-m3-card-item__text" // For attribute source
                    value={text}
                    onChange={(val) => setAttributes({ text: val })}
                    placeholder={__('Card content...', 'charity-m3')}
                    style={{ fontSize: '1rem', margin: '0' }}
                    allowedFormats={['core/bold', 'core/italic', 'core/link']}
                />
                {/* Simplified button preview */}
                <div style={{ marginTop: '0.75rem', display: 'flex', gap: '0.5rem' }}>
                    {button1Text && button1Href && (
                        <span style={{ padding: '0.25rem 0.5rem', border: '1px solid #ddd', borderRadius: '4px', fontSize: '0.8rem' }}>
                            {button1Icon && `[${button1Icon}] `}{button1Text}
                        </span>
                    )}
                    {button2Text && button2Href && (
                         <span style={{ padding: '0.25rem 0.5rem', border: '1px solid #ddd', borderRadius: '4px', fontSize: '0.8rem' }}>
                            {button2Icon && `[${button2Icon}] `}{button2Text}
                        </span>
                    )}
                </div>
            </div>
        </>
    );
}
