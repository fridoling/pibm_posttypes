<?php

add_action('init', function() {
    // Register the editor script (use the built or plain JS file)
    wp_register_script(
        'myplugin-institution-gallery-editor',
        plugins_url('build/institution-gallery.js', dirname(__FILE__)), // ✅ your compiled JS
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/institution-gallery.js'),
        true
    );

    // Register the dynamic block
    register_block_type('myplugin/institution-gallery', [
        'editor_script' => 'myplugin-institution-gallery-editor',
        'render_callback' => 'myplugin_render_institution_gallery',
        'attributes' => [
            'count' => ['type' => 'number', 'default' => 6],
        ],
    ]);
});

function myplugin_render_institution_gallery($attributes) {

    $default_image_url = plugins_url('../images/default-institution.png', __FILE__); // place your default image here

    $query = new WP_Query([
        'post_type'      => 'institution',
        'posts_per_page' => $attributes['count'] ?? -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    if (!$query->have_posts()) {
        return '<p>No institutions found.</p>';
    }

    ob_start();
    ?>
    <div class="institution-gallery">
        <?php while ($query->have_posts()) : $query->the_post(); $institution_url = get_post_meta(get_the_ID(), '_institution_url', true); ?>
            <div class="institution-card">
                <h3 class="institution-name">
                    <?php echo esc_html(get_the_title()); ?>
                </h3>
                <div class="institution-description">
                    <?php echo wp_kses_post(get_post_meta(get_the_ID(), '_short_description', true)); ?>
                </div>
                <a href="<?php echo esc_url($institution_url); ?>" target="_blank" rel="noopener noreferrer">
                <div class="institution-photo">
                    <?php
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('medium');
                    } else {
                        echo '<img src="' . esc_url($default_image_url) . '" alt="Default institution photo">';
                    }                    
                    ?>
                </div>                
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}