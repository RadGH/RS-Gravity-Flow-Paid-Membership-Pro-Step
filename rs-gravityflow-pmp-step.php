<?php
/*
Plugin Name: RS Gravity Flow - Paid Membership Pro Step
Version:     1.0.0
Plugin URI:  http://radleysustaire.com/
Description: Adds a step that is completed when a user changes member level.
Author:      Radley Sustaire
Author URI:  mailto:radleygh@gmail.com
License:     GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

if ( !defined( 'ABSPATH' ) ) exit;

define( 'GF_PMP_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'GF_PMP_PATH', dirname(__FILE__) );
define( 'GF_PMP_VERSION', '1.0.0' );

function init_gf_pmp() {
	if ( !class_exists('Gravity_Flow_API') ) return;
	if ( !function_exists('pmpro_getMembershipLevelForUser') ) return;
	
	include( GF_PMP_PATH . '/includes/step.php' );
}
add_action( 'plugins_loaded', 'init_gf_pmp', 15 );