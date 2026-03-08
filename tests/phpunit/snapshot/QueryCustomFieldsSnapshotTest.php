<?php
/**
 * Snapshot tests for Query::get_markers() using the custom fields (geodata) path.
 *
 * These tests cover the $query_custom_fields = true branch in Query::get_marker_data().
 * Specifically, they verify the text fallback behaviour: when geo_address is empty
 * and the ootb_cf_modal_content filter returns nothing, the popup text defaults to
 * a linked post title (with optional thumbnail prepended).
 *
 * Each test creates its own post with geodata meta and deletes it in tearDown().
 *
 * Normalisation: post IDs and permalink query strings (?p=N) are replaced
 * because they are non-deterministic across test runs.
 *
 * To update snapshots intentionally:
 *   UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --testsuite snapshot
 *
 * Any snapshot update PR must include:
 *   - Updated fixture file(s) in tests/phpunit/fixtures/
 *   - A one-line changelog entry
 *   - A short rationale referencing ARCHITECTURE.md if public output changed
 */

namespace OOTB\Tests\Snapshot;

use OOTB\Query;
use Spatie\Snapshots\MatchesSnapshots;
use WP_UnitTestCase;

class QueryCustomFieldsSnapshotTest extends WP_UnitTestCase {
	use MatchesSnapshots;

	private int $post_id = 0;
	private int $attachment_id = 0;

	protected function getSnapshotDirectory(): string {
		return __DIR__ . '/../fixtures';
	}

	protected function tearDown(): void {
		if ( $this->post_id ) {
			wp_delete_post( $this->post_id, true );
			$this->post_id = 0;
		}
		if ( $this->attachment_id ) {
			wp_delete_post( $this->attachment_id, true );
			$this->attachment_id = 0;
		}
		remove_all_filters( 'ootb_cf_modal_content' );
		remove_all_filters( 'post_thumbnail_html' );
		parent::tearDown();
	}

	// =========================================================================
	// Tests
	// =========================================================================

	/**
	 * When geo_address is present and no filter overrides it, the marker text
	 * is the address value (unchanged behaviour from before the feature).
	 */
	public function test_cf_marker_text_uses_address_when_present(): void {
		$this->post_id = $this->create_post_with_geodata(
			[
				'latitude'  => '37.9838',
				'longitude' => '23.7275',
				'address'   => 'Athens, Greece',
			]
		);

		$result = Query::get_markers( 0, [], true );

		$this->assertIsString( $result );
		$this->assertMatchesSnapshot( $this->normalize_markers( $result ) );
	}

	/**
	 * When geo_address is empty and no filter provides content, the marker text
	 * falls back to a linked post title: <a href="{permalink}">{title}</a>.
	 */
	public function test_cf_marker_text_falls_back_to_title_link_when_no_address(): void {
		$this->post_id = $this->create_post_with_geodata(
			[
				'latitude'  => '37.9838',
				'longitude' => '23.7275',
			]
		);

		$result = Query::get_markers( 0, [], true );

		$this->assertIsString( $result );
		$this->assertMatchesSnapshot( $this->normalize_markers( $result ) );
	}

	/**
	 * When geo_address is empty and the post has a featured image, the marker
	 * text is the thumbnail HTML followed by the linked post title.
	 */
	public function test_cf_marker_text_prepends_thumbnail_when_no_address(): void {
		$this->post_id = $this->create_post_with_geodata(
			[
				'latitude'  => '37.9838',
				'longitude' => '23.7275',
			]
		);

		// Attach a real (but imageless) attachment so get_post_thumbnail_id() returns non-zero.
		$this->attachment_id = self::factory()->post->create( [ 'post_type' => 'attachment' ] );
		update_post_meta( $this->post_id, '_thumbnail_id', $this->attachment_id );

		// Return controlled thumbnail HTML via filter — no real image file needed.
		$test_post_id = $this->post_id;
		add_filter(
			'post_thumbnail_html',
			static function ( $html, $pid ) use ( $test_post_id ) {
				return $pid === $test_post_id
					? '<img class="attachment-thumbnail size-thumbnail" src="http://example.org/thumbnail.jpg" />'
					: $html;
			},
			10,
			2
		);

		$result = Query::get_markers( 0, [], true );

		$this->assertIsString( $result );
		$this->assertMatchesSnapshot( $this->normalize_markers( $result ) );
	}

	/**
	 * When the ootb_cf_modal_content filter returns a non-empty string, that
	 * string is used directly regardless of geo_address or fallback logic.
	 */
	public function test_cf_marker_text_respects_filter_override(): void {
		$this->post_id = $this->create_post_with_geodata(
			[
				'latitude'  => '37.9838',
				'longitude' => '23.7275',
			]
		);

		add_filter(
			'ootb_cf_modal_content',
			static function () {
				return '<strong>Custom popup content</strong>';
			}
		);

		$result = Query::get_markers( 0, [], true );

		$this->assertIsString( $result );
		$this->assertMatchesSnapshot( $this->normalize_markers( $result ) );
	}

	/**
	 * When ootb_cf_modal_content is hooked but intentionally returns empty (e.g.
	 * to suppress popups), the fallback must NOT trigger — the empty string is
	 * preserved. This verifies the has_filter() guard introduced to address the
	 * public filter semantics concern.
	 */
	public function test_cf_marker_text_filter_returning_empty_preserves_empty(): void {
		$this->post_id = $this->create_post_with_geodata(
			[
				'latitude'  => '37.9838',
				'longitude' => '23.7275',
			]
		);

		add_filter(
			'ootb_cf_modal_content',
			static function () {
				return '';
			}
		);

		$result = Query::get_markers( 0, [], true );

		$this->assertIsString( $result );
		$this->assertMatchesSnapshot( $this->normalize_markers( $result ) );
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Creates a published post and sets geo_* post meta.
	 *
	 * @param array<string, string> $geo Keys: latitude, longitude, address (optional).
	 * @return int
	 */
	private function create_post_with_geodata( array $geo ): int {
		$post_id = self::factory()->post->create(
			[
				'post_title'  => 'Test CF Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);

		update_post_meta( $post_id, 'geo_latitude', $geo['latitude'] );
		update_post_meta( $post_id, 'geo_longitude', $geo['longitude'] );

		if ( isset( $geo['address'] ) ) {
			update_post_meta( $post_id, 'geo_address', $geo['address'] );
		}

		return $post_id;
	}

	/**
	 * Normalises non-deterministic values in the markers JSON for stable snapshots.
	 *
	 * - Post IDs are replaced with 0.
	 * - Permalink query strings (?p=N) are replaced with ?p=0.
	 *
	 * @param string $json Raw JSON string from Query::get_markers().
	 * @return string Normalised JSON.
	 */
	private function normalize_markers( string $json ): string {
		$markers = json_decode( $json, true );

		if ( ! is_array( $markers ) ) {
			return $json;
		}

		foreach ( $markers as &$marker ) {
			if ( isset( $marker['id'] ) ) {
				$marker['id'] = 0;
			}
			if ( isset( $marker['text'] ) && is_string( $marker['text'] ) ) {
				$marker['text'] = preg_replace( '/\?p=\d+/', '?p=0', $marker['text'] ) ?? $marker['text'];
			}
		}
		unset( $marker );

		return (string) wp_json_encode( $markers );
	}
}
