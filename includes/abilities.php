<?php
/**
 * Abilities API integration.
 *
 * Registers plugin capabilities for AI agents, the command palette,
 * and automation tools via the WordPress Abilities API.
 *
 * @since   2.8.9
 * @package ootb-openstreetmap
 */

namespace OOTB\Abilities;

/**
 * Registers all plugin abilities.
 *
 * Must be hooked onto `wp_abilities_api_init`, not `init`.
 *
 * @return void
 */
function register_abilities(): void {
	if ( ! function_exists( 'wp_register_ability' ) ) {
		return;
	}

	wp_register_ability(
		'ootb-openstreetmap/add-map-to-post',
		[
			'label'               => __( 'Add OpenStreetMap to Post', 'ootb-openstreetmap' ),
			'description'         => __( 'Inserts an OpenStreetMap block into a post or page. Accepts a centre location, zoom level, one or more markers, and display options.', 'ootb-openstreetmap' ),
			'thinking_message'    => __( 'Adding map to post…', 'ootb-openstreetmap' ),
			'success_message'     => __( 'Map block added successfully.', 'ootb-openstreetmap' ),
			'execute_callback'    => __NAMESPACE__ . '\\execute_add_map_to_post',
			'input_schema'        => [
				'type'                  => 'object',
				'additional_properties' => false,
				'required'              => [ 'post_id' ],
				'properties'            => [
					'post_id'           => [
						'type'        => 'integer',
						'description' => __( 'ID of the post or page to insert the map into.', 'ootb-openstreetmap' ),
					],
					'lat'               => [
						'type'        => 'number',
						'description' => __( 'Latitude for the map centre. Defaults to the first marker\'s latitude, or the plugin default location.', 'ootb-openstreetmap' ),
					],
					'lng'               => [
						'type'        => 'number',
						'description' => __( 'Longitude for the map centre. Defaults to the first marker\'s longitude, or the plugin default location.', 'ootb-openstreetmap' ),
					],
					'zoom'              => [
						'type'        => 'integer',
						'description' => __( 'Initial zoom level (2–18). Defaults to 8.', 'ootb-openstreetmap' ),
						'minimum'     => 2,
						'maximum'     => 18,
						'default'     => 8,
					],
					'min_zoom'          => [
						'type'        => 'integer',
						'description' => __( 'Minimum zoom level (2–18). Defaults to 2.', 'ootb-openstreetmap' ),
						'minimum'     => 2,
						'maximum'     => 18,
						'default'     => 2,
					],
					'max_zoom'          => [
						'type'        => 'integer',
						'description' => __( 'Maximum zoom level (2–18). Defaults to 18.', 'ootb-openstreetmap' ),
						'minimum'     => 2,
						'maximum'     => 18,
						'default'     => 18,
					],
					'map_height'        => [
						'type'        => 'integer',
						'description' => __( 'Map height in pixels. Defaults to 400.', 'ootb-openstreetmap' ),
						'default'     => 400,
					],
					'provider'          => [
						'type'        => 'string',
						'description' => __( 'Tile provider. Accepted values: "openstreetmap", "mapbox". Defaults to "openstreetmap".', 'ootb-openstreetmap' ),
						'enum'        => [ 'openstreetmap', 'mapbox' ],
						'default'     => 'openstreetmap',
					],
					'map_type'          => [
						'type'        => 'string',
						'description' => __( 'Map type. Accepted values: "marker", "polygon", "polyline". Defaults to "marker".', 'ootb-openstreetmap' ),
						'enum'        => [ 'marker', 'polygon', 'polyline' ],
						'default'     => 'marker',
					],
					'markers'           => [
						'type'        => 'array',
						'description' => __( 'List of markers to place on the map.', 'ootb-openstreetmap' ),
						'items'       => [
							'type'                  => 'object',
							'additional_properties' => false,
							'required'              => [ 'lat', 'lng' ],
							'properties'            => [
								'lat'     => [
									'type'        => 'number',
									'description' => __( 'Marker latitude.', 'ootb-openstreetmap' ),
								],
								'lng'     => [
									'type'        => 'number',
									'description' => __( 'Marker longitude.', 'ootb-openstreetmap' ),
								],
								'title'   => [
									'type'        => 'string',
									'description' => __( 'Marker title (shown as tooltip).', 'ootb-openstreetmap' ),
									'default'     => '',
								],
								'content' => [
									'type'        => 'string',
									'description' => __( 'Popup body text or HTML.', 'ootb-openstreetmap' ),
									'default'     => '',
								],
							],
						],
					],
					'show_markers'      => [
						'type'        => 'boolean',
						'description' => __( 'Whether to show markers on the map. Defaults to true.', 'ootb-openstreetmap' ),
						'default'     => true,
					],
					'shape_color'       => [
						'type'        => 'string',
						'description' => __( 'Stroke and fill colour for polygon/polyline shapes (CSS colour value). Defaults to "#008EFF".', 'ootb-openstreetmap' ),
						'default'     => '#008EFF',
					],
					'shape_weight'      => [
						'type'        => 'integer',
						'description' => __( 'Stroke width in pixels for polygon/polyline shapes. Defaults to 3.', 'ootb-openstreetmap' ),
						'default'     => 3,
					],
					'shape_text'        => [
						'type'        => 'string',
						'description' => __( 'Popup text for polygon/polyline shapes.', 'ootb-openstreetmap' ),
						'default'     => '',
					],
					'dragging'          => [
						'type'        => 'boolean',
						'description' => __( 'Allow the map to be dragged. Defaults to true.', 'ootb-openstreetmap' ),
						'default'     => true,
					],
					'touch_zoom'        => [
						'type'        => 'boolean',
						'description' => __( 'Allow pinch-to-zoom on touch devices. Defaults to true.', 'ootb-openstreetmap' ),
						'default'     => true,
					],
					'double_click_zoom' => [
						'type'        => 'boolean',
						'description' => __( 'Allow zooming in by double-clicking. Defaults to true.', 'ootb-openstreetmap' ),
						'default'     => true,
					],
					'scroll_wheel_zoom' => [
						'type'        => 'boolean',
						'description' => __( 'Allow zooming with the mouse scroll wheel. Defaults to true.', 'ootb-openstreetmap' ),
						'default'     => true,
					],
					'fullscreen'        => [
						'type'        => 'boolean',
						'description' => __( 'Show a fullscreen toggle button on the map. Defaults to false.', 'ootb-openstreetmap' ),
						'default'     => false,
					],
					'enable_clustering' => [
						'type'        => 'boolean',
						'description' => __( 'Group nearby markers into clusters. Defaults to false.', 'ootb-openstreetmap' ),
						'default'     => false,
					],
					'gesture_handling'  => [
						'type'        => 'boolean',
						'description' => __( 'Require a modifier key for scroll-wheel zoom (recommended when the map is embedded in a long page). Defaults to false.', 'ootb-openstreetmap' ),
						'default'     => false,
					],
				],
			],
			'output_schema'       => [
				'type'        => 'object',
				'description' => __( 'Result of the operation.', 'ootb-openstreetmap' ),
				'properties'  => [
					'post_id'  => [
						'type'        => 'integer',
						'description' => __( 'ID of the updated post.', 'ootb-openstreetmap' ),
					],
					'edit_url' => [
						'type'        => 'string',
						'description' => __( 'Block-editor URL for the updated post.', 'ootb-openstreetmap' ),
					],
				],
			],
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
		]
	);
}

