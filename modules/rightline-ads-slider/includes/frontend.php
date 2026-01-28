<?php
/**
 * Frontend HTML for Ads Slider Module
 *
 * Mirrors content-slider pattern: always output wrapper first, then content or empty state.
 * Content slider never returns early—it outputs .fl-content-slider and .fl-content-slider-wrapper
 * then loops over $settings->slides (0 or more). This ensures the module always has DOM structure.
 *
 * @package RightLine_Ads
 * @var RightLine_Ads_Slider_Module $module
 * @var object $settings
 * @var string $id Node ID
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ads = $module->get_ads();
$breakpoints = rightline_ads_get_breakpoints();
?>
<div class="rightline-ads-slider" data-transition-speed="500" data-slide-delay="3000">
	<div class="rightline-ads-slider-wrapper">
		<?php
		if ( ! empty( $ads ) ) :
			foreach ( $ads as $index => $ad ) :
				$images     = $module->get_ad_images( $ad->ID );
				$is_active  = 0 === $index ? 'active' : '';
				$ad_title   = get_the_title( $ad->ID );
				$ad_url_raw = get_post_meta( $ad->ID, '_rightline_ad_url', true );
				$ad_url     = is_string( $ad_url_raw ) && $ad_url_raw !== '' ? esc_url( $ad_url_raw, array( 'http', 'https' ) ) : '';
				?>

			<div class="rightline-ads-slide <?php echo esc_attr( $is_active ); ?>" data-slide-index="<?php echo esc_attr( (string) $index ); ?>">
				<?php if ( $ad_url !== '' ) : ?>
					<a href="<?php echo $ad_url; ?>" class="rightline-ads-slide-link" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( sprintf( __( 'View ad: %s', 'rightline-ads' ), $ad_title ) ); ?>">
				<?php endif; ?>
				<?php if ( $images['desktop'] || $images['tablet'] || $images['mobile'] ) : ?>
					<picture class="rightline-ads-picture">
						<?php if ( $images['desktop'] ) : ?>
							<source media="(min-width: <?php echo esc_attr( (string) $breakpoints['desktop'] ); ?>px)" srcset="<?php echo esc_url( $images['desktop'] ); ?>" />
						<?php endif; ?>

						<?php if ( $images['tablet'] ) : ?>
							<source media="(min-width: <?php echo esc_attr( (string) $breakpoints['tablet'] ); ?>px)" srcset="<?php echo esc_url( $images['tablet'] ); ?>" />
						<?php endif; ?>

						<?php
						$default_image = $images['mobile'] ?: ( $images['tablet'] ?: $images['desktop'] );
						?>
						<img src="<?php echo esc_url( $default_image ); ?>"
							 alt="<?php echo esc_attr( $ad_title ); ?>"
							 class="rightline-ads-image"
							 loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" />
					</picture>
				<?php else : ?>
					<div class="rightline-ads-no-image">
						<p><?php esc_html_e( 'No image available', 'rightline-ads' ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( $ad_url ) : ?>
					</a>
				<?php endif; ?>
			</div>
				<?php
			endforeach;
		else :
			if ( FLBuilderModel::is_builder_active() ) :
				?>
				<div class="rightline-ads-slider-empty">
					<p><?php esc_html_e( 'No ads found. Please create some ads and assign them to this ad type.', 'rightline-ads' ); ?></p>
				</div>
				<?php
			endif;
		endif;
		?>
	</div>
</div>
