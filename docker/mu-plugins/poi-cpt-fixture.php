<?php
/**
 * Plugin Name: OOTB Test Fixture — POI CPT
 * Description: Registers a 'poi' custom post type WITHOUT custom-fields support, used by the geodata-cpt Playwright spec to guard against the editor crash fixed in PR #154.
 */

add_action(
	'init',
	function () {
		register_post_type(
			'poi',
			[
				'label'        => 'Points of Interest',
				'public'       => true,
				'show_in_rest' => true,
				'supports'     => [ 'title', 'editor' ], // Deliberately NO 'custom-fields'.
			]
		);
	}
);
