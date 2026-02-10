import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('pibm/jobs', {
    title: 'Jobs',
    icon: 'id',
    category: 'widgets',
    edit() {
        return (
            <div {...useBlockProps()} style={{ padding: '0.5rem', fontStyle: 'italic' }}>
                Jobs Block Preview
            </div>
        );
    },
    save() {
        return null; // dynamic block
    },
});
