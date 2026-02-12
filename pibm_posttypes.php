<?php
/**
 * @package pibm_posttypes
 */
/*
Plugin Name: Post Types for PhilInBiomed
Description: Custom Post Types for PhilInBiomed website.
Version: 1.0
Requires at least: 5.8
Requires PHP: 7.2
Author: Fridolin Gross
Author URI: https://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: akismet
*/


require_once __DIR__ . '/includes/register-member-gallery-block.php';
require_once __DIR__ . '/includes/register-single-member-block.php';
require_once __DIR__ . '/includes/register-institution-gallery-block.php';
require_once __DIR__ . '/includes/register-single-job-block.php';
require_once __DIR__ . '/includes/register-open-jobs-list-block.php';
require_once __DIR__ . '/includes/register-news-list-block.php';
require_once __DIR__ . '/includes/register-jobs-block.php';

/**
 * Register 'member' Custom Post Type
 */
function pibm_register_member_cpt() {
    register_post_type('member', [
        'labels' => [
            'name' => __('Members'),
            'singular_name' => __('Member'),
            'add_new_item' => __('Add New Member'),
            'edit_item' => __('Edit Member'),
			'add_new'               => __( 'Add New', 'textdomain' ),
			'add_new_item'          => __( 'Add New Member', 'textdomain' ),
			'edit_item'			  => __( 'Edit Member', 'textdomain' ),
			'featured_image'        => __( 'Member Photo', 'textdomain' ),			
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-businesswoman',
        'supports' => ['thumbnail'],
        'show_in_rest' => false
    ]);
}
add_action('init', 'pibm_register_member_cpt');

function pibm_register_member_taxonomy() {
    register_taxonomy(
        'member_category',
        ['member'], // your CPT slug
        [
            'label' => __('Member Categories', 'pibm'),
            'rewrite' => ['slug' => 'member-category'],
            'hierarchical' => true,   // behaves like categories
            'show_in_rest' => true,   // important for block editor
            'show_ui' => true,
            'show_admin_column' => true,
        ]
    );
}
add_action('init', 'pibm_register_member_taxonomy');

/**
 * Add custom metabox
 */
function pibm_add_member_metaboxes() {
    add_meta_box(
        'member_details',
        __('Member Details', 'pibm'),
        'pibm_render_member_metabox',
        'member',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'pibm_add_member_metaboxes');

/**
 * Render the metabox form
 */
function pibm_render_member_metabox($post) {
    // Add a nonce for verification
    wp_nonce_field('pibm_member_meta_nonce_action', 'pibm_member_meta_nonce');

    // Get existing values
    $first_name = get_post_meta($post->ID, '_first_name', true);
    $last_name = get_post_meta($post->ID, '_last_name', true);
    $short_description = get_post_meta($post->ID, '_short_description', true);
    ?>
    <p>
        <label for="first_name"><?php _e('First Name:', 'pibm'); ?></label><br>
        <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($first_name); ?>" class="widefat" />
    </p>
    <p>
        <label for="last_name"><?php _e('Last Name:', 'pibm'); ?></label><br>
        <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($last_name); ?>" class="widefat" />
    </p>
    <?php
    wp_editor(
        $short_description,          // existing value
        'short_description_editor',  // editor ID
        [
            'textarea_name' => 'short_description',
            'media_buttons' => false,
            'textarea_rows' => 6,
            'teeny' => false,
            'quicktags' => true
        ]
    );
}

/**
 * Save the metabox data
 */
function pibm_save_member_meta($post_id) {
    // ✅ Security checks
    if (!isset($_POST['pibm_member_meta_nonce']) ||
        !wp_verify_nonce($_POST['pibm_member_meta_nonce'], 'pibm_member_meta_nonce_action')) {
        return;
    }

    // Prevent autosave overwrite
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Check user permissions
    if (isset($_POST['post_type']) && 'member' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) return;
    } else {
        return;
    }

    // ✅ Sanitize and save fields
    $fields = [
        '_first_name' => isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
        '_last_name' => isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
        '_short_description' => isset($_POST['short_description']) ? wp_kses_post($_POST['short_description']) : '',
    ];

    foreach ($fields as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
}
add_action('save_post_member', 'pibm_save_member_meta');

add_action('save_post_member', function($post_id, $post, $update) {

    // Prevent recursion or autosave interference
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    // Get first and last name (adjust keys as needed)
    $first = get_post_meta($post_id, '_first_name', true);
    $last  = get_post_meta($post_id, '_last_name', true);

    // Build the new title
    $new_title = trim("$first $last");

    // Only update if it’s different
    if (!empty($new_title) && $new_title !== $post->post_title) {
        // Remove this hook temporarily to avoid recursion
        remove_action('save_post_member', __FUNCTION__, 10);

        wp_update_post([
            'ID'         => $post_id,
            'post_title' => $new_title,
            'post_name'  => sanitize_title($new_title), // update slug too
        ]);

        // Reattach the hook
        add_action('save_post_member', __FUNCTION__, 10, 3);
    }
}, 10, 3);

/**
 * Register 'institution' Custom Post Type
 */
function pibm_register_institution_cpt() {
    register_post_type('institution', [
        'labels' => [
            'name' => __('Institutions'),
            'singular_name' => __('Institution'),
            'add_new_item' => __('Add New Institution'),
            'edit_item' => __('Edit Institution'),
			'add_new'               => __( 'Add New', 'textdomain' ),
			'add_new_item'          => __( 'Add New Institution', 'textdomain' ),
			'edit_item'			  => __( 'Edit Institution', 'textdomain' ),
			'featured_image'        => __( 'Institution Photo', 'textdomain' ),			
        ],
        'public' => true,
        'publicly_queryable' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => ['thumbnail', 'title', 'e'],
        'show_in_rest' => false
    ]);
}
add_action('init', 'pibm_register_institution_cpt');

add_filter( 'enter_title_here', 'custom_enter_title' );
function custom_enter_title( $input ) {
    if ( 'institution' === get_post_type() ) {
        return __( 'Enter institution name here', 'your_textdomain' );
    }

    return $input;
}

/**
 * Add custom metabox
 */
function pibm_add_institution_metaboxes() {
    add_meta_box(
        'institution details',
        __('Institution Details', 'pibm'),
        'pibm_render_institution_metabox',
        'institution',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'pibm_add_institution_metaboxes');

/**
 * Render the metabox form
 */
function pibm_render_institution_metabox($post) {
    // Add a nonce for verification
    wp_nonce_field('pibm_institution_meta_nonce_action', 'pibm_institution_meta_nonce');

    // Get existing values
    $institution_url   = get_post_meta($post->ID, '_institution_url', true);
    $short_description = get_post_meta($post->ID, '_short_description', true);

    wp_editor(
        $short_description,          // existing value
        'short_description_editor',  // editor ID
        [
            'textarea_name' => 'short_description',
            'media_buttons' => false,
            'textarea_rows' => 6,
            'teeny' => false,
            'quicktags' => true
        ]
    );

    // --- URL FIELD AFTER THE EDITOR ---
    ?>
    <p style="margin-top: 20px;">
        <label for="institution_url"><?php _e('Web address:', 'pibm'); ?></label><br>
        <input type="text"
               name="institution_url"
               id="institution_url"
               class="widefat"
               value="<?php echo esc_attr($institution_url); ?>" />
    </p>
    <?php
}



/**
 * Save the metabox data
 */
function pibm_save_institution_meta($post_id) {
    // ✅ Security checks
    if (!isset($_POST['pibm_institution_meta_nonce']) ||
        !wp_verify_nonce($_POST['pibm_institution_meta_nonce'], 'pibm_institution_meta_nonce_action')) {
        return;
    }

    // Prevent autosave overwrite
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Check user permissions
    if (isset($_POST['post_type']) && 'institution' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) return;
    } else {
        return;
    }

    // ✅ Sanitize and save fields
    $fields = [
        '_institution_url' => isset($_POST['institution_url']) ? sanitize_text_field($_POST['institution_url']) : '',
        '_short_description' => isset($_POST['short_description']) ?  wp_kses_post($_POST['short_description']) : '',
    ];

    foreach ($fields as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
}
add_action('save_post_institution', 'pibm_save_institution_meta');


/**
 * Register 'newsletter' Custom Post Type
 */
function pibm_register_newsletter_cpt() {
    register_post_type('newsletter', [
        'label' => __('Newsletters', 'pibm'),
        'labels' => [
            'name' => __('Newsletters'),
            'singular_name' => __('Newsletter'),
            'add_new_item' => __('Add Newsletter'),
            'edit_item' => __('Edit Newsletter'),
			'add_new'               => __( 'Add', 'textdomain' ),
			'add_new_item'          => __( 'Add Newsletter', 'textdomain' ),
			'edit_item'			  => __( 'Edit Newsletter', 'textdomain' ),
			'featured_image'        => __( 'Newsletter Photo', 'textdomain' ),			
        ],        
        'public' => true,
        'has_archive' => "newsletter",
        'query_var'    => true,
        'rewrite' => ['slug' => 'newsletter'],
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'supports' => ['title'], // editor for HTML
        'menu_icon' => 'dashicons-media-document',
    ]);
}
add_action('init', 'pibm_register_newsletter_cpt', 50);

// add_action('admin_head', function() {
//     global $post;
//     if ($post && $post->post_type === 'newsletter') {
//         echo '<style>#titlediv { display:none; }</style>';
//     }
// });

add_action('init', function() {
    $obj = get_post_type_object('newsletter');
    error_log(print_r($obj, true));
}, 30);

// 2️⃣ Allow RAW HTML for newsletters only
add_filter('the_content', function ($content) {

    if (is_singular('newsletter')) {

        // Completely unfiltered raw HTML output
        remove_all_filters('the_content');

        return $content;
    }

    return $content;
}, 1);


// Add the metaboxes
add_action('add_meta_boxes', function () {
    add_meta_box(
        'newsletter_files',
        'Newsletter File',
        'newsletter_files_metabox_html',
        'newsletter',
        'normal',
        'default'
    );
});

// Render metabox
function newsletter_files_metabox_html($post) {
    $pdf  = get_post_meta($post->ID, 'newsletter_pdf', true);
    $html = get_post_meta($post->ID, 'newsletter_html', true);

    wp_nonce_field('newsletter_file_nonce', 'newsletter_file_nonce_field');
    ?>

    <p><strong>Upload either HTML or PDF</strong></p>

    <p><label>HTML file URL:</label><br>
    <input type="text" name="newsletter_html" style="width:100%" value="<?php echo esc_attr($html); ?>" />
    <button class="button upload-html">Select HTML File</button></p>

    <p><label>PDF file URL:</label><br>
    <input type="text" name="newsletter_pdf" style="width:100%" value="<?php echo esc_attr($pdf); ?>" />
    <button class="button upload-pdf">Select PDF File</button></p>

    <script>
    jQuery(function($){
        $('.upload-html').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({
                title: 'Select HTML File',
                multiple: false,
                library: { type: 'text/html' }
            });
            frame.on('select', function(){
                var file = frame.state().get('selection').first().toJSON();
                $('input[name="newsletter_html"]').val(file.url);
            });
            frame.open();
        });

        $('.upload-pdf').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({
                title: 'Select PDF File',
                multiple: false,
                library: { type: 'application/pdf' }
            });
            frame.on('select', function(){
                var file = frame.state().get('selection').first().toJSON();
                $('input[name="newsletter_pdf"]').val(file.url);
            });
            frame.open();
        });
    });
    </script>

    <?php
}

