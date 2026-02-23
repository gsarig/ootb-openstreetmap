<?php
/**
 * Snapshot tests for the OOTB OpenStreetMap block output.
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

use Spatie\Snapshots\MatchesSnapshots;
use WP_UnitTestCase;

class BlockSnapshotTest extends WP_UnitTestCase {
	use MatchesSnapshots;

	protected function getSnapshotDirectory(): string {
		return __DIR__ . '/../fixtures';
	}

	public function test_default_block_render_with_single_marker(): void {
		$attributes = [
			'mapId'           => 'ootb-test-map-1',
			'lat'             => '37.9838',
			'lng'             => '23.7275',
			'zoom'            => 13,
			'markers'         => [
				[
					'id'      => 'marker-test-1',
					'lat'     => '37.9838',
					'lng'     => '23.7275',
					'title'   => 'Test Marker Athens',
					'content' => 'This is a deterministic test marker.',
					'icon'    => '',
				],
			],
			'provider'        => 'OpenStreetMap.Mapnik',
			'gestureHandling' => false,
		];

		$output     = render_block( [ 'blockName' => 'ootb/openstreetmap', 'attrs' => $attributes ] );
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	public function test_block_render_with_no_markers(): void {
		$attributes = [
			'mapId'           => 'ootb-test-map-empty',
			'lat'             => '37.9838',
			'lng'             => '23.7275',
			'zoom'            => 10,
			'markers'         => [],
			'provider'        => 'OpenStreetMap.Mapnik',
			'gestureHandling' => false,
		];

		$output     = render_block( [ 'blockName' => 'ootb/openstreetmap', 'attrs' => $attributes ] );
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	public function test_block_render_with_gesture_handling(): void {
		$attributes = [
			'mapId'           => 'ootb-test-map-gesture',
			'lat'             => '37.9838',
			'lng'             => '23.7275',
			'zoom'            => 13,
			'markers'         => [],
			'provider'        => 'OpenStreetMap.Mapnik',
			'gestureHandling' => true,
		];

		$output     = render_block( [ 'blockName' => 'ootb/openstreetmap', 'attrs' => $attributes ] );
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	private function normalize_output( string $html ): string {
		$html = preg_replace( '/data-nonce="[^"]*"/', 'data-nonce="NORMALIZED"', $html ) ?? $html;
		$html = preg_replace( '/\?ver=[0-9a-z.\-]+/', '?ver=NORMALIZED', $html ) ?? $html;
		$html = preg_replace( '/\s+/', ' ', $html ) ?? $html;
		return trim( $html );
	}
}
