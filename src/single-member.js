import { registerBlockType } from '@wordpress/blocks';
import { TextControl, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';

registerBlockType('myplugin/single-member', {
    title: 'Single Member',
    icon: 'id',
    category: 'widgets',
    attributes: {
        memberId: {
            type: 'number',
        },
    },
    edit: ({ attributes, setAttributes }) => {
        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Member ID"
                            value={attributes.memberId || ''}
                            onChange={(val) => setAttributes({ memberId: Number(val) })}
                            help="Enter the ID of the member to display"
                        />
                    </PanelBody>
                </InspectorControls>
                <p><em>Single member will be displayed on the front end.</em></p>
            </Fragment>
        );
    },
    save: () => null, // dynamic block, render in PHP
});
