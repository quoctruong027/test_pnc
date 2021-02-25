=== WP RAISER ===
Contributors: Alignak
Tags: Lighthouse, GTmetrix, Pingdom, Pagespeed, CSS Merging, JS Merging, CSS Minification, JS Minification, Speed Optimization, HTML Minification
Requires at least: 4.9
Requires PHP: 5.6
Stable tag: 4.1.7
Tested up to: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Improve your speed score on GTmetrix, Pingdom Tools and Google PageSpeed Insights by merging and minifying CSS, JavaScript and HTML, page caching, loading CSS async and a few more options. 
 

== Description ==

Speed optimization plugin for WordPress.

= WP-CLI Commands =
*	Purge all caches: `wp wpraiser purge`
*	Purge all caches on a network site: `wp --url=blog.example.com wpraiser purge`
*	Purge all caches on the entire network (linux): `wp site list --field=url | xargs -n1 -I % wp --url=% wpraiser purge`

== Changelog ==

= 4.1.7 [2020.12.10] =
* JS exclusions fixes

= 4.1.6 [2020.12.02] =
* Minor CDN Optimization fixes
* Admin UI Improvements
* Added Plugin Filter Capabilities

= 4.1.5 [2020.11.26] =
* Charset bugfix

= 4.1.4 [2020.11.25] =
* Admin UI Improvements
* Added ignore list to the CSS Optimization settings.
* Added user roles option to HTML, CSS Optimization, Lazy Loading and CDN settings.
* Added some CDN rewrite improvements for inline styles.
* Added Picture elements performance improvements

= 4.1.3 [2020.11.18] =
* Performance Improvements

= 4.1.2 [2020.10.25] =
* Bug fixes
* UI Improvements
* Performance Improvements