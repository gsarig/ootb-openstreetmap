<?php
/**
 * Snapshot tests for block attribute variants and PHP render_callback code paths.
 *
 * Covers:
 *   - Group 1: Distinct PHP code paths in Query::render_callback() not covered by
 *     BlockSnapshotTest (snake_case attributes used by the shortcode layer).
 *   - Group 2: Block attribute variant passthrough — realistic HTML is provided as
 *     innerHTML and verified to be returned unchanged when no server-side markers
 *     are found in the test environment.
 *
 * Key insight: render_callback checks snake_case keys (server_side_render,
 * query_args, query_custom_fields) which are set by the shortcode layer.
 * The camelCase block.json attributes (serverSideRender, queryArgs,
 * queryCustomFields) are a separate concern handled by the JS editor.
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

class BlockAttributeSnapshotTest extends WP_UnitTestCase {
	use MatchesSnapshots;

	protected function getSnapshotDirectory(): string {
		return __DIR__ . '/../fixtures';
	}

	// =========================================================================
	// Group 1: PHP render_callback branch coverage
	// =========================================================================

	/**
	 * serverSideRender=true is the camelCase block.json attribute.
	 * render_callback checks the snake_case flag server_side_render (shortcode-only),
	 * so this attribute does not trigger the early return. The WP_Query runs, finds
	 * no posts, and the provided HTML is returned unchanged.
	 */
	public function test_block_render_with_server_side_render_camelcase_attribute(): void {
		$markers = [
			[
				'id'      => 'marker-ssr-1',
				'lat'     => '37.9838',
				'lng'     => '23.7275',
				'title'   => 'SSR Athens',
				'content' => 'serverSideRender camelCase test',
				'icon'    => '',
			],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'serverSideRender' => true,
					'queryArgs'        => [ 'post_type' => 'post' ],
					'markers'          => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * query_custom_fields=true (snake_case — shortcode/internal flag) triggers the
	 * geodata code path in get_markers(): uses a meta_query for geo_latitude and
	 * geo_longitude instead of a content search. No posts with geodata exist in the
	 * test environment, so the provided HTML is returned unchanged.
	 */
	public function test_block_render_with_snake_case_query_custom_fields(): void {
		$markers = [
			[
				'id'      => 'marker-cf-1',
				'lat'     => '37.9838',
				'lng'     => '23.7275',
				'title'   => 'Geodata Athens',
				'content' => 'custom fields test',
				'icon'    => '',
			],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'query_custom_fields' => true,
					'markers'             => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * query_args (snake_case) with a non-default post type targets 'page' instead of
	 * 'post' in the WP_Query content search. No pages with the ootb block exist in
	 * the test environment, so the provided HTML is returned unchanged.
	 */
	public function test_block_render_with_snake_case_custom_query_post_type(): void {
		$markers = [
			[
				'id'      => 'marker-page-1',
				'lat'     => '37.9838',
				'lng'     => '23.7275',
				'title'   => 'Page query test',
				'content' => 'querying pages',
				'icon'    => '',
			],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'query_args' => [ 'post_type' => 'page' ],
					'markers'    => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * server_side_render=false (snake_case) combined with a non-empty markers array
	 * triggers the early-return code path in render_callback — $content is returned
	 * immediately without running any WP_Query.
	 *
	 * This is the path exercised when the shortcode renders a block that already
	 * carries its own static marker data and does not need a server-side query.
	 */
	public function test_block_render_early_return_with_static_markers(): void {
		$markers    = [
			[
				'id'      => 'marker-static-1',
				'lat'     => '37.9838',
				'lng'     => '23.7275',
				'title'   => 'Static Athens',
				'content' => 'Early-return marker',
				'icon'    => '',
			],
		];
		$html       = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);
		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'server_side_render' => false,
					'markers'            => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	// =========================================================================
	// Group 2: Block attribute variant passthrough
	// =========================================================================

	/**
	 * Default block configuration: openstreetmap provider, marker type, single
	 * marker, all default boolean and numeric values.
	 */
	public function test_block_render_passthrough_with_default_attributes(): void {
		$markers = [
			[
				'id'      => 'marker-default-1',
				'lat'     => '37.9838',
				'lng'     => '23.7275',
				'title'   => 'Athens',
				'content' => 'Default marker',
				'icon'    => '',
			],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 8,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [ 'markers' => $markers, 'mapHeight' => 400 ],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Polygon map type with custom shape colour (#FF0000) and weight (5).
	 * Uses three markers as polygon vertices.
	 */
	public function test_block_render_passthrough_with_polygon_maptype(): void {
		$markers = [
			[ 'id' => 'p1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => '', 'content' => '', 'icon' => '' ],
			[ 'id' => 'p2', 'lat' => '38.0000', 'lng' => '23.7500', 'title' => '', 'content' => '', 'icon' => '' ],
			[ 'id' => 'p3', 'lat' => '37.9700', 'lng' => '23.7600', 'title' => '', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'polygon',
				'showMarkers'     => true,
				'shapeColor'      => '#FF0000',
				'shapeWeight'     => 5,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275],[38.0000,23.7500],[37.9700,23.7600]]',
				'zoom'            => 12,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'mapType'    => 'polygon',
					'shapeColor' => '#FF0000',
					'shapeWeight' => 5,
					'markers'    => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Polyline map type with two coordinate points and a shapeText label.
	 */
	public function test_block_render_passthrough_with_polyline_maptype(): void {
		$markers = [
			[ 'id' => 'pl1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => '', 'content' => '', 'icon' => '' ],
			[ 'id' => 'pl2', 'lat' => '38.0000', 'lng' => '23.7500', 'title' => '', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'polyline',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => 'Test route',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275],[38.0000,23.7500]]',
				'zoom'            => 12,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'mapType'   => 'polyline',
					'shapeText' => 'Test route',
					'markers'   => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Three distinct markers with different coordinates and titles.
	 */
	public function test_block_render_passthrough_with_multiple_markers(): void {
		$markers = [
			[ 'id' => 'mm1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => 'Athens', 'content' => 'First marker', 'icon' => '' ],
			[ 'id' => 'mm2', 'lat' => '40.6401', 'lng' => '22.9444', 'title' => 'Thessaloniki', 'content' => 'Second marker', 'icon' => '' ],
			[ 'id' => 'mm3', 'lat' => '35.3387', 'lng' => '25.1442', 'title' => 'Heraklion', 'content' => 'Third marker', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275],[40.6401,22.9444],[35.3387,25.1442]]',
				'zoom'            => 7,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [ 'markers' => $markers, 'zoom' => 7 ],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * All four interaction booleans set to false — non-default for every one.
	 * dragging, touchZoom, doubleClickZoom, scrollWheelZoom all default to true.
	 */
	public function test_block_render_passthrough_with_interactions_disabled(): void {
		$markers = [
			[ 'id' => 'ia1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => 'No interactions', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => false,
				'touchZoom'       => false,
				'doubleClickZoom' => false,
				'scrollWheelZoom' => false,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'dragging'        => false,
					'touchZoom'       => false,
					'doubleClickZoom' => false,
					'scrollWheelZoom' => false,
					'markers'         => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Custom map height (600 px, default is 400) and non-default zoom range.
	 */
	public function test_block_render_passthrough_with_custom_height_and_zoom(): void {
		$markers = [
			[ 'id' => 'z1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => 'Custom zoom', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 15,
				'minZoom'         => 5,
				'maxZoom'         => 16,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 600,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'mapHeight' => 600,
					'zoom'      => 15,
					'minZoom'   => 5,
					'maxZoom'   => 16,
					'markers'   => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * MapBox provider with a mapboxStyleUrl. The data-mapboxstyle attribute is only
	 * present when both provider='mapbox' and a style URL are set.
	 */
	public function test_block_render_passthrough_with_mapbox_provider(): void {
		$markers = [
			[ 'id' => 'mb1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => 'MapBox marker', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'mapbox',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
				'mapboxStyle'     => 'mapbox://styles/testuser/abc123',
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'provider'      => 'mapbox',
					'mapboxStyleUrl' => 'mapbox://styles/testuser/abc123',
					'markers'       => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * showMarkers=false combined with polygon type — renders a closed shape
	 * without displaying individual marker pins.
	 */
	public function test_block_render_passthrough_with_showmarkers_false(): void {
		$markers = [
			[ 'id' => 'sm1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => '', 'content' => '', 'icon' => '' ],
			[ 'id' => 'sm2', 'lat' => '38.0000', 'lng' => '23.7500', 'title' => '', 'content' => '', 'icon' => '' ],
			[ 'id' => 'sm3', 'lat' => '37.9700', 'lng' => '23.7600', 'title' => '', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'polygon',
				'showMarkers'     => false,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275],[38.0000,23.7500],[37.9700,23.7600]]',
				'zoom'            => 12,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'showMarkers' => false,
					'mapType'     => 'polygon',
					'markers'     => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Stamen provider — uses the Stamen tile service (watercolor style).
	 * The data-provider attribute value differs from openstreetmap and mapbox.
	 */
	public function test_block_render_passthrough_with_stamen_provider(): void {
		$markers = [
			[ 'id' => 'st1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => 'Stamen marker', 'content' => '', 'icon' => '' ],
		];
		$html    = $this->build_block_html(
			[
				'provider'        => 'stamen',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [
					'provider' => 'stamen',
					'markers'  => $markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Custom marker icon — a non-default icon object in data-marker.
	 * Exercises the passthrough of the defaultIcon block attribute (set via save.js)
	 * into the data-marker HTML attribute, which the PHP render_callback preserves.
	 */
	public function test_block_render_passthrough_with_custom_marker_icon(): void {
		$custom_icon = [
			'iconUrl'     => 'https://example.com/custom-pin.png',
			'iconAnchor'  => [ 16, 32 ],
			'popupAnchor' => [ 0, -32 ],
		];
		$markers     = [
			[ 'id' => 'ci1', 'lat' => '37.9838', 'lng' => '23.7275', 'title' => 'Custom icon marker', 'content' => 'Marker with a custom icon', 'icon' => '' ],
		];
		$html        = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $markers,
				'bounds'          => '[[37.9838,23.7275]]',
				'zoom'            => 13,
				'minZoom'         => 2,
				'maxZoom'         => 18,
				'dragging'        => true,
				'touchZoom'       => true,
				'doubleClickZoom' => true,
				'scrollWheelZoom' => true,
				'mapHeight'       => 400,
				'marker'          => $custom_icon,
			]
		);

		$output     = render_block(
			[
				'blockName'    => 'ootb/openstreetmap',
				'attrs'        => [ 'markers' => $markers ],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Normalizes non-deterministic parts of the rendered output for stable snapshots.
	 */
	private function normalize_output( string $html ): string {
		$html = preg_replace( '/data-nonce="[^"]*"/', 'data-nonce="NORMALIZED"', $html ) ?? $html;
		$html = preg_replace( '/\?ver=[0-9a-z.\-]+/', '?ver=NORMALIZED', $html ) ?? $html;
		$html = preg_replace( '/\s+/', ' ', $html ) ?? $html;
		return trim( $html );
	}

	/**
	 * Builds a realistic block HTML string matching the structure produced by save.js.
	 *
	 * @param array<string, mixed> $config Map configuration keyed by attribute name.
	 * @return string
	 */
	private function build_block_html( array $config ): string {
		$shape_styles = [
			'fillColor' => $config['shapeColor'],
			'color'     => $config['shapeColor'],
			'weight'    => $config['shapeWeight'],
		];

		$default_icon = [
			'iconUrl'     => 'https://example.com/marker-icon.png',
			'iconAnchor'  => [ 12, 41 ],
			'popupAnchor' => [ 0, -41 ],
		];

		$data_shapestyle = rawurlencode( (string) wp_json_encode( $shape_styles ) );
		$data_markers    = rawurlencode( (string) wp_json_encode( $config['markers'] ) );
		$data_marker     = rawurlencode( (string) wp_json_encode( $config['marker'] ?? $default_icon ) );

		$inner  = 'data-provider="' . $config['provider'] . '"';
		$inner .= ' data-maptype="' . $config['mapType'] . '"';
		$inner .= ' data-showmarkers="' . ( $config['showMarkers'] ? 'true' : 'false' ) . '"';
		$inner .= ' data-shapestyle="' . $data_shapestyle . '"';
		$inner .= ' data-shapetext="' . $config['shapeText'] . '"';
		$inner .= ' data-markers="' . $data_markers . '"';
		$inner .= ' data-bounds="' . $config['bounds'] . '"';
		$inner .= ' data-zoom="' . (int) $config['zoom'] . '"';
		$inner .= ' data-minzoom="' . (int) $config['minZoom'] . '"';
		$inner .= ' data-maxzoom="' . (int) $config['maxZoom'] . '"';
		$inner .= ' data-dragging="' . ( $config['dragging'] ? 'true' : 'false' ) . '"';
		$inner .= ' data-touchzoom="' . ( $config['touchZoom'] ? 'true' : 'false' ) . '"';
		$inner .= ' data-doubleclickzoom="' . ( $config['doubleClickZoom'] ? 'true' : 'false' ) . '"';
		$inner .= ' data-scrollwheelzoom="' . ( $config['scrollWheelZoom'] ? 'true' : 'false' ) . '"';
		$inner .= ' data-marker="' . $data_marker . '"';

		if ( ! empty( $config['mapboxStyle'] ) ) {
			$inner .= ' data-mapboxstyle="' . rawurlencode( (string) wp_json_encode( $config['mapboxStyle'] ) ) . '"';
		}

		$inner .= ' style="height: ' . (int) $config['mapHeight'] . 'px"';

		return '<div class="wp-block-ootb-openstreetmap">'
			. '<div class="ootb-openstreetmap--map" ' . $inner . '>'
			. '</div>'
			. '</div>';
	}
}
