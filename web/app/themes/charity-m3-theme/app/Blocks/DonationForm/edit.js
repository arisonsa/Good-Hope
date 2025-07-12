/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    TextareaControl,
    SelectControl,
} from '@wordpress/components';
import { useEffect } from '@wordpress/element';

import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
    const {
        title,
        description,
        suggestedAmounts,
        defaultFrequency,
        campaignId,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-donation-form--editor-preview',
        style: {
            border: '1px dashed #ccc',
            padding: '1.5rem',
            backgroundColor: '#f9f9f9',
        }
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Form Content', 'charity-m3')}>
                    <TextControl
                        label={__('Form Title', 'charity-m3')}
                        value={title}
                        onChange={(val) => setAttributes({ title: val })}
                    />
                    <TextareaControl
                        label={__('Form Description', 'charity-m3')}
                        value={description}
                        onChange={(val) => setAttributes({ description: val })}
                        rows={3}
                    />
                </PanelBody>
                <PanelBody title={__('Donation Settings', 'charity-m3')}>
                    <TextControl
                        label={__('Suggested Amounts', 'charity-m3')}
                        value={suggestedAmounts}
                        onChange={(val) => setAttributes({ suggestedAmounts: val })}
                        help={__('Enter amounts separated by commas (e.g., 25, 50, 100).', 'charity-m3')}
                    />
                    <SelectControl
                        label={__('Default Frequency', 'charity-m3')}
                        value={defaultFrequency}
                        options={[
                            { label: 'One-time', value: 'one-time' },
                            { label: 'Monthly', value: 'monthly' },
                        ]}
                        onChange={(val) => setAttributes({ defaultFrequency: val })}
                    />
                    <TextControl
                        label={__('Campaign ID (Optional)', 'charity-m3')}
                        type="number"
                        value={campaignId}
                        onChange={(val) => setAttributes({ campaignId: val ? parseInt(val, 10) : undefined })}
                        help={__('Associate donations from this form with a specific campaign.', 'charity-m3')}
                    />
                </PanelBody>
            </InspectorControls>

            {/* Editor Preview */}
            <div {...blockProps}>
                <h3 style={{ marginTop: 0, marginBottom: '0.5rem' }}>{title || __('Donation Form', 'charity-m3')}</h3>
                <p style={{ marginTop: 0, marginBottom: '1rem', fontSize: '0.9rem', color: '#555' }}>{description}</p>
                <div style={{ marginBottom: '1rem' }}>
                    <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: 'bold' }}>Donation Frequency</label>
                    <span style={{ border: '1px solid #ddd', borderRadius: '20px', padding: '0.5rem 1rem', marginRight: '0.5rem', backgroundColor: '#e0e0e0' }}>One-time</span>
                    <span style={{ border: '1px solid #ddd', borderRadius: '20px', padding: '0.5rem 1rem' }}>Monthly</span>
                </div>
                <div style={{ marginBottom: '1rem' }}>
                    <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: 'bold' }}>Select Amount (USD)</label>
                    <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                        {(suggestedAmounts || '25,50,100').split(',').map((amt, i) => (
                            <span key={i} style={{ border: '1px solid #ccc', borderRadius: '20px', padding: '0.5rem 1rem' }}>${amt.trim()}</span>
                        ))}
                         <span style={{ border: '1px solid #ccc', borderRadius: '4px', padding: '0.5rem 1rem', width: '100px' }}>Other</span>
                    </div>
                </div>
                <div style={{ border: '1px solid #ccc', borderRadius: '4px', padding: '1rem', backgroundColor: '#fff', fontStyle: 'italic', color: '#777' }}>
                    Card Details (Stripe Element)
                </div>
                <div style={{ marginTop: '1rem', padding: '0.5rem 1rem', backgroundColor: '#6750A4', color: '#fff', textAlign: 'center', borderRadius: '20px' }}>
                    Donate Now
                </div>
                <p style={{ textAlign: 'center', fontStyle: 'italic', fontSize: '0.8rem', marginTop: '1rem', color: '#777' }}>
                    (This is a simplified preview. The actual form will be interactive and styled with M3.)
                </p>
            </div>
        </>
    );
}
