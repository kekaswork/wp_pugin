<?php
/**
 * Plugin Name: Recommended Products
 * Plugin URI: kekasinho@gmail.com
 * Description: This plugin gives a user an opportunity to put a prepared shortcode and a block with 5 recommended products will appear
 * Author: almaz
 * Author URI: kekasinho@gmail.com
 * Version: 1.0
 * Text Domain: recommended-products
 * Domain Path: /languages
 */

/**
 * This plugin was developed using the WordPress starter plugin template by Arun Basil Lal <arunbasillal@gmail.com>
 * Please leave this credit and the directory structure intact for future developers who might read the code.
 * @GitHub https://github.com/arunbasillal/WordPress-Starter-Plugin
 */
 
/**
 * ~ Directory Structure ~
 *
 * /admin/ 					- Plugin backend stuff.
 * /functions/					- Functions and plugin operations.
 * /includes/					- External third party classes and libraries.
 * /languages/					- Translation files go here. 
 * /public/					- Front end files and functions that matter on the front end go here.
 * index.php					- Dummy file.
 * license.txt					- GPL v2
 * recommended-products.php				- Main plugin file containing plugin name and other version info for WordPress.
 * readme.txt					- Readme for WordPress plugin repository. https://wordpress.org/plugins/files/2018/01/readme.txt
 * uninstall.php				- Fired when the plugin is uninstalled. 
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define constants
 *
 * @since 1.0
 */
if ( ! defined( 'PREFIX_VERSION_NUM' ) ) 		define( 'PREFIX_VERSION_NUM'		, '1.0' ); // Plugin version constant
if ( ! defined( 'PREFIX_RECOMMENDED_PRODUCTS' ) )		define( 'PREFIX_RECOMMENDED_PRODUCTS'		, trim( dirname( plugin_basename( __FILE__ ) ), '/' ) ); // Name of the plugin folder eg - 'recommended-products'
if ( ! defined( 'PREFIX_RECOMMENDED_PRODUCTS_DIR' ) )	define( 'PREFIX_RECOMMENDED_PRODUCTS_DIR'	, plugin_dir_path( __FILE__ ) ); // Plugin directory absolute path with the trailing slash. Useful for using with includes eg - /var/www/html/wp-content/plugins/recommended-products/
if ( ! defined( 'PREFIX_RECOMMENDED_PRODUCTS_URL' ) )	define( 'PREFIX_RECOMMENDED_PRODUCTS_URL'	, plugin_dir_url( __FILE__ ) ); // URL to the plugin folder with the trailing slash. Useful for referencing src eg - http://localhost/wp/wp-content/plugins/recommended-products/

/**
 * Database upgrade todo
 *
 * @since 1.0
 */
function prefix_upgrader() {
	
	// Get the current version of the plugin stored in the database.
	$current_ver = get_option( 'abl_prefix_version', '0.0' );
	
	// Return if we are already on updated version. 
	if ( version_compare( $current_ver, PREFIX_VERSION_NUM, '==' ) ) {
		return;
	}
	
	// This part will only be executed once when a user upgrades from an older version to a newer version.
	
	// Finally add the current version to the database. Upgrade todo complete. 
	update_option( 'abl_prefix_version', PREFIX_VERSION_NUM );
}
add_action( 'admin_init', 'prefix_upgrader' );

// Load everything
require_once( PREFIX_RECOMMENDED_PRODUCTS_DIR . 'loader.php' );

// Register activation hook (this has to be in the main plugin file or refer bit.ly/2qMbn2O)
register_activation_hook( __FILE__, 'prefix_activate_plugin' );