import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { value, prefix, suffix, description, icon } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-impact-stat--editor-preview',
        style: { textAlign: 'center', padding: '1rem', border: '1px dashed #ccc' }
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Statistic Settings', 'charity-m3')}>
                    <TextControl
                        label={__('Value (Number)', 'charity-m3')}
                        type="number"
                        value={value}
                        onChange={(val) => setAttributes({ value: parseInt(val, 10) || 0 })}
                    />
                    <TextControl
                        label={__('Prefix', 'charity-m3')}
                        value={prefix}
                        onChange={(val) => setAttributes({ prefix: val })}
                        help={__('E.g., $, €', 'charity-m3')}
                    />
                    <TextControl
                        label={__('Suffix', 'charity-m3')}
                        value={suffix}
                        onChange={(val) => setAttributes({ suffix: val })}
                        help={__('E.g., %, +, M', 'charity-m3')}
                    />
                    <TextControl
                        label={__('Icon (Material Symbol name)', 'charity-m3')}
                        value={icon}
                        onChange={(val) => setAttributes({ icon: val })}
                        help={__('E.g., "volunteer_activism"', 'charity-m3')}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {icon && (
                    <span className="dashicons dashicons-admin-generic" style={{ fontSize: '2.5rem', color: '#555' }}>
                        {/* In editor, we can use a dashicon as a stand-in for the Material Symbol */}
                    </span>
                )}
                <div style={{ fontSize: '2.5rem', fontWeight: 'bold', color: '#222' }}>
                    {prefix}{value.toLocaleString()}{suffix}
                </div>
                <RichText
                    tagName="p"
                    className="stat-description"
                    value={description}
                    onChange={(val) => setAttributes({ description: val })}
                    placeholder={__('Description...', 'charity-m3')}
                    style={{ fontSize: '1rem', color: '#555', margin: '0.5rem 0 0 0' }}
                    allowedFormats={[]}
                />
            </div>
        </>
    );
}
