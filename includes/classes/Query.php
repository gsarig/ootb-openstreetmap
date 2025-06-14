<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 * Query Maps
 *
 * @since   2.6.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

use WP_Query;

class Query {
	public function __construct() {
		add_action( 'wp_ajax_ootb_get_markers', [ $this, 'handle_ajax_call' ] );
		add_shortcode( 'ootb_query', [ $this, 'shortcode' ] );
	}

	/**
	 * Checks if the post type has been overridden by the filter.
	 *
	 * @param string $fallback The fallback post type.
	 *
	 * @return mixed|null
	 */
	private static function get_post_type( string $fallback = 'post' ) {
		return apply_filters( 'ootb_query_post_type', $fallback );
	}

	/**
	 * Checks if the posts per page has been overridden by the filter.
	 *
	 * @param int $fallback The fallback posts per page.
	 *
	 * @return mixed|null
	 */
	public static function get_posts_per_page( int $fallback = 100 ): mixed {
		return apply_filters( 'ootb_query_posts_per_page', $fallback );
	}

	/**
	 * Checks if there are any extra args to add to the query, but does not overwrite any existing args.
	 *
	 * @param array $args The query args.
	 *
	 * @return array|null
	 */
	private static function maybe_extra_args( array $args = [] ): ?array {
		$extra_args = apply_filters( 'ootb_query_extra_args', [] );

		// Get new args that don't exist in the defaults.
		$new_unique_args = array_diff_key( $extra_args, $args );

		// Combine default args with new args.
		return array_merge( $args, $new_unique_args );
	}

