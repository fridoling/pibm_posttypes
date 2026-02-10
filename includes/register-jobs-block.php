<?php
add_action('init', function () {

    // Register editor script
    wp_register_script(
        'pibm_jobs-editor',
        plugins_url('build/jobs.js', dirname(__FILE__)),
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/jobs.js'),
        true
    );

    // Register block
    register_block_type('pibm/jobs', [
        'editor_script'   => 'pibm_jobs-editor',
        'render_callback' => 'pibm_render_jobs',
    ]);

});

function pibm_render_jobs($attributes) {

    $today = date('Y-m-d');

    $jobs = get_posts([
        'post_type'      => 'job',
        'post_status'    => 'publish',
        'posts_per_page' => $attributes['count'] ?? 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'     => 'job_deadline',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ],
        ],
    ]);

    if (!$jobs) {
        return '<p>No open positions at the moment.</p>';
    }

    ob_start();
    ?>

    <div class="pibm-jobs-list">

        <?php foreach ($jobs as $job) :

            $deadline = get_post_meta($job->ID, 'job_deadline', true);
            $start    = get_post_meta($job->ID, 'job_start_date', true);

            $excerpt = wp_trim_words(
                strip_tags($job->post_content),
                30
            );
        ?>

            <article class="job-card">

                <h3 class="job-title">
                    <a href="<?php echo esc_url(get_permalink($job->ID)); ?>">
                        <?php echo esc_html($job->post_title); ?>
                    </a>
                </h3>

                <div class="job-meta">
                    <?php if ($start) : ?>
                        <span><strong>Start:</strong> <?php echo esc_html($start); ?></span>
                    <?php endif; ?>

                    <?php if ($deadline) : ?>
                        <span><strong>Deadline:</strong> <?php echo esc_html($deadline); ?></span>
                    <?php endif; ?>

                    <span><strong>Posted:</strong>
                        <?php echo esc_html(get_the_date('', $job)); ?>
                    </span>
                </div>

                <p class="job-excerpt">
                    <?php echo esc_html($excerpt); ?>
                </p>

                <a class="job-read-more"
                   href="<?php echo esc_url(get_permalink($job->ID)); ?>">
                    Read more →
                </a>

            </article>

        <?php endforeach; ?>

    </div>

    <?php
    return ob_get_clean();
}
