<?php
if (!defined('ABSPATH')) {
    exit;
}

class MDL_Post_Type {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomy'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_mdl_document', array($this, 'save_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_meta_box_assets'));
    }

    public function enqueue_meta_box_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'mdl_document') {
            return;
        }
        wp_enqueue_media();
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Documents', 'modern-document-library'),
            'singular_name'      => __('Document', 'modern-document-library'),
            'add_new'            => __('Add New', 'modern-document-library'),
            'add_new_item'       => __('Add New Document', 'modern-document-library'),
            'edit_item'          => __('Edit Document', 'modern-document-library'),
            'new_item'           => __('New Document', 'modern-document-library'),
            'view_item'          => __('View Document', 'modern-document-library'),
            'search_items'       => __('Search Documents', 'modern-document-library'),
            'not_found'          => __('No documents found', 'modern-document-library'),
            'not_found_in_trash' => __('No documents found in Trash', 'modern-document-library'),
            'menu_name'          => __('Document Library', 'modern-document-library'),
        );

        $args = array(
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_icon'    => 'dashicons-media-document',
            'supports'     => array('title'),
            'has_archive'  => false,
            'rewrite'      => false,
        );

        register_post_type('mdl_document', $args);
    }

    public function register_taxonomy() {
        $labels = array(
            'name'              => __('Document Categories', 'modern-document-library'),
            'singular_name'     => __('Category', 'modern-document-library'),
            'search_items'      => __('Search Categories', 'modern-document-library'),
            'all_items'         => __('All Categories', 'modern-document-library'),
            'edit_item'         => __('Edit Category', 'modern-document-library'),
            'update_item'       => __('Update Category', 'modern-document-library'),
            'add_new_item'      => __('Add New Category', 'modern-document-library'),
            'new_item_name'     => __('New Category Name', 'modern-document-library'),
            'menu_name'         => __('Categories', 'modern-document-library'),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'             => true,
            'show_admin_column'   => true,
            'rewrite'             => false,
        );

        register_taxonomy('mdl_category', 'mdl_document', $args);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'mdl_document_details',
            __('Document Details', 'modern-document-library'),
            array($this, 'render_meta_box'),
            'mdl_document',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('mdl_document_meta', 'mdl_document_nonce');

        $year    = get_post_meta($post->ID, '_mdl_year', true);
        $file_id = get_post_meta($post->ID, '_mdl_file_id', true);
        $file_url = $file_id ? wp_get_attachment_url($file_id) : '';
        $file_name = $file_id ? basename(get_attached_file($file_id)) : '';
        ?>
        <div class="mdl-meta-field">
            <label for="mdl_year"><?php _e('Year', 'modern-document-library'); ?></label>
            <input type="text" id="mdl_year" name="mdl_year" value="<?php echo esc_attr($year); ?>" placeholder="<?php esc_attr_e('e.g., 2024 or 2023-2024', 'modern-document-library'); ?>">
        </div>

        <div class="mdl-meta-field">
            <label><?php _e('Document File', 'modern-document-library'); ?></label>
            <input type="hidden" id="mdl_file_id" name="mdl_file_id" value="<?php echo esc_attr($file_id); ?>">
            <button type="button" class="button" id="mdl_upload_btn">
                <?php echo $file_id ? esc_html__('Change File', 'modern-document-library') : esc_html__('Upload File', 'modern-document-library'); ?>
            </button>

            <?php if ($file_id && $file_url) : ?>
            <div class="mdl-file-preview" id="mdl_file_preview">
                <span class="dashicons dashicons-media-document"></span>
                <span><?php echo esc_html($file_name); ?></span>
                <a href="#" class="mdl-remove-file" id="mdl_remove_file"><?php _e('Remove', 'modern-document-library'); ?></a>
            </div>
            <?php else : ?>
            <div class="mdl-file-preview" id="mdl_file_preview" style="display: none;">
                <span class="dashicons dashicons-media-document"></span>
                <span id="mdl_file_name"></span>
                <a href="#" class="mdl-remove-file" id="mdl_remove_file"><?php _e('Remove', 'modern-document-library'); ?></a>
            </div>
            <?php endif; ?>
        </div>

        <script>
        jQuery(function($) {
            var mediaUploader;
            $('#mdl_upload_btn').on('click', function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: <?php echo wp_json_encode(__('Select Document', 'modern-document-library')); ?>,
                    button: { text: <?php echo wp_json_encode(__('Use this file', 'modern-document-library')); ?> },
                    multiple: false,
                    library: { type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] }
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#mdl_file_id').val(attachment.id);
                    $('#mdl_file_name').text(attachment.filename);
                    $('#mdl_file_preview').show();
                    $('#mdl_upload_btn').text(<?php echo wp_json_encode(__('Change File', 'modern-document-library')); ?>);
                });
                mediaUploader.open();
            });
            $('#mdl_remove_file').on('click', function(e) {
                e.preventDefault();
                $('#mdl_file_id').val('');
                $('#mdl_file_preview').hide();
                $('#mdl_upload_btn').text(<?php echo wp_json_encode(__('Upload File', 'modern-document-library')); ?>);
            });
        });
        </script>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['mdl_document_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mdl_document_nonce'])), 'mdl_document_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['mdl_year'])) {
            update_post_meta($post_id, '_mdl_year', sanitize_text_field(wp_unslash($_POST['mdl_year'])));
        }

        if (isset($_POST['mdl_file_id'])) {
            $file_id = absint($_POST['mdl_file_id']);
            update_post_meta($post_id, '_mdl_file_id', $file_id);

            if ($file_id) {
                $file_path = get_attached_file($file_id);
                if ($file_path && file_exists($file_path)) {
                    update_post_meta($post_id, '_mdl_file_size', size_format(filesize($file_path)));
                    update_post_meta($post_id, '_mdl_file_type', strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)));
                }
            }
        }
    }
}
