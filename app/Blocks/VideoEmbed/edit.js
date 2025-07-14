import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, SandBox } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes }) {
    const { url, caption, aspectRatio } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-video-embed--editor-preview',
    });

    const embedPreview = useSelect(select => {
        const { getEmbedPreview } = select('core');
        return url ? getEmbedPreview(url) : null;
    }, [url]);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Video Settings', 'charity-m3')}>
                    <TextControl
                        label={__('Video URL', 'charity-m3')}
                        value={url}
                        onChange={(val) => setAttributes({ url: val })}
                        help={__('Enter the URL of the video (e.g., from YouTube, Vimeo).', 'charity-m3')}
                    />
                    <SelectControl
                        label={__('Aspect Ratio', 'charity-m3')}
                        value={aspectRatio}
                        options={[
                            { label: '16:9', value: '16/9' },
                            { label: '4:3', value: '4/3' },
                            { label: '1:1', value: '1/1' },
                        ]}
                        onChange={(val) => setAttributes({ aspectRatio: val })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {!url ? (
                    <p>{__('Enter a video URL in the block settings.', 'charity-m3')}</p>
                ) : (
                    <>
                        <div style={{ position: 'relative', paddingBottom: '56.25%', height: 0, backgroundColor: '#eee' }}>
                            {embedPreview ? (
                                <SandBox html={embedPreview.html} style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%' }} />
                            ) : (
                                <p style={{ textAlign: 'center', paddingTop: '20%' }}>{__('Generating preview...', 'charity-m3')}</p>
                            )}
                        </div>
                        <RichText
                            tagName="figcaption"
                            value={caption}
                            onChange={(val) => setAttributes({ caption: val })}
                            placeholder={__('Write caption...', 'charity-m3')}
                            style={{ textAlign: 'center', fontSize: '0.9rem', color: '#555', marginTop: '0.5rem' }}
                            allowedFormats={['core/bold', 'core/italic', 'core/link']}
                        />
                    </>
                )}
            </div>
        </>
    );
}
