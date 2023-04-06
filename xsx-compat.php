<?php
/**
 * Plugin Name: Compat
 * Plugin URI: https://software.gieffeedizioni.it
 * Description: Better ClassicPress compatibility for WP plugins.
 * Version: 0.0.1
 * Requires CP: 2.0
 * Requires PHP: 7.4
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author: Gieffe edizioni srl
 * Author URI: https://www.gieffeedizioni.it
 * Text Domain: xsx-compat
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
	die('-1');
}

function _using_block_function( $function ) {
	$trace = debug_backtrace();

	if ( strpos( $trace[1]['file'], get_template_directory() ) === 0  ) {
		// A theme is calling the function

	} else {
		// A plugin is calling the function
		$plugins = array_intersect( array_column( $trace, 'file' ), wp_get_active_and_valid_plugins() );
		unset( $plugins[ array_search( __FILE__, $plugins ) ] ); // Remove ourself
		$plugin = array_pop( $plugins );
		if ( $plugin === null) {
			// Nothing found? Bail.
			return;
		}

		$plugins_using_blocks = get_option( 'plugins_using_blocks', array() );
		if ( in_array( $plugin, array_column( $plugins_using_blocks, 'file' ) ) ) {
			// We already have this listed.
			return;
		}

		$data = get_plugin_data($plugin);
		$plugins_using_blocks[] = array(
			'file' => $plugin,
			'name' => esc_html($data['Name']),
		);

		update_option( 'plugins_using_blocks', $plugins_using_blocks );
	}
}

add_action( 'upgrader_process_complete', 'remove_updated_plugins_from_plugins_using_blocks', 10, 2 );
function remove_updated_plugins_from_plugins_using_blocks( $upgrader, $options ) {
	if ( $options['action'] !== 'update' || $options['type'] !== 'plugin' ) {
		return;
	}
	foreach ($options['plugins'] as $plugin) {
		trigger_error('Updated '.$plugin);
		// Updated codepotent-head-cleaner/codepotent-head-cleaner.php in /Users/simo/Sites/ClassicPress-v2/src/wp-content/plugins/xsx-compat/xsx-compat.php on line 59
	}
}

if (!function_exists('register_block_type')) :

	function register_block_type(...$args) {
		_using_block_function(__FUNCTION__);
		return false;
	}

endif;

if (!function_exists('register_block_type_from_metadata')) :

	function register_block_type_from_metadata(...$args) {
		return false;
	}

endif;

if (!function_exists('register_block_pattern')) :

	function register_block_pattern(...$args) {
		return false;
	}

endif;

if (function_exists('runkit7_method_add')) :

	runkit7_method_add(
		'WP_Screen',
		'is_block_editor',
		function ($set) {
			return false;
		},
		RUNKIT7_ACC_PUBLIC,
		null,
		'bool'
	);

endif;