// add_action('add_meta_boxes', function() {
//     add_meta_box(
//         'newsletter_date_meta',
//         'Newsletter Date',
//         'newsletter_date_meta_callback',
//         'newsletter',
//         'side',
//         'high'
//     );
// });

function newsletter_date_meta_callback($post) {
    $year  = get_post_meta($post->ID, 'newsletter_year', true) ?: date('Y');
    $month = get_post_meta($post->ID, 'newsletter_month', true) ?: date('n');

    ?>
    <label>Year:</label>
    <input type="number" name="newsletter_year" value="<?php echo esc_attr($year); ?>" min="2000" max="2100" />

    <br><br>

    <label>Month:</label>
    <select name="newsletter_month">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?php echo $m; ?>" <?php selected($m, $month); ?>>
                <?php echo date('F', mktime(0,0,0,$m,1)); ?>
            </option>
        <?php endfor; ?>
    </select>
    <?php
}

add_action('init', function() {

    register_post_meta('newsletter', 'newsletter_html', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() { return current_user_can('edit_posts'); }
    ]);

    register_post_meta('newsletter', 'newsletter_pdf', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() { return current_user_can('edit_posts'); }
    ]);
});


// Save metadata
add_action('save_post_newsletter', function ($post_id) {

    if (!isset($_POST['newsletter_file_nonce_field']) ||
        !wp_verify_nonce($_POST['newsletter_file_nonce_field'], 'newsletter_file_nonce')) {
        return;
    }

    update_post_meta($post_id, 'newsletter_pdf',  sanitize_text_field($_POST['newsletter_pdf'] ?? ''));
    update_post_meta($post_id, 'newsletter_html', sanitize_text_field($_POST['newsletter_html'] ?? ''));
});

