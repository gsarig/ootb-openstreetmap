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
		add_action( 'wp_ajax_ootb_get_markers', [ $this, 'ajax_call' ] );
	}

	public function ajax_call() {
		if ( isset( $_POST[ 'post_id' ] ) && is_numeric( $_POST[ 'post_id' ] ) ) {
			$post_id = absint( $_POST[ 'post_id' ] );
		} else {
			$post_id = 0;
		}

		$data = self::get_markers( $post_id );

		echo json_encode( $data );
		wp_die();
	}

	public static function get_markers( $current_post_id = 0 ) {
		$args  = [
			'post_type'              => 'post',
			'posts_per_page'         => 100,
			's'                      => '<!-- wp:ootb/openstreetmap ',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		$query = new \WP_Query( $args );
		if ( empty( $query->posts ) ) {
			return false;
		}
		$markers = [];
		foreach ( $query->posts as $post_id ) {
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
				if ( empty( $attrs->markers ) ) {
					continue;
				}
				$markers[] = $attrs->markers;
			}
		}
		$flattened_markers = array_merge( ...$markers );

		return wp_json_encode( $flattened_markers );
	}
}
