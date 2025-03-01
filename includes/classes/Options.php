<?php
/**
 * Plugin Options
 *
 * @package ootb-openstreetmap
 * @since 1.2
 */

namespace OOTB;

class Options {

	/**
	 * The OOTB/Options Class constructor.
	 */
	public function __construct() {
		add_filter( 'plugin_action_links_' . OOTB_PLUGIN_BASENAME, [ $this, 'settings_links' ], 10, 1 );
		add_action( 'admin_menu', [ $this, 'options_page' ], 10, 0 );
		add_action( 'admin_init', [ $this, 'settings_fields' ], 10, 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ], 10, 1 );
	}

	/**
	 * Add settings link to the plugin's page.
	 *
	 * @param string[] $links An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'. With Multisite active this can also include 'network_active' and 'network_only' items.
	 *
	 * @return string[]
	 */
	function settings_links( array $links ): array {
		array_unshift( $links,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'options-general.php?page=ootb-openstreetmap' ) ),
				esc_html__( 'Settings', 'ootb-openstreetmap' )
			)
		);

		return $links;
	}

	/**
	 * The plugin settings fields.
	 *
	 * @return void
	 */
	function settings_fields(): void {
		register_setting(
			'ootb',
			'ootb_options',
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_options' ],
				'default'           => []
			]
		);

		add_settings_section(
			'ootb_section_settings',
			esc_html__( 'Map Providers', 'ootb-openstreetmap' ),
			[ $this, 'section_settings_callback' ],
			'ootb'
		);
		add_settings_field(
			'api_mapbox',
			esc_html__( 'MapBox API Key', 'ootb-openstreetmap' ),
			[ $this, 'field_api_key_mapbox' ],
			'ootb',
			'ootb_section_settings',
			[
				'label_for'        => 'api_mapbox',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
			]
		);
		add_settings_field(
			'global_mapbox_style_url',
			esc_html__( 'MapBox style URL', 'ootb-openstreetmap' ),
			[ $this, 'field_url' ],
			'ootb',
			'ootb_section_settings',
			[
				'label_for'        => 'global_mapbox_style_url',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
				'description'      =>
					sprintf(
						wp_kses(
							/* translators: %1$s is the URL to the Mapbox Studio page */
							__( 'You can find the style URL on <a href="%1$s" target="_blank">Mapbox Studio</a>. There, use the "Share" button, and under "Developer resources", copy the "Style URL". It should look like that: <code>mapbox://styles/username/style-id</code>.', 'ootb-openstreetmap' ),
							[
								'a'    => [ 'href' => [], 'target' => [] ],
								'code' => []
							]
						),
						esc_url( 'https://www.mapbox.com/studio/' )
					),
			]
		);

		add_settings_section(
			'ootb_section_defaults',
			esc_html__( 'Default location', 'ootb-openstreetmap' ),
			[ $this, 'section_defaults_callback' ],
			'ootb'
		);
		add_settings_field(
			'default_lat',
			esc_html__( 'Latitude', 'ootb-openstreetmap' ),
			[ $this, 'field_coordinates' ],
			'ootb',
			'ootb_section_defaults',
			[
				'label_for'        => 'default_lat',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
			]
		);
		add_settings_field(
			'default_lng',
			esc_html__( 'Longitude', 'ootb-openstreetmap' ),
			[ $this, 'field_coordinates' ],
			'ootb',
			'ootb_section_defaults',
			[
				'label_for'        => 'default_lng',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
			]
		);
		add_settings_section(
			'ootb_section_frontend',
			esc_html__( 'Frontend behavior', 'ootb-openstreetmap' ),
			[ $this, 'section_frontend_callback' ],
			'ootb'
		);

		add_settings_field(
			'prevent_default_gestures',
			esc_html__( 'Prevent default gestures', 'ootb-openstreetmap' ),
			[ $this, 'field_prevent_default_gestures' ],
			'ootb',
			'ootb_section_frontend',
			[
				'label_for'        => 'prevent_default_gestures',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
				'label'            => esc_html__( ' Prevent default map scroll/touch behaviours to make it easier for users to navigate in a page (pretty much like in Google Maps).', 'ootb-openstreetmap' ),
			]
		);

		add_settings_section(
			'ootb_section_custom_fields',
			esc_html__( 'Custom Fields', 'ootb-openstreetmap' ),
			[ $this, 'section_custom_fields_callback' ],
			'ootb'
		);

		add_settings_field(
			'geodata',
			esc_html__( 'Location Custom Field', 'ootb-openstreetmap' ),
			[ $this, 'field_geodata' ],
			'ootb',
			'ootb_section_custom_fields',
			[
				'label_for'        => 'geodata',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
				'label'            => sprintf(
					/* translators: %1$s is the URL to the WordPress documentation about Geodata */
					__( 'Enable a location custom field, to store a post\'s or post type\'s location. The data are stored following the <a href="%1$s" target="_blank">official guidelines</a>.', 'ootb-openstreetmap' ),
					esc_url( 'https://codex.wordpress.org/Geodata' )
				),
			]
		);

		add_settings_field(
			'geo_post_types',
			esc_html__( 'Post types', 'ootb-openstreetmap' ),
			[ $this, 'field_geo_post_types' ],
			'ootb',
			'ootb_section_custom_fields',
			[
				'label_for'        => 'geo_post_types',
				'class'            => 'ootb_row ootb--geo_post_types',
				'ootb_custom_data' => 'custom',
				'label'            => __( 'Select the post types to enable the location custom field:', 'ootb-openstreetmap' ),
			]
		);

		add_settings_section(
			'ootb_section_openai',
			esc_html__( 'OpenAI settings', 'ootb-openstreetmap' ),
			[ $this, 'section_openai_callback' ],
			'ootb'
		);

		add_settings_field(
			'api_ai_provider',
			esc_html__( 'AI Provider', 'ootb-openstreetmap' ),
			[ $this, 'field_api_ai_text' ],
			'ootb',
			'ootb_section_openai',
			[
				'label_for'        => 'api_ai_provider',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
				'default'          => OpenAI::ai_api_defaults( 'url' ),
				'description'      => __( 'Set the API endpoint URL for your preferred provider. Defaults to the OpenAI provider.', 'ootb-openstreetmap' )
			]
		);

		add_settings_field(
			'api_ai_model',
			esc_html__( 'AI Model', 'ootb-openstreetmap' ),
			[ $this, 'field_api_ai_text' ],
			'ootb',
			'ootb_section_openai',
			[
				'label_for'        => 'api_ai_model',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
				'default'          => OpenAI::ai_api_defaults( 'model' ),
				'description'      => sprintf(
				/* translators: %1$s is the default model */
					__( 'The identifier of the model from your provider. Typically a short name without spaces. Defaults to %1$s.', 'ootb-openstreetmap' ),
					'<code>' . OpenAI::ai_api_defaults( 'model' ) . '</code>'
				),
			]
		);

		add_settings_field(
			'api_openai',
			esc_html__( 'AI API key', 'ootb-openstreetmap' ),
			[ $this, 'field_api_key_openai' ],
			'ootb',
			'ootb_section_openai',
			[
				'label_for'        => 'api_openai',
				'class'            => 'ootb_row',
				'ootb_custom_data' => 'custom',
				'description'      => __( 'Your API key for this provider.', 'ootb-openstreetmap' )
			]
		);
	}

	/**
	 * The callback method for the option to prevent default gestures.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function field_prevent_default_gestures( array $args ): void {
		$option = Helper::get_option( 'all' );
		?>
		<input type="checkbox" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"
			   value="1" <?php checked( ! empty( $option[ $args[ 'label_for' ] ] ), true ); ?> />
		<label
			for="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"><?php echo esc_html( $args[ 'label' ] ); ?></label>
		<?php
	}

	/**
	 * The callback method for the option to enable the Geodata.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function field_geodata( array $args ): void {
		$option = Helper::get_option( 'all' );
		?>
		<input type="checkbox" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"
			   value="1" <?php checked( ! empty( $option[ $args[ 'label_for' ] ] ), true ); ?> />
		<label for="<?php echo esc_attr( $args[ 'label_for' ] ); ?>">
			<?php
			echo wp_kses(
				$args[ 'label' ],
				[
					'a' => [
						'href'   => [],
						'target' => []
					],
				]
			);
			?>
		</label>
		<?php
	}

	/**
	 * The callback method for the option to enable the Geodata.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function field_geo_post_types( array $args ): void {
		$option     = Helper::get_option( 'all' );
		$post_types = get_post_types( [ 'public' => true ], 'names', 'and' ); // Get public post types
		?>
		<fieldset>
			<legend><?php echo esc_html( $args[ 'label' ] ); ?></legend>
			<?php
			foreach ( $post_types as $post_type ) :
				if ( 'attachment' === $post_type ) {
					continue;
				}
				?>
				<input type="checkbox"
					   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>-<?php echo esc_attr( $post_type ); ?>"
					   name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>][<?php echo esc_attr( $post_type ); ?>]"
					   value="1" <?php checked( ! empty( $option[ $args[ 'label_for' ] ][ $post_type ] ), true ); ?> />
				<label
					for="<?php echo esc_attr( $args[ 'label_for' ] ); ?>-<?php echo esc_attr( $post_type ); ?>"><?php echo esc_html( $post_type ); ?></label>
				<br/>
			<?php
			endforeach;
			?>
		</fieldset>
		<?php
	}

	/**
	 * The callback method for the frontend behavior section.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function section_frontend_callback( array $args ): void {
		?>
		<p id="<?php echo esc_attr( $args[ 'id' ] ); ?>">
			<?php
			echo esc_html__( 'Apply adjustments to the Frontend behavior of the map.', 'ootb-openstreetmap' );
			?>
		</p>
		<?php
	}

	/**
	 * The callback method for the Custom Fields section.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function section_custom_fields_callback( array $args ): void {
		?>
		<p id="<?php echo esc_attr( $args[ 'id' ] ); ?>">
			<?php
			echo esc_html__( 'Enable support for custom fields.', 'ootb-openstreetmap' );
			?>
		</p>
		<?php
	}

	/**
	 * The callback method for the default coordinates.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function section_defaults_callback( array $args ): void {
		?>
		<p id="<?php echo esc_attr( $args[ 'id' ] ); ?>">
			<?php
			echo sprintf(
				wp_kses(
				/* translators: %1$s is the URL to the site's General options (Settings/General) */
					__( 'Set the default coordinates when you add a new block and no marker is yet set. The plugin will try to guess the default location based on the <a href="%1$s">site\'s timezone</a>, but because there is no easy way to match against a specific database of coordinates, it can get it wrong. You can override these values here.', 'ootb-openstreetmap' ),
					[ 'a' => [ 'href' => [] ] ]
				),
				esc_url( admin_url( 'options-general.php' ) )
			);
			?>
		</p>
		<?php
	}

	/**
	 * The Section Settings Callback method.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function section_settings_callback( array $args ): void {
		?>
		<div class="ootb_info">
			<h3><?php echo esc_html__( 'About OpenStreetMap usage limits', 'ootb-openstreetmap' ); ?></h3>
			<p id="<?php echo esc_attr( $args[ 'id' ] ); ?>">
				<?php
				echo sprintf(
					wp_kses(
					/* translators: %1$s is the URL to the OpenStreetMap Tile Usage Policy and %1$s is the URL to the Mapbox homepage */
						__( 'As stated on the <a href="%1$s" target="_blank">OpenStreetMap Tile Usage Policy</a>, OSM’s own servers are run entirely on donated resources and they have strictly limited capacity. Using them on a site with low traffic will probably be fine. Nevertheless, you are advised to create an account to <a href="%2$s" target="_blank">MapBox</a> and get a free API Key.',
							'ootb-openstreetmap'
						),
						[ 'a' => [ 'href' => [], 'target' => [] ] ]
					),
					esc_url( 'https://operations.osmfoundation.org/policies/tiles/' ),
					esc_url( 'https://www.mapbox.com/' )
				);
				?>
			</p>
			<p class="ootb-colophon">
				<a href="https://wordpress.org/support/plugin/ootb-openstreetmap/" target="_blank">
					<?php esc_html_e( 'Support forum', 'ootb-openstreetmap' ); ?>
				</a>
				|
				<?php
				echo sprintf(
					wp_kses(
					/* translators: %s is the URL to the plugin creator's website */
						__( 'Plugin created by <a href="%s" target="_blank">Giorgos Sarigiannidis</a>', 'ootb-openstreetmap' ),
						[ 'a' => [ 'href' => [], 'target' => [] ] ]
					),
					esc_url( 'https://www.gsarigiannidis.gr/' )
				);
				?>
			</p>
		</div>

		<?php
	}

	/**
	 * The plugin options page.
	 *
	 * @return void
	 */
	function options_page(): void {
		add_submenu_page(
			'options-general.php',
			__( 'Out of the Block: OpenStreetMap', 'ootb-openstreetmap' ),
			__( 'OOTB OpenStreetMap', 'ootb-openstreetmap' ),
			'manage_options',
			'ootb-openstreetmap',
			[ $this, 'options_page_html' ]
		);
	}

	/**
	 * The Mapbox API key field.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function field_api_key_mapbox( array $args ): void {
		$option = Helper::get_option( 'all' );
		?>
		<input type="password" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"
			   value="<?php echo isset( $option[ $args[ 'label_for' ] ] ) ? esc_attr( $option[ $args[ 'label_for' ] ] ) : ''; ?>"/>
		<?php
	}

	/**
	 * A URL field.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function field_url( array $args ): void {
		$option = Helper::get_option( 'all' );
		?>
		<input type="url" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"
			   value="<?php echo isset( $option[ $args[ 'label_for' ] ] ) ? esc_attr( $option[ $args[ 'label_for' ] ] ) : ''; ?>"
			   size="60"/>
		<p class="description">
			<?php
			echo wp_kses(
				$args[ 'description' ],
				[
					'a'    => [ 'href' => [], 'target' => [] ],
					'code' => []
				]
			);
			?>
		</p>
		<?php
	}

	/**
	 * The default coordinates.
	 *
	 * @param array $args The settings args.
	 *
	 * @return void
	 */
	function field_coordinates( array $args ): void {
		$option   = Helper::get_option( 'all' );
		$defaults = Helper::default_location();
		$default  = '';
		if ( 'default_lat' === $args[ 'label_for' ] ) {
			$default = $defaults[ 0 ] ?? '';
		}
		if ( 'default_lng' === $args[ 'label_for' ] ) {
			$default = $defaults[ 1 ] ?? '';
		}
		?>
		<input type="text" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>" placeholder="<?php echo esc_html( $default ); ?>"
			   value="<?php echo isset( $option[ $args[ 'label_for' ] ] ) ? esc_attr( $option[ $args[ 'label_for' ] ] ) : esc_attr( $default ); ?>"/>
		<?php
	}

	/**
	 * The callback method for the OpenAI section.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function section_openai_callback( array $args ): void {
		?>
		<p id="<?php echo esc_attr( $args[ 'id' ] ); ?>">
			<?php
			echo esc_html__( 'Set the credentials for your AI provider. Defaults to OpenAI, but you can use any provider.', 'ootb-openstreetmap' );
			?>
		</p>
		<?php
	}

	/**
	 * Renders a text input field for API AI settings.
	 *
	 * @param array $args Array of arguments for the field, including:
	 *                    - 'label_for' (string): The ID and name attribute for the input.
	 *                    - 'default' (string): Default placeholder value for the input field.
	 *                    - 'description' (string, optional): Description displayed below the input field.
	 *
	 * @return void
	 */
	function field_api_ai_text( array $args ): void {
		$option = Helper::get_option( 'all' );
		?>
		<input type="text" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"
			   placeholder="<?php echo esc_html( $args[ 'default' ] ); ?>"
			   value="<?php echo isset( $option[ $args[ 'label_for' ] ] ) ? esc_attr( $option[ $args[ 'label_for' ] ] ) : esc_attr( $args[ 'default' ] ); ?>"/>
		<?php if ( isset( $args[ 'description' ] ) ): ?>
			<p><?php echo wp_kses_post( $args[ 'description' ] ); ?></p>
		<?php
		endif;
	}

	/**
	 * The Mapbox API key field.
	 *
	 * @param array $args THe settings args.
	 *
	 * @return void
	 */
	function field_api_key_openai( array $args ): void {
		$option = Helper::get_option( 'all' );
		?>
		<input type="password" name="ootb_options[<?php echo esc_attr( $args[ 'label_for' ] ); ?>]"
			   id="<?php echo esc_attr( $args[ 'label_for' ] ); ?>"
			   value="<?php echo isset( $option[ $args[ 'label_for' ] ] ) ? esc_attr( $option[ $args[ 'label_for' ] ] ) : ''; ?>"/>
		<?php if ( isset( $args[ 'description' ] ) ): ?>
			<p><?php echo esc_html( $args[ 'description' ] ); ?></p>
		<?php
		endif;
	}

	/**
	 * The options page HTML.
	 *
	 * @return void
	 */
	function options_page_html(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = Helper::get_option( 'all' );
		$current = isset( $options[ 'ootb_field_mode' ] ) && $options[ 'ootb_field_mode' ] ? $options[ 'ootb_field_mode' ] : '';
		?>
		<div id="ootb_form" class="wrap" data-current="<?php echo esc_attr( $current ); ?>">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'ootb' );
				do_settings_sections( 'ootb' );
				submit_button( esc_html__( 'Save Settings', 'ootb-openstreetmap' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Sanitize Options.
	 *
	 * @param array $input The Options to sanitize.
	 *
	 * @return array
	 */
	function sanitize_options( array $input ): array {
		// Sanitize api_mapbox.
		if ( ! empty( $input[ 'api_mapbox' ] ) ) {
			$input[ 'api_mapbox' ] = preg_replace( '/\s+/',
				' ',
				esc_attr( $input[ 'api_mapbox' ] ) );
		}
		// Sanitize coordinates.
		$fallback = Helper::fallback_location();
		if ( ! empty( $input[ 'default_lat' ] ) ) {
			$is_valid = preg_match( '/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $input[ 'default_lat' ] );
			if ( ! $is_valid ) {
				$input[ 'default_lat' ] = $fallback[ 0 ] ?? '';
			}
		}
		if ( ! empty( $input[ 'default_lng' ] ) ) {
			$is_valid = preg_match( '/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $input[ 'default_lng' ] );
			if ( ! $is_valid ) {
				$input[ 'default_lng' ] = $fallback[ 1 ] ?? '';
			}
		}

		return $input;
	}

	/**
	 * Enqueue the admin scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	function enqueues( string $hook ): void {
		if ( 'settings_page_ootb-openstreetmap' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'ootb-admin-scripts',
			OOTB_PLUGIN_URL . 'assets/js/admin/settings.js',
			[],
			OOTB_VERSION,
			true
		);

		wp_enqueue_style(
			'ootb-admin-styles',
			OOTB_PLUGIN_URL . 'assets/css/admin/styles.css',
			[],
			OOTB_VERSION
		);
	}
}
