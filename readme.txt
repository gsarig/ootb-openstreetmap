=== Out of the Block: OpenStreetMap ===
Contributors: gsarig
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MZR4JSRUMH5EA&source=url
Tags: Map, OpenStreetMap, Leaflet, Google Maps, block
Requires at least: 5.8.6
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A map block for Gutenberg using OpenStreetMap and Leaflet that needs no API keys and works out of the box. Or should we say, ...Out of the Block?

== Description ==

A map block for Gutenberg which uses [OpenStreetMap](https://www.openstreetmap.org) and [Leaflet.js](https://leafletjs.com). It needs no API keys and works out of the box (or, out of the Block, if you prefer). Benefiting from Gutenberg's potential, the plugin tries a different take on how to add your locations on the map and rethinks a few things, UX-wise.

Instead of manually adding coordinates for each one of your markers, just click-and-drop them directly on the map. You want to adjust their position? Just drag them wherever you want. And instead of filling-in custom fields to set each marker's popup content, just open that popup and start writing in it, the Gutenberg way (it supports WYSIWYG editing, with links, images, and all). It even stores the map's zoom level as you use it so that you don't have to set it by hand.

[youtube https://www.youtube.com/watch?v=FGe7zJnrIgo]

* [Follow the project's development on GitHub](https://github.com/gsarig/ootb-openstreetmap)
* [Release history](https://github.com/gsarig/ootb-openstreetmap/releases)
* [Roadmap](https://github.com/users/gsarig/projects/1)

= Features =

* No need for API keys. Just install and use it.
* Support for multiple markers.
* Support for a different icon per marker.
* Support for polygons and polylines.
* [Dead-simple interface](https://www.gsarigiannidis.gr/wordpress-gutenberg-map-block-openstreetmap/). Don't search for coordinates and don't get overwhelmed by too many fields when using multiple markers. Just point and click on the map to add your marker where you want it and edit it's popup content directly from there.
* [Place search](https://www.gsarigiannidis.gr/openstreetmap-place-search/). Find locations by typing keywords.
* Remembers the zoom that you set when adding the markers and stores it so that you don't set it by hand (which you can do anyway if you prefer).
* OpenAI integration which allows you to add markers by using commands in natural language. Just say "please" to activate (e.g. "Please, show me where GOT was filmed"). Requires an OpenAI API key. [Read more](https://www.gsarigiannidis.gr/openstreetmap-openai-integration/).
* Query Maps: Supports creating a map out of maps added on other posts or post types. This can be quite powerful when, for example, you have a custom post type for "Places" with each place having its own map, and you want to dynamically gather-up all the places on a single map.
* Shortcode support: You can use the shortcode `[ootb_query]` as an alternative way to use the aforementioned Query Maps feature (see the FAQ for more info).
* Adjust the map height.
* Change the default marker icon with a custom one.
* Enable or disable map dragging.
* Enable or disable touch zoom.
* Enable or disable double-click zoom.
* Enable or disable scroll wheel zoom.
* Set a minimum and maximum limit that the user can zoom on the frontend. Setting the same value to both fields will lock the zoom at that level.
* Support for other Layer Providers: MapBox (using your own API key) and Stamen.
* Option to export locations in a JSON file
* Option to import locations from a JSON file

== Installation ==

1. Upload the plugin to your WordPress plugins directory and activate it.
2. That's it. You can go to a post/page that supports the Gutenberg editor and start using the block called "Out of the Block: OpenStreetMap"

== Frequently Asked Questions ==

= Do I need an API key, like with Google Maps? =

No.

That's the point, actually. Just install the plugin and start adding maps. Keep in mind, though, that as stated on the [OpenStreetMap Tile Usage Policy](https://operations.osmfoundation.org/policies/tiles/), OSM’s own servers are run entirely on donated resources and they have strictly limited capacity. Using them on a site with low traffic will probably be fine. Nevertheless, you are advised to create an account to [MapBox](https://www.mapbox.com/) and get a free API Key.

= How do I add a new location? =

To add a location, left-click on the map for a while, until you see the prompt saying "Release to drop a marker here". On browsers that support it, the cursor transforms from hand to crosshair, to make it even more apparent. As long as the prompt is visible, it means that releasing the click will drop the marker at that spot. That slight delay has been added to prevent you from accidentally adding markers all over the place with every click.

Alternatively, you can use the map's place search functionality.

= How do I remove a location? =

Click on the marker to open up its popup. There, you will see the "Remove" button.

= I can't find some of the options like disable dragging, setting zoom levels etc =

Check under the "Map behavior" section, at the blocks' settings at the sidebar on the right. It's toggled off by default, that's probably why you missed it.

= How does the OpenAI integration work? =

First of all, you will need to create an account to [OpenAI](https://openai.com/) and get an API key. Then, go to the plugin's settings page and paste your key there. After that, you can start adding markers by using commands in natural language. Just say "please" to activate (e.g. "Please, show me where GOT was filmed"). Please keep in mind, though, that it's like asking ChatGPT: the answers you get might not always be 100% reliable, and you should always double-check to confirm their accuracy. [Read more](https://www.gsarigiannidis.gr/openstreetmap-openai-integration/).

= How can I query maps from other posts or post types? =
On the block's side panel, Select the "Map data" panel and click on the "Fetch locations" button. This will automatically retrieve on the frontend all the markers from your posts (you can also select a specific post type from the dropdown). The block will be locked from editing, as the markers will be dynamically retrieved from the selected posts. If you don't want that, there is a "Stop syncing" button that will unlock the block, drop the markers on the map and allow you to edit.

= How can I use the shortcode? =
The shortcode `[ootb_query]` allows you to display a dynamic map, which retrieves markers from other posts or post types. Just add it to a post or page and you're good to go. By default, it will fetch the markers from the 100 most recent posts. The shortcode supports the following attributes:
* post_type: (Optional) The type of post to query. By default, it is set to `post`.
* posts_per_page: (Optional) The number of posts to be displayed on page. Default value is `100`.
* post_ids: (Optional) Comma-separated IDs of the posts to include in the query.
* height: (Optional) The desired height for the map. Default value is empty, which falls back to `400px`.
* provider: (Optional) Specifies the map provider. Options are: `openstreetmap`, `mapbox` and `stamen`. The default value is an empty string which falls back to `openstreetmap`.
* maptype: (Optional) Specifies the type of map. Options are: `markers`, `polygon` and `polyline`. The default value is an empty string, which will fall back to `markers`.
* touchzoom: (Optional) If set, touch zoom will be enabled on the map. It can be either `true` or `false`. The default value is an empty string, which falls back to `true`.
* scrollwheelzoom: (Optional) If set, enables zooming on the map with mouse scroll wheel. It can be either `true` or `false`. The default value is an empty string, which falls back to `true`.
* dragging: (Optional) If set, dragging is enabled on the map. It can be either `true` or `false`. The default value is an empty string, which falls back to `true`.
* doubleclickzoom: (Optional) If set, allows zooming in on the map with a double click. It can be either `true` or `false`. The default value is an empty string, which falls back to `true`.
* marker: (Optional) Specifies the marker for the map. This should correspond to the URL of the image that you want to use as the marker's icon (example: `https://www.example.com/my-custom-icon.png`). The default value is an empty string, which retrieves the default marker.

Here's an example of how you can use it:
```
[ootb_query post_type="post" post_ids="1,2,3,4" height="400px" provider="mapbox" maptype="polygon" touchzoom="true" scrollwheelzoom="true" dragging="true" doubleclickzoom="true" marker="https://www.example.com/my-custom-icon.png"]
```

= I want more control. Are there any hooks that I could use? =
Glad you asked! There are a few hooks that you can use to further customize the plugin's behavior. Here they are:
* `ootb_query_post_type`: Allows you to change the post type that the plugin will query for markers. By default, it is set to `post`. You can pass multiple post types as an array. Example:
```
add_filter( 'ootb_query_post_type', function() { return array( 'post', 'page' ); } );
```
* `ootb_query_posts_per_page`: Allows you to change the number of posts that the plugin will query for markers. By default, it is set to `100`. Example:
```
add_filter( 'ootb_query_posts_per_page', function() { return 500; } );
```
* `ootb_query_extra_args`: Allows you to add extra arguments to the query that the plugin will use to retrieve markers. By default, it is set to an empty array. Example:
```
  add_filter(
     'ootb_query_extra_args',
     function() {
        return [
           'tax_query' => [
           [
              'taxonomy' => 'people',
              'field' => 'slug',
              'terms' => 'bob'
           ]
        ];
     }
  );
```
Keep in mind that the extra args will be merged with the default ones, so you don't have to worry about overriding them. In fact, the args that are required for the query to work, cannot be overridden.

== Screenshots ==

1. Adding markers and rich content
2. Using the place search to throw multiple markers in a row by typing keyword and double+enter (no mouse)
3. The map editing screen
4. Map behavior options
5. Adding a marker
6. Using custom markers
7. Place search
8. Plugin settings page
9. Adding a polygon
10. Adding a polyline
11. Export and import locations
12. Demonstrating the OpenAI integration

== Upgrade Notice ==
= 2.6.0 =
Version 2.6.0 adds the option to query maps: This allows you to create a map consisting of other maps, added on other posts or post types. This can be quite powerful when, for example, you have a custom post type for "Places" with each place having its own map, and you want to dynamically gather-up all the places on a single map. A shortcode has also been added as an alternative way to use this feature.

= 2.5.0 =
Version 2.5.0 adds support for OpenAI integration which allows you to add markers by using commands in natural language. Just say "please" to activate (e.g. "Please, show me where GOT was filmed"). Requires an OpenAI API key.

= 2.4.0 =
Version 2.4.0 adds an option to prevent the default map scroll/touch behaviours to make it easier for users to navigate in a page (pretty much like in Google Maps).

= 2.3.0 =
Version 2.3.0 adds support for different icons per marker.

= 2.2.0 =
Version 2.2.0 adds support for import and export locations [read more](https://github.com/gsarig/ootb-openstreetmap/pull/14).

= 2.1.0 =
Version 2.1.0 introduces 2 new, powerful features: support for polygons, and polylines.

= 2.0.0 =
Version 2.0.0 is a major, almost full, refactoring, both for the build scripts and the codebase. The changes should be backwards compatible and your already existing blocks should keep working as expected. If you notice any issues, though, please report them to the plugin's [support forum](https://wordpress.org/support/plugin/ootb-openstreetmap/).

= 1.0 =

== Changelog ==
= 2.6.0 =
* [NEW] Adds option to query maps, which allows you to create a map out of maps added on other posts or post types. This can be quite powerful when, for example, you have a custom post type for "Places" with each place having its own map, and you want to dynamically gather-up all the places on a single map.
* [NEW] Adds shortcode support. You can use the shortcode `[ootb_query]` to retrieve the aforementioned Query Maps feature (see the FAQ for more info).

= 2.5.0 =
* [New] Adds OpenAI integration which allows you to add markers by using commands in natural language. Just say "please" to activate (e.g. "Please, show me where GOT was filmed"). Requires an OpenAI API key. [Read more](https://www.gsarigiannidis.gr/openstreetmap-openai-integration/).

= 2.4.1 =
* Updates the build scripts.
* Updates compatibility with WordPress 6.4.
* Fixes a bug where the marker's delete button would remove the wrong marker.

= 2.4.0 =
* [New] Adds an option to prevent the default map scroll/touch behaviours.

= 2.3.0 =
* [New] Adds support for different icons per marker.
* Updates the `react-leaflet` script to `v.4.2.1`.
* Updates compatibility with WordPress 6.2.

= 2.2.0 =
* [New] Adds option to export locations to a JSON file
* [New] Adds option to import locations from a previously exported JSON file

= 2.1.0 =
* [New] Adds support for polygon shapes
* [New] Adds support for polyline shapes
* [Fix] Improves the drag and drop of markers
* Updates the `react-leaflet` script to `v.4.2.0`
* Updates the plugin's assets and documentation

= 2.0.2 =
* Replaces `str_contains` with `strpos`, for better backwards compatibility with older versions of PHP / WordPress.

= 2.0.1 =
* Fixes a bug which broke the admin on WordPress versions prior to 5.9.

= 2.0.0 =
* Refactors the build scripts to use the official `@wordpress/create-block` instead of `create-guten-block`, which isn't supported anymore.
* Updates `Leaflet` and `react-leaflet` to their latest versions (Leaflet 1.9.3 and react-leaflet 4.1.0).
* Adds a new option to set the default coordinates when you add a new block. The plugin will try to guess the default location based on the site's timezone.

= 1.3.5 =
* Fixes a PHP warning in the widgets area of the admin
* Updates translations

= 1.3.4 =
* Fixes a PHP warning (more info: https://wordpress.org/support/topic/error-under-php8-2/)

= 1.3.3 =
* Fixes a bug where the block's scripts didn't load if the block is used in a Widget

= 1.3.2 =
* Fixes a bug where the block's scripts didn't load when used as a reusable block

= 1.3 =
* Added Stamen as a Tile Provider
* Fixed a bug with map centering when there is only one location
* Fixed wrong link on attributions

= 1.2 =
* Added MapBox as a Tile Provider

= 1.1 =
* Place search functionality added
* Improved marker precision
* Better handling of pinning on mobile devices
* Overall improvements on dragging smoothness

= 1.0.1 =
* Fixed a bug during plugin activation.

= 1.0 =
* First releases
