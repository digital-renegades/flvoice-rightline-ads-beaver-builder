<?php
/**
 * Beaver Builder Module Registration
 *
 * @package RightLine_Ads
 */

declare(strict_types=1);

namespace RightLine_Ads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BB_Module_Ads {
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'load_module' ) );
	}

	public static function load_module(): void {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return;
		}

		require_once RIGHTLINE_ADS_PLUGIN_DIR . 'modules/rightline-ads-slider/rightline-ads-slider.php';
	}
}
