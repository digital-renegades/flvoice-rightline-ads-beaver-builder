<?php
/**
 * Plugin Name: RightLine Ads Extension
 * Plugin URI: https://digitalrenegades.com
 * Description: Custom post type for ads with Beaver Builder slider module and responsive image support.
 * Version: 1.0.0
 * Author: Digital Renegades
 * Author URI: https://digitalrenegades.com
 * Text Domain: rightline-ads
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package RightLine_Ads
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RIGHTLINE_ADS_VERSION', '1.0.0' );
define( 'RIGHTLINE_ADS_PLUGIN_FILE', __FILE__ );
define( 'RIGHTLINE_ADS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RIGHTLINE_ADS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RIGHTLINE_ADS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

function rightline_ads_get_dimensions(): array {
	return apply_filters(
		'rightline_ads_dimensions',
		array(
			'banner'         => array(
				'1440' => array( 'width' => 1440, 'height' => 100 ),
				'992'  => array( 'width' => 992, 'height' => 100 ),
				'768'  => array( 'width' => 768, 'height' => 100 ),
			),
			'large_homepage' => array(
				'1440' => array( 'width' => 1440, 'height' => 285 ),
				'992'  => array( 'width' => 992, 'height' => 285 ),
				'768'  => array( 'width' => 768, 'height' => 285 ),
			),
		)
	);
}

function rightline_ads_get_breakpoints(): array {
	return apply_filters(
		'rightline_ads_breakpoints',
		array(
			'desktop' => 992,
			'tablet'  => 768,
		)
	);
}

function rightline_ads_init(): void {
	load_plugin_textdomain( 'rightline-ads', false, dirname( RIGHTLINE_ADS_PLUGIN_BASENAME ) . '/languages' );

	require_once RIGHTLINE_ADS_PLUGIN_DIR . 'includes/class-cpt-ads.php';
	require_once RIGHTLINE_ADS_PLUGIN_DIR . 'includes/class-ad-meta.php';

	RightLine_Ads\CPT_Ads::init();
	RightLine_Ads\Ad_Meta::init();

	if ( class_exists( 'FLBuilder' ) ) {
		require_once RIGHTLINE_ADS_PLUGIN_DIR . 'includes/class-bb-module-ads.php';
		RightLine_Ads\BB_Module_Ads::init();
	}
}
add_action( 'plugins_loaded', 'rightline_ads_init' );

function rightline_ads_activate(): void {
	rightline_ads_init();
	RightLine_Ads\CPT_Ads::add_caps_to_roles();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'rightline_ads_activate' );

function rightline_ads_deactivate(): void {
	require_once RIGHTLINE_ADS_PLUGIN_DIR . 'includes/class-cpt-ads.php';
	RightLine_Ads\CPT_Ads::remove_caps_from_roles();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'rightline_ads_deactivate' );