add_filter('the_content', 'newsletter_render_content', 1);

function newsletter_render_content($content) {
    if (!is_singular('newsletter')) {
        return $content;
    }

    $html = get_post_meta(get_the_ID(), 'newsletter_html', true);
    $pdf  = get_post_meta(get_the_ID(), 'newsletter_pdf', true);

    // If no content, override completely
    if (empty($content)) {
        if ($html) {
            return newsletter_display_html($html);
        }
        if ($pdf) {
            return newsletter_display_pdf($pdf);
        }
        return '<p>No newsletter uploaded.</p>';
    }

    // Otherwise append after content
    if ($html) {
        $content .= newsletter_display_html($html);
    } elseif ($pdf) {
        $content .= newsletter_display_pdf($pdf);
    }

    return $content;
}

function newsletter_display_html($file_url) {
    // Convert file URL to local path
    $file_path = str_replace(site_url('/'), ABSPATH, $file_url);

    if (!file_exists($file_path)) {
        return '<p>Newsletter HTML file not found.</p>';
    }

    $html = file_get_contents($file_path);
    if (!$html) {
        return '<p>Unable to read newsletter HTML.</p>';
    }

    // --- Safe HTML parsing ---
    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    // Load with relaxed error handling for messy newsletter HTML
    $doc->loadHTML(
        mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    $xpath = new DOMXPath($doc);

    // Find all <p> elements that contain "unsubscribe"
    $nodes = $xpath->query('//p[contains(translate(., "UNSUBSCRIBE", "unsubscribe"), "unsubscribe")]');

    foreach ($nodes as $node) {
        $node->parentNode->removeChild($node);
    }

    libxml_clear_errors();

    // Get processed HTML back
    $clean_html = $doc->saveHTML();


        return '<div class="newsletter-container">
                <iframe src="' . esc_url($file_url) . '" 
                        style="width: 100%; height: 600px; border: none;"></iframe>
            </div>';
    // return '<div class="newsletter-html">' . $clean_html . '</div>';
}

function newsletter_display_pdf($file_url) {
    return '
        <div class="newsletter-container">
            <iframe 
                src="' . esc_url($file_url) . '" 
            ></iframe>
        </div>';
}

add_action('admin_enqueue_scripts', function ($hook) {

    // Only load on newsletter edit screen
    global $post;
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if (isset($post->post_type) && $post->post_type === 'newsletter') {
            wp_enqueue_media();
        }
    }

    // Always ensure jQuery is available
    wp_enqueue_script('jquery');
});

