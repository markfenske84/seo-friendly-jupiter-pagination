=== SEO Friendly Jupiter Pagination ===
Contributors: webforagency
Tags: jupiter, pagination, seo, disable ajax, artbees
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 5.6
Stable tag: 1.4.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Converts Jupiter theme's AJAX pagination to SEO-friendly standard pagination with proper URLs. Completely disables AJAX pagination for better SEO. Adds rel=prev/next links to head.

== Description ==

SEO Friendly Jupiter Pagination fixes a critical SEO issue in the Jupiter WordPress theme (by Artbees) where pagination links use only `href="#"` with data attributes for AJAX functionality. While this provides a smooth user experience, it creates serious SEO problems.

**The Problem:**

The Jupiter theme outputs pagination like this:

`<a class="page-number js-pagination-page" href="#" data-page-id="2">2</a>`

This causes:
* Search engines can't crawl pagination links
* Users can't bookmark specific pages
* Social media shares don't work properly
* Page URLs don't reflect the actual page number
* Duplicate content issues

**The Solution:**

This plugin automatically converts pagination to proper SEO-friendly URLs and completely disables AJAX:

`<a class="page-number" href="/page/2/">2</a>`

**Key Benefits:**

* SEO-friendly URLs that search engines can crawl
* Adds rel=prev/next links to `<head>` for proper pagination indexing
* Bookmarkable pages
* Social media shareable
* Disables AJAX pagination for standard page loads (better for SEO)
* Removes all AJAX-related classes and attributes
* Implements sliding window (shows only 8 pages to prevent overwhelming pagination)
* No theme modification required
* Automatic detection - only activates when Jupiter pagination is present

**Features:**

