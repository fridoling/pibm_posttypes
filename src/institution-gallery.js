import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

registerBlockType('myplugin/institution-gallery', {
  title: 'Institution Gallery',
  icon: 'groups',
  category: 'design',
  attributes: {
    count: { type: 'number', default: -1 }, // -1 => show all
  },
  edit: ({ attributes, setAttributes }) => {
    const showAll = attributes.count === -1;

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title="Settings">
            <RangeControl
              label="Number of Institutions"
              value={showAll ? 20 : attributes.count}
              onChange={(val) => setAttributes({ count: val })}
              min={1}
              max={200}
            />
            <ToggleControl
              label="Show all institutions"
              checked={showAll}
              onChange={(value) => setAttributes({ count: value ? -1 : 20 })}
            />
          </PanelBody>
        </InspectorControls>

        <p><em>Institution gallery will be shown on the front end.</em></p>
      </Fragment>
    );
  },
  save: () => null,
});


