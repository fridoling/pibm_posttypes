import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

registerBlockType('pibm/single-job', {
    title: 'Single Job',
    icon: 'id',
    category: 'widgets',
    attributes: {
        postId: { type: 'number' },
    },
    edit({ attributes, setAttributes }) {
        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Job Settings">
                        <TextControl
                            label="Post ID"
                            value={attributes.postId || ''}
                            onChange={(val) => setAttributes({ postId: parseInt(val) || 0 })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...useBlockProps()} style={{ padding: '0.5rem', fontStyle: 'italic' }}>
                    Single Job Block Preview (Post ID: {attributes.postId || 'none'})
                </div>
            </Fragment>
        );
    },
    save() {
        return null; // dynamic block
    },
});
