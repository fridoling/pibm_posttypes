<?php
add_action('init', function () {

    // Register editor script
    wp_register_script(
        'pibm-open-jobs-list-editor',
        plugins_url('build/open-jobs-list.js', dirname(__FILE__)),
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/open-jobs-list.js'),
        true
    );

    // Register block
    register_block_type('pibm/open-jobs-list', [
        'editor_script'   => 'pibm-open-jobs-list-editor',
        'render_callback' => 'pibm_render_open_jobs_list',
    ]);

});

function pibm_render_open_jobs_list($attr) {
    $today = date('Y-m-d');
    $jobs = get_posts([
        'post_type'      => 'job',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'job_deadline',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ],
        ],
        'orderby'        => 'date',
        'meta_key'       => 'job_deadline',
        'order'          => 'ASC'
    ]);

    if (!$jobs) {
        return '<div class="pibm-open-jobs-list"><p>No open positions at the moment.</p></div>';
    }

    ob_start();
    ?>

    <div class="pibm-widget-list">
        <ul>
            <?php foreach ($jobs as $job) :
                $location = get_post_meta($job->ID, 'job_location', true);
            ?>
                <li>
                    <a class="widget-title" href="<?php echo esc_url(get_permalink($job->ID)); ?>">
                        <?php echo esc_html($job->post_title); ?>
                    </a>

                    <?php if ($location) : ?>
                        <div class="widget-text">
                            <?php echo esc_html($location); ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>

        </ul>
    </div>

    <?php
    return ob_get_clean();
}