add_shortcode('newsletter_archive', function() {

    $query = new WP_Query([
        'post_type'      => 'newsletter',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);


    ob_start();

    if (!$query->have_posts()) {
        return '<p>No newsletters found.</p>';
    }

    $current_year = null;

    echo '<div class="newsletter-archive">';

    while ($query->have_posts()) {
        $query->the_post();

        $month = get_the_date('F');   // e.g. March
        $year  = get_the_date('Y');   // e.g. 2026

        if (!$year) { continue; }

        // Start a new year section
        if ($year !== $current_year) {
            if ($current_year !== null) {
                echo '</ul>'; // close previous year list
            }
            echo "<h2 class='newsletter-archive'>{$year}</h2>";
            echo '<ul class="newsletter-archive">';
            $current_year = $year;
        }

        echo '<li class="newsletter-item"><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
    }

    echo '</ul></div>';

    wp_reset_postdata();

    return ob_get_clean();
});


add_action('admin_menu', function () {
    remove_meta_box(
        'pageparentdiv',    // The Post Attributes meta box
        'newsletter',       // Your CPT slug
        'normal'            // Context
    );
});

add_action('init', function() {

    register_post_type('job', [
        'label' => __('Jobs'),
        'labels' => [
            'name' => __('Jobs'),
            'singular_name' => __('Job'),
            'add_new_item' => __('Add Job'),
            'edit_item' => __('Edit Job'),
        ],
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'rewrite' => ['slug' => 'jobs'],
        'show_in_rest' => false,   // ❌ disable block editor
        'supports' => ['title', 'editor', 'thumbnail'], // classic editor
        'menu_icon' => 'dashicons-id-alt',
    ]);

});

add_action('init', function() {

    $fields = [
        'job_location',
        'job_start_date',
        'job_deadline',
    ];

    foreach ($fields as $key) {
        register_post_meta('job', $key, [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,   // classic editor
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            },
        ]);
    }
});

