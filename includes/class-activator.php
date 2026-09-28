<?php

defined( 'ABSPATH' ) || exit;

class CookiesWithEase_Activator
{

	/**
	 * Seeds a handful of draft Applications for the most common third-party
	 * scripts on plugin activation, so users have a starting point to edit
	 * instead of creating every Application from scratch. They are created as
	 * drafts, never published automatically, and nothing is blocked or loaded
	 * until the user reviews them, fills in the real script, and publishes.
	 * Runs once per activation and skips any slug that already exists (e.g. on
	 * reactivation, or if the user already created/renamed one).
	 */
	public static function activate() {

		$drafts = require COOKIESWITHEASE_DIR . 'includes/default-applications.php';

		// The integration code of an Application is only output if its author
		// has the unfiltered_html capability (see output_integration_codes()).
		// Without a logged-in user (WP-CLI, automated installs) the author
		// would be 0, so fall back to the first administrator.
		$author_id = get_current_user_id();
		if ( ! $author_id ) {
			$admins    = get_users( array(
				'capability' => 'manage_options',
				'orderby'    => 'ID',
				'order'      => 'ASC',
				'number'     => 1,
				'fields'     => 'ID',
			) );
			$author_id = empty( $admins ) ? 0 : (int) $admins[0];
		}

		foreach ( $drafts as $slug => $data ) {

			$existing = get_posts( array(
				'post_type'      => 'cookieswithease_app',
				'name'           => $slug,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing ) ) {
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_type'   => 'cookieswithease_app',
				'post_status' => 'draft',
				'post_author' => $author_id,
				'post_title'  => $data['title'],
				'post_name'   => $slug,
			) );

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, 'klaro_purposes', $data['purpose'] );
				// '1' here means the "Set Default state for app to false"
				// checkbox is checked, i.e. Klaro's default is false (opt-in,
				// not auto-loaded) - the safe, GDPR-compliant starting point.
				update_post_meta( $post_id, 'klaro_default_state', '1' );
				update_post_meta( $post_id, 'klaro_required', '0' );
				update_post_meta( $post_id, 'klaro_optout', '0' );
				update_post_meta( $post_id, 'klaro_onlyonce', '0' );
				update_post_meta( $post_id, 'klaro_description', $data['description'] );

				// Stored unescaped on purpose: klaro_integration_code is output
				// raw on the frontend by output_integration_codes(), see the
				// comments there and in save_metabox().
				if ( ! empty( $data['snippet'] ) ) {
					update_post_meta( $post_id, 'klaro_integration_code', $data['snippet'] );
				}
			}
		}
	}

}
