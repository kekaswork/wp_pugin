<?php 
/**
 * Basic setup functions for the plugin
 *
 * @since 1.0
 * @function	prefix_activate_plugin()		Plugin activatation todo list
 * @function	prefix_load_plugin_textdomain()	Load plugin text domain
 * @function	prefix_settings_link()			Print direct link to plugin settings in plugins list in admin
 * @function	prefix_plugin_row_meta()		Add donate and other links to plugins list
 * @function	prefix_footer_text()			Admin footer text
 * @function	prefix_footer_version()			Admin footer version
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
/**
 * Plugin activatation todo list
 *
 * This function runs when user activates the plugin. Used in register_activation_hook in the main plugin file. 
 * @since 1.0
 */
function prefix_activate_plugin() {
	
}

/**
 * Load plugin text domain
 *
 * @since 1.0
 */
function prefix_load_plugin_textdomain() {
    load_plugin_textdomain( 'recommended-products', false, '/recommended-products/languages/' );
}
add_action( 'plugins_loaded', 'prefix_load_plugin_textdomain' );

/**
 * Print direct link to plugin settings in plugins list in admin
 *
 * @since 1.0
 */
function prefix_settings_link( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=recommended-products' ) . '">' . __( 'Settings', 'recommended-products' ) . '</a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . PREFIX_RECOMMENDED_PRODUCTS . '/recommended-products.php', 'prefix_settings_link' );

/**
 * Add donate and other links to plugins list
 *
 * @since 1.0
 */
function prefix_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'recommended-products.php' ) !== false ) {
		$new_links = array(
				'hireme' 	=> '<a href="mailto:kekasinho@gmail.com" target="_blank">Hire Me For A Project</a>',
				);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'prefix_plugin_row_meta', 10, 2 );

/**
 * Admin footer version
 *
 * @since 1.0
 */
function prefix_footer_version($default) {
	
	// Retun default on non-plugin pages
	$screen = get_current_screen();
	if ( $screen->id !== 'settings_page_recommended-products' ) {
		return $default;
	}
	
	return 'Plugin version ' . PREFIX_VERSION_NUM;
}
add_filter( 'update_footer', 'prefix_footer_version', 11 );