add_action('add_meta_boxes', function() {
    add_meta_box(
        'job_details_box',
        'Job Details',
        'render_job_details_metabox',
        'job',
        'normal',
        'default'
    );
});

function render_job_details_metabox($post) {

    $location = get_post_meta($post->ID, 'job_location', true);
    $start    = get_post_meta($post->ID, 'job_start_date', true);
    $deadline = get_post_meta($post->ID, 'job_deadline', true);

    wp_nonce_field('save_job_details', 'job_details_nonce');

    ?>
    <table class="form-table">

        <tr>
            <th><label>Location</label></th>
            <td><input type="text" name="job_location" value="<?php echo esc_attr($location); ?>" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Start Date</label></th>
            <td><input type="date" name="job_start_date" value="<?php echo esc_attr($start); ?>"></td>
        </tr>

        <tr>
            <th><label>Deadline</label></th>
            <td><input type="date" name="job_deadline" value="<?php echo esc_attr($deadline); ?>"></td>
        </tr>

    </table>
    <?php
}

add_action('save_post_job', function($post_id) {

    if (!isset($_POST['job_details_nonce']) ||
        !wp_verify_nonce($_POST['job_details_nonce'], 'save_job_details')) {
        return;
    }

    $fields = [
        'job_location',
        'job_start_date',
        'job_deadline',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

});

function pibm_newsletter_shortcode() {

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pibm_newsletter_email'])) {

        $email = sanitize_email($_POST['pibm_newsletter_email']);

        if (!empty($email) && is_email($email)) {

            // Send email to colleague
            wp_mail(
                'jrsholl.edu@gmail.com',               // CHANGE THIS
                'New newsletter subscription',
                "A new user has subscribed:\n\nEmail: $email"
            );

            // redirect to avoid form resubmission
            wp_redirect(add_query_arg('subscribed', '1'));
            exit;
        }
    }

    // Start output buffer
    ob_start();
    ?>
        <div class="newsletter-box">
            <div class="newsletter-heading">Subscribe to our newsletter</div>

            <?php if (isset($_GET['subscribed']) && $_GET['subscribed'] === '1') : ?>

                <div class="newsletter-success">
                    Thank you for subscribing!
                </div>

            <?php else : ?>

                <form method="post" class="newsletter-form">
                    <label class="newsletter-label">
                        Email
                        <input type="email" name="pibm_newsletter_email" required>
                    </label>

                    <button type="submit" class="newsletter-button">
                        Subscribe
                    </button>
                </form>

            <?php endif; ?>
        </div>

    <?php
    return ob_get_clean();
}
add_shortcode('newsletter_signup', 'pibm_newsletter_shortcode');




add_action('enqueue_block_editor_assets', function() {
    wp_add_inline_script(
        'wp-block-editor',
        "
        wp.data.dispatch('core/edit-post').removeEditorPanel('page-attributes');
        "
    );
});


add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('theme-styles', get_stylesheet_uri());
});

// function pibm_import_members_from_csv() {

//     // Adjust paths
//     $csv_file   = plugin_dir_path(__FILE__) . 'members.csv';
//     $image_dir  = WP_CONTENT_DIR . '/uploads/member-import/';

//     if (!file_exists($csv_file)) {
//         error_log('Members CSV not found');
//         return;
//     }

//     if (($handle = fopen($csv_file, 'r')) === false) {
//         error_log('Could not open CSV');
//         return;
//     }

//     // Read header
//     $header = fgetcsv($handle, 0, ',');

//     while (($row = fgetcsv($handle, 0, ',')) !== false) {

//         $data = array_combine($header, $row);

//         $first = trim($data['first_name']);
//         $last  = trim($data['last_name']);
//         $desc  = trim($data['description']);
//         $image = trim($data['image_file']);

//         if (!$first || !$last) {
//             continue;
//         }

//         $title = "$first $last";

