<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 * Helper functions
 *
 * @since   1.2
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Helper {
	/**
	 * Asset Providers.
	 *
	 * @param array $options Options to be used with json_decode().
	 *
	 * @return mixed
	 */
	public static function providers( array $options = [] ): mixed {
		$json_file = OOTB_PLUGIN_PATH . 'assets/providers.json';

		return wp_json_file_decode( $json_file, $options );
	}

	/**
	 * The valid map types.
	 *
	 * @return string[]
	 */
	public static function map_types(): array {
		return [ 'markers', 'polygon', 'polyline' ];
	}

	/**
	 * Get the default values.
	 *
	 * @param string $key The key to check.
	 *
	 * @return string
	 */
	public static function get_default( string $key = '' ): string {
		if ( empty( $key ) ) {
			return '';
		}
		$defaults = [
			'height'    => '400px',
			'post_type' => 'post',
		];
		if ( empty( $defaults[ $key ] ) ) {
			return '';
		}

		return $defaults[ $key ];
	}

	/**
	 * Checks if the block exists in the frontend.
	 *
	 * @param string $block_name The block name.
	 *
	 * @return bool
	 */
	public static function has_block_in_frontend( string $block_name = '' ): bool {
		if ( empty( $block_name ) ) {
			return false;
		}

		return (
			       has_block( $block_name ) ||
			       self::has_block_in_reusable( $block_name ) ||
			       self::has_block_in_widget( $block_name )
		       )
		       && ! is_admin();
	}

	/**
	 * Check if the block exists in a reusable block.
	 *
	 * @param string $block_name The block name.
	 * @param int $post_id The post ID.
	 *
	 * @return bool
	 */
	public static function has_block_in_reusable( string $block_name = '', int $post_id = 0 ): bool {
		if ( empty( $block_name ) ) {
			return false;
		}

		$post_id = ( 0 !== $post_id ) ? $post_id : get_the_ID();

		if ( empty( $post_id ) || ! has_block( 'block', $post_id ) ) {
			return false;
		}

		$content = get_post_field( 'post_content', $post_id );
		$blocks  = parse_blocks( $content );

		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			if ( 'core/block' === $block[ 'blockName' ] && ! empty( $block[ 'attrs' ][ 'ref' ] ) ) {
				if ( has_block( $block_name, $block[ 'attrs' ][ 'ref' ] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if the block exists in a widget.
	 *
	 * @param string $block_name The block name.
	 *
	 * @return bool
	 */
	public static function has_block_in_widget( string $block_name = '' ): bool {
		if ( empty( $block_name ) ) {
			return false;
		}

		$blocks = get_option( 'widget_block' );

		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			if ( is_array( $block ) && isset( $block[ 'content' ] ) && has_block( $block_name, $block[ 'content' ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The fallback coordinates.
	 *
	 * @return string[]
	 */
	public static function fallback_location(): array {
		return [
			"37.97155174977503",
			"23.72656345367432",
		];
	}

	/**
	 * Get the default location and return its coordinates.
	 *
	 * @return array|string[]
	 */
	public static function default_location(): array {
		$options = self::get_option();
		if ( ! empty( $options[ 'default_lat' ] ) && ! empty( $options[ 'default_lng' ] ) ) {
			return [
				$options[ 'default_lat' ],
				$options[ 'default_lng' ],
			];
		}
		$timezone = wp_timezone_string();
		// Return empty if a manual timezone is set.
		if ( false === strpos( $timezone, '/' ) ) {
			return self::fallback_location();
		}

		$defaults = wp_json_file_decode( OOTB_PLUGIN_PATH . '/assets/defaults.json', [
			'associative' => true,
		] );

		$column = array_column( $defaults, 'timezone' );
		$entry  = array_search( $timezone, $column );
		if ( empty( $defaults[ $entry ] ) || empty( $defaults[ $entry ]->lat ) || empty( $defaults[ $entry ]->lng ) ) {
			return self::fallback_location();
		}

		return [
			strval( $defaults[ $entry ]->lat ),
			strval( $defaults[ $entry ]->lng ),
		];
	}

	/**
	 * Get the gesture handling locale. Since `leaflet-gesture-handling` has its own locales, we need to check if the WordPress locale matches one of them. If not, we return `en` as the default.
	 *
	 * @return string
	 */
	public static function get_gesture_handling_locale(): string {
		$wp_locale         = get_locale();
		$unfiltered_locale = str_replace( '_', '-', $wp_locale );
		$locales_path      = OOTB_PLUGIN_PATH . 'assets/vendor/leaflet-gesture-handling/locales/';
		if ( file_exists( $locales_path . $unfiltered_locale . '.js' ) ) {
			return $unfiltered_locale;
		}

		$maybe_locale = substr( $unfiltered_locale, 0, 2 );

		if ( file_exists( $locales_path . $maybe_locale . '.js' ) ) {
			return $maybe_locale;
		}

		return 'en';
	}

	/**
	 * Get the plugin options.
	 *
	 * @param string $option The option name or `all` to return all options, including the API keys.
	 *
	 * @return mixed
	 */
	public static function get_option( string $option = '' ) {
		$options = get_option( 'ootb_options' );
		if ( 'all' === $option ) {
			return $options;
		}
		if ( ! empty( $option ) ) {
			return $options[ $option ] ?? '';
		}

		if ( ! empty( $options[ 'api_openai' ] ) ) {
			// We don't want to expose the OpenAI API key to the client.
			unset( $options[ 'api_openai' ] );
		}

		return $options;
	}

	/**
	 * Get the post types that support the block editor.
	 *
	 * @return array
	 */
	public static function get_post_types(): array {
		if ( has_filter( 'ootb_query_post_type' ) ) {
			return [];
		}
		$args                    = [
			'public' => true
		];
		$post_types              = get_post_types( $args, 'objects' );
		$block_editor_post_types = [];

		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type->name, 'editor' ) ) {
				$block_editor_post_types[] = [
					'label' => $post_type->label,
					'value' => $post_type->name,
				];
			}
		}

		return $block_editor_post_types;
	}

	/**
	 * Retrieves the default block attributes in PHP.
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @noinspection PhpRedundantOptionalArgumentInspection
	 * @return array|mixed|string
	 */
	public static function default_block_attributes( string $attr_name = '' ) {
		$file = OOTB_PLUGIN_PATH . 'build/block/block.json';
		if ( ! file_exists( $file ) ) {
			return [];
		}

		$data       = wp_json_file_decode( $file, [
			'associative' => true,
		] );
		$attributes = $data[ 'attributes' ] ?? [];
		if ( empty( $attributes ) ) {
			return [];
		}

		$defaults = array_map( function ( $attr ) {
			return $attr[ 'default' ];
		}, $attributes );

		$defaults = array_change_key_case( $defaults, CASE_LOWER );
		if ( ! empty( $attr_name ) ) {
			if ( isset( $defaults[ $attr_name ] ) && true === $defaults[ $attr_name ] ) {
				return 'true';
			} elseif ( isset( $defaults[ $attr_name ] ) && false === $defaults[ $attr_name ] ) {
				return 'false';
			} elseif ( 'bounds' === $attr_name ) {
				return '[null, null]';
			} elseif ( 'marker' === $attr_name ) {
				return self::get_marker_attr_from_url( OOTB_PLUGIN_URL . 'assets/vendor/leaflet/images/marker-icon.png' );
			} elseif ( isset( $defaults[ $attr_name ] ) && is_array( $defaults[ $attr_name ] ) ) {
				return wp_json_encode( $defaults[ $attr_name ] );
			}

			return $defaults[ $attr_name ] ?? null;
		}

		return $defaults;
	}

	/**
	 * Gets the marker attributes from the image URL.
	 *
	 * @param string $img_src The image URL.
	 *
	 * @return string|void
	 */
	public static function get_marker_attr_from_url( string $img_src = '' ) {
		if ( empty( $img_src ) ) {
			return '';
		}
		$image_size = getimagesize( $img_src );
		if ( empty( $image_size[ 0 ] ) || empty( $image_size[ 1 ] ) ) {
			return '';
		}
		$width     = $image_size[ 0 ];
		$height    = $image_size[ 1 ];
		$jsonArray = [
			"iconUrl"     => $img_src,
			"iconAnchor"  => [
				round( $width / 2 ),
				$height
			],
			"popupAnchor" => [ 0, - $height ]
		];
		$jsonStr   = wp_json_encode( $jsonArray );

		return urlencode( $jsonStr );
	}

	public static function sanitize_attrs( array $attrs ): array {
		$valid_args = [
			'source',
			'post_type',
			'posts_per_page',
			'post_ids',
			'height',
			'provider',
			'maptype',
			'touchzoom',
			'scrollwheelzoom',
			'dragging',
			'doubleclickzoom',
			'marker',
			'fullscreen',
		];

		foreach ( $attrs as $key => $value ) {
			if ( ! in_array( $key, $valid_args, true ) ) {
				unset( $attrs[ $key ] );
			}
			$attrs[ $key ] = match ( $key ) {
				'source' => in_array( $value, [ 'geodata', 'block' ], true ) ? $value : '',
				'post_type' => in_array( $value, array_column( self::get_post_types(), 'value' ), true ) ? $value : self::get_default('post_type'),
				'posts_per_page' => ( is_int( $value ) || $value === - 1 ) ? $value : Query::get_posts_per_page(),
				'post_ids' => ( preg_match( '/^(\d+,)*\d+$/', $value ) === 1 ) ? $value : '',
				'height' => ( preg_match( '/^\d+px$/', $value ) === 1 ) ? $value : self::get_default( 'height' ),
				'provider' => in_array( $value, array_keys( self::providers( [ 'associative' => true ] ) ), true ) ? $value : '',
				'maptype' => in_array( $value, self::map_types(), true ) ? $value : '',
				'touchzoom', 'scrollwheelzoom', 'dragging', 'doubleclickzoom', 'fullscreen' => in_array( $value, [
					'true',
					'false'
				], true ) ? $value : '',
				'marker' => ( filter_var( $value, FILTER_VALIDATE_URL ) !== false ) ? $value : '',
				default => $value,
			};
		}

		return $attrs;
	}
}
