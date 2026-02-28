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
					'post_id'          => [
						'type'        => 'integer',
						'description' => __( 'ID of the post or page to insert the map into.', 'ootb-openstreetmap' ),
					],
					'lat'              => [
						'type'        => 'number',
						'description' => __( 'Latitude for the map centre. Defaults to the first marker\'s latitude, or the plugin default location.', 'ootb-openstreetmap' ),
					],
					'lng'              => [
						'type'        => 'number',
						'description' => __( 'Longitude for the map centre. Defaults to the first marker\'s longitude, or the plugin default location.', 'ootb-openstreetmap' ),
					],
					'zoom'             => [
						'type'        => 'integer',
						'description' => __( 'Initial zoom level (2–18). Defaults to 8.', 'ootb-openstreetmap' ),
						'minimum'     => 2,
						'maximum'     => 18,
						'default'     => 8,
					],
					'map_height'       => [
						'type'        => 'integer',
						'description' => __( 'Map height in pixels. Defaults to 400.', 'ootb-openstreetmap' ),
						'default'     => 400,
					],
					'provider'         => [
						'type'        => 'string',
						'description' => __( 'Tile provider. Accepted values: "openstreetmap", "mapbox". Defaults to "openstreetmap".', 'ootb-openstreetmap' ),
						'enum'        => [ 'openstreetmap', 'mapbox' ],
						'default'     => 'openstreetmap',
					],
					'markers'          => [
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
					'gesture_handling' => [
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
	$zoom        = isset( $args['zoom'] ) ? max( 2, min( 18, (int) $args['zoom'] ) ) : 8;
	$map_height  = isset( $args['map_height'] ) ? (int) $args['map_height'] : 400;
	$provider    = $args['provider'] ?? 'openstreetmap';
	$raw_markers = $args['markers'] ?? [];

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

	$centre_lat = (float) ( $args['lat'] ?? ( $markers[0]['lat'] ?? 37.9715 ) );
	$centre_lng = (float) ( $args['lng'] ?? ( $markers[0]['lng'] ?? 23.7266 ) );

	$block_markup = build_block_markup(
		$centre_lat,
		$centre_lng,
		$zoom,
		$map_height,
		$provider,
		$markers,
		(bool) ( $args['gesture_handling'] ?? false )
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
 * @return string Serialised block markup.
 */
function build_block_markup(
	float $lat,
	float $lng,
	int $zoom,
	int $map_height,
	string $provider,
	array $markers,
	bool $gesture_handling
): string {
	$provider_slug = ( 'mapbox' === $provider ) ? 'mapbox' : 'openstreetmap';

	// Block comment attributes (what the editor stores and uses to reconstruct UI state).
	// Use the same block_id that data-markers will use so both stay in sync.
	// Include every non-default value so the editor can correctly reconstruct UI state.
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
		'mapHeight'         => $map_height,
		'provider'          => $provider_slug,
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

	// Data attributes consumed by view.js / Leaflet at runtime.
	$shape_style = rawurlencode(
		(string) wp_json_encode(
			[
				'fillColor' => '#008EFF',
				'color'     => '#008EFF',
				'weight'    => 3,
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

	$inner_html = sprintf(
		'<div class="wp-block-ootb-openstreetmap">'
		. '<div class="ootb-openstreetmap--map"'
		. ' data-provider="%1$s"'
		. ' data-maptype="marker"'
		. ' data-showmarkers="true"'
		. ' data-shapestyle="%2$s"'
		. ' data-shapetext=""'
		. ' data-markers="%3$s"'
		. ' data-bounds="%4$s"'
		. ' data-zoom="%5$d"'
		. ' data-minzoom="2"'
		. ' data-maxzoom="18"'
		. ' data-dragging="true"'
		. ' data-touchzoom="true"'
		. ' data-doubleclickzoom="true"'
		. ' data-scrollwheelzoom="true"'
		. ' data-marker="%7$s"'
		. ' style="height:%6$dpx"'
		. '></div></div>',
		esc_attr( $provider_slug ),
		esc_attr( $shape_style ),
		esc_attr( $data_markers ),
		esc_attr( $data_bounds ),
		$zoom,
		$map_height,
		$marker_icon
	);

	$inner_html = "\n" . $inner_html . "\n";

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
