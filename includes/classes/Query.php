<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 * Query Maps
 *
 * @since   2.6.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Query {
	public function __construct() {
		add_action( 'wp_ajax_ootb_get_markers', [ $this, 'handle_ajax_call' ] );
	}

	private static function get_post_type( $fallback = 'post' ) {
		return apply_filters( 'ootb_query_post_type', $fallback );
	}

	private static function maybe_extra_args( $args = [] ) {
		$extra_args = apply_filters( 'ootb_query_extra_args', [] );

		// Get new args that don't exist in the defaults.
		$new_unique_args = array_diff_key( $extra_args, $args );

		// Combine default args with new args.
		return array_merge( $args, $new_unique_args );
	}

	public function handle_ajax_call() {
		$post_id = $_POST[ 'post_id' ] ?? 0;
		$post_id = is_numeric( $post_id ) ? absint( $post_id ) : 0;

		$args = $_POST[ 'query_args' ] ?? '';
		$args = ! empty( $args ) ? json_decode( stripslashes( $args ), true ) : '';

		$data = self::get_markers( $post_id, $args );
		echo json_encode( $data );
		wp_die();
	}

	public static function get_markers( $current_post_id = 0, $query_args = [] ) {
		$default_args = [
			'post_type'              => self::get_post_type(),
			'posts_per_page'         => apply_filters( 'ootb_query_posts_per_page', 100 ),
			's'                      => '<!-- wp:ootb/openstreetmap ',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		$args         = wp_parse_args( $query_args, $default_args );

		$query        = new \WP_Query( self::maybe_extra_args( $args ) );

		if ( empty( $query->posts ) ) {
			return false;
		}

		return self::get_marker_data( $current_post_id, $query->posts );

	}

	public static function render_callback( $attributes, $content ) {
		if ( ( isset( $attributes[ 'serverSideRender' ] ) && ! $attributes[ 'serverSideRender' ] ) && ! empty( $attributes[ 'markers' ] ) ) {
			return $content;
		}
		$post_type                                = $attributes[ 'queryArgs' ][ 'post_type' ] ?? '';
		$attributes[ 'queryArgs' ][ 'post_type' ] = self::get_post_type( $post_type );

		$markers = Query::get_markers(
			get_the_ID(),
			$attributes[ 'queryArgs' ]
		);
		if ( empty( $markers ) ) {
			return $content;
		}
//xdebug_var_dump( $attributes);
		$escaped_markers = htmlentities( $markers, ENT_QUOTES, 'UTF-8' );

		return preg_replace(
			'/data-markers=".*?"/',
			sprintf( 'data-markers="%s"', $escaped_markers ),
			$content
		);
	}

	private static function get_marker_data( $current_post_id, $post_ids ) {
		$markers = [];
		foreach ( $post_ids as $post_id ) {
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
				$markers[] = $attrs->markers;
			}
		}
		$flattened_markers = array_merge( ...$markers );

		return wp_json_encode( $flattened_markers );
	}
}
