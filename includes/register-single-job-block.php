<?php
add_action('init', function () {

    // Register editor script
    wp_register_script(
        'pibm-single-job-editor',
        plugins_url('blocks/single-job.js', dirname(__FILE__)),
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/single-job.js'),
        true
    );

    // Register dynamic block
    register_block_type('pibm/single-job', [
        'editor_script'   => 'pibm-single-job-editor',
        'style'           => 'pibm-single-job-style',
        'render_callback' => 'pibm_render_single_job',
        'attributes' => [
            'postId' => [
                'type' => 'number'
            ],
        ],
    ]);
});

function pibm_render_single_job($attributes) {
    
    $post_id = $attributes['postId'] ?? get_the_ID();

    if (!$post_id || get_post_type($post_id) !== 'job') {
        return '<p><em>No job selected.</em></p>';
    }

    $title = get_the_title($post_id);
    $desc  = get_post_field('post_content', $post_id);
    $location = get_post_meta($post_id, 'job_location', true);
    $start    = get_post_meta($post_id, 'job_start_date', true);
    $deadline = get_post_meta($post_id, 'job_deadline', true);

    ob_start();
    ?>
    <div class="pibm-single-job">

        <h1 class="job-title"><?php echo esc_html($title); ?></h1>
        <div class="job-description">
            <?php echo wp_kses_post($desc); ?>
        </div>

        <ul class="job-meta">
            <?php if ($location) : ?><li><strong>Location:</strong> <span><?php echo esc_html($location); ?></span></li><?php endif; ?>
            <?php if ($start) : ?><li><strong>Start:</strong> <span><?php echo esc_html($start); ?></span></li><?php endif; ?>
            <?php if ($deadline) : ?><li><strong>Deadline:</strong> <span><?php echo esc_html($deadline); ?></span></li><?php endif; ?>
        </ul>

    </div>
    <?php
    return ob_get_clean();
}

