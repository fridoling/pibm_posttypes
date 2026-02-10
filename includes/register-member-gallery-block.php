<?php
add_action('init', function() {

    // Register the editor script (use the built or plain JS file)
    wp_register_script(
        'myplugin-member-gallery-editor',
        plugins_url('build/member-gallery.js', dirname(__FILE__)), // ✅ your compiled JS
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/member-gallery.js'),
        true
    );

    // Register the dynamic block
    register_block_type('myplugin/member-gallery', [
        'editor_script' => 'myplugin-member-gallery-editor',
        'render_callback' => 'myplugin_render_member_gallery',
        'attributes' => [
            'count' => ['type' => 'number', 'default' => 6],
        ],
    ]);
});

function myplugin_render_member_gallery($attributes) {
    // Default to all members if not set
    $count = isset($attributes['count']) ? intval($attributes['count']) : -1;
    $default_image_url = plugins_url('../images/default-member.png', __FILE__); // place your default image here

    $args = [
        'post_type' => 'member',
        'posts_per_page' => $attributes['count'],
        'post_status'    => 'publish',
        'meta_key'       => '_last_name',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',    
    ];

    if (!empty($attributes['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'member_category',
                'field'    => 'slug',
                'terms'    => $attributes['category'],
            ]
        ];
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No members found.</p>';
    }

    ob_start();
    ?>
    <div class="member-gallery">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <div class="member-card">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="member-link">
                <h3 class="member-name">
                    <?php 
                        $permalink = get_permalink(get_the_ID());
                        echo
                            esc_html(get_post_meta(get_the_ID(), '_first_name', true)) . ' ' .
                            esc_html(get_post_meta(get_the_ID(), '_last_name', true));
                    ?>
                </h3>
                <div class="member-photo">
                    <?php
                    echo
                        '<a href="' . esc_url($permalink) . '">';
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('medium');
                    } else {
                        echo '<img src="' . esc_url($default_image_url) . '" alt="Default member photo">';
                    }
                    echo '</a>';
                    ?>
                </div>
                <div class="member-description">
                    <?php 
                    $full = get_post_meta(get_the_ID(), '_short_description', true);
                    $excerpt = wp_trim_words(strip_tags($full), 10, '...'); // limit to 10 words
                    if (str_word_count(strip_tags($full)) > 10) {
                        $excerpt .= '<br /> <a class="read-more-link" href="' . get_permalink() . '">Read more</a>';
                    }
                    echo wp_kses_post($excerpt);
                    ?>
                    
                </div> </a>               
            </div>
        <?php endwhile; ?>
    </div>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
