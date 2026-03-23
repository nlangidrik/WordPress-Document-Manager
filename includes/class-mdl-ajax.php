<?php
if (!defined('ABSPATH')) {
    exit;
}

class MDL_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_mdl_filter_documents', array($this, 'filter_documents'));
        add_action('wp_ajax_nopriv_mdl_filter_documents', array($this, 'filter_documents'));
    }

    /**
     * Match search against title, year meta, and category name/slug.
     */
    private function document_matches_search($post, $search) {
        if ($search === '') {
            return true;
        }
        $q = wp_strip_all_tags($search);
        if (stripos($post->post_title, $q) !== false) {
            return true;
        }
        $year = get_post_meta($post->ID, '_mdl_year', true);
        if ($year && stripos((string) $year, $q) !== false) {
            return true;
        }
        $terms = get_the_terms($post->ID, 'mdl_category');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $t) {
                if (stripos($t->name, $q) !== false || stripos($t->slug, $q) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    public function filter_documents() {
        check_ajax_referer('mdl_nonce', 'nonce');

        $search   = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
        $sort     = isset($_POST['sort']) ? sanitize_text_field(wp_unslash($_POST['sort'])) : 'newest';

        $args = array(
            'post_type'      => 'mdl_document',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

        if (!empty($category) && $category !== 'all') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'mdl_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ),
            );
        }

        $args['orderby'] = 'date';
        $args['order']   = 'DESC';

        $documents = get_posts($args);

        $sort_mode = in_array($sort, array('oldest', 'name'), true) ? $sort : 'newest';
        $documents = MDL_Frontend::sort_documents_list($documents, $sort_mode);

        if ($search !== '') {
            $documents = array_values(
                array_filter(
                    $documents,
                    function ($post) use ($search) {
                        return $this->document_matches_search($post, $search);
                    }
                )
            );
        }

        $data = MDL_Frontend::get_documents_data($documents);

        wp_send_json_success($data);
    }
}
