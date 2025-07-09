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
    PanelColorSettings, // For color pickers
    AlignmentToolbar, // For text-align
    BlockVerticalAlignmentToolbar, // Could be used for content vertical align if needed
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    Button,
    SelectControl,
    ExternalLink,
    IconButton, // For removing/editing buttons in repeater
    __experimentalUnitControl as UnitControl, // For minHeight with units
    __experimentalBoxControl as BoxControl, // For padding/margin if needed for wrapper
    ColorPalette, // Alternative to PanelColorSettings for more control
} from '@wordpress/components';
import { useEffect } from '@wordpress/element'; // For generating blockId
import { v4 as uuidv4 } from 'uuid'; // For generating unique IDs for buttons in repeater


/**
 * Internal dependencies
 */
import metadata from './block.json'; // Import G G's metadata to use its name

// Helper: Button Repeater Item (simplified)
const ButtonEditor = ({ button, index, attributes, setAttributes }) => {
    const updateButton = (key, value) => {
        const newButtons = [...attributes.buttons];
        newButtons[index] = { ...newButtons[index], [key]: value };
        setAttributes({ buttons: newButtons });
    };

    const removeButton = () => {
        const newButtons = attributes.buttons.filter((_, i) => i !== index);
        setAttributes({ buttons: newButtons });
    };

    return (
        <PanelBody title={`${__('Button', 'charity-m3')} ${index + 1}`} initialOpen={false}>
            <TextControl
                label={__('Text', 'charity-m3')}
                value={button.text}
                onChange={(val) => updateButton('text', val)}
            />
            <TextControl
                label={__('Link URL', 'charity-m3')}
                value={button.href}
                onChange={(val) => updateButton('href', val)}
                placeholder="https://example.com"
            />
            <SelectControl
                label={__('Type/Variant', 'charity-m3')}
                value={button.type}
                options={[
                    { label: 'Filled', value: 'filled' },
                    { label: 'Outlined', value: 'outlined' },
                    { label: 'Text', value: 'text' },
                    { label: 'Elevated', value: 'elevated' },
                    { label: 'Tonal', value: 'tonal' },
                ]}
                onChange={(val) => updateButton('type', val)}
            />
            <TextControl
                label={__('Icon (Material Symbol name)', 'charity-m3')}
                value={button.icon}
                onChange={(val) => updateButton('icon', val)}
                help={__('E.g., "favorite", "arrow_forward". Leave empty for no icon.', 'charity-m3')}
            />
            <TextControl
                label={__('Link Target (e.g., _blank)', 'charity-m3')}
                value={button.target}
                onChange={(val) => updateButton('target', val)}
            />
             <TextControl
                label={__('Link Rel', 'charity-m3')}
                value={button.rel}
                onChange={(val) => updateButton('rel', val)}
            />
            <Button isDestructive onClick={removeButton}>
                {__('Remove Button', 'charity-m3')}
            </Button>
        </PanelBody>
    );
};


