# PSS Document Plugin

A WordPress plugin that displays documents in a modern card layout with search, category filters, sorting, uploads, and optional external links.

## Install

1. Copy this folder to `wp-content/plugins/` (or your preferred plugins directory).
2. Activate **PSS Document Plugin** under **Plugins** (bootstrap file: `pss-document-plugin.php`).
3. Add documents under **PSS Documents** in the admin.
4. On any page, add the **PSS Document Library** block or shortcode: `[document_library]`

## Shortcode options

| Attribute | Example | Purpose |
|-----------|---------|---------|
| `category` | `category="accreditation"` | Limit to a category slug |
| `limit` | `limit="10"` | Max number of documents |
| `show_search` | `show_search="false"` | Hide search |
| `show_filter` | `show_filter="false"` | Hide category chips |
| `show_stats` | `show_stats="false"` | Hide hero / stats |

## License

GPLv2 or later. See [LICENSE](LICENSE).

## Requirements

WordPress 5.0+ recommended.
