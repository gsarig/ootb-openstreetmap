<?php

/**
 * PHPStan bootstrap stubs.
 * Defines constants that are normally set at runtime by WordPress,
 * so that static analysis can resolve them without a full WP environment.
 */

defined( 'OOTB_PLUGIN_URL' )      || define( 'OOTB_PLUGIN_URL', '' );
defined( 'OOTB_PLUGIN_PATH' )     || define( 'OOTB_PLUGIN_PATH', '' );
defined( 'OOTB_PLUGIN_BASENAME' ) || define( 'OOTB_PLUGIN_BASENAME', '' );

// Stubs for WordPress 7.0+ WP AI Client API (static analysis only).
if ( ! class_exists( 'OOTB_WP_AI_Client_Prompt' ) ) {
	class OOTB_WP_AI_Client_Prompt {
		public function using_system_instruction( string $instruction ): static {
			return $this;
		}
		public function generate_text(): string|\WP_Error {
			return '';
		}
	}
}
if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
	function wp_ai_client_prompt( string $prompt ): OOTB_WP_AI_Client_Prompt {
		return new OOTB_WP_AI_Client_Prompt();
	}
}