/**
 * Execute callback for the `ootb-openstreetmap/add-map-to-post` ability.
 *
 * Builds a serialized `ootb/openstreetmap` block and prepends it to the
 * post content, then saves the post.
 *
 * @param array<string, mixed> $args Validated input matching the ability's input_schema.
 * @return array{post_id: int, edit_url: string}|\WP_Error
 */
function execute_add_map_to_post( array $args ): array|\WP_Error {
	$post_id = (int) $args['post_id'];

	$post = get_post( $post_id );
	if ( ! $post ) {
		return new \WP_Error(
			'ootb_post_not_found',
			__( 'The specified post does not exist.', 'ootb-openstreetmap' )
		);
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return new \WP_Error(
			'ootb_forbidden',
			__( 'You do not have permission to edit this post.', 'ootb-openstreetmap' )
		);
	}

	// Normalise and fill defaults.
	$map_height  = isset( $args['map_height'] ) ? (int) $args['map_height'] : 400;
	$provider    = $args['provider'] ?? 'openstreetmap';
	$raw_markers = $args['markers'] ?? [];

	// Clamp min/max zoom individually, then ensure min <= max, then clamp
	// the initial zoom to the resulting range.
	$min_zoom = isset( $args['min_zoom'] ) ? max( 2, min( 18, (int) $args['min_zoom'] ) ) : 2;
	$max_zoom = isset( $args['max_zoom'] ) ? max( 2, min( 18, (int) $args['max_zoom'] ) ) : 18;
	if ( $min_zoom > $max_zoom ) {
		[ $min_zoom, $max_zoom ] = [ $max_zoom, $min_zoom ];
	}
	$zoom = isset( $args['zoom'] ) ? max( $min_zoom, min( $max_zoom, (int) $args['zoom'] ) ) : max( $min_zoom, min( $max_zoom, 8 ) );

	// Build normalised marker list and derive centre from the first marker
	// when explicit lat/lng are omitted.
	$markers = [];
	foreach ( $raw_markers as $index => $m ) {
		$block_id  = (int) ( microtime( true ) * 1000 ) + $index;
		$markers[] = [
			'block_id' => $block_id,
			'lat'      => (string) $m['lat'],
			'lng'      => (string) $m['lng'],
			'title'    => $m['title'] ?? '',
			'content'  => $m['content'] ?? '',
			'text'     => $m['content'] ?? '',
			'icon'     => '',
		];
	}

	$fallback   = \OOTB\Helper::fallback_location();
	$centre_lat = (float) ( $args['lat'] ?? ( $markers[0]['lat'] ?? (float) $fallback[0] ) );
	$centre_lng = (float) ( $args['lng'] ?? ( $markers[0]['lng'] ?? (float) $fallback[1] ) );

	$options = [
		'map_type'          => $args['map_type'] ?? 'marker',
		'show_markers'      => $args['show_markers'] ?? true,
		'shape_color'       => $args['shape_color'] ?? '#008EFF',
		'shape_weight'      => isset( $args['shape_weight'] ) ? (int) $args['shape_weight'] : 3,
		'shape_text'        => $args['shape_text'] ?? '',
		'min_zoom'          => $min_zoom,
		'max_zoom'          => $max_zoom,
		'dragging'          => $args['dragging'] ?? true,
		'touch_zoom'        => $args['touch_zoom'] ?? true,
		'double_click_zoom' => $args['double_click_zoom'] ?? true,
		'scroll_wheel_zoom' => $args['scroll_wheel_zoom'] ?? true,
		'fullscreen'        => $args['fullscreen'] ?? false,
		'enable_clustering' => $args['enable_clustering'] ?? false,
	];

	$block_markup = build_block_markup(
		$centre_lat,
		$centre_lng,
		$zoom,
		$map_height,
		$provider,
		$markers,
		(bool) ( $args['gesture_handling'] ?? false ),
		$options
	);

	// Prepend the block to existing content.
	$updated_content = $block_markup . "\n\n" . $post->post_content;

	$result = wp_update_post(
		[
			'ID'           => $post_id,
			'post_content' => $updated_content,
		],
		true
	);

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return [
		'post_id'  => $post_id,
		'edit_url' => get_edit_post_link( $post_id, 'raw' ),
	];
}

