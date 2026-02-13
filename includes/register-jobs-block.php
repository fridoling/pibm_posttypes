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
            $location = get_post_meta($job->ID, 'job_location', true);
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
                <div class="job-post-date">
                    Posted on <?php echo esc_html(get_the_date('F d, Y', $job)); ?>
                </div>
                <div class="job-excerpt">
                    <?php echo esc_html($excerpt); ?>
                </div>
                <ul class="job-meta">
                    <?php if ($location) : ?>
                        <li>
                            <strong>Location:</strong>
                            <span><?php echo esc_html($location); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($deadline) : ?>
                        <li>
                            <strong>Deadline:</strong>
                            <span><?php echo date("F j, Y", strtotime($deadline)); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($start) : ?>
                        <li>
                            <strong>Start:</strong>
                            <span><?php echo date("F j, Y", strtotime($start)); ?></span>
                        </li>
                    <?php endif; ?>            
                </ul>      
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
