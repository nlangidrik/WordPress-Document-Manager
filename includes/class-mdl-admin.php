<?php
if (!defined('ABSPATH')) {
    exit;
}

class MDL_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function is_mdl_admin_screen() {
        if (!is_admin()) {
            return false;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen) {
            return false;
        }
        if ($screen->post_type === 'mdl_document') {
            return true;
        }
        if (strpos((string) $screen->id, 'mdl_document') !== false) {
            return true;
        }
        if (strpos((string) $screen->id, 'mdl-shortcode-help') !== false) {
            return true;
        }
        return false;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_filter('manage_mdl_document_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_mdl_document_posts_custom_column', array($this, 'render_admin_columns'), 10, 2);
    }

    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=mdl_document',
            __('Shortcode Help', 'modern-document-library'),
            __('Shortcode Help', 'modern-document-library'),
            'manage_options',
            'mdl-shortcode-help',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Document Library Shortcode', 'modern-document-library'); ?></h1>

            <div class="card" style="max-width: 800px; padding: 20px;">
                <h2><?php _e('Basic Usage', 'modern-document-library'); ?></h2>
                <p><?php _e('If the shortcode appears as plain text on the page, the plugin may be inactive or the text is in a block that does not run shortcodes (use the Shortcode block or the Document Library block).', 'modern-document-library'); ?></p>
                <p><strong><?php _e('Block editor (recommended):', 'modern-document-library'); ?></strong> <?php _e('Add a block and search for “Document Library”, or add a Shortcode block and paste:', 'modern-document-library'); ?></p>
                <code style="display: block; padding: 15px; background: #f0f0f1; font-size: 14px;">[document_library]</code>
                <p><strong><?php _e('Shortcode:', 'modern-document-library'); ?></strong> <?php _e('Same text in a Shortcode block or classic editor.', 'modern-document-library'); ?></p>

                <h2 style="margin-top: 30px;"><?php _e('Shortcode Attributes', 'modern-document-library'); ?></h2>
                <table class="widefat" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e('Attribute', 'modern-document-library'); ?></th>
                            <th><?php _e('Default', 'modern-document-library'); ?></th>
                            <th><?php _e('Description', 'modern-document-library'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>category</code></td>
                            <td>all</td>
                            <td><?php _e('Filter by category slug (e.g., "accreditation")', 'modern-document-library'); ?></td>
                        </tr>
                        <tr>
                            <td><code>limit</code></td>
                            <td>-1 (all)</td>
                            <td><?php _e('Maximum number of documents to show', 'modern-document-library'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_search</code></td>
                            <td>true</td>
                            <td><?php _e('Show or hide the search bar', 'modern-document-library'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_filter</code></td>
                            <td>true</td>
                            <td><?php _e('Show or hide category filter buttons', 'modern-document-library'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_stats</code></td>
                            <td>true</td>
                            <td><?php _e('Show or hide the statistics section', 'modern-document-library'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <h2 style="margin-top: 30px;"><?php _e('Examples', 'modern-document-library'); ?></h2>
                <p><strong><?php _e('Show only Accreditation documents:', 'modern-document-library'); ?></strong></p>
                <code style="display: block; padding: 10px; background: #f0f0f1;">[document_library category="accreditation"]</code>

                <p style="margin-top: 15px;"><strong><?php _e('Show 10 documents without search:', 'modern-document-library'); ?></strong></p>
                <code style="display: block; padding: 10px; background: #f0f0f1;">[document_library limit="10" show_search="false"]</code>

                <p style="margin-top: 15px;"><strong><?php _e('Minimal view (no search, filter, or stats):', 'modern-document-library'); ?></strong></p>
                <code style="display: block; padding: 10px; background: #f0f0f1;">[document_library show_search="false" show_filter="false" show_stats="false"]</code>
            </div>
        </div>
        <?php
    }

    public function add_admin_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[ $key ] = $value;
            if ($key === 'title') {
                $new_columns['mdl_year'] = __('Year', 'modern-document-library');
                $new_columns['mdl_file'] = __('File', 'modern-document-library');
            }
        }
        return $new_columns;
    }

    public function render_admin_columns($column, $post_id) {
        switch ($column) {
            case 'mdl_year':
                echo esc_html(get_post_meta($post_id, '_mdl_year', true) ?: '-');
                break;
            case 'mdl_file':
                $file_id = get_post_meta($post_id, '_mdl_file_id', true);
                if ($file_id) {
                    $file_type = get_post_meta($post_id, '_mdl_file_type', true);
                    $file_size = get_post_meta($post_id, '_mdl_file_size', true);
                    echo '<span class="dashicons dashicons-media-document" style="color: #2271b1;"></span> ';
                    echo esc_html($file_type) . ' (' . esc_html($file_size) . ')';
                } else {
                    echo '<span style="color: #b32d2e;">' . esc_html__('No file', 'modern-document-library') . '</span>';
                }
                break;
        }
    }
}
