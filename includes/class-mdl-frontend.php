<?php
if (!defined('ABSPATH')) {
    exit;
}

class MDL_Frontend {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('document_library', array($this, 'render_shortcode'));
        add_action('init', array($this, 'register_block'));
    }

    /**
     * Gutenberg / Site Editor block (avoids raw [document_library] text if shortcodes do not run).
     */
    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type(
            'modern-document-library/document-library',
            array(
                'api_version'     => 3,
                'title'           => __('PSS Document Library', 'pss-document-plugin'),
                'category'        => 'widgets',
                'icon'            => 'media-document',
                'description'     => __('Display the document grid with search, filters, downloads, and external links.', 'pss-document-plugin'),
                'keywords'        => array('pss', 'documents', 'pdf', 'library', 'files'),
                'attributes'      => array(
                    'category'   => array(
                        'type'    => 'string',
                        'default' => '',
                    ),
                    'limit'      => array(
                        'type'    => 'string',
                        'default' => '-1',
                    ),
                    'showSearch' => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
                    'showFilter' => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
                    'showStats'  => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
                ),
                'supports'        => array(
                    'align'  => array('wide', 'full'),
                    'html'   => false,
                    'anchor' => true,
                ),
                'render_callback' => array($this, 'render_document_library_block'),
            )
        );
    }

    /**
     * @param array    $atts
     * @param string   $content
     * @param WP_Block $block
     */
    public function render_document_library_block($atts, $content, $block) {
        $atts = is_array($atts) ? $atts : array();

        $show_search = array_key_exists('showSearch', $atts) ? (bool) $atts['showSearch'] : true;
        $show_filter = array_key_exists('showFilter', $atts) ? (bool) $atts['showFilter'] : true;
        $show_stats  = array_key_exists('showStats', $atts) ? (bool) $atts['showStats'] : true;

        return $this->render_shortcode(
            array(
                'category'    => isset($atts['category']) ? (string) $atts['category'] : '',
                'limit'       => isset($atts['limit']) ? $atts['limit'] : '-1',
                'show_search' => $show_search ? 'true' : 'false',
                'show_filter' => $show_filter ? 'true' : 'false',
                'show_stats'  => $show_stats ? 'true' : 'false',
            )
        );
    }

    /**
     * Parse year meta for sorting (first 4-digit year, or 0 if none).
     */
    public static function year_sort_value($post_id) {
        $year = get_post_meta($post_id, '_mdl_year', true);
        if ($year !== '' && $year !== null && preg_match('/(\d{4})/', (string) $year, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    /**
     * Sort published documents without excluding posts missing _mdl_year (SQL meta_value order does).
     *
     * @param WP_Post[] $posts
     * @param string    $sort  newest|oldest|name
     * @return WP_Post[]
     */
    public static function sort_documents_list($posts, $sort) {
        if ($sort === 'name') {
            usort(
                $posts,
                function ($a, $b) {
                    return strcasecmp($a->post_title, $b->post_title);
                }
            );
            return $posts;
        }

        $direction = ($sort === 'oldest') ? 1 : -1;

        usort(
            $posts,
            function ($a, $b) use ($direction) {
                $ya = self::year_sort_value($a->ID);
                $yb = self::year_sort_value($b->ID);
                if ($ya !== $yb) {
                    return ($ya < $yb) ? -$direction : $direction;
                }
                $ta = strtotime($a->post_date);
                $tb = strtotime($b->post_date);
                if ($ta === $tb) {
                    return 0;
                }
                return ($ta < $tb) ? -$direction : $direction;
            }
        );

        return $posts;
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'category'    => '',
                'limit'       => -1,
                'show_search' => 'true',
                'show_filter' => 'true',
                'show_stats'  => 'true',
            ),
            $atts,
            'document_library'
        );

        $show_search = filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN);
        $show_filter = filter_var($atts['show_filter'], FILTER_VALIDATE_BOOLEAN);
        $show_stats  = filter_var($atts['show_stats'], FILTER_VALIDATE_BOOLEAN);

        $limit = intval($atts['limit']);

        $args = array(
            'post_type'      => 'mdl_document',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'mdl_category',
                    'field'    => 'slug',
                    'terms'    => sanitize_title($atts['category']),
                ),
            );
        }

        $documents = get_posts($args);
        $documents   = self::sort_documents_list($documents, 'newest');

        if ($limit > 0) {
            $documents = array_slice($documents, 0, $limit);
        }

        $categories = get_terms(
            array(
                'taxonomy'   => 'mdl_category',
                'hide_empty' => true,
            )
        );

        if (is_wp_error($categories)) {
            $categories = array();
        }

        $counts       = wp_count_posts('mdl_document');
        $total_docs   = isset($counts->publish) ? (int) $counts->publish : 0;
        $total_categories = count($categories);

        $initial_category = !empty($atts['category']) ? sanitize_title($atts['category']) : 'all';

        $mdl_initial_json = wp_json_encode(
            self::get_documents_data($documents),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if (!is_string($mdl_initial_json)) {
            $mdl_initial_json = '[]';
        }

        ob_start();
        include PSS_DOC_PLUGIN_DIR . 'templates/document-library.php';
        return ob_get_clean();
    }

    public static function get_documents_data($documents) {
        $data = array();
        foreach ($documents as $doc) {
            $terms          = get_the_terms($doc->ID, 'mdl_category');
            $category      = ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';
            $category_slug = ($terms && !is_wp_error($terms)) ? $terms[0]->slug : '';

            $asset = MDL_Post_Type::get_document_asset_data($doc->ID);

            $data[] = array(
                'id'             => $doc->ID,
                'title'          => $doc->post_title,
                'category'       => $category,
                'categorySlug'   => $category_slug,
                'year'           => get_post_meta($doc->ID, '_mdl_year', true),
                'fileType'       => $asset['file_type'],
                'fileSize'       => $asset['file_size'],
                'fileUrl'        => $asset['url'],
                'isExternal'     => $asset['is_external'],
            );
        }
        return $data;
    }
}
