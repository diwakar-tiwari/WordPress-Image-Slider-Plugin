<?php

/**
 * Plugin Name: Image Sliders
 * Description: Image Slider WordPress Plugin
 * Version: 1.0.0
 * Author: Diwakar Tiwari
 */

if (!defined('ABSPATH')) exit; // Die if Access Directly

//enqueue scripts for admin dashboard
function image_admin_scripts()
{
    wp_enqueue_media(); // Add this line to enqueue media scripts and styles
    wp_enqueue_style('image-slider', plugins_url('assets/admin.css', __FILE__));
    wp_enqueue_script('image-slider', plugins_url('assets/admin.js', __FILE__), array('jquery', 'media-upload'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'image_admin_scripts');

//enqueue scripts for frontend
function enqueue_frontend_scripts()
{
    wp_enqueue_style('slick-slider', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
    wp_enqueue_style('slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css');
    wp_enqueue_style('image-slider', plugins_url('assets/frontend.css', __FILE__));
    wp_enqueue_script('image-slider', plugins_url('assets/frontend.js', __FILE__), array('jquery', 'media-upload'), '1.0', true);
    wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js', array('jquery'), '1.6.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_frontend_scripts');

//Register the shortcode
function image_slider_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);

    // Get the slider ID
    $id = $atts['id'];

    // Get the slider images
    $slider_images = get_post_meta($id, 'slider_images', true);

    // Output the slider HTML
    $slider_html = '<div class="image-slider">';
    foreach ($slider_images as $image_id) {
        $image_url = wp_get_attachment_image_src($image_id, 'full')[0];
        $slider_html .= '<img src="' . $image_url . '" />';
    }
    $slider_html .= '</div>';

    return $slider_html;
}
add_shortcode('image_slider', 'image_slider_shortcode');

// Add the shortcode column to the slider table
function image_slider_shortcode_column($columns)
{
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_image_slider_posts_columns', 'image_slider_shortcode_column');

// Display the shortcode in the slider table
function image_slider_shortcode_column_content($column_name, $post_id)
{
    if ($column_name == 'shortcode') {
        echo '[image_slider id="' . $post_id . '"]';
    }
}
add_action('manage_image_slider_posts_custom_column', 'image_slider_shortcode_column_content', 10, 2);

// Create the slider post type
function image_slider_post_type()
{
    $labels = array(
        'name' => 'Image Sliders',
        'singular_name' => 'Image Slider',
        'all_items' => __('All Sliders'),
        'search_items' => __('Search Sliders'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-format-gallery',
        'supports' => array('title', 'author',),
    );

    register_post_type('image_slider', $args);
}
add_action('init', 'image_slider_post_type');

// Reorder the columns in the slider post type table
function image_slider_columns($columns)
{
    $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'title' => 'Title',
        'shortcode' => 'Shortcode',
        'author' => 'Author',
        'date' => 'Date'
    );

    return $new_columns;
}
add_filter('manage_image_slider_posts_columns', 'image_slider_columns', 10, 1);

// Add custom class to "my_custom_post_type" table
function add_custom_class_to_post_table($classes)
{
    global $post;
    if ($post->post_type == 'image_slider') {
        $classes[] = 'slide-custom-class';
    }
    return $classes;
}
add_filter('post_class', 'add_custom_class_to_post_table');

// Add the slider images metabox
function image_slider_images_metabox()
{
    add_meta_box('image_slider_images_metabox', 'Slider Images', 'image_slider_images_metabox_callback', 'image_slider');
}
add_action('add_meta_boxes', 'image_slider_images_metabox');
// callback function for the slider images metabox
function image_slider_images_metabox_callback($post)
{
    $slider_images = get_post_meta($post->ID, 'slider_images', true);
    wp_nonce_field('image_slider_images_metabox', 'image_slider_images_metabox_nonce');
?>
    <a href="#" class="add-image button">Add Image</a><br>
    <hr>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Image</th>
                <th>Dimensions</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody class="slider-images-container">
            <?php if ($slider_images) : ?>
                <?php foreach ($slider_images as $image_id) : ?>
                    <?php
                    $image_src = wp_get_attachment_image_src($image_id, 'thumbnail');
                    $image_metadata = wp_get_attachment_metadata($image_id);
                    $image_dimensions = $image_metadata['width'] . ' x ' . $image_metadata['height'];
                    ?>
                    <tr class="slider-image">
                        <td><img src="<?php echo $image_src[0]; ?>" width="<?php echo $image_src[1]; ?>" height="<?php echo $image_src[2]; ?>" /></td>
                        <td><?php echo $image_dimensions; ?></td>
                        <td><a href="#" class="remove-image">Remove</a><input type="hidden" name="slider_images[]" value="<?php echo $image_id; ?>" /></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php
}
// Save slider images
function image_slider_save_meta_boxes($post_id)
{
    if (!isset($_POST['image_slider_images_metabox_nonce']) || !wp_verify_nonce($_POST['image_slider_images_metabox_nonce'], 'image_slider_images_metabox')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['slider_images'])) {
        update_post_meta($post_id, 'slider_images', $_POST['slider_images']);
    } else {
        delete_post_meta($post_id, 'slider_images');
    }
}
add_action('save_post', 'image_slider_save_meta_boxes');
