<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div
    class="mdl-wrapper"
    data-initial="<?php echo esc_attr($mdl_initial_json); ?>"
    data-initial-category="<?php echo esc_attr($initial_category); ?>"
    role="region"
    aria-label="<?php esc_attr_e('Document library', 'pss-document-plugin'); ?>"
>

    <header class="mdl-header">
        <div class="mdl-header-content">
            <div class="mdl-logo" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
            </div>
            <div>
                <h1 class="mdl-title"><?php _e('PSS Document Library', 'pss-document-plugin'); ?></h1>
                <p class="mdl-subtitle"><?php _e('Browse, view, and download official documents', 'pss-document-plugin'); ?></p>
            </div>
        </div>
    </header>

    <?php if ($show_stats) : ?>
    <section class="mdl-hero" aria-labelledby="mdl-hero-heading">
        <div class="mdl-hero-content">
            <h2 id="mdl-hero-heading" class="mdl-hero-title"><?php _e('Public Resources', 'pss-document-plugin'); ?></h2>
            <p class="mdl-hero-description">
                <?php _e('Access our collection of official documents including accreditation reports, annual reports, policies, and forms. All documents are available for viewing and download.', 'pss-document-plugin'); ?>
            </p>
        </div>

        <div class="mdl-stats">
            <div class="mdl-stat-card">
                <div class="mdl-stat-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                </div>
                <div>
                    <p class="mdl-stat-value"><?php echo esc_html($total_docs); ?>+</p>
                    <p class="mdl-stat-label"><?php _e('Total Documents', 'pss-document-plugin'); ?></p>
                </div>
            </div>
            <div class="mdl-stat-card">
                <div class="mdl-stat-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                </div>
                <div>
                    <p class="mdl-stat-value"><?php echo esc_html($total_categories); ?></p>
                    <p class="mdl-stat-label"><?php _e('Categories', 'pss-document-plugin'); ?></p>
                </div>
            </div>
            <?php
            $cat_icons = array(
                'accreditation'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>',
                'annual-reports' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.342a2 2 0 0 0-.602-1.43l-4.44-4.342A2 2 0 0 0 13.56 2H6a2 2 0 0 0-2 2z"/><path d="M9 13h6"/><path d="M9 17h3"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>',
            );
            foreach (array_slice($categories, 0, 2) as $cat) :
                $count = $cat->count;
                $icon  = isset($cat_icons[ $cat->slug ]) ? $cat_icons[ $cat->slug ] : $cat_icons['annual-reports'];
                ?>
            <div class="mdl-stat-card">
                <div class="mdl-stat-icon" aria-hidden="true">
                    <?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG markup ?>
                </div>
                <div>
                    <p class="mdl-stat-value"><?php echo esc_html($count); ?></p>
                    <p class="mdl-stat-label"><?php echo esc_html($cat->name); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="mdl-controls" aria-label="<?php esc_attr_e('Search and sort', 'pss-document-plugin'); ?>">
        <?php if ($show_search) : ?>
        <div class="mdl-search">
            <label for="mdl-search" class="screen-reader-text"><?php esc_html_e('Search documents', 'pss-document-plugin'); ?></label>
            <div class="mdl-search-field">
                <span class="mdl-search-icon-wrap" aria-hidden="true">
                    <svg class="mdl-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <input type="search" class="mdl-search-input" id="mdl-search" placeholder="<?php esc_attr_e('Search by title, category, or year...', 'pss-document-plugin'); ?>" autocomplete="off">
                <button type="button" class="mdl-search-clear" id="mdl-search-clear" style="display: none;" aria-label="<?php esc_attr_e('Clear search', 'pss-document-plugin'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="mdl-sort">
            <button type="button" class="mdl-sort-btn" id="mdl-sort-btn" aria-expanded="false" aria-haspopup="listbox" aria-controls="mdl-sort-dropdown">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
                <span><?php _e('Sort by:', 'pss-document-plugin'); ?></span>
                <span id="mdl-sort-label"><?php _e('Newest', 'pss-document-plugin'); ?></span>
            </button>
            <div class="mdl-sort-dropdown" id="mdl-sort-dropdown" role="listbox" aria-label="<?php esc_attr_e('Sort options', 'pss-document-plugin'); ?>">
                <button type="button" data-sort="newest" class="active" role="option"><?php _e('Newest First', 'pss-document-plugin'); ?></button>
                <button type="button" data-sort="oldest" role="option"><?php _e('Oldest First', 'pss-document-plugin'); ?></button>
                <button type="button" data-sort="name" role="option"><?php _e('Name (A-Z)', 'pss-document-plugin'); ?></button>
            </div>
        </div>
    </section>

    <?php if ($show_filter) : ?>
    <section class="mdl-filter" aria-label="<?php esc_attr_e('Filter by category', 'pss-document-plugin'); ?>">
        <button type="button" class="mdl-filter-btn<?php echo ($initial_category === 'all') ? ' active' : ''; ?>" data-category="all">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
            <?php _e('All Documents', 'pss-document-plugin'); ?>
            <span class="mdl-filter-count"><?php echo esc_html($total_docs); ?></span>
        </button>
        <?php foreach ($categories as $cat) : ?>
        <button type="button" class="mdl-filter-btn<?php echo ($initial_category === $cat->slug) ? ' active' : ''; ?>" data-category="<?php echo esc_attr($cat->slug); ?>" data-category-label="<?php echo esc_attr($cat->name); ?>">
            <?php echo esc_html($cat->name); ?>
            <span class="mdl-filter-count"><?php echo esc_html($cat->count); ?></span>
        </button>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <div class="mdl-results">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
        <span id="mdl-results-text"><?php printf(esc_html__('Showing %d documents', 'pss-document-plugin'), count($documents)); ?></span>
    </div>

    <div class="mdl-grid" id="mdl-grid">
        <?php
        foreach ($documents as $doc) :
            $terms          = get_the_terms($doc->ID, 'mdl_category');
            $category       = ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';
            $category_slug  = ($terms && !is_wp_error($terms)) ? $terms[0]->slug : '';
            $asset          = MDL_Post_Type::get_document_asset_data($doc->ID);
            $file_url       = $asset['url'] !== '' ? $asset['url'] : '#';
            $year           = get_post_meta($doc->ID, '_mdl_year', true);
            $file_type      = $asset['file_type'];
            $file_size      = $asset['file_size'];
            $is_external    = $asset['is_external'];
            ?>
        <article class="mdl-card" data-id="<?php echo esc_attr($doc->ID); ?>">
            <div class="mdl-card-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
            </div>
            <div class="mdl-card-content">
                <h3 class="mdl-card-title"><?php echo esc_html($doc->post_title); ?></h3>
                <div class="mdl-card-meta">
                    <span class="mdl-card-badge mdl-badge-<?php echo esc_attr($category_slug); ?>"><?php echo esc_html($category); ?></span>
                    <span class="mdl-card-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <?php echo esc_html($year); ?>
                    </span>
                    <span class="mdl-card-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                        <?php echo esc_html($file_type); ?> &bull; <?php echo esc_html($file_size); ?>
                    </span>
                </div>
                <div class="mdl-card-actions<?php echo $is_external ? ' mdl-card-actions-external' : ''; ?>">
                    <?php if ($is_external) : ?>
                    <a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer" class="mdl-btn mdl-btn-primary mdl-btn-open-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                        <?php _e('Open link', 'pss-document-plugin'); ?>
                    </a>
                    <?php else : ?>
                    <a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer" class="mdl-btn mdl-btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?php _e('View', 'pss-document-plugin'); ?>
                    </a>
                    <a href="<?php echo esc_url($file_url); ?>" download class="mdl-btn mdl-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        <?php _e('Download', 'pss-document-plugin'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <div class="mdl-empty" id="mdl-empty" style="display: none;" role="status">
        <div class="mdl-empty-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/><path d="M12 10v6"/><path d="m15 13-3 3-3-3"/></svg>
        </div>
        <h3 class="mdl-empty-title"><?php _e('No documents found', 'pss-document-plugin'); ?></h3>
        <p class="mdl-empty-text"><?php _e('Try a different search term or clear your filters.', 'pss-document-plugin'); ?></p>
        <button type="button" class="mdl-btn mdl-btn-outline" id="mdl-clear-filters"><?php _e('Clear filters', 'pss-document-plugin'); ?></button>
    </div>

    <footer class="mdl-footer">
        <p><?php _e('Need assistance? Contact our administrative office for help with documents.', 'pss-document-plugin'); ?></p>
        <p><?php printf(esc_html__('Last updated: %s', 'pss-document-plugin'), esc_html(date_i18n(get_option('date_format')))); ?></p>
    </footer>

</div>
