<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://marketmentors.com/
 * @since             1.0.0
 * @package           Easy_Locations
 *
 * @wordpress-plugin
 * Plugin Name:       Easy Locations
 * Plugin URI:        https://https://github.com/Market-Mentors-LLC/easy-locations
 * Description:       A WordPress plugin that adds easy location management, listing, and maps functionality.
 * Version:           1.0.0
 * Author:            Market Mentors, LLC.
 * Author URI:        https://https://marketmentors.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       easy-locations
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EASY_LOCATIONS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-easy-locations-activator.php
 */
function activate_easy_locations() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-easy-locations-activator.php';
	Easy_Locations_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-easy-locations-deactivator.php
 */
function deactivate_easy_locations() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-easy-locations-deactivator.php';
	Easy_Locations_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_easy_locations' );
register_deactivation_hook( __FILE__, 'deactivate_easy_locations' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-easy-locations.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_easy_locations() {

	$plugin = new Easy_Locations();
	$plugin->run();

}
run_easy_locations();