export default function Edit({ attributes, setAttributes, clientId }) {
    const {
        title,
        subtitle,
        backgroundImageId,
        backgroundImageUrl,
        showOverlay,
        backgroundColor,
        textColor,
        contentWidth,
        textAlignment,
        minHeight,
        buttons,
        blockId, // Used for unique IDs, nonces, etc.
    } = attributes;

    // Generate a unique blockId if it doesn't exist
    useEffect(() => {
        if (!blockId) {
            setAttributes({ blockId: `charity-m3-fc-${clientId.substring(0, 8)}` });
        }
    }, [blockId, clientId, setAttributes]);


    const blockProps = useBlockProps({
        className: `wp-block-charity-m3-featured-callout--editor-preview align${attributes.align || ''}`,
        // Inline styles for the editor preview to mimic the frontend component
        // These should roughly match what the <charity-hero> web component does.
        style: {
            backgroundColor: backgroundImageUrl ? undefined : backgroundColor,
            backgroundImage: backgroundImageUrl ? `url(${backgroundImageUrl})` : undefined,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            color: textColor,
            textAlign: textAlignment,
            minHeight: minHeight,
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: textAlignment === 'left' ? 'flex-start' : textAlignment === 'right' ? 'flex-end' : 'center',
            padding: '2rem', // Generic padding for preview
            position: 'relative', // For overlay
        },
    });

    const onSelectMedia = (media) => {
        setAttributes({
            backgroundImageId: media.id,
            backgroundImageUrl: media.url,
        });
    };

    const onRemoveMedia = () => {
        setAttributes({
            backgroundImageId: 0,
            backgroundImageUrl: '',
        });
    };

    const addButtonStyle = { // For the "Add Button" button itself
        marginTop: '10px',
        marginBottom: '10px',
    };

    const colorPalette = [ // Example M3 palette for pickers (subset)
        // These should ideally come from a shared JS token definition if possible
        { name: 'Primary', slug: 'primary', color: 'var(--md-sys-color-primary)' },
        { name: 'On Primary', slug: 'on-primary', color: 'var(--md-sys-color-on-primary)' },
        { name: 'Primary Container', slug: 'primary-container', color: 'var(--md-sys-color-primary-container)' },
        { name: 'On Primary Container', slug: 'on-primary-container', color: 'var(--md-sys-color-on-primary-container)' },
        { name: 'Secondary', slug: 'secondary', color: 'var(--md-sys-color-secondary)' },
        { name: 'Secondary Container', slug: 'secondary-container', color: 'var(--md-sys-color-secondary-container)' },
        { name: 'Surface Variant', slug: 'surface-variant', color: 'var(--md-sys-color-surface-variant)' },
        { name: 'On Surface Variant', slug: 'on-surface-variant', color: 'var(--md-sys-color-on-surface-variant)' },
        { name: 'Surface', slug: 'surface', color: 'var(--md-sys-color-surface)' },
        { name: 'On Surface', slug: 'on-surface', color: 'var(--md-sys-color-on-surface)' },
        { name: 'Background', slug: 'background', color: 'var(--md-sys-color-background)' },
        { name: 'On Background', slug: 'on-background', color: 'var(--md-sys-color-on-background)' },
    ];


    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Content', 'charity-m3')}>
                    <TextControl
                        label={__('Title (for editor only, real title uses RichText)', 'charity-m3')}
                        value={title} // This is just for the TextControl, RichText below is the source
                        onChange={(val) => setAttributes({ title: val })} // Syncs if you type here
                        help={__('Edit the title directly in the block preview.', 'charity-m3')}
                    />
                     <TextControl
                        label={__('Subtitle (for editor only, real subtitle uses RichText)', 'charity-m3')}
                        value={subtitle}
                        onChange={(val) => setAttributes({ subtitle: val })}
                        help={__('Edit the subtitle directly in the block preview.', 'charity-m3')}
                    />
                </PanelBody>

                <PanelBody title={__('Background', 'charity-m3')}>
                    <MediaUploadCheck fallback={<div>{__('Please enable media uploads.', 'charity-m3')}</div>}>
                        <MediaUpload
                            onSelect={onSelectMedia}
                            allowedTypes={['image']}
                            value={backgroundImageId}
                            render={({ open }) => (
                                <Button onClick={open} isPrimary>
                                    {!backgroundImageId ? __('Upload/Select Image', 'charity-m3') : __('Replace Image', 'charity-m3')}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    {backgroundImageId !== 0 && (
                        <Button onClick={onRemoveMedia} isLink isDestructive style={{ marginTop: '10px' }}>
                            {__('Remove Background Image', 'charity-m3')}
                        </Button>
                    )}
                    <ToggleControl
                        label={__('Show Overlay on Image', 'charity-m3')}
                        checked={showOverlay}
                        onChange={(val) => setAttributes({ showOverlay: val })}
                        disabled={!backgroundImageUrl}
                    />
                    <PanelColorSettings
                        title={__('Background Color (if no image)', 'charity-m3')}
                        colorSettings={[
                            {
                                value: backgroundColor,
                                onChange: (val) => setAttributes({ backgroundColor: val || 'var(--md-sys-color-surface-variant)' }),
                                label: __('Background Color', 'charity-m3'),
                                colors: colorPalette, // Providing our M3 palette
                            },
                        ]}
                        enableAlpha
                    />
                </PanelBody>

                <PanelBody title={__('Layout & Text', 'charity-m3')}>
                     <PanelColorSettings
                        title={__('Text Color', 'charity-m3')}
                        colorSettings={[
                            {
                                value: textColor,
                                onChange: (val) => setAttributes({ textColor: val || 'var(--md-sys-color-on-surface-variant)' }),
                                label: __('Text Color', 'charity-m3'),
                                colors: colorPalette,
                            },
                        ]}
                        enableAlpha
                    />
                    <SelectControl
                        label={__('Content Width', 'charity-m3')}
                        value={contentWidth}
                        options={[
                            { label: 'Container (Standard)', value: 'container' },
                            { label: 'Narrow', value: 'narrow' },
                            { label: 'Wide', value: 'wide' },
                            { label: 'Full Width (padded)', value: 'full' },
                            { label: 'Edge-to-Edge (no padding)', value: 'edge-to-edge' },
                        ]}
                        onChange={(val) => setAttributes({ contentWidth: val })}
                    />
                    <TextControl // Using TextControl for minHeight for flexibility (e.g. '50vh', '400px')
                        label={__('Minimum Height (e.g., 60vh, 500px)', 'charity-m3')}
                        value={minHeight}
                        onChange={(val) => setAttributes({ minHeight: val })}
                    />
                     <div>
                        <p>{__('Text Alignment', 'charity-m3')}</p>
                        <AlignmentToolbar
                            value={textAlignment}
                            onChange={(val) => setAttributes({ textAlignment: val })}
                        />
                    </div>
                </PanelBody>

                <PanelBody title={__('Buttons', 'charity-m3')}>
                    {buttons.map((button, index) => (
                        <ButtonEditor
                            key={button.id || index} // Use a unique key if buttons have IDs
                            button={button}
                            index={index}
                            attributes={attributes}
                            setAttributes={setAttributes}
                        />
                    ))}
                    <Button
                        variant="secondary"
                        onClick={() => setAttributes({ buttons: [...buttons, { text: 'New Button', href: '#', type: 'filled', id: uuidv4() }] })}
                        style={addButtonStyle}
                    >
                        {__('Add Button', 'charity-m3')}
                    </Button>
                </PanelBody>

            </InspectorControls>

            {/* Editor Preview */}
            <div {...blockProps}>
                {backgroundImageUrl && showOverlay && (
                    <div style={{ position: 'absolute', inset: 0, backgroundColor: 'rgba(0,0,0,0.4)', zIndex: 0 }}></div>
                )}
                <div style={{ position: 'relative', zIndex: 1, width: contentWidth === 'container' || contentWidth === 'narrow' || contentWidth === 'wide' ? '80%' : '100%', margin: '0 auto' }}>
                    <RichText
                        tagName="h2" // Semantic for a section title within the editor
                        className="wp-block-charity-m3-featured-callout__title" // For attribute source
                        value={title}
                        onChange={(val) => setAttributes({ title: val })}
                        placeholder={__('Enter Title...', 'charity-m3')}
                        style={{
                            // Rough approximation of M3 Display Medium/Large for preview
                            fontSize: '2.5rem', fontWeight: 'bold', marginBottom: '0.5em',
                            color: 'inherit' // Inherits from blockProps.style.color
                        }}
                    />
                    <RichText
                        tagName="p"
                        className="wp-block-charity-m3-featured-callout__subtitle" // For attribute source
                        value={subtitle}
                        onChange={(val) => setAttributes({ subtitle: val })}
                        placeholder={__('Enter Subtitle...', 'charity-m3')}
                        style={{
                            fontSize: '1.25rem', marginBottom: '1em', opacity: 0.9,
                            color: 'inherit'
                        }}
                    />
                    {/* Simplified button preview */}
                    {buttons.length > 0 && (
                        <div style={{ marginTop: '1.5em', display: 'flex', gap: '10px', justifyContent: textAlignment === 'left' ? 'flex-start' : textAlignment === 'right' ? 'flex-end' : 'center' }}>
                            {buttons.map((btn, index) => (
                                <div key={index} style={{
                                    padding: '10px 15px',
                                    backgroundColor: btn.type === 'filled' ? 'var(--md-sys-color-primary)' : 'transparent',
                                    color: btn.type === 'filled' ? 'var(--md-sys-color-on-primary)' : 'var(--md-sys-color-primary)',
                                    border: btn.type === 'outlined' ? '1px solid var(--md-sys-color-outline)' : '1px solid transparent',
                                    borderRadius: '20px'
                                }}>
                                    {btn.icon && <span className="dashicons dashicons-admin-generic" style={{marginRight: '5px'}}></span>}
                                    {btn.text || __('Button', 'charity-m3')}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
                 {/* Hidden element for backgroundImageUrl source attribute if needed, though not strictly if not using save() */}
                <div className="wp-block-charity-m3-featured-callout__background" data-background-url={backgroundImageUrl} style={{display: 'none'}}></div>
            </div>
        </>
    );
}
