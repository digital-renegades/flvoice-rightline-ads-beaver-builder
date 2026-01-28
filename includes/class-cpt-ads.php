<?php
/**
 * Custom Post Type: Ads
 *
 * @package RightLine_Ads
 */

declare(strict_types=1);

namespace RightLine_Ads;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT_Ads {
	const POST_TYPE = 'rightline_ad';

	/** Capability type (singular, plural) for Editor-and-above access. */
	const CAPABILITY_TYPE = 'rightline_ad';

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_filter( 'map_meta_cap', array( __CLASS__, 'map_read_ad_to_read' ), 10, 4 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'add_list_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'render_list_column' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'orderby_ad_type' ) );
	}

	/**
	 * Lets anyone with 'read' (including logged-out visitors) read rightline_ad posts on the frontend.
	 * Without this, WP_Query filters out ads for users without read_rightline_ad (e.g. subscribers, guests).
	 *
	 * @param string[] $caps   Required capabilities.
	 * @param string   $cap   Capability being checked.
	 * @param int      $user_id User ID.
	 * @param array    $args   Additional args (e.g. post ID).
	 * @return string[]
	 */
	public static function map_read_ad_to_read( array $caps, string $cap, int $user_id, array $args ): array {
		if ( 'read_post' !== $cap || empty( $args[0] ) ) {
			return $caps;
		}
		$post = get_post( $args[0] );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return $caps;
		}
		// Published ads are readable by anyone who can read the site.
		if ( 'publish' === $post->post_status ) {
			return array( 'read' );
		}
		return $caps;
	}

	public static function register_post_type(): void {
		$labels = array(
			'name'                  => _x( 'Ads', 'Post type general name', 'rightline-ads' ),
			'singular_name'         => _x( 'Ad', 'Post type singular name', 'rightline-ads' ),
			'menu_name'             => _x( 'Ads', 'Admin Menu text', 'rightline-ads' ),
			'name_admin_bar'        => _x( 'Ad', 'Add New on Toolbar', 'rightline-ads' ),
			'add_new'               => __( 'Add New', 'rightline-ads' ),
			'add_new_item'          => __( 'Add New Ad', 'rightline-ads' ),
			'new_item'              => __( 'New Ad', 'rightline-ads' ),
			'edit_item'             => __( 'Edit Ad', 'rightline-ads' ),
			'view_item'             => __( 'View Ad', 'rightline-ads' ),
			'all_items'             => __( 'All Ads', 'rightline-ads' ),
			'search_items'          => __( 'Search Ads', 'rightline-ads' ),
			'parent_item_colon'     => __( 'Parent Ads:', 'rightline-ads' ),
			'not_found'             => __( 'No ads found.', 'rightline-ads' ),
			'not_found_in_trash'    => __( 'No ads found in Trash.', 'rightline-ads' ),
			'archives'              => _x( 'Ad archives', 'The post type archive label', 'rightline-ads' ),
			'insert_into_item'      => _x( 'Insert into ad', 'Overrides the "Insert into post" phrase', 'rightline-ads' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this ad', 'Overrides the "Uploaded to this post" phrase', 'rightline-ads' ),
			'filter_items_list'     => _x( 'Filter ads list', 'Screen reader text', 'rightline-ads' ),
			'items_list_navigation' => _x( 'Ads list navigation', 'Screen reader text', 'rightline-ads' ),
			'items_list'            => _x( 'Ads list', 'Screen reader text', 'rightline-ads' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'ad' ),
			'capability_type'     => array( self::CAPABILITY_TYPE, self::CAPABILITY_TYPE . 's' ),
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-megaphone',
			'supports'            => array( 'title' ),
			'show_in_rest'        => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Adds Ad Type column to the Ads list table.
	 *
	 * @param string[] $columns Existing columns.
	 * @return string[]
	 */
	public static function add_list_columns( array $columns ): array {
		$insert = array( 'rightline_ad_type' => __( 'Ad Type', 'rightline-ads' ) );
		$pos    = array_search( 'title', array_keys( $columns ), true );
		if ( false !== $pos ) {
			$keys  = array_keys( $columns );
			$after = array_slice( $columns, 0, $pos + 1, true );
			$rest  = array_slice( $columns, $pos + 1, null, true );
			return $after + $insert + $rest;
		}
		return $insert + $columns;
	}

	/**
	 * Outputs the Ad Type column content for each row.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function render_list_column( string $column, int $post_id ): void {
		if ( 'rightline_ad_type' !== $column ) {
			return;
		}
		$ad_type = get_post_meta( $post_id, Ad_Meta::META_AD_TYPE, true );
		if ( '' === $ad_type ) {
			$ad_type = 'banner';
		}
		$types = self::get_ad_types();
		$label = isset( $types[ $ad_type ] ) ? $types[ $ad_type ] : $ad_type;
		echo esc_html( $label );
	}

	/**
	 * Makes the Ad Type column sortable.
	 *
	 * @param string[] $columns Sortable columns.
	 * @return string[]
	 */
	public static function sortable_columns( array $columns ): array {
		$columns['rightline_ad_type'] = 'rightline_ad_type';
		return $columns;
	}

	/**
	 * Handles ordering by Ad Type (meta value) on the Ads list screen.
	 *
	 * @param \WP_Query $query Main query.
	 */
	public static function orderby_ad_type( \WP_Query $query ): void {
		if ( ! $query->is_main_query() || ! is_admin() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type || 'edit' !== $screen->id ) {
			return;
		}
		$orderby = $query->get( 'orderby' );
		if ( 'rightline_ad_type' !== $orderby ) {
			return;
		}
		$query->set( 'meta_key', Ad_Meta::META_AD_TYPE );
		$query->set( 'orderby', 'meta_value' );
	}

	public static function get_ad_types(): array {
		return apply_filters(
			'rightline_ads_types',
			array(
				'banner'         => __( 'Banner', 'rightline-ads' ),
				'large_homepage' => __( 'Large Homepage', 'rightline-ads' ),
			)
		);
	}

	/**
	 * Returns the capabilities used by this post type (for Editor-and-above assignment).
	 *
	 * @return string[]
	 */
	public static function get_capabilities(): array {
		$cap_type = self::CAPABILITY_TYPE;
		$plural   = $cap_type . 's';
		return array(
			'edit_post'              => 'edit_' . $cap_type,
			'read_post'              => 'read_' . $cap_type,
			'delete_post'            => 'delete_' . $cap_type,
			'edit_posts'             => 'edit_' . $plural,
			'edit_others_posts'      => 'edit_others_' . $plural,
			'publish_posts'          => 'publish_' . $plural,
			'read_private_posts'     => 'read_private_' . $plural,
			'delete_posts'           => 'delete_' . $plural,
			'delete_private_posts'   => 'delete_private_' . $plural,
			'delete_published_posts' => 'delete_published_' . $plural,
			'delete_others_posts'     => 'delete_others_' . $plural,
			'edit_private_posts'     => 'edit_private_' . $plural,
			'edit_published_posts'   => 'edit_published_' . $plural,
		);
	}

	/**
	 * Grants Ads CPT capabilities.
	 *
	 * Backend (admin): Only Editors and Administrators can see the Ads menu and edit ads.
	 * Frontend: Everyone (all roles + logged-out via map_meta_cap) can see ads when displayed.
	 *
	 * - read_rightline_ad: granted to every role (so WP_Query returns ads for any logged-in user).
	 * - edit_rightline_ads, publish_rightline_ads, etc.: Editor and Administrator only (menu + edit).
	 *
	 * Call on plugin activation.
	 */
	public static function add_caps_to_roles(): void {
		$caps     = self::get_capabilities();
		$read_cap = $caps['read_post'];

		// Frontend: every role can "read" ads so the slider query returns posts for any user.
		$all_roles = wp_roles()->roles;
		if ( is_array( $all_roles ) ) {
			foreach ( array_keys( $all_roles ) as $role_slug ) {
				$role = get_role( $role_slug );
				if ( $role instanceof \WP_Role ) {
					$role->add_cap( $read_cap );
				}
			}
		}

		// Backend: only Editor and Administrator can see the Ads menu and create/edit/delete ads.
		$roles_with_full = array( 'administrator', 'editor' );
		foreach ( $roles_with_full as $role_slug ) {
			$role = get_role( $role_slug );
			if ( $role instanceof \WP_Role ) {
				foreach ( $caps as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Removes Ads CPT capabilities on deactivation.
	 * - Removes read_rightline_ad from all roles except Administrator.
	 * - Removes all caps from Editor.
	 */
	public static function remove_caps_from_roles(): void {
		$caps     = self::get_capabilities();
		$read_cap = $caps['read_post'];

		$all_roles = wp_roles()->roles;
		if ( is_array( $all_roles ) ) {
			foreach ( array_keys( $all_roles ) as $role_slug ) {
				if ( 'administrator' === $role_slug ) {
					continue;
				}
				$role = get_role( $role_slug );
				if ( $role instanceof \WP_Role ) {
					$role->remove_cap( $read_cap );
					if ( 'editor' === $role_slug ) {
						foreach ( $caps as $cap ) {
							$role->remove_cap( $cap );
						}
					}
				}
			}
		}
	}
}
