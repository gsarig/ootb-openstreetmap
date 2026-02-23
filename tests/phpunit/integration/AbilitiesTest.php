<?php
/**
 * Integration tests for the Abilities API implementation.
 *
 * Tests the `ootb-openstreetmap/add-map-to-post` ability registration
 * and execution callback.
 *
 * @package ootb-openstreetmap
 */

namespace OOTB\Tests\Integration;

use WP_UnitTestCase;

class AbilitiesTest extends WP_UnitTestCase {

	public function test_ability_is_registered(): void {
		if ( ! function_exists( 'wp_register_ability' ) || ! function_exists( 'wp_list_abilities' ) ) {
			$this->markTestSkipped( 'Abilities API not available.' );
		}

		do_action( 'wp_abilities_api_init' );

		$abilities = wp_list_abilities();
		$this->assertArrayHasKey( 'ootb-openstreetmap/add-map-to-post', $abilities );
	}

	public function test_execute_callback_requires_valid_post(): void {
		$result = \OOTB\Abilities\execute_add_map_to_post( [ 'post_id' => 999999 ] );

		$this->assertWPError( $result );
		$this->assertEquals( 'ootb_post_not_found', $result->get_error_code() );
	}

	public function test_execute_callback_checks_permissions(): void {
		$post_id = $this->factory()->post->create();

		// Simulate no permissions
		wp_set_current_user( 0 );

		$result = \OOTB\Abilities\execute_add_map_to_post( [ 'post_id' => $post_id ] );

		$this->assertWPError( $result );
		$this->assertEquals( 'ootb_forbidden', $result->get_error_code() );
	}

	public function test_execute_callback_adds_map_block_with_minimal_args(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create( [
			'post_content' => 'Existing content.',
		] );

		$result = \OOTB\Abilities\execute_add_map_to_post( [ 'post_id' => $post_id ] );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'post_id', $result );
		$this->assertArrayHasKey( 'edit_url', $result );
		$this->assertEquals( $post_id, $result[ 'post_id' ] );

		$post = get_post( $post_id );
		$this->assertStringContainsString( '<!-- wp:ootb/openstreetmap', $post->post_content );
		$this->assertStringContainsString( 'Existing content.', $post->post_content );
	}

	public function test_execute_callback_with_custom_center_and_zoom(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create();

		$result = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id' => $post_id,
			'lat'     => 51.5074,
			'lng'     => - 0.1278,
			'zoom'    => 12,
		] );

		$this->assertIsArray( $result );

		$post = get_post( $post_id );
		// Centre is stored in bounds, not as top-level lat/lng.
		$this->assertStringContainsString( '"bounds":[["51.5074","-0.1278"]]', $post->post_content );
		$this->assertStringContainsString( '"zoom":12', $post->post_content );
	}

	public function test_execute_callback_with_markers(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create();

		$result = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id' => $post_id,
			'markers' => [
				[
					'lat'     => 51.5074,
					'lng'     => - 0.1278,
					'title'   => 'London',
					'content' => 'Capital of England',
				],
				[
					'lat'     => 48.8566,
					'lng'     => 2.3522,
					'title'   => 'Paris',
					'content' => 'Capital of France',
				],
			],
		] );

		$this->assertIsArray( $result );

		$post = get_post( $post_id );
		// Markers now use numeric timestamp IDs, not string slugs.
		$this->assertStringContainsString( 'Capital of England', $post->post_content );
		$this->assertStringContainsString( 'Capital of France', $post->post_content );
	}

	public function test_execute_callback_centers_on_first_marker_when_no_explicit_center(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create();

		$result = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id' => $post_id,
			'markers' => [
				[
					'lat' => 40.7128,
					'lng' => - 74.0060,
				],
			],
		] );

		$this->assertIsArray( $result );

		$post = get_post( $post_id );
		$this->assertStringContainsString( '"lat":"40.7128"', $post->post_content );
		$this->assertStringContainsString( '"lng":"-74.006"', $post->post_content );
	}

	public function test_execute_callback_with_gesture_handling(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create();

		$result = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id'          => $post_id,
			'gesture_handling' => true,
		] );

		$this->assertIsArray( $result );

		$post = get_post( $post_id );
		$this->assertStringContainsString( '"gestureHandling":true', $post->post_content );
	}

	public function test_execute_callback_with_custom_provider_and_height(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create();

		$result = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id'    => $post_id,
			'provider'   => 'mapbox',
			'map_height' => 600,
		] );

		$this->assertIsArray( $result );

		$post = get_post( $post_id );
		// provider and mapHeight are no longer in the block comment attrs,
		// but provider appears in the inner HTML data-provider attribute.
		$this->assertStringContainsString( 'data-provider="mapbox"', $post->post_content );
		$this->assertStringContainsString( 'height:600px', $post->post_content );
	}

	public function test_execute_callback_clamps_zoom_levels(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$post_id = $this->factory()->post->create();

		// Test zoom too low
		$result = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id' => $post_id,
			'zoom'    => 1,
		] );

		$post = get_post( $post_id );
		$this->assertStringContainsString( '"zoom":2', $post->post_content );

		// Test zoom too high
		$post_id2 = $this->factory()->post->create();
		$result   = \OOTB\Abilities\execute_add_map_to_post( [
			'post_id' => $post_id2,
			'zoom'    => 25,
		] );

		$post2 = get_post( $post_id2 );
		$this->assertStringContainsString( '"zoom":18', $post2->post_content );
	}

	public function test_execute_callback_prepends_block_to_existing_content(): void {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$original_content = "<!-- wp:paragraph -->\n<p>Existing paragraph.</p>\n<!-- /wp:paragraph -->";
		$post_id          = $this->factory()->post->create( [
			'post_content' => $original_content,
		] );

		$result = \OOTB\Abilities\execute_add_map_to_post( [ 'post_id' => $post_id ] );

		$this->assertIsArray( $result );

		$post = get_post( $post_id );

		// Map block should come first
		$map_pos  = strpos( $post->post_content, '<!-- wp:ootb/openstreetmap' );
		$para_pos = strpos( $post->post_content, '<!-- wp:paragraph -->' );

		$this->assertNotFalse( $map_pos );
		$this->assertNotFalse( $para_pos );
		$this->assertLessThan( $para_pos, $map_pos );
	}

	public function test_build_block_markup_structure(): void {
		$markup = \OOTB\Abilities\build_block_markup(
			51.5074,
			- 0.1278,
			10,
			400,
			'openstreetmap',
			[],
			false
		);

		$this->assertStringStartsWith( '<!-- wp:ootb/openstreetmap', $markup );
		$this->assertStringEndsWith( '<!-- /wp:ootb/openstreetmap -->', trim( $markup ) );
		$this->assertStringContainsString( 'wp-block-ootb-openstreetmap', $markup );
		$this->assertStringContainsString( 'data-provider="openstreetmap"', $markup );
		$this->assertStringContainsString( 'data-zoom="10"', $markup );
		$this->assertStringContainsString( 'height:400px', $markup );
	}
}