/**
 * Builds the serialised block string that WordPress stores in post_content.
 *
 * @param float                            $lat              Centre latitude.
 * @param float                            $lng              Centre longitude.
 * @param int                              $zoom             Zoom level.
 * @param int                              $map_height       Map height in px.
 * @param string                           $provider         Tile provider slug.
 * @param array<int, array<string, mixed>> $markers          Normalised marker list.
 * @param bool                             $gesture_handling Whether gesture handling is on.
 * @param array<string, mixed>             $options          Additional display options.
 * @return string Serialised block markup.
 */
function build_block_markup(
	float $lat,
	float $lng,
	int $zoom,
	int $map_height,
	string $provider,
	array $markers,
	bool $gesture_handling,
	array $options = []
): string {
	$provider_slug     = ( 'mapbox' === $provider ) ? 'mapbox' : 'openstreetmap';
	$map_type_raw      = $options['map_type'] ?? 'marker';
	$map_type          = in_array( $map_type_raw, [ 'marker', 'polygon', 'polyline' ], true )
		? $map_type_raw
		: 'marker';
	$show_markers      = (bool) ( $options['show_markers'] ?? true );
	$shape_color       = (string) ( $options['shape_color'] ?? '#008EFF' );
	$shape_weight      = (int) ( $options['shape_weight'] ?? 3 );
	$shape_text        = (string) ( $options['shape_text'] ?? '' );
	$min_zoom          = max( 2, min( 18, (int) ( $options['min_zoom'] ?? 2 ) ) );
	$max_zoom          = max( 2, min( 18, (int) ( $options['max_zoom'] ?? 18 ) ) );
	if ( $min_zoom > $max_zoom ) {
		[ $min_zoom, $max_zoom ] = [ $max_zoom, $min_zoom ];
	}
	$zoom              = max( $min_zoom, min( $max_zoom, $zoom ) );
	$dragging          = (bool) ( $options['dragging'] ?? true );
	$touch_zoom        = (bool) ( $options['touch_zoom'] ?? true );
	$double_click_zoom = (bool) ( $options['double_click_zoom'] ?? true );
	$scroll_wheel_zoom = (bool) ( $options['scroll_wheel_zoom'] ?? true );
	$fullscreen        = (bool) ( $options['fullscreen'] ?? false );
	$enable_clustering = (bool) ( $options['enable_clustering'] ?? false );

	$bool = static fn( bool $v ): string => $v ? 'true' : 'false';

	// Block comment attributes (what the editor stores and uses to reconstruct UI state).
	$block_attrs = [
		'markers'           => array_map(
			static function ( array $m ): array {
				return [
					'id'      => $m['block_id'],
					'lat'     => $m['lat'],
					'lng'     => $m['lng'],
					'text'    => $m['content'],
					'textRaw' => $m['content'],
				];
			},
			$markers
		),
		'zoom'              => $zoom,
		'minZoom'           => $min_zoom,
		'maxZoom'           => $max_zoom,
		'mapHeight'         => $map_height,
		'provider'          => $provider_slug,
		'mapType'           => $map_type,
		'showMarkers'       => $show_markers,
		'shapeColor'        => $shape_color,
		'shapeWeight'       => $shape_weight,
		'shapeText'         => $shape_text,
		'dragging'          => $dragging,
		'touchZoom'         => $touch_zoom,
		'doubleClickZoom'   => $double_click_zoom,
		'scrollWheelZoom'   => $scroll_wheel_zoom,
		'fullscreen'        => $fullscreen,
		'enableClustering'  => $enable_clustering,
		'showDefaultBounds' => false,
		'bounds'            => [ [ (string) $lat, (string) $lng ] ],
		'gestureHandling'   => $gesture_handling,
	];

	// Default Leaflet marker icon - required by view.js.
	$site_scheme = wp_parse_url( get_home_url(), PHP_URL_SCHEME );
	$icon_url    = preg_replace( '/^https?/', $site_scheme, OOTB_PLUGIN_URL ) . 'assets/vendor/leaflet/images/marker-icon.png';
	$marker_icon = rawurlencode(
		(string) wp_json_encode(
			[
				'iconUrl'     => $icon_url,
				'iconAnchor'  => [ 12, 41 ],
				'popupAnchor' => [ 0, - 41 ],
			],
			JSON_UNESCAPED_SLASHES
		)
	);

	$shape_styles = rawurlencode(
		(string) wp_json_encode(
			[
				'fillColor' => $shape_color,
				'color'     => $shape_color,
				'weight'    => $shape_weight,
			]
		)
	);

	$data_markers = rawurlencode(
		(string) wp_json_encode(
			array_map(
				static function ( array $m ): array {
					return [
						'id'      => $m['block_id'],
						'lat'     => $m['lat'],
						'lng'     => $m['lng'],
						'text'    => $m['content'],
						'textRaw' => $m['content'],
					];
				},
				$markers
			)
		)
	);

	// data-bounds is plain JSON (view.js uses JSON.parse without decodeURIComponent).
	$data_bounds = (string) wp_json_encode( [ $lat, $lng ] );

	$inner_html = "\n"
		. '<div class="wp-block-ootb-openstreetmap">'
		. '<div class="ootb-openstreetmap--map"'
		. ' data-provider="' . esc_attr( $provider_slug ) . '"'
		. ' data-maptype="' . esc_attr( $map_type ) . '"'
		. ' data-showmarkers="' . $bool( $show_markers ) . '"'
		. ' data-shapestyle="' . esc_attr( $shape_styles ) . '"'
		. ' data-shapetext="' . esc_attr( $shape_text ) . '"'
		. ' data-markers="' . esc_attr( $data_markers ) . '"'
		. ' data-bounds="' . esc_attr( $data_bounds ) . '"'
		. ' data-zoom="' . $zoom . '"'
		. ' data-minzoom="' . $min_zoom . '"'
		. ' data-maxzoom="' . $max_zoom . '"'
		. ' data-dragging="' . $bool( $dragging ) . '"'
		. ' data-touchzoom="' . $bool( $touch_zoom ) . '"'
		. ' data-doubleclickzoom="' . $bool( $double_click_zoom ) . '"'
		. ' data-scrollwheelzoom="' . $bool( $scroll_wheel_zoom ) . '"'
		. ' data-fullscreen="' . $bool( $fullscreen ) . '"'
		. ' data-enableclustering="' . $bool( $enable_clustering ) . '"'
		. ' data-marker="' . esc_attr( $marker_icon ) . '"'
		. ' style="height:' . $map_height . 'px"'
		. '></div></div>'
		. "\n";

	return serialize_block(
		[
			'blockName'    => OOTB_BLOCK_NAME,
			'attrs'        => $block_attrs,
			'innerBlocks'  => [],
			'innerHTML'    => $inner_html,
			'innerContent' => [ $inner_html ],
		]
	);
}
