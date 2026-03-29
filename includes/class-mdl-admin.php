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
            __('Shortcode Help', 'pss-document-plugin'),
            __('Shortcode Help', 'pss-document-plugin'),
            'manage_options',
            'mdl-shortcode-help',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('PSS Document Plugin — Shortcode', 'pss-document-plugin'); ?></h1>

            <div class="card" style="max-width: 800px; padding: 20px;">
                <h2><?php _e('Basic Usage', 'pss-document-plugin'); ?></h2>
                <p><?php _e('If the shortcode appears as plain text on the page, the plugin may be inactive or the text is in a block that does not run shortcodes (use the Shortcode block or the Document Library block).', 'pss-document-plugin'); ?></p>
                <p><strong><?php _e('Block editor (recommended):', 'pss-document-plugin'); ?></strong> <?php _e('Add a block and search for “PSS Document Library”, or add a Shortcode block and paste:', 'pss-document-plugin'); ?></p>
                <code style="display: block; padding: 15px; background: #f0f0f1; font-size: 14px;">[document_library]</code>
                <p><strong><?php _e('Shortcode:', 'pss-document-plugin'); ?></strong> <?php _e('Same text in a Shortcode block or classic editor.', 'pss-document-plugin'); ?></p>

                <h2 style="margin-top: 30px;"><?php _e('Shortcode Attributes', 'pss-document-plugin'); ?></h2>
                <table class="widefat" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e('Attribute', 'pss-document-plugin'); ?></th>
                            <th><?php _e('Default', 'pss-document-plugin'); ?></th>
                            <th><?php _e('Description', 'pss-document-plugin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>category</code></td>
                            <td>all</td>
                            <td><?php _e('Filter by category slug (e.g., "accreditation")', 'pss-document-plugin'); ?></td>
                        </tr>
                        <tr>
                            <td><code>limit</code></td>
                            <td>-1 (all)</td>
                            <td><?php _e('Maximum number of documents to show', 'pss-document-plugin'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_search</code></td>
                            <td>true</td>
                            <td><?php _e('Show or hide the search bar', 'pss-document-plugin'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_filter</code></td>
                            <td>true</td>
                            <td><?php _e('Show or hide category filter buttons', 'pss-document-plugin'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_stats</code></td>
                            <td>true</td>
                            <td><?php _e('Show or hide the statistics section', 'pss-document-plugin'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <h2 style="margin-top: 30px;"><?php _e('Examples', 'pss-document-plugin'); ?></h2>
                <p><strong><?php _e('Show only Accreditation documents:', 'pss-document-plugin'); ?></strong></p>
                <code style="display: block; padding: 10px; background: #f0f0f1;">[document_library category="accreditation"]</code>

                <p style="margin-top: 15px;"><strong><?php _e('Show 10 documents without search:', 'pss-document-plugin'); ?></strong></p>
                <code style="display: block; padding: 10px; background: #f0f0f1;">[document_library limit="10" show_search="false"]</code>

                <p style="margin-top: 15px;"><strong><?php _e('Minimal view (no search, filter, or stats):', 'pss-document-plugin'); ?></strong></p>
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
                $new_columns['mdl_year'] = __('Year', 'pss-document-plugin');
                $new_columns['mdl_file'] = __('File', 'pss-document-plugin');
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
                $asset = MDL_Post_Type::get_document_asset_data($post_id);
                if ($asset['url'] !== '') {
                    if ($asset['is_external']) {
                        echo '<span class="dashicons dashicons-admin-links" style="color: #2271b1;" title="' . esc_attr__('External link', 'pss-document-plugin') . '"></span> ';
                        echo esc_html($asset['file_type']) . ' <span class="description">(' . esc_html__('link', 'pss-document-plugin') . ')</span>';
                    } else {
                        echo '<span class="dashicons dashicons-media-document" style="color: #2271b1;"></span> ';
                        echo esc_html($asset['file_type']) . ' (' . esc_html($asset['file_size']) . ')';
                    }
                } else {
                    echo '<span style="color: #b32d2e;">' . esc_html__('No file or URL', 'pss-document-plugin') . '</span>';
                }
                break;
        }
    }
}
