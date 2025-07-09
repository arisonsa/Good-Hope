/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks, // Crucial for nested blocks
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    TextControl,
} from '@wordpress/components';

// Import metadata to use its name, attributes, etc.
import metadata from './block.json';

const ALLOWED_BLOCKS = ['charity-m3/card-item'];

// Optional: Define a template for when the block is first inserted
const GRID_TEMPLATE = [
    ['charity-m3/card-item', { title: 'Card 1 Title', text: 'Some placeholder text for card 1.' }],
    ['charity-m3/card-item', { title: 'Card 2 Title', text: 'Some placeholder text for card 2.' }],
    ['charity-m3/card-item', { title: 'Card 3 Title', text: 'Some placeholder text for card 3.' }],
];


export default function Edit({ attributes, setAttributes, clientId }) {
    const {
        gridTag,
        cols,
        gap,
        // Note: 'allowedBlocks' from block.json is used by InnerBlocks, not directly managed as an attribute here
    } = attributes;

    const blockProps = useBlockProps({
        className: `wp-block-charity-m3-card-grid--editor-preview align${attributes.align || ''}`,
        // Add any specific preview styles for the grid container if needed
        // For example, a dashed border to visualize the grid area in the editor
        // style: { border: '1px dashed #ccc', padding: '1rem' }
    });

    // Options for the 'cols' SelectControl
    const columnOptions = [
        { label: __('Responsive Default (1 -> 2 -> 3)', 'charity-m3'), value: 'responsive-default' },
        { label: __('1 Column', 'charity-m3'), value: '1' },
        { label: __('2 Columns', 'charity-m3'), value: '2' },
        { label: __('3 Columns', 'charity-m3'), value: '3' },
        { label: __('4 Columns', 'charity-m3'), value: '4' },
        // Add more predefined responsive options if the Lit component supports them by keyword
        // e.g. { label: '1 -> 2 Columns (sm)', value: '1_sm:2'}
    ];

    // Options for the 'gap' SelectControl (could also be TextControl for CSS units)
    const gapOptions = [
        { label: __('Small (0.5rem / 8px)', 'charity-m3'), value: '2' }, // Maps to StyleXJS gap2
        { label: __('Medium (1rem / 16px)', 'charity-m3'), value: '4' }, // Maps to StyleXJS gap4
        { label: __('Large (1.5rem / 24px)', 'charity-m3'), value: '6' }, // Maps to StyleXJS gap6 (default)
        { label: __('Extra Large (2rem / 32px)', 'charity-m3'), value: '8' }, // Maps to StyleXJS gap8
    ];


    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Grid Layout Settings', 'charity-m3')}>
                    <SelectControl
                        label={__('HTML Tag for Grid Container', 'charity-m3')}
                        value={gridTag}
                        options={[
                            { label: 'div', value: 'div' },
                            { label: 'ul (for lists of cards)', value: 'ul' },
                            { label: 'section', value: 'section' },
                            // Add other semantic tags if appropriate
                        ]}
                        onChange={(val) => setAttributes({ gridTag: val })}
                    />
                    <SelectControl
                        label={__('Columns Configuration', 'charity-m3')}
                        value={cols}
                        options={columnOptions}
                        onChange={(val) => setAttributes({ cols: val })}
                        help={__('Select a predefined column layout or the responsive default.', 'charity-m3')}
                    />
                    <SelectControl
                        label={__('Gap Between Items', 'charity-m3')}
                        value={gap}
                        options={gapOptions}
                        onChange={(val) => setAttributes({ gap: val })}
                        help={__('Select the spacing between grid items.', 'charity-m3')}
                    />
                     <TextControl
                        label={__('Custom Gap (CSS value)', 'charity-m3')}
                        value={gap.match(/^[0-9.]+([a-z%]+)$/i) ? gap : ''} // Show only if it's a custom CSS value
                        onChange={(val) => setAttributes({ gap: val || '6' })} // Revert to default if cleared
                        help={__('Overrides selection above. E.g., "1.25rem", "20px".', 'charity-m3')}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {/*
                  The editor preview for the grid itself.
                  We could try to render the <charity-grid> Lit component here,
                  but that can be complex within Gutenberg's iframe and React context.
                  A simpler approach is to let InnerBlocks handle the child items,
                  and the grid styling will be apparent from how those items are laid out.
                  The `blockProps` can have some minimal styling to delineate the grid area.
                */}
                <InnerBlocks
                    allowedBlocks={ALLOWED_BLOCKS}
                    template={GRID_TEMPLATE}
                    // orientation="horizontal" // if you want horizontal block list controls
                    // renderAppender={() => <InnerBlocks.ButtonBlockAppender />} // Default appender
                />
            </div>
        </>
    );
}
