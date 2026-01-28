<?php
/**
 * Beaver Builder Ads Slider Module
 *
 * @package RightLine_Ads
 */

declare(strict_types=1);

use RightLine_Ads\CPT_Ads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RightLine_Ads_Slider_Module extends FLBuilderModule {
	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Ads Slider', 'rightline-ads' ),
				'description'     => __( 'Display a rotating ad slider with fade effect.', 'rightline-ads' ),
				'group'           => __( 'RightLine', 'rightline-ads' ),
				'category'        => __( 'Content', 'rightline-ads' ),
				'dir'             => RIGHTLINE_ADS_PLUGIN_DIR . 'modules/rightline-ads-slider/',
				'url'             => RIGHTLINE_ADS_PLUGIN_URL . 'modules/rightline-ads-slider/',
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
			)
		);
	}

	public function get_ads(): array {
		$ad_type = isset( $this->settings->ad_type ) ? sanitize_text_field( $this->settings->ad_type ) : '';
		if ( '' === $ad_type ) {
			$ad_type = 'banner';
		}

		$allowed_types = array_keys( CPT_Ads::get_ad_types() );
		if ( ! in_array( $ad_type, $allowed_types, true ) ) {
			return array();
		}

		$args = array(
			'post_type'      => CPT_Ads::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'rand',
			'meta_query'     => array(
				array(
					'key'     => '_rightline_ad_type',
					'value'   => $ad_type,
					'compare' => '=',
				),
			),
		);

		$args = apply_filters( 'rightline_ads_slider_query_args', $args, $this->settings );

		$query = new WP_Query( $args );

		return $query->posts;
	}

	public function get_ad_images( int $post_id ): array {
		$desktop_id = (int) get_post_meta( $post_id, '_rightline_ad_image_desktop', true );
		$tablet_id  = (int) get_post_meta( $post_id, '_rightline_ad_image_tablet', true );
		$mobile_id  = (int) get_post_meta( $post_id, '_rightline_ad_image_mobile', true );

		$desktop_url = $desktop_id ? wp_get_attachment_image_url( $desktop_id, 'full' ) : false;
		$tablet_url  = $tablet_id ? wp_get_attachment_image_url( $tablet_id, 'full' ) : false;
		$mobile_url  = $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'full' ) : false;

		if ( ! $tablet_url ) {
			$tablet_url = $desktop_url ?: $mobile_url;
		}
		if ( ! $mobile_url ) {
			$mobile_url = $tablet_url ?: $desktop_url;
		}
		if ( ! $desktop_url ) {
			$desktop_url = $tablet_url ?: $mobile_url;
		}

		return array(
			'desktop' => $desktop_url,
			'tablet'  => $tablet_url,
			'mobile'  => $mobile_url,
		);
	}
}

FLBuilder::register_module(
	'RightLine_Ads_Slider_Module',
	array(
		'content' => array(
			'title'    => __( 'Content', 'rightline-ads' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'rightline-ads' ),
					'fields' => array(
						'ad_type' => array(
							'type'    => 'select',
							'label'   => __( 'Ad Type', 'rightline-ads' ),
							'default' => 'banner',
							'options' => CPT_Ads::get_ad_types(),
							'help'    => __( 'Select the type of ads to display in the slider.', 'rightline-ads' ),
						),
					),
				),
			),
		),
	)
);
