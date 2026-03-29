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
            'name'               => __('Documents', 'pss-document-plugin'),
            'singular_name'      => __('Document', 'pss-document-plugin'),
            'add_new'            => __('Add New', 'pss-document-plugin'),
            'add_new_item'       => __('Add New Document', 'pss-document-plugin'),
            'edit_item'          => __('Edit Document', 'pss-document-plugin'),
            'new_item'           => __('New Document', 'pss-document-plugin'),
            'view_item'          => __('View Document', 'pss-document-plugin'),
            'search_items'       => __('Search Documents', 'pss-document-plugin'),
            'not_found'          => __('No documents found', 'pss-document-plugin'),
            'not_found_in_trash' => __('No documents found in Trash', 'pss-document-plugin'),
            'menu_name'          => __('PSS Documents', 'pss-document-plugin'),
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
            'name'              => __('Document Categories', 'pss-document-plugin'),
            'singular_name'     => __('Category', 'pss-document-plugin'),
            'search_items'      => __('Search Categories', 'pss-document-plugin'),
            'all_items'         => __('All Categories', 'pss-document-plugin'),
            'edit_item'         => __('Edit Category', 'pss-document-plugin'),
            'update_item'       => __('Update Category', 'pss-document-plugin'),
            'add_new_item'      => __('Add New Category', 'pss-document-plugin'),
            'new_item_name'     => __('New Category Name', 'pss-document-plugin'),
            'menu_name'         => __('Categories', 'pss-document-plugin'),
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
            __('Document Details', 'pss-document-plugin'),
            array($this, 'render_meta_box'),
            'mdl_document',
            'normal',
            'high'
        );
    }

    /**
     * Resolved file URL, type/size labels, and whether the asset is an external link.
     *
     * @param int $post_id
     * @return array{url:string,file_type:string,file_size:string,is_external:bool}
     */
    public static function get_document_asset_data($post_id) {
        $post_id = (int) $post_id;
        $source  = get_post_meta($post_id, '_mdl_source', true);

        if ($source === 'url') {
            $raw = get_post_meta($post_id, '_mdl_external_url', true);
            $url = $raw ? esc_url($raw) : '';
            $type = get_post_meta($post_id, '_mdl_file_type', true);
            if ($type === '' || $type === null) {
                $type = 'LINK';
            }
            $size = get_post_meta($post_id, '_mdl_file_size', true);
            if ($size === '' || $size === null) {
                $size = '—';
            }
            return array(
                'url'           => $url,
                'file_type'     => $type,
                'file_size'     => $size,
                'is_external'   => true,
            );
        }

        $file_id  = (int) get_post_meta($post_id, '_mdl_file_id', true);
        $file_url = $file_id ? wp_get_attachment_url($file_id) : '';

        return array(
            'url'           => $file_url ? $file_url : '',
            'file_type'     => get_post_meta($post_id, '_mdl_file_type', true),
            'file_size'     => get_post_meta($post_id, '_mdl_file_size', true),
            'is_external'   => false,
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('mdl_document_meta', 'mdl_document_nonce');

        $year         = get_post_meta($post->ID, '_mdl_year', true);
        $file_id      = (int) get_post_meta($post->ID, '_mdl_file_id', true);
        $file_url     = $file_id ? wp_get_attachment_url($file_id) : '';
        $file_name    = $file_id ? basename(get_attached_file($file_id)) : '';
        $saved_source = get_post_meta($post->ID, '_mdl_source', true);
        $external_url = get_post_meta($post->ID, '_mdl_external_url', true);
        $url_label    = get_post_meta($post->ID, '_mdl_file_type', true);

        if ($saved_source === 'url' || ($saved_source !== 'upload' && $external_url && !$file_id)) {
            $source = 'url';
        } else {
            $source = 'upload';
        }

        if ($source === 'url' && ($url_label === '' || $url_label === 'LINK' || $url_label === null)) {
            $url_type_display = '';
        } else {
            $url_type_display = $source === 'url' ? $url_label : '';
        }
        ?>
        <p class="description" style="margin-top:0;">
            <?php _e('Use the title field above for the document name shown in the library. Assign a category in the sidebar.', 'pss-document-plugin'); ?>
        </p>

        <div class="mdl-meta-field">
            <label for="mdl_year"><?php _e('Year', 'pss-document-plugin'); ?></label>
            <input type="text" id="mdl_year" name="mdl_year" value="<?php echo esc_attr($year); ?>" placeholder="<?php esc_attr_e('e.g., 2024 or 2023-2024', 'pss-document-plugin'); ?>">
        </div>

        <div class="mdl-meta-field">
            <span class="mdl-meta-field-label"><?php _e('Document source', 'pss-document-plugin'); ?></span>
            <fieldset class="mdl-source-fieldset">
                <label class="mdl-source-option">
                    <input type="radio" name="mdl_source" value="upload" class="mdl-source-choice" <?php checked($source, 'upload'); ?>>
                    <?php _e('Upload a file', 'pss-document-plugin'); ?>
                </label>
                <label class="mdl-source-option">
                    <input type="radio" name="mdl_source" value="url" class="mdl-source-choice" <?php checked($source, 'url'); ?>>
                    <?php _e('Link to external file (URL)', 'pss-document-plugin'); ?>
                </label>
            </fieldset>
            <p class="description"><?php _e('Use a URL for files hosted elsewhere (for example Google Drive, Dropbox, or your school website).', 'pss-document-plugin'); ?></p>
        </div>

        <div class="mdl-meta-panel" id="mdl-panel-upload" <?php echo $source === 'upload' ? '' : 'style="display:none;"'; ?>>
            <div class="mdl-meta-field">
                <label><?php _e('Uploaded file', 'pss-document-plugin'); ?></label>
                <input type="hidden" id="mdl_file_id" name="mdl_file_id" value="<?php echo esc_attr($file_id); ?>">
                <button type="button" class="button" id="mdl_upload_btn">
                    <?php echo $file_id ? esc_html__('Change file', 'pss-document-plugin') : esc_html__('Upload file', 'pss-document-plugin'); ?>
                </button>

                <?php if ($file_id && $file_url) : ?>
                <div class="mdl-file-preview" id="mdl_file_preview">
                    <span class="dashicons dashicons-media-document"></span>
                    <span><?php echo esc_html($file_name); ?></span>
                    <a href="#" class="mdl-remove-file" id="mdl_remove_file"><?php _e('Remove', 'pss-document-plugin'); ?></a>
                </div>
                <?php else : ?>
                <div class="mdl-file-preview" id="mdl_file_preview" style="display: none;">
                    <span class="dashicons dashicons-media-document"></span>
                    <span id="mdl_file_name"></span>
                    <a href="#" class="mdl-remove-file" id="mdl_remove_file"><?php _e('Remove', 'pss-document-plugin'); ?></a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mdl-meta-panel" id="mdl-panel-url" <?php echo $source === 'url' ? '' : 'style="display:none;"'; ?>>
            <div class="mdl-meta-field">
                <label for="mdl_external_url"><?php _e('File URL', 'pss-document-plugin'); ?></label>
                <input type="url" id="mdl_external_url" name="mdl_external_url" value="<?php echo esc_attr($external_url); ?>" class="large-text" placeholder="https://">
            </div>
            <div class="mdl-meta-field">
                <label for="mdl_url_file_label"><?php _e('Type label (optional)', 'pss-document-plugin'); ?></label>
                <input type="text" id="mdl_url_file_label" name="mdl_url_file_label" value="<?php echo esc_attr($url_type_display); ?>" placeholder="<?php esc_attr_e('e.g. PDF, Google Drive, Spreadsheet', 'pss-document-plugin'); ?>">
                <p class="description"><?php _e('Shown on the document card next to the year (defaults to “LINK” if empty).', 'pss-document-plugin'); ?></p>
            </div>
        </div>

        <script>
        jQuery(function($) {
            function mdlToggleSource() {
                var v = $('.mdl-source-choice:checked').val();
                if (v === 'url') {
                    $('#mdl-panel-upload').hide();
                    $('#mdl-panel-url').show();
                } else {
                    $('#mdl-panel-url').hide();
                    $('#mdl-panel-upload').show();
                }
            }
            $('.mdl-source-choice').on('change', mdlToggleSource);

            var mediaUploader;
            $('#mdl_upload_btn').on('click', function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: <?php echo wp_json_encode(__('Select document', 'pss-document-plugin')); ?>,
                    button: { text: <?php echo wp_json_encode(__('Use this file', 'pss-document-plugin')); ?> },
                    multiple: false,
                    library: { type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] }
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#mdl_file_id').val(attachment.id);
                    $('#mdl_file_name').text(attachment.filename);
                    $('#mdl_file_preview').show();
                    $('#mdl_upload_btn').text(<?php echo wp_json_encode(__('Change file', 'pss-document-plugin')); ?>);
                });
                mediaUploader.open();
            });
            $('#mdl_remove_file').on('click', function(e) {
                e.preventDefault();
                $('#mdl_file_id').val('');
                $('#mdl_file_preview').hide();
                $('#mdl_upload_btn').text(<?php echo wp_json_encode(__('Upload file', 'pss-document-plugin')); ?>);
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

        $mdl_source = isset($_POST['mdl_source']) && $_POST['mdl_source'] === 'url' ? 'url' : 'upload';
        update_post_meta($post_id, '_mdl_source', $mdl_source);

        if ($mdl_source === 'url') {
            update_post_meta($post_id, '_mdl_file_id', 0);
            $ext_url = isset($_POST['mdl_external_url']) ? wp_unslash($_POST['mdl_external_url']) : '';
            update_post_meta($post_id, '_mdl_external_url', esc_url_raw(trim($ext_url)));

            $label = isset($_POST['mdl_url_file_label']) ? sanitize_text_field(wp_unslash($_POST['mdl_url_file_label'])) : '';
            update_post_meta($post_id, '_mdl_file_type', $label !== '' ? $label : 'LINK');
            update_post_meta($post_id, '_mdl_file_size', '—');
            return;
        }

        delete_post_meta($post_id, '_mdl_external_url');

        if (isset($_POST['mdl_file_id'])) {
            $file_id = absint($_POST['mdl_file_id']);
            update_post_meta($post_id, '_mdl_file_id', $file_id);

            if ($file_id) {
                $file_path = get_attached_file($file_id);
                if ($file_path && file_exists($file_path)) {
                    update_post_meta($post_id, '_mdl_file_size', size_format(filesize($file_path)));
                    update_post_meta($post_id, '_mdl_file_type', strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)));
                }
            } else {
                delete_post_meta($post_id, '_mdl_file_size');
                delete_post_meta($post_id, '_mdl_file_type');
            }
        }
    }
}
