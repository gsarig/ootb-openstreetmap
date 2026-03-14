<?php
/**
 * Integration tests for the ootb_marker_cluster_options filter hook.
 *
 * Verifies that Assets::script_variables() forwards the filtered options
 * into the ootb inline script as clusterOptions, and that the key is
 * omitted when the filter returns an empty array (the default).
 *
 * @package ootb-openstreetmap
 */

namespace OOTB\Tests\Integration;

use OOTB\Assets;
use WP_UnitTestCase;

class AssetsHookTest extends WP_UnitTestCase {

	private Assets $assets;

	protected function setUp(): void {
		parent::setUp();

		global $ootb_inline_scripts_tracking;
		$ootb_inline_scripts_tracking = [];

		// Register the handle so wp_add_inline_script has somewhere to attach.
		wp_register_script( 'ootb-openstreetmap-view-script', 'https://example.com/view.js', [], '1.0', true );

		// Create a single Assets instance for the test; constructor hooks are
		// removed in tearDown() to avoid polluting other tests.
		$this->assets = new Assets();
	}

	protected function tearDown(): void {
		remove_all_filters( 'ootb_marker_cluster_options' );
		wp_deregister_script( 'ootb-openstreetmap-view-script' );
		// Remove only the specific hooks registered by this test's Assets instance.
		remove_action( 'enqueue_block_assets', [ $this->assets, 'frontend' ] );
		remove_action( 'enqueue_block_assets', [ $this->assets, 'maybe_enqueue_clustering' ] );
		parent::tearDown();
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Returns the concatenated 'before' inline script content for our handle.
	 */
	private function get_inline_script(): string {
		$data = wp_scripts()->get_data( 'ootb-openstreetmap-view-script', 'before' );

		if ( empty( $data ) ) {
			return '';
		}

		return is_array( $data ) ? implode( "\n", $data ) : (string) $data;
	}

	// =========================================================================
	// Tests
	// =========================================================================

	/**
	 * clusterOptions must be absent when no filter is registered (default).
	 */
	public function test_cluster_options_absent_by_default(): void {
		$this->assets->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * clusterOptions must be absent when the filter returns an empty array.
	 */
	public function test_cluster_options_absent_when_filter_returns_empty_array(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return [];
		} );

		$this->assets->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * clusterOptions must be absent when the filter returns a non-array value.
	 */
	public function test_cluster_options_absent_when_filter_returns_non_array(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return 'invalid';
		} );

		$this->assets->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * JS-only keys (iconCreateFunction, spiderfyShapePositions, chunkProgress)
	 * must be stripped even when the filter returns other valid options.
	 */
	public function test_js_only_keys_are_stripped_from_cluster_options(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return [
				'maxClusterRadius'       => 40,
				'iconCreateFunction'     => 'function(c){return c;}',
				'spiderfyShapePositions' => 'function(n,c){return [];}',
				'chunkProgress'          => 'function(p,t){return p/t;}',
			];
		} );

		$this->assets->script_variables();

		$inline = $this->get_inline_script();
		$this->assertStringContainsString( 'clusterOptions', $inline );
		$this->assertStringContainsString( '"maxClusterRadius":40', $inline );
		$this->assertStringNotContainsString( 'iconCreateFunction', $inline );
		$this->assertStringNotContainsString( 'spiderfyShapePositions', $inline );
		$this->assertStringNotContainsString( 'chunkProgress', $inline );
	}

	/**
	 * clusterOptions must be absent when the filter returns only JS-only keys.
	 */
	public function test_cluster_options_absent_when_only_js_only_keys_set(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return [
				'iconCreateFunction' => 'function(c){return c;}',
			];
		} );

		$this->assets->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * clusterOptions must be present and contain the filtered values.
	 */
	public function test_cluster_options_present_when_filter_returns_array(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return [
				'maxClusterRadius'   => 40,
				'showCoverageOnHover' => false,
			];
		} );

		$this->assets->script_variables();

		$inline = $this->get_inline_script();
		$this->assertStringContainsString( 'clusterOptions', $inline );
		$this->assertStringContainsString( '"maxClusterRadius":40', $inline );
		$this->assertStringContainsString( '"showCoverageOnHover":false', $inline );
	}
}
