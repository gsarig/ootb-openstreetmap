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

	protected function setUp(): void {
		parent::setUp();

		global $ootb_inline_scripts_tracking;
		$ootb_inline_scripts_tracking = [];

		// Register the handle so wp_add_inline_script has somewhere to attach.
		wp_register_script( 'ootb-openstreetmap-view-script', 'https://example.com/view.js', [], '1.0', true );
	}

	protected function tearDown(): void {
		remove_all_filters( 'ootb_marker_cluster_options' );
		wp_deregister_script( 'ootb-openstreetmap-view-script' );
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
		( new Assets() )->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * clusterOptions must be absent when the filter returns an empty array.
	 */
	public function test_cluster_options_absent_when_filter_returns_empty_array(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return [];
		} );

		( new Assets() )->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * clusterOptions must be absent when the filter returns a non-array value.
	 */
	public function test_cluster_options_absent_when_filter_returns_non_array(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return 'invalid';
		} );

		( new Assets() )->script_variables();

		$this->assertStringNotContainsString( 'clusterOptions', $this->get_inline_script() );
	}

	/**
	 * clusterOptions must be present and contain the filtered values.
	 */
	public function test_cluster_options_present_when_filter_returns_array(): void {
		add_filter( 'ootb_marker_cluster_options', static function () {
			return [
				'maxClusterRadius'  => 40,
				'showCoverageOnHover' => false,
			];
		} );

		( new Assets() )->script_variables();

		$inline = $this->get_inline_script();
		$this->assertStringContainsString( 'clusterOptions', $inline );
		$this->assertStringContainsString( '"maxClusterRadius":40', $inline );
		$this->assertStringContainsString( '"showCoverageOnHover":false', $inline );
	}
}
