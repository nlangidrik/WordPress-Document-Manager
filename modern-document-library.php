<?php
/**
 * Plugin Name: Modern Document Library
 * Description: A modern, card-based document library with search, filtering, and sorting
 * Version: 1.0.0
 * Author: Your Name
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: modern-document-library
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MDL_VERSION', '1.0.0');
define('MDL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MDL_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once MDL_PLUGIN_DIR . 'includes/class-mdl-post-type.php';
require_once MDL_PLUGIN_DIR . 'includes/class-mdl-admin.php';
require_once MDL_PLUGIN_DIR . 'includes/class-mdl-frontend.php';
require_once MDL_PLUGIN_DIR . 'includes/class-mdl-ajax.php';

class Modern_Document_Library {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        MDL_Post_Type::get_instance();
        MDL_Admin::get_instance();
        MDL_Frontend::get_instance();
        MDL_Ajax::get_instance();
    }

    public function init() {
        load_plugin_textdomain('modern-document-library', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'mdl-frontend',
            MDL_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            MDL_VERSION
        );

        wp_enqueue_script(
            'mdl-frontend',
            MDL_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            MDL_VERSION,
            true
        );

        wp_localize_script('mdl-frontend', 'mdlAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('mdl_nonce'),
        ));
    }

    public function enqueue_admin_assets($hook) {
        if (!MDL_Admin::is_mdl_admin_screen()) {
            return;
        }

        wp_enqueue_style(
            'mdl-admin',
            MDL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MDL_VERSION
        );

        wp_enqueue_script(
            'mdl-admin',
            MDL_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MDL_VERSION,
            true
        );
    }
}

Modern_Document_Library::get_instance();

register_activation_hook(__FILE__, function () {
    MDL_Post_Type::get_instance()->register_post_type();
    MDL_Post_Type::get_instance()->register_taxonomy();
    flush_rewrite_rules();

    $default_categories = array('Accreditation', 'Annual Reports', 'Policies', 'Forms');
    foreach ($default_categories as $cat) {
        if (!term_exists($cat, 'mdl_category')) {
            wp_insert_term($cat, 'mdl_category');
        }
    }
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
