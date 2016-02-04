=== Get WordPress Plugin Data ===
Contributors: qlstudio
Tags: plugin-api, plugins, api, promote, info, directory, specs, developer
Requires at least: 3.5
Tested up to: 4.4.1
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Get WordPress.org Plugin data including stats and display using a shortcode.

== Description ==
This plugin display WordPress.org plugin data such as version, requires and compatible up to, release and last update date, number of downloads, rating, description, installation steps, faq and screenshots etc. into pages / posts using simple shortcode.

= Shortcode examples =
* Display specs only. This will display version, requires and compatible up to, release and last update date, total number of downloads, average rating and download link.
`[wp_plugin_data name='export-user-data']`
* Display specs with plugin description
`[wp_plugin_data name='export-user-data' description="true"]`
* Display specs with installation instructions
`[wp_plugin_data name='export-user-data' installation="true"]`
* Display specs with FAQ
`[wp_plugin_data name='export-user-data' faq="true"]`
* Display specs with screenshot(s)
`[wp_plugin_data name='export-user-data' screenshots="true"]`
* Display all data (everything)
`[wp_plugin_data name='export-user-data' description="true" installation="true" faq="true" screenshots="true"]`

* Change 'dcg-display-plugin-data' to appropriate plugin slug for which you want to display data.*

= Note: In shortcode, you must have to pass name attribute with the correct plugin slug =
* Correct slug: dcg-display-plugin-data
* Wrong slug: DCG Display Plugin Data

== Installation ==
1. Upload the 'dcg-display-plugin-data' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. That’s it!

== Frequently Asked Questions ==
= What does this plugin do? =

This plugin display WordPress.org plugin data such as version, active installs, stats, required version, release and last updated date, number of downloads, rating, description, installation steps, faq and screenshots etc. into pages / posts using a simple shortcode.

= How can I find plugin slug? =

The last part of plugin URL in WordPress.org is the plugin slug.
For example, plugin slug for ***Export User Data*** will be ***export-user-data***
( https://wordpress.org/plugins/**export-user-data**/ )

= Will displaying Plugin Data slow my site down? =

Yes, a little - but the plugin has a caching system built in which can reduce page load times.

= Any specific requirements for this plugin to work? =

No.

= How can I ask a question that is not answered here? =

You can always open a [support thread](https://qstudio.us/support) if you have any question(s).

== Screenshots ==

== Changelog ==

=== 0.2 ===
* Added HTML parsing class to format screenshots and changelog

=== 0.1 ===
* Initial release