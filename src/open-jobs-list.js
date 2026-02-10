import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('pibm/open-jobs-list', {
    title: 'Open Jobs List',
    icon: 'id',
    category: 'widgets',
    edit() {
        return (
            <div {...useBlockProps()} style={{ padding: '0.5rem', fontStyle: 'italic' }}>
                Open Jobs List Block Preview
            </div>
        );
    },
    save() {
        return null; // dynamic block
    },
});
