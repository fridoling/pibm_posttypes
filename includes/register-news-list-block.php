<?php
add_action('init', function () {

    // Register editor script
    wp_register_script(
        'pibm-news-editor',
        plugins_url('build/news-list.js', dirname(__FILE__)),
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
        filemtime(plugin_dir_path(__DIR__) . 'build/news-list.js'),
        true
    );

    // Register block
    register_block_type('pibm/news-list', [
        'editor_script'   => 'pibm-news-editor',
        'style'           => 'pibm-news-style',
        'render_callback' => 'pibm_render_news_block',
        'attributes'      => [
            'count' => [
                'type'    => 'number',
                'default' => 5
            ]
        ]
    ]);
});

function pibm_render_news_block($attributes) {

    $count = $attributes['count'] ?? 5;

    $items = get_posts([
        'post_type'      => ['post', 'newsletter'],
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'ASC'
    ]);

    ob_start();

    if (!$items) {
        return '<div class="pibm-open-jobs-list"><p>No open positions at the moment.</p></div>';
    }
    ?>

    <div class="pibm-widget-list">
        <ul>
        <?php foreach ($items as $item): 
            $type = get_post_type($item);
            $title = esc_html(get_the_title($item));
            $url = get_permalink($item);
            ?>
            <li>
            <?php
            if ($type === 'post') {
                $words = wp_trim_words(strip_tags($item->post_content), 10);
                $extra = '<div class="widget-info">'.esc_html($words).'</div>';
                ?>
                <a class="widget-title" href="<?php echo esc_url($url); ?>">
                    <?php echo $title; ?>
                </a>
                <div class="widget-info">
                <?php echo $extra; ?>
                </div>
            <?php
            }
            if ($type === 'newsletter') {
                ?>
                <a class="widget-title" href="<?php echo esc_url($url); ?>">
                <?php echo $title . ' is out!'; ?>
                </a>
                <div class="widget-info">
                    Check out what's new in the PhilInBioMed world.
                </div>
            <?php
            }
            ?>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
