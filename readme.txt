=== WP Themes Information Plugin ===
Contributors: williampatton
Tags: theme-information, api, widget, shortcode
Requires at least: 4.5
Tested up to: 4.9
Stable tag: 0.2.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This plugin is used to get information about a theme from the wordpress.org Themes API. It has a shortcode as well as a custom widget.

== Description ==

Use this plugin to get theme information about a theme in the wordpress.org theme directory. Information is retrieved from the wordpress.org Themes API.

A meta box is added to posts, pages and jetpack portfolio post types. You can enter a specific theme slug in this box and it will be used by the custom widget if it is set on a visited page/post.

A shortcode can be used to get a specific piece of information about a theme by using a shortcode like this:

`[theme-info slug="theme-slug" field="downloaded"]`

Supported values are as follows:

```
$array = array(
	'name',
	'slug',
	'version',
	'preview_url',
	'author',
	'screenshot_url',
	'rating',
	'num_ratings',
	'downloaded',
	'last_updated',
	'homepage',
	'download_link',
);
```


A few notes about the sections above:

*   "Contributors" is a comma separated list of wp.org/wp-plugins.org usernames
*   "Tags" is a comma separated list of tags that apply to the plugin
*   "Requires at least" is the lowest version that the plugin will work on
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
higher versions... this is just the highest one you've verified.
*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place a shortcode into a post or add a slug to it and add the custom widget to the sidebar

== Changelog ==

= 0.2.0 =
* Added custom widget.
* Core re-factor.

= 0.1.0 =
* Initial version, works with shortcode only.