//         // Avoid duplicates
//         if (get_page_by_title($title, OBJECT, 'member')) {
//             continue;
//         }

//         // Create post
//         $post_id = wp_insert_post([
//             'post_type'   => 'member',
//             'post_title'  => $title,
//             'post_status' => 'publish',
//         ]);

//         if (is_wp_error($post_id)) {
//             continue;
//         }

//         // Save meta
//         update_post_meta($post_id, '_first_name', $first);
//         update_post_meta($post_id, '_last_name', $last);
//         update_post_meta($post_id, '_short_description', $desc);

//         // Handle image
//         $image_path = $image_dir . $image;
//         if (file_exists($image_path)) {
//             pibm_attach_image_to_post($image_path, $post_id);
//         }
//     }

//     fclose($handle);
// }

// function pibm_attach_image_to_post($image_path, $post_id) {

//     require_once ABSPATH . 'wp-admin/includes/file.php';
//     require_once ABSPATH . 'wp-admin/includes/media.php';
//     require_once ABSPATH . 'wp-admin/includes/image.php';

//     $upload = wp_upload_bits(
//         basename($image_path),
//         null,
//         file_get_contents($image_path)
//     );

//     if ($upload['error']) {
//         return;
//     }

//     $filetype = wp_check_filetype($upload['file'], null);

//     $attachment_id = wp_insert_attachment([
//         'post_mime_type' => $filetype['type'],
//         'post_title'     => sanitize_file_name(basename($upload['file'])),
//         'post_status'    => 'inherit',
//     ], $upload['file'], $post_id);

//     if (is_wp_error($attachment_id)) {
//         return;
//     }

//     $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
//     wp_update_attachment_metadata($attachment_id, $attach_data);

//     set_post_thumbnail($post_id, $attachment_id);
// }

// add_action('admin_init', function () {
//     if (current_user_can('manage_options') && isset($_GET['import_members'])) {
//         pibm_import_members_from_csv();
//         exit('Member import finished');
//     }
// });

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css'
    );

    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'landing-post-slider',
        plugins_url('assets/js/post-slider.js', __FILE__),
        ['swiper-js'],
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/post-slider.js'),
        true // IMPORTANT: footer
    );
});

add_shortcode('landing_post_slider', function ($atts) {

    $colors = ['bg-1', 'bg-2', 'bg-3'];
    $color_index = 0;

    $query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 6
    ]);

    if (!$query->have_posts()) return '';

    ob_start();
    ?>

    <div class="swiper landing-post-slider">
        <div class="swiper-wrapper">
            <?php while ($query->have_posts()): $query->the_post(); 
                $color_class = $colors[$color_index % count($colors)];
                $color_index++;?>
                <div class="swiper-slide <?php echo esc_attr($color_class); ?>">
                    <div class="slider-text">                        
                        <h2 class="slider-title"><?php the_title(); ?></h2>
                        <div class="slider-author">By <?php the_author(); ?></div>
                        <div class="slider-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></div>
                        <a href="<?php the_permalink(); ?>" class="slider-readmore">Read more</a>
                    </div>
                    <?php if (has_post_thumbnail()): ?>
                        <div class="slider-image">
                            <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endwhile; ?>
        </div>
        <!-- Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
});

// Email shortcode: [email address="foo@bar.com"]Name[/email]
function myplugin_email_shortcode($atts, $content = null) {
    $atts = shortcode_atts([
        'address' => '',
    ], $atts, 'email');

    if (!$atts['address']) return '';

    $parts = explode('@', $atts['address']);
    if(count($parts) !== 2) return esc_html($content ?: $atts['address']);

    $user = esc_attr($parts[0]);
    $domain = esc_attr($parts[1]);

    $name = $content ?: $atts['address'];

    // Output span with obfuscation data
    return '<span class="obfuscated-email" data-user="' . $user . '" data-domain="' . $domain . '">' . esc_html($name) . '</span>';
}
add_shortcode('email', 'myplugin_email_shortcode');




add_action('wp_enqueue_scripts', function () {
    wp_add_inline_script('jquery-core', "
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.obfuscated-email').forEach(function(el) {
                const user = el.dataset.user;
                const domain = el.dataset.domain;
                const email = user + '@' + domain;

                const a = document.createElement('a');
                a.href = 'mailto:' + email;
                a.textContent = el.textContent || email;

                el.replaceWith(a);
            });
        });
    ");
});
