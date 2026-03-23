(function ($) {
    'use strict';

    $.debounce = function (delay, callback) {
        var timeout;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    };

    var MDL = {
        wrapper: null,
        documents: [],
        currentCategory: 'all',
        currentSort: 'newest',
        currentSearch: '',

        init: function () {
            this.wrapper = $('.mdl-wrapper');
            if (!this.wrapper.length) {
                return;
            }

            try {
                this.documents = JSON.parse(this.wrapper.attr('data-initial') || '[]');
            } catch (e) {
                this.documents = [];
            }

            var initialCat = this.wrapper.attr('data-initial-category') || 'all';
            this.currentCategory = initialCat;

            if (initialCat !== 'all') {
                $('.mdl-filter-btn').removeClass('active');
                var $match = $('.mdl-filter-btn[data-category="' + initialCat.replace(/"/g, '\\"') + '"]');
                if ($match.length) {
                    $match.addClass('active');
                } else {
                    $('.mdl-filter-btn[data-category="all"]').addClass('active');
                    this.currentCategory = 'all';
                }
            }

            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $('#mdl-search').on(
                'input',
                $.debounce(300, function () {
                    self.currentSearch = $(this).val();
                    self.filterDocuments();
                    $('#mdl-search-clear').toggle(self.currentSearch.length > 0);
                })
            );

            $('#mdl-search-clear').on('click', function () {
                $('#mdl-search').val('');
                self.currentSearch = '';
                self.filterDocuments();
                $(this).hide();
            });

            $('#mdl-sort-btn').on('click', function (e) {
                e.stopPropagation();
                var $dd = $('#mdl-sort-dropdown');
                $dd.toggleClass('active');
                $(this).attr('aria-expanded', $dd.hasClass('active'));
            });

            $('#mdl-sort-dropdown').on('click', 'button', function (e) {
                e.stopPropagation();
                var sort = $(this).data('sort');
                self.currentSort = sort;

                var labels = {
                    newest: 'Newest',
                    oldest: 'Oldest',
                    name: 'Name (A-Z)',
                };
                $('#mdl-sort-label').text(labels[sort] || labels.newest);

                $('#mdl-sort-dropdown button').removeClass('active');
                $(this).addClass('active');
                $('#mdl-sort-dropdown').removeClass('active');
                $('#mdl-sort-btn').attr('aria-expanded', 'false');

                self.filterDocuments();
            });

            $(document).on('click', function () {
                $('#mdl-sort-dropdown').removeClass('active');
                $('#mdl-sort-btn').attr('aria-expanded', 'false');
            });

            $('.mdl-filter-btn').on('click', function () {
                self.currentCategory = $(this).data('category');
                $('.mdl-filter-btn').removeClass('active');
                $(this).addClass('active');
                self.filterDocuments();
            });

            $('#mdl-clear-filters').on('click', function () {
                self.currentCategory = 'all';
                self.currentSearch = '';
                self.currentSort = 'newest';

                $('#mdl-search').val('');
                $('#mdl-search-clear').hide();
                $('#mdl-sort-label').text('Newest');
                $('#mdl-sort-dropdown button').removeClass('active').first().addClass('active');
                $('.mdl-filter-btn').removeClass('active').first().addClass('active');

                self.filterDocuments();
            });
        },

        getActiveCategoryLabel: function () {
            var $btn = $('.mdl-filter-btn.active');
            var label = $btn.data('category-label');
            if (label) {
                return label;
            }
            if (this.currentCategory === 'all') {
                return '';
            }
            return this.currentCategory.replace(/-/g, ' ');
        },

        filterDocuments: function () {
            var self = this;

            $.ajax({
                url: mdlAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'mdl_filter_documents',
                    nonce: mdlAjax.nonce,
                    search: this.currentSearch,
                    category: this.currentCategory,
                    sort: this.currentSort,
                },
                success: function (response) {
                    if (response.success) {
                        self.renderDocuments(response.data);
                    }
                },
            });
        },

        renderDocuments: function (documents) {
            var self = this;
            var grid = $('#mdl-grid');
            var empty = $('#mdl-empty');

            if (documents.length === 0) {
                grid.hide();
                empty.show();
                $('#mdl-results-text').text('No documents found');
                return;
            }

            empty.hide();
            grid.show();

            var html = '';
            documents.forEach(function (doc) {
                html += self.renderCard(doc);
            });

            grid.html(html);

            var text =
                'Showing ' +
                documents.length +
                ' document' +
                (documents.length !== 1 ? 's' : '');
            if (this.currentCategory !== 'all') {
                var catLabel = this.getActiveCategoryLabel();
                if (catLabel) {
                    text += ' in ' + catLabel;
                }
            }
            if (this.currentSearch) {
                text += ' matching "' + this.currentSearch + '"';
            }
            $('#mdl-results-text').text(text);
        },

        renderCard: function (doc) {
            var slug = doc.categorySlug || '';
            var safeUrl = doc.fileUrl ? String(doc.fileUrl).replace(/"/g, '&quot;') : '#';
            return (
                '<article class="mdl-card" data-id="' +
                this.escapeHtml(String(doc.id)) +
                '">' +
                '<div class="mdl-card-icon" aria-hidden="true">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>' +
                '</div>' +
                '<div class="mdl-card-content">' +
                '<h3 class="mdl-card-title">' +
                this.escapeHtml(doc.title) +
                '</h3>' +
                '<div class="mdl-card-meta">' +
                '<span class="mdl-card-badge mdl-badge-' +
                this.escapeHtml(slug) +
                '">' +
                this.escapeHtml(doc.category) +
                '</span>' +
                '<span class="mdl-card-info">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>' +
                this.escapeHtml(doc.year) +
                '</span>' +
                '<span class="mdl-card-info">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>' +
                this.escapeHtml(doc.fileType) +
                ' &bull; ' +
                this.escapeHtml(doc.fileSize) +
                '</span>' +
                '</div>' +
                '<div class="mdl-card-actions">' +
                '<a href="' +
                safeUrl +
                '" target="_blank" rel="noopener noreferrer" class="mdl-btn mdl-btn-outline">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>' +
                'View' +
                '</a>' +
                '<a href="' +
                safeUrl +
                '" download class="mdl-btn mdl-btn-primary">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>' +
                'Download' +
                '</a>' +
                '</div>' +
                '</div>' +
                '</article>'
            );
        },

        escapeHtml: function (text) {
            if (text === null || text === undefined) {
                return '';
            }
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
    };

    $(function () {
        MDL.init();
    });

    window.MDL = MDL;
})(jQuery);
