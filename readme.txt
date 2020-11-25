=== Out of the Block: OpenStreetMap ===
Contributors: gsarig
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MZR4JSRUMH5EA&source=url
Tags: Map, OpenStreetMap, Leaflet, Google Maps, block
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 7.2
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A map block for Gutenberg using OpenStreetMap and Leaflet that needs no API keys and works out of the box. Or should we say, ...Out of the Block?

== Description ==

A map block for Gutenberg which uses [OpenStreetMap](https://www.openstreetmap.org) and [Leaflet.js](https://leafletjs.com). It needs no API keys and works out of the box (or, out of the Block, if you prefer). Benefiting from Gutenberg's potential, the plugin tries a different take on how to add your locations on the map and rethinks a few things, UX-wise.

Instead of manually adding coordinates for each one of your markers, just click-and-drop them directly on the map. You want to adjust their position? Just drag them wherever you want. And instead of filling-in custom fields to set each marker's popup content, just open that popup and start writing in it, the Gutenberg way (it supports WYSIWYG editing, with links, images, and all). It even stores the map's zoom level as you use it so that you don't have to set it by hand.

[youtube https://www.youtube.com/watch?v=FGe7zJnrIgo]

= Features =

* No need for API keys. Just install and use it.
* Support for multiple markers.
* [Dead-simple interface](https://www.gsarigiannidis.gr/wordpress-gutenberg-map-block-openstreetmap/). Don't search for coordinates and don't get overwhelmed by too many fields when using multiple markers. Just point and click on the map to add your marker where you want it and edit it's popup content directly from there.
* [Place search](https://www.gsarigiannidis.gr/openstreetmap-place-search/). Find locations by typing keywords.
* Remembers the zoom that you set when adding the markers and stores it so that you don't set it by hand (which you can do anyway if you prefer).
* Adjust the map height.
* Change the default marker icon with a custom one.
* Enable or disable map dragging.
* Enable or disable touch zoom.
* Enable or disable double-click zoom.
* Enable or disable scroll wheel zoom.
* Set a minimum and maximum limit that the user can zoom on the frontend. Setting the same value to both fields will lock the zoom at that level.
* Support for other Layer Providers: MapBox (using your own API key) and Stamen.

== Installation ==

1. Upload the plugin to your WordPress plugins directory and activate it.
2. That's it. You can go to a post/page that supports the Gutenberg editor and start using the block called "Out of the Block: OpenStreetMap"

== Frequently Asked Questions ==

= Do I need an API key, like with Google Maps? =

No.

That's the point actually. Just install the plugin and start adding maps. Keep in mind, though, that as stated on the [OpenStreetMap Tile Usage Policy](https://operations.osmfoundation.org/policies/tiles/), OSM’s own servers are run entirely on donated resources and they have strictly limited capacity. Using them on a site with low traffic will probably be fine. Nevertheless, you are advised to create an account to [MapBox](https://www.mapbox.com/) and get a free API Key.

= How do I add a new location? =

To add a location, left-click on the map for a while, until the cursor transforms from hand to crosshair. As long as the cursor is a crosshair, it means that releasing it will drop the marker at that spot. That slight delay has been added to prevent you from accidentally add markers all over the place with every click.

Alternatively, you can use the map's place search functionality.

= How do I remove a location? =

Click on the marker to open up its popup. There, you will see the "Remove" button.

= I can't find some of the options like disable dragging, setting zoom levels etc =

Check under the "Map behavior" section, at the blocks' settings at the sidebar on the right. It's toggled off by default, that's probably why you missed it.

== Screenshots ==

1. Adding markers and rich content
2. Using the place search to throw multiple markers in a row by typing keyword and double+enter (no mouse)
3. The map editing screen
4. Map behavior options
5. Adding a marker
6. Using custom markers
7. Place search
8. Plugin settings page

== Upgrade Notice ==
= 1.0 =

== Changelog ==

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
