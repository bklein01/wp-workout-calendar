<?php
/**
 * Workout Calendar
 *
 * Wordpress Plugin To Manage Workout Schedules. Used For Coaches or Trainers
 *
 * @package   Workout Calendar
 * @author    Ben Klein <bklein73@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.cogncio.com
 * @copyright 2015 Ben Klein
 *
 * @wordpress-plugin
 * Plugin Name:       Workout Calendar
 * Plugin URI:        http://www.cognicio.com/workout-calendar
 * Description:       Plugin To Manage Workout Schedules. Used For Coaches or Trainers
 * Version:           1.0.0
 * Author:            Ben Klein
 * Author URI:        http://www.cognicio.com
 * Text Domain:       workout-calendar
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: bklein01\wp_workout_calendar
 */

session_start();

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'class-workout-calendar.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Workout_Calendar', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Workout_Calendar', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Workout_Calendar', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-workout-calendar-admin.php' );
	add_action( 'plugins_loaded', array( 'Workout_Calendar_Admin', 'get_instance' ) );

}
