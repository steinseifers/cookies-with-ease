<?php
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
	}

	/**
	 * Removes all plugin data of the current site: the settings option, all
	 * Applications (post type 'cookieswithease_app', including seeded drafts
	 * and their post meta) and transients.
	 */
	function cookieswithease_uninstall_site() {

		delete_option( 'cookieswithease_options' );

		$apps = get_posts( array(
			'post_type'      => 'cookieswithease_app',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		foreach ( $apps as $app_id ) {
			// Force delete also removes the post meta.
			wp_delete_post( $app_id, true );
		}

		global $wpdb;
		$like         = $wpdb->esc_like( '_transient_cookieswithease_' ) . '%';
		$like_timeout = $wpdb->esc_like( '_transient_timeout_cookieswithease_' ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $like, $like_timeout ) );
	}

	if ( is_multisite() ) {
		$cookieswithease_site_ids = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
		foreach ( $cookieswithease_site_ids as $cookieswithease_site_id ) {
			switch_to_blog( $cookieswithease_site_id );
			cookieswithease_uninstall_site();
			restore_current_blog();
		}
	} else {
		cookieswithease_uninstall_site();
	}
