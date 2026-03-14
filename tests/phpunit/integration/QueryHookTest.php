<?php
/**
 * Integration tests for Query filter hooks:
 * ootb_query_post_type, ootb_query_posts_per_page, ootb_query_extra_args,
 * ootb_block_marker_text, and ootb_cf_marker_icon.
 *
 * @package ootb-openstreetmap
 */

namespace OOTB\Tests\Integration;

use OOTB\Query;
use WP_UnitTestCase;

class QueryHookTest extends WP_UnitTestCase {

	private int $post_id = 0;

	protected function tearDown(): void {
		if ( $this->post_id ) {
			wp_delete_post( $this->post_id, true );
			$this->post_id = 0;
		}
		remove_all_filters( 'ootb_query_post_type' );
		remove_all_filters( 'ootb_query_posts_per_page' );
		remove_all_filters( 'ootb_query_extra_args' );
		remove_all_filters( 'ootb_block_marker_text' );
		remove_all_filters( 'ootb_cf_marker_icon' );
		parent::tearDown();
	}

	// =========================================================================
	// ootb_query_posts_per_page
	// =========================================================================

	/**
	 * Default value is 100 when no filter is registered.
	 */
	public function test_posts_per_page_default_is_100(): void {
		$this->assertSame( 100, Query::get_posts_per_page() );
	}

	/**
	 * Filter return value replaces the default.
	 */
	public function test_posts_per_page_filter_overrides_default(): void {
		add_filter( 'ootb_query_posts_per_page', static fn() => 25 );

		$this->assertSame( 25, Query::get_posts_per_page() );
	}

	/**
	 * Filter receives the default value as its argument.
	 */
	public function test_posts_per_page_filter_receives_default_as_argument(): void {
		add_filter( 'ootb_query_posts_per_page', static fn( int $v ) => $v * 2 );

		$this->assertSame( 200, Query::get_posts_per_page() );
	}

	// =========================================================================
	// ootb_query_post_type
	// =========================================================================

