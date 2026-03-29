=== PSS Document Plugin ===
Contributors: yourname
Tags: documents, library, file management, download, pdf
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, card-based document library with search, filtering, sorting, uploads, and external links.

== Description ==

PSS Document Plugin provides a user-friendly way to display documents on your WordPress site. Features include:

* Modern card-based grid layout
* Real-time search filtering
* Category-based filtering
* Multiple sorting options (newest, oldest, name)
* Upload files or link to external URLs (e.g. Google Drive)
* Responsive design for all devices
* Shortcode and block editor support with customizable options

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory (main file: `pss-document-plugin.php`).
2. Activate **PSS Document Plugin** through the **Plugins** menu in WordPress.
3. If you previously used an older bootstrap file name, you may need to activate the plugin again once.
4. Go to **PSS Documents** > Add New to add your first document.
5. Use the shortcode `[document_library]` on any page or post, or add the **PSS Document Library** block.

== Shortcode Usage ==

Basic: `[document_library]`

With options:
* `[document_library category="accreditation"]` - Filter by category
* `[document_library limit="10"]` - Limit number of documents
* `[document_library show_search="false"]` - Hide search bar
* `[document_library show_filter="false"]` - Hide category filter
* `[document_library show_stats="false"]` - Hide statistics section

== License ==

This plugin is free software released under the GPLv2 (or later). In
plain terms, you may:

* Use it on any site, including commercial sites
* Copy and share it with others
* Change the code and distribute your changes

The same license family as WordPress applies. See LICENSE in this
plugin folder and https://www.gnu.org/licenses/gpl-2.0.html for the
full legal terms.

== Changelog ==

= 1.0.0 =
* Initial release (PSS Document Plugin branding)
