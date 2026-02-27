<?php
/**
 * Snapshot tests for the Query Maps and Shortcode code paths.
 *
 * These tests require a real published post in the database so that
 * Query::get_markers() finds it and the render_callback replaces
 * data-markers and data-bounds with live data.
 *
 * setUp() creates a post with a serialised ootb/openstreetmap block.
 * tearDown() deletes it so tests are fully isolated.
 *
 * Normalisation: data-marker (singular, the default icon) is normalised
 * because its value depends on getimagesize() reaching the plugin URL,
 * which is environment-dependent when called from the shortcode path.
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

class QuerySnapshotTest extends WP_UnitTestCase {
	use MatchesSnapshots;

	/**
	 * ID of the test post created in setUp and deleted in tearDown.
	 *
	 * @var int
	 */
	private int $test_post_id = 0;

	protected function getSnapshotDirectory(): string {
		return __DIR__ . '/../fixtures';
	}

	protected function setUp(): void {
		parent::setUp();

		$markers      = [
			[
				'id'      => 'qm1',
				'lat'     => '37.9838',
				'lng'     => '23.7275',
				'title'   => 'Query Test Marker',
				'content' => 'From a queried post',
				'icon'    => '',
			],
		];
		$attrs_json   = (string) wp_json_encode( [ 'markers' => $markers ] );
		$post_content = "<!-- wp:ootb/openstreetmap {$attrs_json} -->\n"
			. '<div class="wp-block-ootb-openstreetmap"></div>' . "\n"
			. '<!-- /wp:ootb/openstreetmap -->';

		$this->test_post_id = self::factory()->post->create(
			[
				'post_title'   => 'Test Query Map Post',
				'post_content' => $post_content,
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]
		);
	}

	protected function tearDown(): void {
		wp_delete_post( $this->test_post_id, true );
		$this->test_post_id = 0;
		parent::tearDown();
	}

	/**
	 * Query Maps: when published posts containing the ootb/openstreetmap block
	 * exist, render_callback replaces data-markers and data-bounds in the
	 * provided innerHTML with the markers fetched from those posts.
	 */
	public function test_query_maps_replaces_markers_from_queried_post(): void {
		$placeholder_markers = [
			[ 'id' => 'placeholder', 'lat' => '0', 'lng' => '0', 'title' => '', 'content' => '', 'icon' => '' ],
		];
		$html                = $this->build_block_html(
			[
				'provider'        => 'openstreetmap',
				'mapType'         => 'marker',
				'showMarkers'     => true,
				'shapeColor'      => '#008EFF',
				'shapeWeight'     => 3,
				'shapeText'       => '',
				'markers'         => $placeholder_markers,
				'bounds'          => '[[0,0]]',
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
				'attrs'        => [
					'query_args' => [
						'post_type'      => 'post',
						'posts_per_page' => 10,
					],
					'markers'    => $placeholder_markers,
				],
				'innerHTML'    => $html,
				'innerContent' => [ $html ],
			]
		);
		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	/**
	 * Shortcode output: [ootb_query] builds its own map HTML and calls
	 * render_callback with server_side_render=true. When matching posts exist,
	 * the markers from those posts are injected into data-markers and
	 * data-bounds is reset to [null,null].
	 *
	 * Note: OOTB_PLUGIN_URL is not a real URL in the Docker test environment,
	 * so getimagesize() in Helper::get_marker_attr_from_url() emits E_WARNING.
	 * We suppress it here — the function gracefully returns '' on failure and
	 * the empty data-marker is normalised away by normalize_output().
	 */
	public function test_shortcode_render_with_actual_post_data(): void {
		$query = new Query();

		// Suppress E_WARNING from getimagesize() called by default_attrs() when
		// building the shortcode HTML. It fails silently (returns ''), so the
		// only consequence is an empty data-marker attribute, which is normalised.
		set_error_handler( static fn() => true, E_WARNING );
		try {
			$output = $query->shortcode(
				[
					'post_type'      => 'post',
					'posts_per_page' => 10,
				]
			);
		} finally {
			restore_error_handler();
		}

		$normalized = $this->normalize_output( $output );
		$this->assertMatchesSnapshot( $normalized );
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Normalizes non-deterministic parts of the rendered output for stable snapshots.
	 * Also normalizes data-marker (singular, the icon) because its value depends on
	 * getimagesize() reaching the plugin URL — environment-dependent in Docker.
	 */
	private function normalize_output( string $html ): string {
		$html = preg_replace( '/data-nonce="[^"]*"/', 'data-nonce="NORMALIZED"', $html ) ?? $html;
		$html = preg_replace( '/\?ver=[0-9a-z.\-]+/', '?ver=NORMALIZED', $html ) ?? $html;
		$html = preg_replace( '/data-marker="[^"]*"/', 'data-marker="NORMALIZED"', $html ) ?? $html;
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
