<?php
/**
 * Plugin Name: SEO Friendly Jupiter Pagination
 * Plugin URI: https://webfor.com
 * Description: Converts Jupiter theme's AJAX pagination to SEO-friendly standard pagination with proper URLs. Disables AJAX functionality and makes pagination work as normal links. Includes sliding window to show only 8 pages. Adds rel=prev/next links to head for SEO.
 * Version: 1.4.3
 * Author: Webfor Agency
 * Author URI: https://webfor.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: seo-friendly-jupiter-pagination
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load Plugin Update Checker library
require_once plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Initialize GitHub updater
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/markfenske84/seo-friendly-jupiter-pagination/',
    __FILE__,
    'seo-friendly-jupiter-pagination'
);

// Optional: Set the branch that contains the stable release
$myUpdateChecker->setBranch('main');

// Optional: If your repository is private, specify an access token
// $myUpdateChecker->setAuthentication('your-token-here');

class SEO_Friendly_Jupiter_Pagination {
    
    /**
     * Number of pages to show in sliding window
     */
    private $pages_to_show = 8;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into the_content with high priority to catch pagination
        add_filter('the_content', array($this, 'fix_jupiter_pagination'), 999);
        
        // Also hook into loop_end to catch pagination that appears after content
        add_action('loop_end', array($this, 'start_output_buffer'));
        add_action('wp_footer', array($this, 'fix_pagination_buffer'), 1);
        
        // Enqueue JavaScript to disable Jupiter AJAX pagination
        add_action('wp_enqueue_scripts', array($this, 'enqueue_disable_ajax_script'));
        
        // Add rel=prev/next links to <head> for SEO (priority 35 to group with Yoast canonical)
        add_action('wp_head', array($this, 'add_rel_prev_next_links'), 35);
    }
    
    /**
     * Enqueue script to disable Jupiter's AJAX pagination
     */
    public function enqueue_disable_ajax_script() {
        // Add inline script to disable Jupiter AJAX pagination
        wp_add_inline_script('jquery', "
            jQuery(document).ready(function($) {
                // Remove Jupiter's AJAX pagination event handlers
                $(document).off('click', '.js-pagination-page');
                $(document).off('click', '.mk-pagination-inner a');
                $(document).off('click', '.js-pagination-prev');
                $(document).off('click', '.js-pagination-next');
                
                // Prevent any delegated events on pagination
                $('.mk-pagination-inner').off('click');
                $('.mk-pagination-previous').off('click');
                $('.mk-pagination-next').off('click');
                
                // Remove the js-pagination-page class to prevent Jupiter from targeting these links
                $('.js-pagination-page').removeClass('js-pagination-page');
                $('.js-pagination-prev').removeClass('js-pagination-prev');
                $('.js-pagination-next').removeClass('js-pagination-next');
                
                // Ensure all pagination links work as normal links (including ellipsis and arrows)
                $('.mk-pagination-inner a, .mk-pagination-previous, .mk-pagination-next').off('click').on('click', function(e) {
                    // Don't prevent default - let the link work normally
                    if ($(this).attr('href') && $(this).attr('href') !== '#') {
                        // Allow normal navigation
                        return true;
                    } else {
                        // Only prevent default if href is # or empty
                        e.preventDefault();
                        return false;
                    }
                });
            });
        ");
    }
    
    /**
     * Add rel=prev/next links to <head> for SEO
     */
    public function add_rel_prev_next_links() {
        // Get current page number from URL
        $paged = get_query_var('paged');
        if (!$paged) {
            $paged = get_query_var('page'); // For static front page
        }
        $current_page = max(1, intval($paged));
        
        // Get total pages from the main query (works for native WP blog)
        global $wp_query;
        $total_pages = isset($wp_query->max_num_pages) ? intval($wp_query->max_num_pages) : 0;
        
        // Try to get cached total pages from Jupiter pagination (set during HTML processing)
        // This is needed for page builder modules where $wp_query doesn't have pagination info
        $cached_total = get_transient('seo_jupiter_pagination_total_' . get_the_ID());
        if ($cached_total && $cached_total > $total_pages) {
            $total_pages = intval($cached_total);
        }
        
        // For page builder modules: if we're on page 1 and total is 0/1, check if pagination will exist
        if ($current_page === 1 && $total_pages <= 1) {
            // Check if the page might have Jupiter pagination by looking for common indicators
            global $post;
            if ($post && (
                strpos($post->post_content, 'mk_blog') !== false ||
                strpos($post->post_content, 'vc_row') !== false ||
                strpos($post->post_content, '[blog') !== false
            )) {
                // Page likely has pagination, continue to output rel=next
            } else {
                // No pagination detected, skip
                return;
            }
        }
        
        // If we're on page 2+ but total_pages is unknown, we can at least show prev
        if ($current_page > 1 && $total_pages < $current_page) {
            $total_pages = $current_page;
        }
        
        // Get the base URL for pagination
        $base_url = $this->get_pagination_base_url();
        
        // Add rel="prev" link if not on first page
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            if ($prev_page === 1) {
                // First page uses base URL without /page/1/
                $prev_url = trailingslashit($base_url);
            } else {
                $prev_url = trailingslashit($base_url) . 'page/' . $prev_page . '/';
            }
            echo '<link rel="prev" href="' . esc_url($prev_url) . '" />' . "\n";
        }
        
        // Add rel="next" link if not on last page (or if page 1 with potential pagination)
        if ($current_page === 1 || ($total_pages > 0 && $current_page < $total_pages)) {
            $next_page = $current_page + 1;
            $next_url = trailingslashit($base_url) . 'page/' . $next_page . '/';
            echo '<link rel="next" href="' . esc_url($next_url) . '" />' . "\n";
        }
    }
    
    /**
     * Start output buffering to catch pagination
     */
    public function start_output_buffer($query) {
        if ($query->is_main_query() && !is_admin()) {
            ob_start(array($this, 'fix_jupiter_pagination'));
        }
    }
    
    /**
     * Process the output buffer
     */
    public function fix_pagination_buffer() {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
    
    /**
     * Fix Jupiter pagination HTML
     */
    public function fix_jupiter_pagination($content) {
        // Check if this is Jupiter pagination
        if (strpos($content, 'mk-pagination-inner') === false && 
            strpos($content, 'js-pagination-page') === false) {
            return $content;
        }
        
        // Get the base URL for pagination
        $base_url = $this->get_pagination_base_url();
        
        // Use DOMDocument to parse and modify the HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        
        // Handle libxml constants that may not be available in all PHP versions
        $loadhtml_options = 0;
        if (defined('LIBXML_HTML_NOFRAG')) {
            $loadhtml_options |= LIBXML_HTML_NOFRAG;
        }
        if (defined('LIBXML_HTML_NODEFDTD')) {
            $loadhtml_options |= LIBXML_HTML_NODEFDTD;
        }
        
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content, $loadhtml_options);
        libxml_clear_errors();
        
        // Find all pagination links and current page elements
        $xpath = new DOMXPath($dom);
        
        // Detect current page from Jupiter's data attributes or page number display
        $current_page = 0;
        
        // Method 1: Check data-init-pagination attribute on pagination container
        $pagination_containers = $xpath->query('//*[contains(@class, "mk-pagination") and @data-init-pagination]');
        if ($pagination_containers->length > 0) {
            $init_page = $pagination_containers->item(0)->getAttribute('data-init-pagination');
            if ($init_page && $init_page !== '' && $init_page !== '0') {
                $current_page = intval($init_page);
            }
        }
        
        // Method 2: Check the pagination-current-page span (in mk-total-pages)
        if ($current_page === 0) {
            $current_page_display = $xpath->query('//*[contains(@class, "mk-total-pages")]//*[contains(@class, "pagination-current-page") or contains(@class, "js-current-page")]');
            if ($current_page_display->length > 0) {
                $page_text = trim($current_page_display->item(0)->textContent);
                if ($page_text && $page_text !== '' && $page_text !== '0') {
                    $current_page = intval($page_text);
                }
            }
        }
        
        // Method 3: Fallback to WordPress query var
        if ($current_page === 0) {
            $paged_var = get_query_var('paged');
            if (!$paged_var) {
                $paged_var = get_query_var('page'); // For static front page
            }
            $current_page = max(1, intval($paged_var));
        }
        
        // Ensure we have a valid page number
        if ($current_page === 0) {
            $current_page = 1;
        }
        
        // Ensure current_page is always an integer
        $current_page = intval($current_page);
        
        $links = $xpath->query('//a[@data-page-id]');
        
        // Also find current page span elements ONLY within pagination-inner (not mk-total-pages)
        $current_page_spans = $xpath->query('//*[contains(@class, "mk-pagination-inner")]//*[contains(@class, "current-page")]');
        
        if ($links->length === 0 && $current_page_spans->length === 0) {
            return $content;
        }
        
        // Get total pages from data-max-pages attribute (most reliable)
        $total_pages = 1;
        $max_pages_attr = $pagination_containers->length > 0 ? $pagination_containers->item(0)->getAttribute('data-max-pages') : '';
        if ($max_pages_attr) {
            $total_pages = intval($max_pages_attr);
        }
        
        // Fallback: Get from the highest data-page-id
        if ($total_pages === 1) {
            foreach ($links as $link) {
                $page_id = intval($link->getAttribute('data-page-id'));
                if ($page_id > $total_pages) {
                    $total_pages = $page_id;
                }
            }
        }
        
        // Another fallback: Check pagination-max-pages span
        if ($total_pages === 1) {
            $max_pages_span = $xpath->query('//*[contains(@class, "pagination-max-pages")]');
            if ($max_pages_span->length > 0) {
                $max_text = trim($max_pages_span->item(0)->textContent);
                if ($max_text) {
                    $total_pages = intval($max_text);
                }
            }
        }
        
        // Cache total pages for use in wp_head rel=prev/next links
        // This is needed for page builder modules where $wp_query doesn't have pagination info
        if ($total_pages > 1 && get_the_ID()) {
            set_transient('seo_jupiter_pagination_total_' . get_the_ID(), $total_pages, 1 * HOUR_IN_SECONDS);
        }
        
        // Apply sliding window if needed
        if ($total_pages > $this->pages_to_show) {
            $content = $this->apply_sliding_window($content, $current_page, $total_pages);
            
            // Re-parse after sliding window
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="UTF-8">' . $content, $loadhtml_options);
            libxml_clear_errors();
            $xpath = new DOMXPath($dom);
            $links = $xpath->query('//a[@data-page-id]');
            
            // Also re-query for current page spans after sliding window (only in pagination-inner)
            $current_page_spans = $xpath->query('//*[contains(@class, "mk-pagination-inner")]//*[contains(@class, "current-page")]');
        } else {
            // No sliding window needed, but we still need to fix the current page element
            // Jupiter sometimes marks the wrong page as current, so we need to fix it
            $content = $this->fix_current_page_element($content, $current_page, $loadhtml_options);
            
            // Re-parse after fixing current page
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="UTF-8">' . $content, $loadhtml_options);
            libxml_clear_errors();
            $xpath = new DOMXPath($dom);
            $links = $xpath->query('//a[@data-page-id]');
            
            // Also re-query for current page spans after fixing
            $current_page_spans = $xpath->query('//*[contains(@class, "mk-pagination-inner")]//*[contains(@class, "current-page")]');
        }
        
        // Handle current page elements (spans) - ensure they keep proper styling
        foreach ($current_page_spans as $current_span) {
            // Remove AJAX-related classes but keep current-page class for styling
            $classes = $current_span->getAttribute('class');
            $classes = str_replace('js-pagination-page', '', $classes);
            $classes = trim(preg_replace('/\s+/', ' ', $classes));
            
            // Ensure current-page class is present
            if (strpos($classes, 'current-page') === false) {
                $classes .= ' current-page';
            }
            // Also add page-number class if not present for consistent styling
            if (strpos($classes, 'page-number') === false) {
                $classes .= ' page-number';
            }
            
            $current_span->setAttribute('class', trim($classes));
            
            // Remove data-page-id if present
            if ($current_span->hasAttribute('data-page-id')) {
                $current_span->removeAttribute('data-page-id');
            }
        }
        
        // Update prev/next arrows
        $prev_arrows = $xpath->query('//*[contains(@class, "js-pagination-prev") or contains(@class, "mk-pagination-previous")]');
        foreach ($prev_arrows as $prev_arrow) {
            if ($current_page > 1) {
                $prev_page = $current_page - 1;
                $prev_url = ($prev_page === 1) ? $base_url : trailingslashit($base_url) . 'page/' . $prev_page . '/';
                $prev_arrow->setAttribute('href', esc_url($prev_url));
            }
            // Remove AJAX class
            $classes = $prev_arrow->getAttribute('class');
            $classes = str_replace('js-pagination-prev', '', $classes);
            $prev_arrow->setAttribute('class', trim($classes));
        }
        
        $next_arrows = $xpath->query('//*[contains(@class, "js-pagination-next") or contains(@class, "mk-pagination-next")]');
        foreach ($next_arrows as $next_arrow) {
            if ($current_page < $total_pages) {
                $next_page = $current_page + 1;
                $next_url = trailingslashit($base_url) . 'page/' . $next_page . '/';
                $next_arrow->setAttribute('href', esc_url($next_url));
            }
            // Remove AJAX class
            $classes = $next_arrow->getAttribute('class');
            $classes = str_replace('js-pagination-next', '', $classes);
            $next_arrow->setAttribute('class', trim($classes));
        }
        
        // Update all pagination links with proper URLs and disable AJAX
        // Note: After sliding window, current page should already be a span, not a link
        foreach ($links as $link) {
            $page_id = $link->getAttribute('data-page-id');
            
            // Skip ellipsis links - they're handled separately
            if (!$page_id || $page_id === '...') {
                continue;
            }
            
            $page_num = intval($page_id);
            if ($page_num <= 0) {
                continue;
            }
            
            // Generate proper URL
            if ($page_num === 1) {
                $page_url = $base_url;
            } else {
                $page_url = trailingslashit($base_url) . 'page/' . $page_num . '/';
            }
            
            // Update href with proper URL
            $link->setAttribute('href', esc_url($page_url));
            
            // Remove AJAX-related classes and attributes
            $classes = $link->getAttribute('class');
            $classes = str_replace('js-pagination-page', '', $classes);
            $classes = trim(preg_replace('/\s+/', ' ', $classes));
            $link->setAttribute('class', $classes);
            
            // Remove data-page-id to fully disable AJAX
            $link->removeAttribute('data-page-id');
        }
        
        // Update ellipsis links with proper URLs
        $ellipsis_links = $xpath->query('//a[contains(@class, "dots") or @data-page-id="..."]');
        foreach ($ellipsis_links as $ellipsis) {
            $ellipsis_page = $ellipsis->getAttribute('data-ellipsis-page');
            if ($ellipsis_page) {
                $page_num = intval($ellipsis_page);
                $ellipsis_url = trailingslashit($base_url) . 'page/' . $page_num . '/';
                $ellipsis->setAttribute('href', esc_url($ellipsis_url));
                $ellipsis->removeAttribute('data-ellipsis-page');
                $ellipsis->removeAttribute('data-page-id');
            }
        }
        
        // Get the modified HTML
        $modified_content = $dom->saveHTML();
        
        // Remove XML encoding declaration that was added
        $modified_content = str_replace('<?xml encoding="UTF-8">', '', $modified_content);
        
        return $modified_content;
    }
    
    /**
     * Apply sliding window to pagination
     */
    private function apply_sliding_window($content, $current_page, $total_pages) {
        // Sliding window configuration
        $half_pages = floor($this->pages_to_show / 2);
        
        // Calculate window
        $start_page = max(1, $current_page - $half_pages);
        $end_page = min($total_pages, $start_page + $this->pages_to_show - 1);
        
        // Adjust start if we're near the end
        if ($end_page - $start_page < $this->pages_to_show - 1) {
            $start_page = max(1, $end_page - $this->pages_to_show + 1);
        }
        
        // Parse the HTML to find pagination container
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        
        // Handle libxml constants that may not be available in all PHP versions
        $loadhtml_options = 0;
        if (defined('LIBXML_HTML_NOFRAG')) {
            $loadhtml_options |= LIBXML_HTML_NOFRAG;
        }
        if (defined('LIBXML_HTML_NODEFDTD')) {
            $loadhtml_options |= LIBXML_HTML_NODEFDTD;
        }
        
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content, $loadhtml_options);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $pagination_container = $xpath->query('//*[contains(@class, "mk-pagination-inner")]')->item(0);
        
        if (!$pagination_container) {
            return $content;
        }
        
        // Get all pagination elements (links AND current page spans)
        $all_links = $xpath->query('.//a[@data-page-id]', $pagination_container);
        $all_current_spans = $xpath->query('.//*[contains(@class, "current-page")]', $pagination_container);
        
        // Combine into a single array with page numbers
        $all_elements = array();
        
        // Add links (but skip ellipsis links with data-page-id="..." or text content "...")
        foreach ($all_links as $link) {
            $page_id_attr = $link->getAttribute('data-page-id');
            $link_text = trim($link->textContent);
            
            // Skip ellipsis links
            if ($page_id_attr === '...' || $link_text === '...' || $link_text === '…') {
                continue;
            }
            
            if ($page_id_attr !== '') {
                $page_num = intval($page_id_attr);
                if ($page_num > 0) {
                    $all_elements[$page_num] = $link;
                }
            }
        }
        
        // Add current page spans
        foreach ($all_current_spans as $span) {
            // Try to get page number from text content or data attribute
            $page_id_attr = $span->getAttribute('data-page-id');
            if ($page_id_attr && $page_id_attr !== '...') {
                $page_num = intval($page_id_attr);
            } else {
                // Get page number from text content
                $page_num = intval(trim($span->textContent));
            }
            
            if ($page_num > 0) {
                // Current page span takes precedence over link
                $all_elements[$page_num] = $span;
            }
        }
        
        // Sort by page number
        ksort($all_elements);
        
        // Build array of pages to keep
        $pages_to_keep = array();
        
        // Always show first page if not in window
        if ($start_page > 1) {
            $pages_to_keep[] = 1;
        }
        
        // Add sliding window pages
        for ($i = $start_page; $i <= $end_page; $i++) {
            $pages_to_keep[] = $i;
        }
        
        // Always show last page if not in window
        if ($end_page < $total_pages) {
            $pages_to_keep[] = $total_pages;
        }
        
        // Build final array with ellipsis and convert current page to span
        // Create links for pages that don't exist in Jupiter's HTML
        $prev_page_shown = 0;
        $links_array = array();
        
        foreach ($pages_to_keep as $page_num) {
            // Ensure page_num is an integer for proper comparison
            $page_num = intval($page_num);
            
            // Check if we need ellipsis before this page
            if ($prev_page_shown > 0 && $page_num > $prev_page_shown + 1) {
                // Add ellipsis as clickable link to the next page after prev_page_shown
                $ellipsis_target_page = $prev_page_shown + 1;
                $ellipsis = $dom->createElement('a');
                $ellipsis->setAttribute('class', 'page-number dots');
                $ellipsis->setAttribute('href', '#'); // Will be fixed by main function
                $ellipsis->setAttribute('data-ellipsis-page', strval($ellipsis_target_page)); // Store target page
                $ellipsis->nodeValue = '...';
                $links_array[] = $ellipsis;
            }
            
            // Check if we have an existing element for this page
            $element = isset($all_elements[$page_num]) ? $all_elements[$page_num] : null;
            
            // If this is the current page, always create/convert to span
            // Force strict type comparison
            if (intval($page_num) === intval($current_page)) {
                $span = $dom->createElement('span');
                $span->nodeValue = strval($page_num);
                $span->setAttribute('class', 'page-number current-page');
                $links_array[] = $span;
            } else {
                // NOT the current page - create or use existing link
                if ($element) {
                    // Element exists in Jupiter's HTML
                    if ($element->nodeName === 'a') {
                        // It's a link, use it and clean up classes
                        $classes = $element->getAttribute('class');
                        $classes = str_replace('current-page', '', $classes);
                        $classes = str_replace('js-pagination-page', '', $classes);
                        $classes = trim(preg_replace('/\s+/', ' ', $classes));
                        $element->setAttribute('class', $classes);
                        $links_array[] = $element;
                    } else {
                        // It's a span (shouldn't happen for non-current pages, but handle it)
                        // Convert span to link
                        $new_link = $dom->createElement('a');
                        $new_link->nodeValue = $element->nodeValue;
                        $new_link->setAttribute('class', 'page-number');
                        $new_link->setAttribute('href', '#');
                        $new_link->setAttribute('data-page-id', strval($page_num));
                        $links_array[] = $new_link;
                    }
                } else {
                    // Page doesn't exist in Jupiter's HTML - create new link
                    $new_link = $dom->createElement('a');
                    $new_link->nodeValue = strval($page_num);
                    $new_link->setAttribute('class', 'page-number');
                    $new_link->setAttribute('href', '#'); // Will be fixed by main function
                    $new_link->setAttribute('data-page-id', strval($page_num)); // So main function can find and fix it
                    $links_array[] = $new_link;
                }
            }
            
            $prev_page_shown = $page_num;
        }
        
        // Remove ALL existing pagination elements (links, spans, ellipsis)
        foreach ($all_links as $link) {
            if ($link->parentNode) {
                $link->parentNode->removeChild($link);
            }
        }
        foreach ($all_current_spans as $span) {
            if ($span->parentNode) {
                $span->parentNode->removeChild($span);
            }
        }
        
        // Also remove any existing ellipsis elements
        $existing_ellipsis = $xpath->query('.//*[contains(@class, "dots")]', $pagination_container);
        foreach ($existing_ellipsis as $el) {
            if ($el->parentNode) {
                $el->parentNode->removeChild($el);
            }
        }
        
        // Add back only the elements we want in the correct order
        foreach ($links_array as $element) {
            $pagination_container->appendChild($element->cloneNode(true));
        }
        
        // Get the modified HTML
        $modified_content = $dom->saveHTML();
        
        // Remove XML encoding declaration
        $modified_content = str_replace('<?xml encoding="UTF-8">', '', $modified_content);
        
        return $modified_content;
    }
    
    /**
     * Fix current page element when not using sliding window
     * Jupiter sometimes marks the wrong page as current, so we need to correct it
     */
    private function fix_current_page_element($content, $current_page, $loadhtml_options) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content, $loadhtml_options);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $pagination_container = $xpath->query('//*[contains(@class, "mk-pagination-inner")]')->item(0);
        
        if (!$pagination_container) {
            return $content;
        }
        
        // Remove current-page class from ALL elements in pagination-inner
        $all_with_current = $xpath->query('.//*[contains(@class, "current-page")]', $pagination_container);
        foreach ($all_with_current as $element) {
            $classes = $element->getAttribute('class');
            $classes = str_replace('current-page', '', $classes);
            $classes = trim(preg_replace('/\s+/', ' ', $classes));
            $element->setAttribute('class', $classes);
        }
        
        // Find the element that should be current (link with matching data-page-id)
        $current_link = null;
        $all_links = $xpath->query('.//a[@data-page-id]', $pagination_container);
        foreach ($all_links as $link) {
            $page_id = intval($link->getAttribute('data-page-id'));
            if ($page_id === intval($current_page)) {
                $current_link = $link;
                break;
            }
        }
        
        if ($current_link) {
            // Convert the link to a span with current-page class
            $span = $dom->createElement('span');
            $span->nodeValue = trim($current_link->textContent);
            $span->setAttribute('class', 'page-number current-page');
            
            // Replace the link with the span
            $current_link->parentNode->replaceChild($span, $current_link);
        }
        
        // Get the modified HTML
        $modified_content = $dom->saveHTML();
        
        // Remove XML encoding declaration
        $modified_content = str_replace('<?xml encoding="UTF-8">', '', $modified_content);
        
        return $modified_content;
    }
    
    /**
     * Get the base URL for pagination
     */
    private function get_pagination_base_url() {
        global $wp;
        
        // Get current URL without pagination
        $current_url = home_url($wp->request);
        
        // Remove any existing /page/X/ from URL
        $base_url = preg_replace('/\/page\/\d+\/?/', '', $current_url);
        
        // Remove trailing slash
        $base_url = untrailingslashit($base_url);
        
        return $base_url;
    }
}

// Initialize the plugin
new SEO_Friendly_Jupiter_Pagination();

