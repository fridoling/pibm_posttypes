<?php
add_action('init', function() {

    register_block_type('myplugin/single-member', [
        'render_callback' => 'render_single_member_block',
        'attributes' => [
            'memberId' => [
                'type' => 'number',
            ],
        ],
    ]);

});


add_action('init', function() {

    // Register the editor script (use the built or plain JS file)
    wp_register_script(
        'myplugin-member-editor',
        plugins_url('build/single-member.js', dirname(__FILE__)), // ✅ your compiled JS
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/single-member.js'),
        true
    );

    // Register the dynamic block
    register_block_type('myplugin/member', [
        'editor_script' => 'myplugin-member-editor',
        'render_callback' => 'myplugin_render_member',
    ]);
});

function render_single_member_block($attributes) {
    
    $member_id = !empty($attributes['memberId']) ? intval($attributes['memberId']) : get_the_ID();
    $default_image_url = plugins_url('../images/default-member.png', __FILE__);

    if (!$member_id) {
        return '<p>No member selected.</p>';
    }

    // Get member meta
    $first = get_post_meta($member_id, '_first_name', true);
    $last  = get_post_meta($member_id, '_last_name', true);
    $short = get_post_meta($member_id, '_short_description', true);

    // Featured image or fallback
    if (has_post_thumbnail($member_id)) {
        $photo = get_the_post_thumbnail($member_id, 'medium', ['class' => 'member-photo-img']);
    } else {
        $photo = '<img src="' . esc_url($default_image_url) . '" class="member-photo-img" alt="Default member photo">';
    }
    
    $output  = '<div class="single-member">';
    $output .= '<h2 class="single-member-name">' . esc_html($first . ' ' . $last) . '</h2>';    
    $output .= '<div class="single-member-photo">' . $photo . '</div>';
    $output .= '<p class="single-member-short-description">' . wp_kses_post($short) . '</p>';
    $output .= '</div>';

    return $output;
}