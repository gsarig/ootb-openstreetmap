<?php
/**
 * Plugin Options
 *
 * @package OOTB
 * @since 1.2
 */

namespace OOTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'ootb_options_page' ] );
		add_action( 'admin_init', [ $this, 'ootb_settings_init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
	}

	function ootb_settings_init() {
		register_setting( 'ootb',
				'ootb_options',
				[
						'sanitize_callback' => [ $this, 'ootb_options_validate' ],
				] );

		add_settings_section(
				'ootb_section_settings',
				__( 'Map Provider API key', 'ootb-openstreetmap' ),
				[ $this, 'ootb_section_settings_cb' ],
				'ootb'
		);
		add_settings_field(
				'api_mapbox',
				__( 'MapBox', 'ootb-openstreetmap' ),
				[ $this, 'ootb_field_api_key_mapbox' ],
				'ootb',
				'ootb_section_settings',
				[
						'label_for'        => 'api_mapbox',
						'class'            => 'ootb_row',
						'ootb_custom_data' => 'custom',
				]
		);
	}

	function ootb_section_settings_cb( $args ) {
		?>
		<div class="ootb_info">
			<h3><?php _e( 'About OpenStreetMap usage limits', 'ootb-openstreetmap' ); ?></h3>
			<p id="<?php echo esc_attr( $args['id'] ); ?>">
				<?php echo sprintf(
						wp_kses(
								__( 'As stated on the <a href="%1$s" target="_blank">OpenStreetMap Tile Usage Policy</a>, OSM’s own servers are run entirely on donated resources and they have strictly limited capacity. Using them on a site with low traffic will probably be fine. Nevertheless, you are advised to create an account to <a href="%2$s" target="_blank">MapBox</a> and get a free API Key.',
										'ootb-openstreetmap'
								),
								array( 'a' => array( 'href' => array(), 'target' => array() ) )
						),
						esc_url( 'https://operations.osmfoundation.org/policies/tiles/' ),
						esc_url( 'https://www.mapbox.com/' )
				); ?>
			</p>
			<p class="ootb-colophon"><a href="https://wordpress.org/support/plugin/ootb-openstreetmap/"
										target="_blank"><?php _e( 'Support forum',
							'ootb-openstreetmap' ); ?></a>
				| <?php echo sprintf( wp_kses( __( 'Plugin created by <a href="%s" target="_blank">Giorgos Sarigiannidis</a>',
						'ootb-openstreetmap' ),
						array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
						esc_url( 'https://www.gsarigiannidis.gr/' ) ); ?></p>
		</div>

		<?php
	}

	function ootb_options_page() {
		add_submenu_page(
				'options-general.php',
				'Out of the Block: OpenStreetMap',
				'OOTB OpenStreetMap',
				'manage_options',
				'ootb-openstreetmap',
				[ $this, 'ootb_options_page_html' ]
		);
	}

	function ootb_field_api_key_mapbox( $args ) {
		$option = get_option( 'ootb_options' );
		?>
		<input type="text" name="ootb_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			   id="<?php echo esc_attr( $args['label_for'] ); ?>"
			   value="<?php echo isset( $option[ $args['label_for'] ] ) ? esc_attr( $option[ $args['label_for'] ] ) : ''; ?>"/>
		<?php
	}

	function ootb_options_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = get_option( 'ootb_options' );
		$current = isset( $options['ootb_field_mode'] ) && $options['ootb_field_mode'] ? $options['ootb_field_mode'] : '';
		?>
		<div id="ootb_form" class="wrap" data-current="<?php echo $current; ?>">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'ootb' );
				do_settings_sections( 'ootb' );
				submit_button( __( 'Save Settings', 'ootb-openstreetmap' ) );
				?>
			</form>
		</div>
		<?php
	}

	function ootb_options_validate( $input ) {
		$input['api_mapbox'] = preg_replace( '/\s+/',
				' ',
				esc_attr( $input['api_mapbox'] ) );

		return $input;
	}

	function enqueues( $hook ) {
		if ( 'settings_page_ootb-openstreetmap' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'ootb-admin-styles',
				plugins_url( '/admin/css/styles.css', dirname( __FILE__ ) ),
				[],
				filemtime( plugin_dir_path( __FILE__ ) . 'css/styles.css' ) );
	}
}
