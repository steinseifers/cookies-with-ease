<?php

defined( 'ABSPATH' ) || exit;

/**
 * Reads a boolean-like flag from an Application's post meta
 * (klaro_default_state, klaro_required, klaro_optout, klaro_onlyonce).
 * Returns true for any stored value except '0' - including an empty
 * (missing) one - exactly like the parsing that used to be repeated
 * four times in klaro_script().
 */
function cookieswithease_meta_flag( $post_id, $meta_key ) {
	return '0' !== get_post_meta( $post_id, $meta_key, true );
}
