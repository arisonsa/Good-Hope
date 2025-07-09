/**
 * Gutenberg Block Editor JS for Newsletter Signup
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, RichText, PanelColorSettings, AlignmentToolbar } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json'; // Import metadata to ensure consistency

// Import MWC components needed for the editor preview (if different from frontend)
// These are already imported in main.js, but ensure they are available.
// For editor-specific JS, it's good practice to ensure dependencies are explicitly handled.
import '@material/web/textfield/outlined-text-field.js';
import '@material/web/button/filled-button.js';
import '@material/web/icon/icon.js'; // For potential icons in editor controls

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const {
            title,
            description,
            emailPlaceholder,
            buttonText,
            textAlign,
            backgroundColor,
            textColor,
            showNameField,
            namePlaceholder,
        } = attributes;

        const blockProps = useBlockProps({
            className: `charity-m3-newsletter-signup-block-editor align${attributes.align || ''}`,
            style: {
                textAlign: textAlign,
                backgroundColor: backgroundColor, // Applied by block supports if used directly
                color: textColor, // Applied by block supports
            }
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Content Settings', 'charity-m3')}>
                        <TextControl
                            label={__('Title', 'charity-m3')}
                            value={title}
                            onChange={(val) => setAttributes({ title: val })}
                        />
                        <TextControl
                            label={__('Description', 'charity-m3')}
                            value={description}
                            onChange={(val) => setAttributes({ description: val })}
                            multiline={true}
                            rows={3}
                        />
                        <TextControl
                            label={__('Email Placeholder', 'charity-m3')}
                            value={emailPlaceholder}
                            onChange={(val) => setAttributes({ emailPlaceholder: val })}
                        />
                        <TextControl
                            label={__('Button Text', 'charity-m3')}
                            value={buttonText}
                            onChange={(val) => setAttributes({ buttonText: val })}
                        />
                        <ToggleControl
                            label={__('Show Name Field', 'charity-m3')}
                            checked={showNameField}
                            onChange={(val) => setAttributes({ showNameField: val })}
                        />
                        {showNameField && (
                            <TextControl
                                label={__('Name Field Placeholder', 'charity-m3')}
                                value={namePlaceholder}
                                onChange={(val) => setAttributes({ namePlaceholder: val })}
                            />
                        )}
                    </PanelBody>
                    <PanelColorSettings
                        title={__('Color Settings', 'charity-m3')}
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: backgroundColor,
                                onChange: (colorValue) => setAttributes({ backgroundColor: colorValue }),
                                label: __('Background Color', 'charity-m3'),
                            },
                            {
                                value: textColor,
                                onChange: (colorValue) => setAttributes({ textColor: colorValue }),
                                label: __('Text Color', 'charity-m3'),
                            },
                        ]}
                    />
                    {/* Consider adding more controls like typography, spacing if needed */}
                </InspectorControls>

                <div {...blockProps}>
                    <AlignmentToolbar
                        value={textAlign}
                        onChange={(newAlign) => setAttributes({ textAlign: newAlign })}
                    />
                    <RichText
                        tagName="h3"
                        className="md-typescale-headline-small"
                        value={title}
                        onChange={(val) => setAttributes({ title: val })}
                        placeholder={__('Enter title...', 'charity-m3')}
                        style={{ color: textColor }} // Ensure text color applies in editor
                    />
                    <RichText
                        tagName="p"
                        className="md-typescale-body-medium"
                        value={description}
                        onChange={(val) => setAttributes({ description: val })}
                        placeholder={__('Enter description...', 'charity-m3')}
                        style={{ color: textColor }} // Ensure text color applies in editor
                    />
                    <form className="newsletter-form-preview" onSubmit={(e) => e.preventDefault()}>
                        {showNameField && (
                            <md-outlined-text-field
                                label={namePlaceholder || __('Your Name', 'charity-m3')}
                                type="text"
                                disabled
                                style={{ marginBottom: '16px', width: '100%', maxWidth: '400px' }}
                            ></md-outlined-text-field>
                        )}
                        <md-outlined-text-field
                            label={emailPlaceholder || __('Enter your email', 'charity-m3')}
                            type="email"
                            disabled // Non-functional in editor preview
                            style={{ marginBottom: '16px', width: '100%', maxWidth: '400px' }}
                        ></md-outlined-text-field>
                        <md-filled-button type="button" disabled>
                            {buttonText || __('Subscribe', 'charity-m3')}
                        </md-filled-button>
                    </form>
                </div>
            </>
        );
    },
    // Save function is not needed as we are using PHP render_callback (dynamic block)
    // save: () => null, (or simply omit it)
});
