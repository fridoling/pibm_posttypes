import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { useSelect } from '@wordpress/data'; // <-- missing import

registerBlockType('myplugin/member-gallery', {
  title: 'Member Gallery',
  icon: 'groups',
  category: 'design',
  attributes: {
    count: { type: 'number', default: -1 },
    category: { type: 'string', default: '' },
  },
  edit: ({ attributes, setAttributes }) => {
    const { count, category } = attributes;

    // Get all member categories from WordPress
    const terms = useSelect((select) => {
      const t = select('core').getEntityRecords('taxonomy', 'member_category', { per_page: -1 });
      return t || [];
    }, []);

    const options = [
      { label: 'All categories', value: '' },
      ...terms.map(term => ({ label: term.name, value: term.slug })),
    ];

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title="Settings">
            <RangeControl
              label="Number of Members"
              value={count}
              onChange={(val) => setAttributes({ count: val })}
              min={1}
              max={20}
            />
            <SelectControl
              label="Category"
              value={category}
              options={options}
              onChange={(val) => setAttributes({ category: val })}
            />
          </PanelBody>
        </InspectorControls>

        <p><em>Member gallery will be shown on the front end.</em></p>
      </Fragment>
    );
  },
  save: () => null,
});