	/**
	 * Handles the ajax call to get the markers.
	 * @return void
	 */
	public function handle_ajax_call() {
		// Verify the nonce before processing the request.
		if ( ! isset( $_POST[ 'nonce' ] ) || ! check_ajax_referer( 'ootb_get_markers_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Nonce verification failed.', 'ootb-openstreetmap' ) ] );
		}

		// Sanitize and validate the `post_id`.
		$post_id = isset( $_POST[ 'post_id' ] ) && is_numeric( $_POST[ 'post_id' ] )
			? absint( $_POST[ 'post_id' ] )
			: 0;

		$raw_query_args = isset( $_POST[ 'query_args' ] )
			? sanitize_text_field( wp_unslash( $_POST[ 'query_args' ] ) )
			: '';

		$args = ! empty( $raw_query_args )
			? json_decode( $raw_query_args, true )
			: [];

		// Sanitize each element in the array.
		if ( is_array( $args ) ) {
			$args = array_map( 'sanitize_text_field', $args );
		} else {
			// Handle invalid or non-decodable JSON input.
			wp_send_json_error( [ 'message' => __( 'Invalid query arguments provided.', 'ootb-openstreetmap' ) ] );
		}

		if ( empty( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid input provided.', 'ootb-openstreetmap' ) ] );
		}

		// Get the data.
		$data = self::get_markers( $post_id, $args );

		// Return the data as JSON response.
		if ( $data ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( [ 'message' => __( 'No markers found.', 'ootb-openstreetmap' ) ] );
		}

		// Always die (ensures WordPress doesn’t output additional content).
		wp_die();
	}

	/**
	 * Gets the markers from the query.
	 *
	 * @param int $current_post_id The current post id.
	 * @param array $query_args The query args.
	 * @param bool $query_custom_fields Whether to query custom fields or not.
	 *
	 * @return false|string
	 */
	public static function get_markers( int $current_post_id = 0, array $query_args = [], bool $query_custom_fields = false ) {
		$default_args = [
			'post_type'              => self::get_post_type(),
			'posts_per_page'         => self::get_posts_per_page(),
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		if ( $query_custom_fields ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$default_args[ 'meta_query' ] = [
				'relation' => 'AND',
				[
					'key'     => 'geo_latitude',
					'value'   => '',
					'compare' => '!=',
				],
				[
					'key'     => 'geo_longitude',
					'value'   => '',
					'compare' => '!=',
				],
			];
		} else {
			$default_args[ 's' ] = '<!-- wp:ootb/openstreetmap ';
		}
		$args = wp_parse_args( $query_args, $default_args );

		$query = new WP_Query( self::maybe_extra_args( $args ) );

		if ( empty( $query->posts ) ) {
			return false;
		}

		return self::get_marker_data( $current_post_id, $query->posts, $query_custom_fields );

	}

	/**
	 * The render callback for the block.
	 *
	 * @param array $attributes The block attributes.
	 * @param string $content The block content.
	 *
	 * @return array|string|string[]|null
	 */
	public static function render_callback( array $attributes, string $content ) {
		if ( ( isset( $attributes[ 'serverSideRender' ] ) && ! $attributes[ 'serverSideRender' ] ) && ! empty( $attributes[ 'markers' ] ) ) {
			return $content;
		}

		$post_type                                = $attributes[ 'queryArgs' ][ 'post_type' ] ?? '';
		$attributes[ 'queryArgs' ][ 'post_type' ] = self::get_post_type( $post_type );
		$post_id                                  = is_singular() ? get_the_ID() : 0;
		$markers                                  = Query::get_markers(
			$post_id,
			$attributes[ 'queryArgs' ],
			$attributes[ 'queryCustomFields' ] ?? false
		);
		if ( empty( $markers ) ) {
			return $content;
		}

		$escaped_markers = htmlentities( $markers, ENT_QUOTES, 'UTF-8' );

		return preg_replace(
			[
				'/data-markers=".*?"/',
				'/data-bounds=".*?"/'
			],
			[
				sprintf( 'data-markers="%s"', $escaped_markers ),
				'data-bounds="[null,null]"',
			],
			$content
		);
	}

	/**
	 * Gets the marker data from the post ids.
	 *
	 * @param int $current_post_id The current post id.
	 * @param array $post_ids The post ids.
	 * @param bool $query_custom_fields Whether to query custom fields or not.
	 *
	 * @return false|string
	 */
	private static function get_marker_data( int $current_post_id, array $post_ids, bool $query_custom_fields = false ) {
		$markers = [];
		foreach ( $post_ids as $post_id ) {
			if ( $query_custom_fields ) {
				$latitude  = get_post_meta( $post_id, 'geo_latitude', true );
				$longitude = get_post_meta( $post_id, 'geo_longitude', true );
				$address   = get_post_meta( $post_id, 'geo_address', true );
				if ( empty( $latitude ) || empty( $longitude ) ) {
					continue;
				}

				$markers[][] = (object) [
					'lat'  => $latitude,
					'lng'  => $longitude,
					'text' => wp_kses_post( apply_filters( 'ootb_cf_modal_content', $address, $post_id ) ),
					'id'   => $post_id,
					'icon' => self::get_cf_marker_icon( $post_id ),
				];
			} else {
				if ( $post_id === $current_post_id ) {
					continue;
				}
				$content = get_post_field( 'post_content', $post_id );
				$blocks  = parse_blocks( $content );
				foreach ( $blocks as $block ) {
					if ( $block[ 'blockName' ] !== 'ootb/openstreetmap' || empty( $block[ 'attrs' ] ) ) {
						continue;
					}

					$attrs = json_decode( wp_json_encode( $block[ 'attrs' ] ) );

					if (
						empty( $attrs->markers ) ||
						( isset( $attrs->serverSideRender ) && true === $attrs->serverSideRender )
					) {
						continue;
					}
					foreach ( $attrs->markers as $marker ) {
						if ( isset( $marker->text ) ) {
							$marker->text = wp_kses_post( apply_filters( 'ootb_block_marker_text', $marker->text ) );
						}
					}
					$markers[] = $attrs->markers;
				}
			}
		}
		$flattened_markers = array_merge( ...$markers );

		return wp_json_encode( $flattened_markers );
	}

	/**
	 * Renders a map based on the provided shortcode attributes.
	 *
	 * Shortcode format:
	 * [ootb_query post_type="post" posts_per_page="10" post_ids="1,2,3", height="400px", provider="", maptype="", touchzoom="", scrollwheelzoom="", dragging="", doubleclickzoom="", marker=""]
	 *
	 * Replace "post" with your desired post_type, "10" with the number of posts you want per page,
	 * "1,2,3" with your desired post IDs, "400px" with the desired height, and the remaining arguments with your desired settings for the map attributes.
	 *
	 * @param string|array $attrs The attributes for the shortcode.
	 *
	 * @type string $source (Optional) The source of the data. Can be either "geodata" or "block" (default).
	 * @type string $post_type (Optional) The post type to query. Default "post".
	 * @type int $posts_per_page (Optional) The number of posts per page. Default 10.
	 * @type string $post_ids (Optional) Comma-separated IDs of posts to include in the query.
	 * @type string $height (Optional) The wanted height for the map. Default '400px'.
	 * @type string $provider (Optional) The map provider. Default empty string.
	 * @type string $maptype (Optional) The map type. Default empty string.
	 * @type string $touchzoom (Optional) Enable touch zoom on the map. Default empty string.
	 * @type string $scrollwheelzoom (Optional) Enable zooming on the map with a mouse scroll wheel. Default empty string.
	 * @type string $dragging (Optional) Enable dragging on the map. Default empty string.
	 * @type string $doubleclickzoom (Optional) Enable zooming in on the map with a double click. Default empty string.
	 * @type string $marker (Optional) The marker for the map. Default empty string.
	 *
	 * @return string Rendered HTML content for the map.
	 */
	public function shortcode( $attrs ) {
		if ( is_admin() ) {
			return '';
		}
		if ( ! Helper::has_block_in_frontend( OOTB_BLOCK_NAME ) ) {
			$assets = new Assets();
			$assets->shortcode_assets();
		}
		// Only allow specific attributes.
		$attrs = shortcode_atts(
			array_merge(
				[
					'source'         => '',
					'post_type'      => Helper::get_default( 'post_type' ),
					'posts_per_page' => self::get_posts_per_page(),
					'post_ids'       => '',
					'height'         => Helper::get_default( 'height' ),
				],
				self::overridable_attrs()
			)
			, $attrs, 'ootb_query' );

		// Construct the queryArgs for the render_callback method.
		$queryArgs = [
			'source'         => $attrs[ 'source' ],
			'post_type'      => $attrs[ 'post_type' ],
			'posts_per_page' => $attrs[ 'posts_per_page' ],
		];

		// Check if specific posts are requested
		if ( $attrs[ 'post_ids' ] !== '' ) {
			$post_ids                = array_map( 'intval', explode( ',', $attrs[ 'post_ids' ] ) );
			$queryArgs[ 'post__in' ] = $post_ids;
		}

		$render_callback_attrs = [
			'serverSideRender' => true,
			'queryArgs'        => $queryArgs,
		];

		$escaped_attrs = array_map( 'esc_attr', Helper::sanitize_attrs( $attrs ) );

		$content = sprintf(
			'<div class="ootb-openstreetmap--map" %1$s style="height: %2$s;"></div>',
			self::default_attrs( $escaped_attrs ),
			$escaped_attrs[ 'height' ]
		);

		return $this->render_callback( $render_callback_attrs, $content );
	}

	/**
	 * Gets the default attributes for the map.
	 *
	 * @param array $overrides The attributes to override.
	 *
	 * @return string
	 */
	private static function default_attrs( array $overrides = [] ): string {
		$data_attrs = array_merge(
			array_keys( self::overridable_attrs() ),
			[
				'showmarkers',
				'shapestyle',
				'shapetext',
				'markers',
				'bounds',
				'zoom',
				'minzoom',
				'maxzoom',
			]
		);
		$attrs      = [];
		foreach ( $data_attrs as $attr ) {
			$default_attr = Helper::default_block_attributes( $attr );
			if ( isset( $default_attr ) ) {
				if ( ! empty( $overrides[ $attr ] ) && $overrides[ $attr ] !== $default_attr ) {
					if ( 'marker' === $attr ) {
						$overrides[ $attr ] = Helper::get_marker_attr_from_url( $overrides[ $attr ] );
					}
					$default_attr = $overrides[ $attr ];
				}
				$attr    = 'data-' . $attr;
				$attrs[] = sprintf( '%s="%s"', $attr, $default_attr );
			}
		}

		return implode( ' ', $attrs );
	}

	/**
	 * Returns the attributes that can be overridden.
	 * @return string[]
	 */
	private static function overridable_attrs(): array {
		return [
			'provider'        => '',
			'maptype'         => '',
			'touchzoom'       => '',
			'scrollwheelzoom' => '',
			'dragging'        => '',
			'doubleclickzoom' => '',
			'marker'          => '',
		];
	}

	/**
	 * Gets the marker icon and allows the user to override it with a hook.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array|null
	 */
	private static function get_cf_marker_icon( int $post_id = 0 ): ?array {
		if ( empty( $post_id ) ) {
			return null;
		}
		$icon_url = apply_filters( 'ootb_cf_marker_icon', '', $post_id );
		$icon     = null;
		if ( ! empty( $icon_url ) && filter_var( $icon_url, FILTER_VALIDATE_URL ) ) {
			$icon = [
				'url' => esc_url( $icon_url ),
			];
		}

		return $icon;
	}
}
