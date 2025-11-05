# SEO Friendly Jupiter Pagination

Converts Jupiter theme's AJAX pagination to SEO-friendly standard pagination with proper URLs. Completely disables AJAX pagination for better SEO and adds rel=prev/next links to head.

## Description

SEO Friendly Jupiter Pagination fixes a critical SEO issue in the Jupiter WordPress theme (by Artbees) where pagination links use only `href="#"` with data attributes for AJAX functionality. While this provides a smooth user experience, it creates serious SEO problems.

### The Problem

The Jupiter theme outputs pagination like this:

```html
<a class="page-number js-pagination-page" href="#" data-page-id="2">2</a>
```

This causes:
- Search engines can't crawl pagination links
- Users can't bookmark specific pages
- Social media shares don't work properly
- Page URLs don't reflect the actual page number
- Duplicate content issues

### The Solution

This plugin automatically converts pagination to proper SEO-friendly URLs and completely disables AJAX:

```html
<a class="page-number" href="/page/2/">2</a>
```

Plus adds proper rel=prev/next links to the `<head>` section:

```html
<link rel="prev" href="https://example.com/blog/" />
<link rel="next" href="https://example.com/blog/page/3/" />
```

## Key Benefits

- **SEO-friendly URLs** that search engines can crawl
- **rel=prev/next links** in `<head>` for proper pagination indexing
- **Bookmarkable pages**
- **Social media shareable**
- **Disables AJAX pagination** for standard page loads (better for SEO)
- **Removes all AJAX-related classes** and attributes
- **Sliding window pagination** (shows only 8 pages to prevent overwhelming pagination)
- **No theme modification required**
- **Automatic detection** - only activates when Jupiter pagination is present

## Features

- Automatically detects Jupiter theme pagination
- Converts `#` links to proper `/page/X/` URLs
- Adds `<link rel="prev">` and `<link rel="next">` tags to `<head>` for SEO
- Completely disables AJAX pagination functionality
- Removes `js-pagination-page` class and `data-page-id` attributes
- Sliding window pagination (shows only 8 pages around current page)
- Makes pagination work as standard page navigation
- Works with all Jupiter theme versions that use AJAX pagination
- **Works with both native WP blog pages AND page builder blog modules** (WPBakery/Visual Composer)
- No configuration needed - works automatically

## Installation

### From GitHub