* Automatically detects Jupiter theme pagination
* Converts `#` links to proper `/page/X/` URLs
* Adds `<link rel="prev">` and `<link rel="next">` tags to `<head>` for SEO
* Completely disables AJAX pagination functionality
* Removes `js-pagination-page` class and `data-page-id` attributes
* Sliding window pagination (shows only 8 pages around current page)
* Makes pagination work as standard page navigation
* Works with all Jupiter theme versions that use AJAX pagination
* No configuration needed - works automatically

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/seo-friendly-jupiter-pagination` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. That's it! The plugin works automatically with no configuration needed.

== Frequently Asked Questions ==

= Does this disable Jupiter's AJAX pagination? =

Yes, completely. The plugin removes all AJAX-related classes (`js-pagination-page`) and attributes (`data-page-id`), and adds JavaScript to prevent Jupiter's AJAX handlers from firing. Pagination will work as standard page navigation with full page reloads. This is intentional for better SEO.

= Will this work with my Jupiter theme version? =

Yes. The plugin works with any Jupiter theme version that uses the AJAX pagination system with `.mk-pagination-inner` and `.js-pagination-page` classes.

= Do I need to configure anything? =

No. Once activated, the plugin works automatically. There are no settings to configure.

= What is the sliding window? =

When you have many pages (more than 8), the plugin shows a "window" of 8 pages around your current page, plus the first and last pages. For example, if you're on page 15 of 50, you'll see: `1 ... 11 12 13 14 15 16 17 18 ... 50`

This prevents overwhelming pagination displays while keeping the navigation intuitive.

= Can I change the number of pages shown? =

Yes. Edit line 21 in the plugin file and change `private $pages_to_show = 8;` to your preferred number.

= Will this affect my site's performance? =

The plugin uses efficient DOM manipulation and only activates when Jupiter pagination is detected. The performance impact is minimal.

= Does this work with custom post types? =

Yes. The plugin works with any content that uses Jupiter's pagination system, including custom post types, archives, and search results.

== How It Works ==

The plugin uses WordPress output buffering, DOM manipulation, and JavaScript to:

1. Detect Jupiter pagination HTML (`.mk-pagination-inner` class)
2. Parse the `data-page-id` attributes to understand page structure
3. Generate proper SEO-friendly URLs for each page
4. Apply sliding window logic (8 pages visible)
5. Remove AJAX classes (`js-pagination-page`) and attributes (`data-page-id`)
6. Inject JavaScript to disable Jupiter's AJAX event handlers
7. Make pagination work as normal page links

== Compatibility ==

Tested with:
* Jupiter theme versions 5.x - 6.x
* WordPress 5.0 - 6.8
* PHP 5.6 - 8.2

== Changelog ==

= 1.4.6 =
* Improved: Enhanced JavaScript to properly destroy Jupiter's Pagination component
* Improved: Better prevention of MK.component re-initialization
* Fixed: More comprehensive removal of pagination event handlers
* Fixed: Added destruction of Jupiter component instances for better compatibility across sites

= 1.4.3 =
* Fixed: Current page styling now works correctly on sites with fewer than 8 pages
* Fixed: Plugin now properly corrects Jupiter's current-page class when it's on the wrong element
* Improved: Added fix_current_page_element() function to handle current page detection for non-sliding-window pagination

= 1.4.2 =
* Added: GitHub auto-updater for automatic plugin updates
* Added: Comprehensive documentation (README.md, RELEASE.md)
* Improved: Better integration with SEO plugins for rel=prev/next placement

= 1.4.1 =
* Cleanup: Removed debug comments from output
* Improved: rel=prev/next links output at priority 35 to group with canonical tags

= 1.4.0 =
* NEW: Added rel=prev/next links to `<head>` for improved SEO
* Feature: Automatically adds `<link rel="prev">` for previous page
* Feature: Automatically adds `<link rel="next">` for next page
* Feature: Works on native WP blog pages AND page builder blog modules (WPBakery)
* Feature: Smart detection of pagination in page builder modules
* SEO: Helps search engines understand pagination structure
* SEO: Improves indexing of paginated content

= 1.3.1 =
* Fixed: Improved current page detection with more robust XPath queries
* Fixed: Changed initial current_page value from 1 to 0 for better fallback detection
* Fixed: Added explicit integer type casting throughout comparison logic
* Added: Debug HTML comments showing detected current page and pages in window (temporary for troubleshooting)
* Improved: Better handling of edge cases in current page detection

= 1.3.0 =
* MAJOR FIX: Current page styling now works correctly on all pages (including pages 6+)
* MAJOR FIX: Ellipsis (...) is now a clickable link to the next sequential page
* MAJOR FIX: Previous and Next arrow buttons now work with proper URLs
* Fixed: Current page detection properly handles pages that don't exist in Jupiter's initial HTML
* Fixed: Newly created page links (for pages 9+) correctly identify if they're the current page
* Improved: JavaScript now removes AJAX handlers from prev/next arrows
* Improved: Better handling of element type checking (link vs span) in sliding window
* Note: Ellipsis links to the page immediately after the last shown page (e.g., "8 ... 14" links to page 9)

= 1.2.0 =
* MAJOR FIX: Sliding window now creates pagination links for all pages in the window
* Fixed: Jupiter only outputs 8 pages in HTML, now plugin generates missing page links
* Fixed: Last page now properly displays with ellipsis when outside window
* Fixed: Total pages detection now uses `data-max-pages` attribute (most reliable)
* Fixed: Current page spans scoped to only `mk-pagination-inner` (not `mk-total-pages`)
* Fixed: Prevents `page-number` class from being added to page counter display
* Improved: Multi-fallback for total pages (data attribute, max-pages span, link parsing)
* Improved: Current page detection uses `mk-total-pages` span for better accuracy

= 1.1.2 =
* Fixed: Current page detection now uses Jupiter's `data-init-pagination` attribute
* Fixed: Current page detection also checks `pagination-current-page` span for accuracy
* Fixed: Properly removes `current-page` class from non-current pages
* Fixed: Filters out ellipsis links with "..." text content to prevent duplicates
* Fixed: Current page is now correctly converted to span with proper styling
* Improved: Multi-method current page detection (data attribute, page span, WordPress query var)

= 1.1.1 =
* Fixed: Multiple elements getting `current-page` class
* Fixed: Duplicate ellipsis elements (both span and link appearing)
* Fixed: Sliding window now properly handles both links and current page spans
* Fixed: Improved current page detection and styling preservation
* Improved: Sliding window now excludes ellipsis links (data-page-id="...")
* Improved: Better cleanup of pagination elements before rebuilding

= 1.1.0 =
* BREAKING CHANGE: Completely disables AJAX pagination functionality
* Removes `js-pagination-page` class from pagination links
* Removes `data-page-id` attributes from pagination links
* Adds JavaScript to prevent Jupiter's AJAX handlers from firing
* Changes ellipsis from clickable links to non-clickable spans
* Properly handles current page styling - converts current page to span with `current-page` class
* Ensures `page-number` and `current-page` classes are preserved for proper styling
* Pagination now works as standard page navigation (full page reloads)
* Better for SEO - eliminates AJAX-related issues

= 1.0.0 =
* Initial release
* SEO-friendly URL generation for Jupiter pagination
* Sliding window pagination (8 pages)
* Maintained Jupiter AJAX functionality (now removed in 1.1.0)
* Automatic detection and activation

== Upgrade Notice ==

= 1.4.6 =
Improved compatibility: Better handling of Jupiter Pagination component destruction for consistent behavior across different sites.

= 1.3.0 =
Critical update: Fixes current page styling on all pages, makes ellipsis clickable, and enables prev/next arrows. Highly recommended.

= 1.2.0 =
Major update: Fixes sliding window pagination to show all pages properly, including last page with ellipsis. Fixes styling issues with page counter.

= 1.1.2 =
Critical bug fix: Corrects current page detection and styling. Recommended for all users.

= 1.1.1 =
Bug fix release: Fixes duplicate current-page classes and duplicate ellipsis elements in pagination.

= 1.1.0 =
BREAKING CHANGE: This version completely disables AJAX pagination. Pagination will now work as standard page navigation with full page reloads for better SEO.

= 1.0.0 =
Initial release of SEO Friendly Jupiter Pagination.

