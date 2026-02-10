/**
 * News Block (dynamic)
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('pibm/news-list', {
    apiVersion: 2,
    title: 'News List',
    icon: 'megaphone',
    category: 'widgets',

    edit() {
        const blockProps = useBlockProps({
            className: 'pibm-news-editor-preview',
            style: {
                padding: '1rem',
                border: '1px dashed #ccc',
                background: '#fafafa',
                fontStyle: 'italic',
            },
        });

        return (
            <div {...blockProps}>
                Latest News Preview (Rendered on frontend)
            </div>
        );
    },

    save() {
        return null; // Dynamic block → rendered via PHP
    }
});
