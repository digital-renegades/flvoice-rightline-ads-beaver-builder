/**
 * Frontend CSS for Ads Slider Module
 *
 * @package RightLine_Ads
 * @var string $id Node ID
 */

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-slider {
	margin: 0 !important;
	padding: 0 !important;
	position: relative;
	width: 100%;
	overflow: hidden;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-slider-wrapper {
	position: relative;
	width: 100%;
	margin: 0;
	padding: 0;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-slide {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	opacity: 0;
	transition: opacity 0.5s ease-in-out;
	pointer-events: none;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-slide.active {
	position: relative;
	opacity: 1;
	pointer-events: auto;
	z-index: 1;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-slide-link {
	display: block;
	width: 100%;
	text-decoration: none;
	color: inherit;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-picture {
	display: block;
	width: 100%;
	margin: 0;
	padding: 0;
	line-height: 0;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-image {
	display: block;
	width: 100%;
	height: auto;
	margin: 0;
	padding: 0;
	border: none;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-no-image {
	background: #f0f0f0;
	padding: 40px;
	text-align: center;
	color: #666;
}

.fl-node-<?php echo esc_attr( $id ); ?> .rightline-ads-slider-empty {
	padding: 40px;
	background: #f9f9f9;
	border: 2px dashed #ddd;
	text-align: center;
	color: #666;
}
