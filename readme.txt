=== OAS ToolBox ===
Contributors: Online Associates, UAE
Donate link: 
Tags: developer, toolbox, wpx, oas
Requires at least: 2.7
Tested up to: 2.8.1
Stable tag: 1.0

OAS ToolBox plugin contains functions that helpful to develop plugins for wordpress.

== Description ==
OAS ToolBox is a plugin that simplifies & enhances the use of WordPress. It includes functions & tools which are useful to developers and short codes that are useful to authors.

---------------------------------------
Functions & Tools Useful to Developers
---------------------------------------
1. oas_get_page_title() - Queries the wp_cache to identify the title of a given page/post ID

2. oas_get_page_anchor_tag() - Queries the wp_cache to identify the title & permalink of a given page/post ID and add a 'current-page' value to the anchor tag's class in case the page/post being queried is that which the site visitor is currently viewing.

3. oas_get_post_exists() - Checks to make sure that a given post/page exists and informs the user through the OAS WP ErrorFree plugin in case bla bla bla.

4. oas_print_array() - Similar to PHP built in print_r() but it returns the output with <pre> html tags so the output maintains both spaces and line breaks as is.

5. oas_report_404() - When a 404 is in place, this function allows you to log the error in the "OAS WP Error Free" dashboard with the requested url.

6. oas_get_parents() - Retrieves the parents/grand parents id(s) of a given post/page in an array. 

7. oas_get_section_id() - Similar to oas_get_parents() but this one retrieves the grand parent id of a given post.

8. oas_get_meta() - When you deal with huge number of post_meta you would notice that wordpress had generated lots queries to database. You can reduce the number of queries by using this function. Basically this function caches the whole meta-data in to the wordpress built-in cache.

9. oas_date_unix() - Allows you to convert the wordpress date format in to UNIX equivalent.

10. oas_date_format() - This function is very handy if you want the default wordpress date format to be converted to your desired date format. (Accepts all kind of PHP date format parameter string)

11. oas_get_user_type() - Allows you to indentify the current user role. ('subscriber', 'contributor', 'author', 'editor', 'admin' and 'visitor')

12. oas_output_buffer() - Allows you to modify the wordpress generated html body content to be modified just before it's ready to be sent(printed) to the browser.

----------------------------------
Short Codes Useful to Blog Authors
----------------------------------

1. [OAS get_page_title=PostID /] Generates the title of a given post/page id.

2. [OAS get_page_anchor_tag=PostID /] Generates the html anchor tag with link to the given post/page id.

3. [OAS get_page_href=PostID /] Generates the permalink to a given post/page id.

4. [OAS get_date=Y-m-d /] - Generates the current date.


== Installation ==

Instructions for installing the OAS ToolBox Plugin. 

1. Download and extract the Plugin zip file.
2. Upload the 'oas-toolbox' folder  to the '/wp-content/plugins/' directory.
3. Activate the Plugin via the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

No FAQ as yet...

== Screenshots ==

Not Available

== Changelog ==

= Version 1.0 - 09 Aug 2009 =
 * Initial Release