1. Download the latest release from the [Releases page](https://github.com/webfor/seo-friendly-jupiter-pagination/releases)
2. Upload the plugin files to the `/wp-content/plugins/seo-friendly-jupiter-pagination` directory
3. Activate the plugin through the 'Plugins' screen in WordPress
4. That's it! The plugin works automatically with no configuration needed

### Manual Installation

1. Clone this repository or download the ZIP file
2. Upload to `/wp-content/plugins/seo-friendly-jupiter-pagination`
3. Activate the plugin through the 'Plugins' screen in WordPress

## Frequently Asked Questions

### Does this disable Jupiter's AJAX pagination?

Yes, completely. The plugin removes all AJAX-related classes (`js-pagination-page`) and attributes (`data-page-id`), and adds JavaScript to prevent Jupiter's AJAX handlers from firing. Pagination will work as standard page navigation with full page reloads. This is intentional for better SEO.

### Will this work with my Jupiter theme version?

Yes. The plugin works with any Jupiter theme version that uses the AJAX pagination system with `.mk-pagination-inner` and `.js-pagination-page` classes.

### Does it work with page builder blog modules?

Yes! The plugin works with both:
- Native WordPress blog pages (set via Reading Settings)
- Page builder blog modules (WPBakery/Visual Composer on regular pages)

### Do I need to configure anything?

No. Once activated, the plugin works automatically. There are no settings to configure.

### What is the sliding window?

When you have many pages (more than 8), the plugin shows a "window" of 8 pages around your current page, plus the first and last pages. For example, if you're on page 15 of 50, you'll see:

```
1 ... 11 12 13 14 15 16 17 18 ... 50
```

This prevents overwhelming pagination displays while keeping the navigation intuitive.

### Can I change the number of pages shown?

Yes. Edit line 24 in the plugin file and change `private $pages_to_show = 8;` to your preferred number.

### Will this affect my site's performance?

The plugin uses efficient DOM manipulation and only activates when Jupiter pagination is detected. The performance impact is minimal.

### Does this work with custom post types?

Yes. The plugin works with any content that uses Jupiter's pagination system, including custom post types, archives, and search results.

## How It Works

The plugin uses WordPress output buffering, DOM manipulation, and JavaScript to:

1. Detect Jupiter pagination HTML (`.mk-pagination-inner` class)
2. Parse the `data-page-id` attributes to understand page structure
3. Generate proper SEO-friendly URLs for each page
4. Apply sliding window logic (8 pages visible)
5. Remove AJAX classes (`js-pagination-page`) and attributes (`data-page-id`)
6. Inject JavaScript to disable Jupiter's AJAX event handlers
7. Make pagination work as normal page links
8. Add `rel=prev/next` links to `<head>` for search engines

## Compatibility

Tested with:
- Jupiter theme versions 5.x - 6.x
- WordPress 5.0 - 6.8+
- PHP 5.6 - 8.2

## Changelog

### 1.4.1
- Cleanup: Removed debug comments from output
- Improved: rel=prev/next links output at priority 35 to group with canonical tags

### 1.4.0
- NEW: Added rel=prev/next links to `<head>` for improved SEO
- Feature: Automatically adds `<link rel="prev">` for previous page
- Feature: Automatically adds `<link rel="next">` for next page
- Feature: Works on native WP blog pages AND page builder blog modules (WPBakery)
- Feature: Smart detection of pagination in page builder modules
- SEO: Helps search engines understand pagination structure
- SEO: Improves indexing of paginated content

### 1.3.1
- Fixed: Improved current page detection with more robust XPath queries
- Fixed: Changed initial current_page value from 1 to 0 for better fallback detection
- Fixed: Added explicit integer type casting throughout comparison logic
- Improved: Better handling of edge cases in current page detection

### 1.3.0
- MAJOR FIX: Current page styling now works correctly on all pages (including pages 6+)
- MAJOR FIX: Ellipsis (...) is now a clickable link to the next sequential page
- MAJOR FIX: Previous and Next arrow buttons now work with proper URLs
- Fixed: Current page detection properly handles pages that don't exist in Jupiter's initial HTML
- Improved: JavaScript now removes AJAX handlers from prev/next arrows
- Improved: Better handling of element type checking (link vs span) in sliding window

### 1.2.0
- MAJOR FIX: Sliding window now creates pagination links for all pages in the window
- Fixed: Jupiter only outputs 8 pages in HTML, now plugin generates missing page links
- Fixed: Last page now properly displays with ellipsis when outside window
- Fixed: Total pages detection now uses `data-max-pages` attribute (most reliable)
- Improved: Multi-fallback for total pages detection

### 1.1.2
- Fixed: Current page detection now uses Jupiter's `data-init-pagination` attribute
- Fixed: Current page detection also checks `pagination-current-page` span for accuracy
- Fixed: Properly removes `current-page` class from non-current pages

### 1.1.1
- Fixed: Multiple elements getting `current-page` class
- Fixed: Duplicate ellipsis elements
- Fixed: Sliding window now properly handles both links and current page spans

### 1.1.0
- BREAKING CHANGE: Completely disables AJAX pagination functionality
- Removes `js-pagination-page` class from pagination links
- Removes `data-page-id` attributes from pagination links
- Adds JavaScript to prevent Jupiter's AJAX handlers from firing
- Properly handles current page styling

### 1.0.0
- Initial release
- SEO-friendly URL generation for Jupiter pagination
- Sliding window pagination (8 pages)
- Automatic detection and activation

## Support

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/webfor/seo-friendly-jupiter-pagination/issues) page.

## Credits

- **Author:** Webfor Agency
- **Author URI:** [https://webfor.com](https://webfor.com)
- **License:** GPL v2 or later
- **License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

## Automatic Updates

This plugin supports automatic updates from GitHub releases. Once activated, you'll receive update notifications in your WordPress admin panel when new versions are available.

### How It Works

1. The plugin checks GitHub for new releases
2. When a new version is detected, you'll see an update notification in WordPress
3. Click "Update Now" to install the latest version
4. The plugin updates automatically without manual download

### For Developers

To create a new release:

1. Update version numbers in plugin files
2. Create a Git tag (e.g., `v1.4.2`)
3. Push the tag to GitHub
4. Create a release on GitHub

See [RELEASE.md](RELEASE.md) for detailed release instructions.