	/**
	 * Without the filter, only 'post' type is queried; a 'page' with block
	 * content must not appear. With the filter returning 'page', it must.
	 */
	public function test_post_type_filter_restricts_query_to_filtered_type(): void {
		$this->post_id = self::factory()->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => $this->build_block_content(
					[ [ 'lat' => '37.9838', 'lng' => '23.7275', 'text' => 'Athens' ] ]
				),
			]
		);

		// Default post_type is 'post'; the page must not be found.
		$this->assertFalse( Query::get_markers() );

		// With the filter, the page must be found.
		add_filter( 'ootb_query_post_type', static fn() => 'page' );
		$this->assertIsString( Query::get_markers() );
	}

	// =========================================================================
	// ootb_query_extra_args
	// =========================================================================

	/**
	 * A new key added by the filter is merged into the WP_Query args and
	 * affects the result — here post__not_in excludes the only matching post.
	 */
	public function test_extra_args_are_appended_to_query(): void {
		$this->post_id = self::factory()->post->create(
			[
				'post_status'  => 'publish',
				'post_content' => $this->build_block_content(
					[ [ 'lat' => '37.9838', 'lng' => '23.7275', 'text' => 'Athens' ] ]
				),
			]
		);

		// Without filter, the post is found.
		$this->assertIsString( Query::get_markers() );

		// post__not_in is not a default arg key, so it is merged in.
		$excluded_id = $this->post_id;
		add_filter( 'ootb_query_extra_args', static fn() => [ 'post__not_in' => [ $excluded_id ] ] );
		$this->assertFalse( Query::get_markers() );
	}

	/**
	 * Extra args must not overwrite keys already present in the default args.
	 * Attempting to set posts_per_page = 0 via the filter must be ignored.
	 */
	public function test_extra_args_do_not_overwrite_existing_args(): void {
		$this->post_id = self::factory()->post->create(
			[
				'post_status'  => 'publish',
				'post_content' => $this->build_block_content(
					[ [ 'lat' => '37.9838', 'lng' => '23.7275', 'text' => 'Athens' ] ]
				),
			]
		);

		// posts_per_page is already set in default args; the filter cannot override it.
		add_filter( 'ootb_query_extra_args', static fn() => [ 'posts_per_page' => 0 ] );
		$this->assertIsString( Query::get_markers() );
	}

	// =========================================================================
	// ootb_block_marker_text
	// =========================================================================

	/**
	 * Filter return value replaces the original marker text.
	 */
	public function test_block_marker_text_filter_modifies_popup_text(): void {
		$this->post_id = self::factory()->post->create(
			[
				'post_status'  => 'publish',
				'post_content' => $this->build_block_content(
					[ [ 'lat' => '37.9838', 'lng' => '23.7275', 'text' => 'Original text' ] ]
				),
			]
		);

		add_filter( 'ootb_block_marker_text', static fn() => 'Filtered text', 10, 3 );

		// current_post_id = 0 so the queried post is not skipped.
		$result = Query::get_markers( 0 );
		$this->assertIsString( $result );
		$this->assertStringContainsString( 'Filtered text', $result );
		$this->assertStringNotContainsString( 'Original text', $result );
	}

	/**
	 * Filter receives ($marker_text, $post_id, $current_post_id) as arguments.
	 */
	public function test_block_marker_text_filter_receives_correct_arguments(): void {
		$this->post_id = self::factory()->post->create(
			[
				'post_status'  => 'publish',
				'post_content' => $this->build_block_content(
					[ [ 'lat' => '37.9838', 'lng' => '23.7275', 'text' => 'Hello' ] ]
				),
			]
		);

		$captured     = [];
		$test_post_id = $this->post_id;

		add_filter(
			'ootb_block_marker_text',
			static function ( string $text, int $post_id, int $current_post_id ) use ( &$captured, $test_post_id ) {
				$captured = compact( 'text', 'post_id', 'current_post_id' );
				return $text;
			},
			10,
			3
		);

		Query::get_markers( 0 );

		$this->assertSame( 'Hello', $captured['text'] );
		$this->assertSame( $test_post_id, $captured['post_id'] );
		$this->assertSame( 0, $captured['current_post_id'] );
	}

	// =========================================================================
	// ootb_cf_marker_icon
	// =========================================================================

	/**
	 * Without the filter, icon is null in the marker data.
	 */
	public function test_cf_marker_icon_absent_by_default(): void {
		$this->post_id = $this->create_geodata_post();

		$result  = Query::get_markers( 0, [], true );
		$decoded = json_decode( (string) $result, true );
		$this->assertNull( $decoded[0]['icon'] );
	}

	/**
	 * A valid URL returned by the filter is exposed as icon.url in the marker.
	 */
	public function test_cf_marker_icon_filter_sets_icon_url(): void {
		$this->post_id = $this->create_geodata_post();

		add_filter( 'ootb_cf_marker_icon', static fn() => 'https://example.com/icon.png', 10, 2 );

		$result  = Query::get_markers( 0, [], true );
		$decoded = json_decode( (string) $result, true );
		$this->assertNotNull( $decoded[0]['icon'] );
		$this->assertSame( 'https://example.com/icon.png', $decoded[0]['icon']['url'] );
	}

	/**
	 * An invalid URL returned by the filter is discarded; icon remains null.
	 */
	public function test_cf_marker_icon_filter_invalid_url_is_ignored(): void {
		$this->post_id = $this->create_geodata_post();

		add_filter( 'ootb_cf_marker_icon', static fn() => 'not-a-url', 10, 2 );

		$result  = Query::get_markers( 0, [], true );
		$decoded = json_decode( (string) $result, true );
		$this->assertNull( $decoded[0]['icon'] );
	}

	/**
	 * Filter receives ('', $post_id) as arguments.
	 */
	public function test_cf_marker_icon_filter_receives_post_id_as_argument(): void {
		$this->post_id = $this->create_geodata_post();

		$captured     = null;
		$test_post_id = $this->post_id;

		add_filter(
			'ootb_cf_marker_icon',
			static function ( string $icon, int $post_id ) use ( &$captured, $test_post_id ) {
				$captured = $post_id;
				return '';
			},
			10,
			2
		);

		Query::get_markers( 0, [], true );

		$this->assertSame( $test_post_id, $captured );
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Builds a block comment string with the given markers so that
	 * Query::get_markers() finds the post via its default search
	 * (s = '<!-- wp:ootb/openstreetmap ').
	 *
	 * @param array<int, array<string, string>> $markers
	 */
	private function build_block_content( array $markers ): string {
		$attrs = (string) wp_json_encode( [ 'markers' => $markers ] );
		return "<!-- wp:ootb/openstreetmap {$attrs} -->\n"
			. '<div class="wp-block-ootb-openstreetmap"></div>' . "\n"
			. '<!-- /wp:ootb/openstreetmap -->';
	}

	/**
	 * Creates a published post with geo_latitude and geo_longitude meta.
	 */
	private function create_geodata_post(): int {
		$post_id = self::factory()->post->create( [ 'post_status' => 'publish' ] );
		update_post_meta( $post_id, 'geo_latitude', '37.9838' );
		update_post_meta( $post_id, 'geo_longitude', '23.7275' );
		return $post_id;
	}
}
