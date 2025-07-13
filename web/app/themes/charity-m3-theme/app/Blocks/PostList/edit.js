/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl,
    Spinner,
    QueryControls, // Handles order, orderBy
    FormTokenField, // For selecting multiple terms
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
    const {
        postType,
        taxonomy,
        terms,
        count,
        orderBy,
        order,
        columns,
        gap,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'wp-block-charity-m3-post-list--editor-preview',
        style: {
            border: '1px dashed #ccc',
            padding: '1rem',
        }
    });

    // Fetch all available, public post types
    const postTypes = useSelect((select) => {
        const data = select(coreStore).getPostTypes({ per_page: -1 });
        return data?.filter(pt => pt.visibility.publicly_queryable && pt.slug !== 'attachment');
    }, []);

    // Fetch taxonomies available for the selected post type
    const taxonomies = useSelect((select) => {
        return select(coreStore).getTaxonomies({ type: postType, per_page: -1, context: 'view' });
    }, [postType]);

    // Fetch terms for the selected taxonomy
    const { termsList, hasResolvedTerms } = useSelect((select) => {
        if (!taxonomy) {
            return { termsList: [], hasResolvedTerms: true };
        }
        const selectorArgs = ['getEntities', 'taxonomy', taxonomy, { per_page: -1 }];
        return {
            termsList: select(coreStore).getEntities('taxonomy', taxonomy, { per_page: -1 }),
            hasResolvedTerms: select(coreStore).hasFinishedResolution('getEntities', ['taxonomy', taxonomy, { per_page: -1 }]),
        };
    }, [taxonomy]);

    const postTypeOptions = postTypes ? postTypes.map(pt => ({ label: pt.name, value: pt.slug })) : [];
    const taxonomyOptions = taxonomies ? [{label: 'Select a taxonomy...', value: ''}, ...taxonomies.map(tax => ({ label: tax.name, value: tax.slug }))] : [];

    // Prepare terms for FormTokenField
    const termNames = termsList?.map(term => term.name) || [];
    const selectedTermNames = termsList?.filter(term => terms.includes(term.id)).map(term => term.name) || [];

    const onTermsChange = (newTermNames) => {
        const newTermIds = newTermNames.map(name => {
            const found = termsList.find(term => term.name === name);
            return found ? found.id : null;
        }).filter(id => id !== null);
        setAttributes({ terms: newTermIds });
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Query Settings', 'charity-m3')}>
                    <SelectControl
                        label={__('Content Type', 'charity-m3')}
                        value={postType}
                        options={postTypeOptions}
                        onChange={(val) => setAttributes({ postType: val, taxonomy: '', terms: [] })} // Reset taxonomy/terms on change
                    />
                    {taxonomies && taxonomies.length > 0 && (
                        <SelectControl
                            label={__('Filter by Taxonomy', 'charity-m3')}
                            value={taxonomy}
                            options={taxonomyOptions}
                            onChange={(val) => setAttributes({ taxonomy: val, terms: [] })} // Reset terms on change
                        />
                    )}
                    {taxonomy && (
                        <div>
                            <label>{__('Filter by Terms', 'charity-m3')}</label>
                            {!hasResolvedTerms ? <Spinner /> : (
                                <FormTokenField
                                    label={__('Select Terms', 'charity-m3')}
                                    value={selectedTermNames}
                                    suggestions={termNames}
                                    onChange={onTermsChange}
                                    __experimentalExpandOnFocus // Better UX
                                />
                            )}
                        </div>
                    )}
                    <QueryControls
                        numberOfItems={count}
                        onNumberOfItemsChange={(val) => setAttributes({ count: val })}
                        minItems={1}
                        maxItems={12}
                        orderBy={orderBy}
                        onOrderByChange={(val) => setAttributes({ orderBy: val })}
                        order={order}
                        onOrderChange={(val) => setAttributes({ order: val })}
                    />
                </PanelBody>
                <PanelBody title={__('Layout Settings', 'charity-m3')}>
                    <SelectControl
                        label={__('Columns', 'charity-m3')}
                        value={columns}
                        options={[
                            { label: 'Responsive Default (1-3)', value: 'responsive-default' },
                            { label: '1', value: '1' },
                            { label: '2', value: '2' },
                            { label: '3', value: '3' },
                            { label: '4', value: '4' },
                        ]}
                        onChange={(val) => setAttributes({ columns: val })}
                    />
                     <SelectControl
                        label={__('Gap', 'charity-m3')}
                        value={gap}
                        options={[
                            { label: 'Small', value: '2' },
                            { label: 'Medium', value: '4' },
                            { label: 'Large', value: '6' },
                            { label: 'Extra Large', value: '8' },
                        ]}
                        onChange={(val) => setAttributes({ gap: val })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <h4 style={{marginTop: 0}}>{__('Post List Preview', 'charity-m3')}</h4>
                <p style={{fontSize: '0.9em', color: '#555'}}>
                    <strong>{__('Content Type:', 'charity-m3')}</strong> {postType}<br/>
                    <strong>{__('Count:', 'charity-m3')}</strong> {count}<br/>
                    <strong>{__('Order By:', 'charity-m3')}</strong> {orderBy} ({order})<br/>
                    {taxonomy && <strong>{__('Taxonomy:', 'charity-m3')}</strong>} {taxonomy}<br/>
                    {terms.length > 0 && <strong>{__('Terms:', 'charity-m3')}</strong>} {selectedTermNames.join(', ')}
                </p>
                <p style={{fontSize: '0.9em', color: '#555', fontStyle: 'italic'}}>
                    {__('Content is dynamically displayed on the frontend.', 'charity-m3')}
                </p>
            </div>
        </>
    );
}
