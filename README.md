[![Download from WordPress.org](.wordpress-org/banner-1544-500.jpg)](https://wordpress.org/plugins/ootb-openstreetmap/)

# Out of the Block: OpenStreetMap

A map block for WordPress' Gutenberg Editor which uses [OpenStreetMap](https://www.openstreetmap.org) and [Leaflet.js](http://https://leafletjs.com). It needs no API keys and works out of the box (or, out of the Block, if you prefer). Benefiting from Gutenberg's potential, the plugin tries a different take on how to add your locations on the map and rethinks a few things, UX-wise.

Instead of manually adding coordinates for each one of your markers, just click-and-drop them directly on the map. You want to adjust their position? Just drag them wherever you want. And instead of filling-in custom fields to set each marker's popup content, just open that popup and start writing in it, the Gutenberg way (it supports WYSIWYG editing, with links, images, and all). It even stores the map's zoom level as you use it so that you don't have to set it by hand.

---

👇 [**Jump to the available Hooks**](#hooks)🪝

👇 [**Jump to the available Shortcodes**](#shortcodes)

---

👉 [Read more about the overall UX challenges](https://www.gsarigiannidis.gr/wordpress-gutenberg-map-block-openstreetmap/)

👉 [The challenges of building a user-friendly place search for OpenStreetMap](https://www.gsarigiannidis.gr/openstreetmap-place-search/)

👉 [Lessons learned from integrating OpenAI to a WordPress plugin](https://www.gsarigiannidis.gr/openstreetmap-openai-integration/)


## Demos

### The main functionality, with drag and drop pins and WYSIWYG editing
![Demo GIF](.wordpress-org/screenshot-1.gif)

### Using the location search
![Demo GIF](.wordpress-org/screenshot-2.gif)

### Drawing a polygon

![Demo GIF](.wordpress-org/screenshot-9.gif)

### Drawing a polyline

![Demo GIF](.wordpress-org/screenshot-10.gif)

### AI integration in action:

![Demo GIF](.wordpress-org/screenshot-12.gif)

## Features

* No need for API keys. Just install and use it.
* Support for multiple markers.
* Support for a different icon per marker.
* Support for polygons and polylines.
* [Dead-simple interface](https://www.gsarigiannidis.gr/wordpress-gutenberg-map-block-openstreetmap/). Don't search for coordinates and don't get overwhelmed by too many fields when using multiple markers. Just point and click on the map to add your marker where you want it and edit it's popup content directly from there.
* [Place search](https://www.gsarigiannidis.gr/openstreetmap-place-search/). Find locations by typing keywords.
* Remembers the zoom that you set when adding the markers and stores it so that you don't set it by hand (which you can do anyway if you prefer).
* AI integration which allows you to add markers by using commands in natural language. Just say "please" to activate (e.g. "Please, show me where GOT was filmed"). Requires an API key from an AI provider. [Read more](https://www.gsarigiannidis.gr/openstreetmap-openai-integration/).
* Query Maps: Supports creating a map out of maps added on other posts or post types. This can be quite powerful when, for example, you have a custom post type for "Places" with each place having its own map, and you want to dynamically gather-up all the places on a single map.
* Shortcode support: You can use the shortcode `[ootb_query]` as an alternative way to use the aforementioned Query Maps feature (see the FAQ for more info).
* Support for a location custom field, which can be used to store a post's or post type's location, following the [Geodata guidelines](https://codex.wordpress.org/Geodata). Read more in the [v.2.8.0 release notes](https://github.com/gsarig/ootb-openstreetmap/releases/tag/2.8.0).
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

## Installation

1. Upload the plugin to your WordPress plugins directory and activate it.
2. That's it. You can go to a post/page that supports the Gutenberg editor and start using the block called "Out of the Block: OpenStreetMap"

## Frequently Asked Questions

### Do I need an API key, like with Google Maps?

No.

That's the point, actually. Just install the plugin and start adding maps. Keep in mind, though, that as stated on the [OpenStreetMap Tile Usage Policy](https://operations.osmfoundation.org/policies/tiles/), OSM’s own servers are run entirely on donated resources and they have strictly limited capacity. Using them on a site with low traffic will probably be fine. Nevertheless, you are advised to create an account to [MapBox](https://www.mapbox.com/) and get a free API Key.

### How can I add a custom Mapbox style?
You can find the style URL on [Mapbox Studio](https://www.mapbox.com/studio/). There, use the "Share" button, and under "Developer resources", copy the "Style URL". It should look like that: `mapbox://styles/username/style-id`. You can declare a global style on the plugin's settings, to be used as a default for all the maps, or you can set a custom style for each map, by using the block's settings panel.

### How do I add a new location?

To add a location, left-click on the map for a while, until you see the prompt saying "Release to drop a marker here". On browsers that support it, the cursor transforms from hand to crosshair, to make it even more apparent. As long as the prompt is visible, it means that releasing the click will drop the marker at that spot. That slight delay has been added to prevent you from accidentally adding markers all over the place with every click.

### How do I remove a location?

Click on the marker to open up its popup. There, you will see the "Remove" button.

### I can't find some of the options like disable dragging, setting zoom levels, etc

Check under the "Map behavior" section, at the blocks' settings at the sidebar on the right. It's toggled off by default, that's probably why you missed it.

### How does the AI integration work?

First of all, you will need to create an account to an AI provider (like [OpenAI](https://openai.com/), [Gemini](https://gemini.google.com/), [Anthropic](https://console.anthropic.com/) or similar) and get an API key. Then, go to the plugin's settings page and paste your API provider's endpoint URL, Model and API key there. After that, you can start adding markers by using commands in natural language. Just say "please" to activate (e.g. "Please, show me where GOT was filmed"). Please keep in mind, though, that it's like asking ChatGPT, Claude or any other AI Assistant: the answers you get might not always be 100% reliable, and you should always double-check to confirm their accuracy. [Read more](https://www.gsarigiannidis.gr/openstreetmap-openai-integration/).

### How can I query maps from other posts or post types?
On the block's side panel, Select the "Map data" panel and click on the "Fetch locations" button. This will automatically retrieve on the frontend all the markers from your posts (you can also select a specific post type from the dropdown). The block will be locked from editing, as the markers will be dynamically retrieved from the selected posts. If you don't want that, there is a "Stop syncing" button that will unlock the block, drop the markers on the map and allow you to edit.

## Shortcodes
### [ootb_query]
The shortcode `[ootb_query]` allows you to display a dynamic map, which retrieves markers from other posts or post types. Just add it to a post or page and you're good to go. By default, it will fetch the markers from the 100 most recent posts. The shortcode supports the following attributes:
* source: (Optional) The source of the data. Can be either `geodata`, if you want to retrieve the posts based on their Location custom meta field, or `block`, to retrieve posts containing map blocks in their content. The default option, which will be used if the attribute is omitted, is `block`.
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
## Hooks
There are a few hooks that you can use to further customize the plugin's behavior. Here they are:

### ootb_query_post_type
Allows you to change the post type that the plugin will query for markers. By default, it is set to `post`. You can pass multiple post types as an array. Example:
```
add_filter( 'ootb_query_post_type', function() { return array( 'post', 'page' ); } );
```

### ootb_query_posts_per_page
Allows you to change the number of posts that the plugin will query for markers. By default, it is set to `100`. Example:
```
add_filter( 'ootb_query_posts_per_page', function() { return 500; } );
```

### ootb_query_extra_args
Allows you to add extra arguments to the query that the plugin will use to retrieve markers. By default, it is set to an empty array. Example:
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

### ootb_cf_modal_content
Allows you to change the content of the modal that appears when you query posts based on their "Location" custom fields. By default, it will display the value set in the Address field. For example, the following code will display the post's title, thumbnail, excerpt and a link to the post:
```
add_filter( 'ootb_cf_modal_content', 'my_modal_content', 10, 2 );

function my_modal_content( $address, $post_id ) {

	return sprintf(
		'<div>
			<h3>%1$s</h3>
			<figure>%2$s</figure>
			<p>%3$s</p>
			<p><a href="%4$s">View post</a></p>
		</div>',
		get_the_title( $post_id ),
		get_the_post_thumbnail( $post_id, 'thumbnail' ),
		has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : $address,
		get_the_permalink( $post_id )
	);
}
```
Keep in mind that this filter will only work for the queries based on the "Location" custom fields. If you want to modify the content of the markers on standard blocks, see the next hook.

### ootb_block_marker_text
This filter allows customizing the text content that appears in marker popups. It's particularly useful when querying maps from other posts, as it provides the ability to customize the popup content for each marker. The filter accepts three parameters:
* `$marker_text`: The original text content of the marker.
* `$post_id`: The ID of the post where the marker was defined.
* `$current_post_id` The ID of the current post where the map is being displayed.

Example:
```
add_filter( 'ootb_block_marker_text', 'customize_marker_text', 10, 3 );

function customize_marker_text($text, $post_id, $current_post_id) {

    // Only modify content if we're showing markers from other posts
    if ($post_id !== $current_post_id) {
        // Get post title and URL
        $post_title = get_the_title( $post_id );
        $post_url   = get_permalink( $post_id );

        // Add a simple header with link above the original content
        $text = sprintf('<h4><a href="%s">%s</a></h4>%s',
		esc_url($post_url),
		esc_html($post_title),
		$text
	);
    }

    return $text;
}
```

### ootb_cf_marker_icon
Allows you to change the marker icon for posts that have a "Location" custom field. By default, it will use the default marker. For example, the following code will use a custom marker for a post with ID `123`:
```
add_filter( 'ootb_cf_marker_icon', 'my_marker_icon', 10, 2 );

function my_marker_icon( $icon_url, $post_id ){
	if( 123 === $post_id ) {
		$icon_url = 'https://example.com/my-marker.jpg';
	}
	return $icon_url;
}
```

## Screenshots

![The map editing screen](.wordpress-org/screenshot-3.jpg)
The map editing screen

![Map behavior options](.wordpress-org/screenshot-4.jpg)
Map behavior options

![Adding a marker](.wordpress-org/screenshot-5.jpg)
Adding a marker

![Using custom markers](.wordpress-org/screenshot-6.jpg)
Using custom markers

![Place search](.wordpress-org/screenshot-7.jpg)
Place search
