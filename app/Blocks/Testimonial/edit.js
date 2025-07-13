import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { quote, authorName, authorTitle, authorImageId, authorImageUrl } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-testimonial--editor-preview',
        style: { border: '1px dashed #ccc', padding: '1.5rem', backgroundColor: '#f9f9f9', textAlign: 'center' }
    });

    const onSelectMedia = (media) => setAttributes({ authorImageId: media.id, authorImageUrl: media.url });
    const onRemoveMedia = () => setAttributes({ authorImageId: 0, authorImageUrl: '' });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Attribution', 'charity-m3')}>
                    <TextControl
                        label={__('Author Name', 'charity-m3')}
                        value={authorName}
                        onChange={(val) => setAttributes({ authorName: val })}
                    />
                    <TextControl
                        label={__('Author Title / Affiliation', 'charity-m3')}
                        value={authorTitle}
                        onChange={(val) => setAttributes({ authorTitle: val })}
                    />
                    <p>{__('Author Image', 'charity-m3')}</p>
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={onSelectMedia}
                            allowedTypes={['image']}
                            value={authorImageId}
                            render={({ open }) => (
                                <Button onClick={open} isPrimary>
                                    {!authorImageId ? __('Set Image', 'charity-m3') : __('Replace Image', 'charity-m3')}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    {authorImageId !== 0 && (
                        <Button onClick={onRemoveMedia} isLink isDestructive style={{ marginTop: '10px' }}>
                            {__('Remove Image', 'charity-m3')}
                        </Button>
                    )}
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <RichText
                    tagName="blockquote"
                    value={quote}
                    onChange={(val) => setAttributes({ quote: val })}
                    placeholder={__('Enter quote here...', 'charity-m3')}
                    style={{ fontSize: '1.25rem', fontStyle: 'italic', margin: '0 0 1rem 0', borderLeft: '3px solid #ddd', paddingLeft: '1rem' }}
                />
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '1rem', marginTop: '1rem' }}>
                    {authorImageUrl && <img src={authorImageUrl} alt={authorName} style={{ width: '56px', height: '56px', borderRadius: '50%' }} />}
                    <div style={{ textAlign: 'left' }}>
                        <p style={{ fontWeight: 'bold', margin: 0 }}>{authorName || __('Author Name', 'charity-m3')}</p>
                        <p style={{ fontStyle: 'italic', color: '#555', margin: 0 }}>{authorTitle || __('Author Title', 'charity-m3')}</p>
                    </div>
                </div>
            </div>
        </>
    );
}
