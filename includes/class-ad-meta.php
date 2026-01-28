<?php
/**
 * Ad Meta Boxes and Save Logic
 *
 * @package RightLine_Ads
 */

declare(strict_types=1);

namespace RightLine_Ads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ad_Meta {
	const META_AD_TYPE = '_rightline_ad_type';
	const META_AD_URL  = '_rightline_ad_url';
	const META_IMAGE_DESKTOP = '_rightline_ad_image_desktop';
	const META_IMAGE_TABLET = '_rightline_ad_image_tablet';
	const META_IMAGE_MOBILE = '_rightline_ad_image_mobile';

	public static function init(): void {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_url_validation_notice' ) );
	}

	public static function add_meta_boxes(): void {
		add_meta_box(
			'rightline_ad_settings',
			__( 'Ad Settings', 'rightline-ads' ),
			array( __CLASS__, 'render_ad_settings_meta_box' ),
			CPT_Ads::POST_TYPE,
			'normal',
			'high'
		);
	}

	/** Invalid URL notice after save. */
	public static function maybe_show_url_validation_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || CPT_Ads::POST_TYPE !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id ) {
			return;
		}

		if ( get_transient( 'rightline_ads_url_validation_error_' . $post_id ) ) {
			delete_transient( 'rightline_ads_url_validation_error_' . $post_id );
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Please enter a valid URL for the ad (e.g. https://example.com).', 'rightline-ads' ) . '</p></div>';
		}
	}

	public static function enqueue_admin_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || CPT_Ads::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'rightline-ads-admin',
			RIGHTLINE_ADS_PLUGIN_URL . 'assets/admin.js',
			array( 'jquery' ),
			RIGHTLINE_ADS_VERSION,
			true
		);

		$dimensions = rightline_ads_get_dimensions();
		$ad_types   = CPT_Ads::get_ad_types();
		$type_labels = array();
		foreach ( $ad_types as $key => $label ) {
			$type_labels[ $key ] = $label;
		}

		wp_localize_script(
			'rightline-ads-admin',
			'rightlineAdsDimensions',
			$dimensions
		);

		wp_localize_script(
			'rightline-ads-admin',
			'rightlineAdsTypeLabels',
			$type_labels
		);

		wp_enqueue_style(
			'rightline-ads-admin',
			RIGHTLINE_ADS_PLUGIN_URL . 'assets/admin.css',
			array(),
			RIGHTLINE_ADS_VERSION
		);
	}

	public static function render_ad_settings_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'rightline_ad_meta_save', 'rightline_ad_meta_nonce' );

		$ad_type        = get_post_meta( $post->ID, self::META_AD_TYPE, true );
		if ( '' === $ad_type ) {
			$ad_type = 'banner';
		}
		$ad_url         = get_post_meta( $post->ID, self::META_AD_URL, true );
		$image_desktop  = (int) get_post_meta( $post->ID, self::META_IMAGE_DESKTOP, true );
		$image_tablet   = (int) get_post_meta( $post->ID, self::META_IMAGE_TABLET, true );
		$image_mobile   = (int) get_post_meta( $post->ID, self::META_IMAGE_MOBILE, true );

		$ad_types   = CPT_Ads::get_ad_types();
		$dimensions = rightline_ads_get_dimensions();
		$dimension_summary = self::get_dimension_summary_text( $ad_type, $dimensions );

		?>
		<div class="rightline-ads-meta-box">
			<div class="rightline-ads-field">
				<label for="rightline_ad_type">
					<strong><?php esc_html_e( 'Ad Type', 'rightline-ads' ); ?></strong>
				</label>
				<select name="rightline_ad_type" id="rightline_ad_type" class="widefat">
					<?php foreach ( $ad_types as $type_key => $type_label ) : ?>
						<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $ad_type, $type_key ); ?>>
							<?php echo esc_html( $type_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description rightline-ads-dimension-summary" id="rightline-ads-dimension-summary">
					<?php echo esc_html( $dimension_summary ); ?>
				</p>
			</div>

			<div class="rightline-ads-field">
				<label for="rightline_ad_url">
					<strong><?php esc_html_e( 'URL', 'rightline-ads' ); ?></strong>
				</label>
				<input type="url"
					   name="rightline_ad_url"
					   id="rightline_ad_url"
					   class="widefat"
					   value="<?php echo esc_attr( $ad_url ); ?>"
					   placeholder="https://" />
				<p class="description">
					<?php esc_html_e( 'Optional. If set, the ad will be clickable and link to this URL.', 'rightline-ads' ); ?>
				</p>
			</div>

			<div class="rightline-ads-field">
				<h3><?php esc_html_e( 'Ad Images', 'rightline-ads' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Upload images for each screen size. If a size is not uploaded, the plugin will fall back to desktop, then tablet, then mobile.', 'rightline-ads' ); ?>
				</p>

				<?php
				self::render_image_field(
					'desktop',
					__( 'Desktop Image (≥992px)', 'rightline-ads' ),
					self::META_IMAGE_DESKTOP,
					$image_desktop,
					$ad_type,
					$dimensions
				);

				self::render_image_field(
					'tablet',
					__( 'Tablet Image (768px – 991px)', 'rightline-ads' ),
					self::META_IMAGE_TABLET,
					$image_tablet,
					$ad_type,
					$dimensions
				);

				self::render_image_field(
					'mobile',
					__( 'Mobile Image (<768px)', 'rightline-ads' ),
					self::META_IMAGE_MOBILE,
					$image_mobile,
					$ad_type,
					$dimensions
				);
				?>
			</div>
		</div>
		<?php
	}

	private static function render_image_field( string $size_key, string $label, string $meta_key, int $image_id, string $ad_type, array $dimensions ): void {
		$dimension_text = self::get_dimension_text( $size_key, $ad_type, $dimensions );
		$image_url      = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

		?>
		<div class="rightline-ads-image-field" data-size="<?php echo esc_attr( $size_key ); ?>">
			<label>
				<strong><?php echo esc_html( $label ); ?></strong>
				<?php if ( $dimension_text ) : ?>
					<span class="dimension-label"><?php echo esc_html( $dimension_text ); ?></span>
				<?php endif; ?>
			</label>

			<div class="image-preview">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Preview', 'rightline-ads' ); ?>" />
				<?php endif; ?>
			</div>

			<input type="hidden" name="<?php echo esc_attr( $meta_key ); ?>" class="image-id-input" value="<?php echo esc_attr( (string) $image_id ); ?>" />

			<button type="button" class="button rightline-ads-upload-button">
				<?php $image_id ? esc_html_e( 'Change Image', 'rightline-ads' ) : esc_html_e( 'Upload Image', 'rightline-ads' ); ?>
			</button>

			<?php if ( $image_id ) : ?>
				<button type="button" class="button rightline-ads-remove-button">
					<?php esc_html_e( 'Remove Image', 'rightline-ads' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Dimension summary for ad type (e.g. "Banner: 1440 × 100 px").
	 *
	 * @param string $ad_type    Ad type key.
	 * @param array  $dimensions rightline_ads_get_dimensions().
	 * @return string
	 */
	private static function get_dimension_summary_text( string $ad_type, array $dimensions ): string {
		$ad_types = CPT_Ads::get_ad_types();
		$label    = $ad_types[ $ad_type ] ?? $ad_type;
		if ( empty( $ad_type ) || ! isset( $dimensions[ $ad_type ]['1440'] ) ) {
			return '';
		}
		$dim = $dimensions[ $ad_type ]['1440'];
		return sprintf(
			/* translators: 1: ad type label, 2: width in pixels, 3: height in pixels */
			__( '%1$s: %2$d × %3$d px', 'rightline-ads' ),
			$label,
			$dim['width'],
			$dim['height']
		);
	}

	private static function get_dimension_text( string $size_key, string $ad_type, array $dimensions ): string {
		$breakpoint_map = array(
			'desktop' => '1440',
			'tablet'  => '992',
			'mobile'  => '768',
		);

		$breakpoint_key = $breakpoint_map[ $size_key ] ?? '';
		if ( ! $breakpoint_key ) {
			return '';
		}

		$ad_types = CPT_Ads::get_ad_types();
		$parts    = array();

		if ( ! empty( $ad_type ) && isset( $dimensions[ $ad_type ][ $breakpoint_key ] ) ) {
			$dim = $dimensions[ $ad_type ][ $breakpoint_key ];
			return sprintf( '(%d × %d px)', $dim['width'], $dim['height'] );
		}

		foreach ( $ad_types as $type_key => $type_label ) {
			if ( isset( $dimensions[ $type_key ][ $breakpoint_key ] ) ) {
				$dim     = $dimensions[ $type_key ][ $breakpoint_key ];
				$parts[] = sprintf( '%s: %d × %d px', $type_label, $dim['width'], $dim['height'] );
			}
		}

		return ! empty( $parts ) ? '(' . implode( ' | ', $parts ) . ')' : '';
	}

	public static function save_meta( int $post_id, \WP_Post $post ): void {
		if ( CPT_Ads::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( ! isset( $_POST['rightline_ad_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rightline_ad_meta_nonce'] ) ), 'rightline_ad_meta_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['rightline_ad_type'] ) ) {
			$ad_type       = sanitize_text_field( wp_unslash( $_POST['rightline_ad_type'] ) );
			$allowed_types = array_keys( CPT_Ads::get_ad_types() );

			if ( in_array( $ad_type, $allowed_types, true ) ) {
				update_post_meta( $post_id, self::META_AD_TYPE, $ad_type );
			} else {
				delete_post_meta( $post_id, self::META_AD_TYPE );
			}
		}

		if ( isset( $_POST['rightline_ad_url'] ) ) {
			$raw_url = sanitize_text_field( wp_unslash( $_POST['rightline_ad_url'] ) );
			$url     = esc_url_raw( trim( $raw_url ), array( 'http', 'https' ) );
			if ( '' !== trim( $raw_url ) && '' === $url ) {
				set_transient( 'rightline_ads_url_validation_error_' . $post_id, 1, 45 );
			} elseif ( '' !== $url ) {
				delete_transient( 'rightline_ads_url_validation_error_' . $post_id );
				update_post_meta( $post_id, self::META_AD_URL, $url );
			} else {
				delete_post_meta( $post_id, self::META_AD_URL );
			}
		}

		$image_fields = array(
			self::META_IMAGE_DESKTOP,
			self::META_IMAGE_TABLET,
			self::META_IMAGE_MOBILE,
		);

		foreach ( $image_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$image_id = absint( $_POST[ $field ] );
				if ( $image_id > 0 ) {
					update_post_meta( $post_id, $field, $image_id );
				} else {
					delete_post_meta( $post_id, $field );
				}
			}
		}
	}
}
