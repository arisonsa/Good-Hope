/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks,
} from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    RangeControl,
    SelectControl,
    TextControl,
} from '@wordpress/components';

import metadata from './block.json';

const ALLOWED_BLOCKS = true; // Allow any block for maximum flexibility

// A simple template to guide users
const CAROUSEL_TEMPLATE = [
    ['core/image', {}],
    ['core/image', {}],
    ['charity-m3/card-item', { title: 'Card Slide Example' }],
];


export default function Edit({ attributes, setAttributes }) {
    const {
        slidesPerView,
        spaceBetween,
        loop,
        autoplay,
        delay,
        showNavigation,
        showPagination,
        effect,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-carousel--editor-preview',
        style: {
            border: '2px dashed #938F99', // M3 outline color
            padding: '1rem',
        }
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Carousel Settings', 'charity-m3')}>
                    <RangeControl
                        label={__('Slides Per View', 'charity-m3')}
                        value={slidesPerView}
                        onChange={(val) => setAttributes({ slidesPerView: val })}
                        min={1}
                        max={6}
                        step={1}
                    />
                    <RangeControl
                        label={__('Space Between Slides (px)', 'charity-m3')}
                        value={spaceBetween}
                        onChange={(val) => setAttributes({ spaceBetween: val })}
                        min={0}
                        max={64}
                        step={4}
                    />
                    <SelectControl
                        label={__('Transition Effect', 'charity-m3')}
                        value={effect}
                        options={[
                            { label: 'Slide', value: 'slide' },
                            { label: 'Fade', value: 'fade' },
                            { label: 'Cube', value: 'cube' },
                            { label: 'Coverflow', value: 'coverflow' },
                            { label: 'Flip', value: 'flip' },
                        ]}
                        onChange={(val) => setAttributes({ effect: val })}
                    />
                </PanelBody>
                <PanelBody title={__('Controls & Behavior', 'charity-m3')}>
                    <ToggleControl
                        label={__('Show Navigation Arrows', 'charity-m3')}
                        checked={showNavigation}
                        onChange={(val) => setAttributes({ showNavigation: val })}
                    />
                    <ToggleControl
                        label={__('Show Pagination Dots', 'charity-m3')}
                        checked={showPagination}
                        onChange={(val) => setAttributes({ showPagination: val })}
                    />
                    <ToggleControl
                        label={__('Loop Slides', 'charity-m3')}
                        checked={loop}
                        onChange={(val) => setAttributes({ loop: val })}
                    />
                    <ToggleControl
                        label={__('Autoplay', 'charity-m3')}
                        checked={autoplay}
                        onChange={(val) => setAttributes({ autoplay: val })}
                    />
                    {autoplay && (
                        <TextControl
                            label={__('Autoplay Delay (ms)', 'charity-m3')}
                            type="number"
                            value={delay}
                            onChange={(val) => setAttributes({ delay: parseInt(val, 10) })}
                            step={100}
                        />
                    )}
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <p style={{ margin: '0 0 1rem 0', textAlign: 'center', fontStyle: 'italic', color: '#555' }}>
                    {__('Carousel Slides (displays horizontally on frontend)', 'charity-m3')}
                </p>
                <InnerBlocks
                    allowedBlocks={ALLOWED_BLOCKS}
                    template={CAROUSEL_TEMPLATE}
                    // The 'renderAppender' prop can be used to customize the button for adding new blocks
                    // renderAppender={() => <InnerBlocks.ButtonBlockAppender />}
                />
            </div>
        </>
    );
}
