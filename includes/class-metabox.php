<?php

defined( 'ABSPATH' ) || exit;

//=============================================================================================
// CLASS -> ADD META BOX TO POST PAGE OR CUSTOM POST (EXTENDS CUSTOM FIELDS)
//=============================================================================================
/**
 * Register a meta box using a class.
 */
class CookiesWithEase_Metabox {

    /**
     * Constructor.
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			add_filter( 'is_protected_meta', array( $this, 'exclude_custom_fields' ), 10, 2);

        }

    }


	/**
     * Hide meta boxes on pages and posts and cpt's
     */

	public function exclude_custom_fields( $protected, $meta_key ) {
  		if ( 'cookieswithease_app' != get_post_type() ) {
    		 if ( in_array( $meta_key, array( 'klaro_default_state', 'klaro_onlyonce', 'klaro_purposes', 'klaro_required', 'klaro_optout' ), true ) ) {
      			return true;
    		}
 		}
  		return $protected;
	}


    /**
     * Meta box initialization.
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
        add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
    }

    /**
     * Adds the meta box.
     */
    public function add_metabox() {
        add_meta_box(
            'klaro-meta-box',
            __( 'Application settings', 'stein-cookies-with-ease' ),
            array( $this, 'render_metabox' ),
			'cookieswithease_app',
            'advanced',
            'default'
        );

    }

    /**
     * Renders the meta box.
     */
    public function render_metabox( $post ) {


		$meta = get_post_meta( $post->ID );
		$klaro_purposes = ( isset( $meta['klaro_purposes'][0] ) && '' !== $meta['klaro_purposes'][0] ) ? $meta['klaro_purposes'][0] : '';
		$klaro_default_state = ( isset( $meta['klaro_default_state'][0] ) &&  '1' === $meta['klaro_default_state'][0] ) ? 1 : 0;
		$klaro_required = ( isset( $meta['klaro_required'][0] ) &&  '1' === $meta['klaro_required'][0] ) ? 1 : 0;
		$klaro_optout = ( isset( $meta['klaro_optout'][0] ) &&  '1' === $meta['klaro_optout'][0] ) ? 1 : 0;
		$klaro_onlyonce = ( isset( $meta['klaro_onlyonce'][0] ) &&  '1' === $meta['klaro_onlyonce'][0] ) ? 1 : 0;

		wp_nonce_field( 'cookieswithease_save_metabox_' . $post->ID, 'cookieswithease_metabox_nonce' );

		?>

        <div class="post_meta_extras">

             <p>
				<label><?php esc_html_e( 'Purpose', 'stein-cookies-with-ease' ); ?></label>

              	<select id="klaro_purposes" name="klaro_purposes">
	                <option value="analytics" <?php selected( esc_attr( $klaro_purposes ), 'analytics' )?> ><?php esc_html_e("Analytics", "stein-cookies-with-ease"); ?></option>
	                <option value="advertising" <?php selected( esc_attr( $klaro_purposes ), 'advertising')?> ><?php esc_html_e("Advertising", "stein-cookies-with-ease");?></option>
                    <option value="security" <?php selected( esc_attr( $klaro_purposes ), 'security')?> ><?php esc_html_e("Security", "stein-cookies-with-ease");?></option>
                    <option value="statistics" <?php selected( esc_attr( $klaro_purposes ), 'statistics')?> ><?php esc_html_e("Statistics", "stein-cookies-with-ease");?></option>
                    <option value="functionality" <?php selected( esc_attr( $klaro_purposes ), 'functionality')?> ><?php esc_html_e("Functionality", "stein-cookies-with-ease");?></option>
                    <option value="styling" <?php selected( esc_attr( $klaro_purposes ), 'styling')?> ><?php esc_html_e("Styling", "stein-cookies-with-ease");?></option>
                    <option value="other" <?php selected( esc_attr( $klaro_purposes ), 'other')?> ><?php esc_html_e("Other", "stein-cookies-with-ease");?></option>
	          	</select>
			</p>
			<p>
				<label><input type="checkbox" name="klaro_default_state" value="1" <?php checked( $klaro_default_state, 1 ); ?> /><?php esc_html_e( 'Set Default state for app to false. Overwrites global "default" setting. We recommend set this to "false" for apps that collect personal information.', 'stein-cookies-with-ease' ); ?></label>
			</p>

            <p>
				<label><input type="checkbox" name="klaro_required" value="1" <?php checked( $klaro_required, 1 ); ?> /><?php esc_html_e( 'Required. If "required" is set to true, Klaro will not allow this app to be disabled by the user.', 'stein-cookies-with-ease' ); ?></label>
			</p>

             <p>
				<label><input type="checkbox" name="klaro_optout" value="1" <?php checked( $klaro_optout, 1 ); ?> /><?php esc_html_e( 'Opt-out. If "optOut" is set to true, Klaro will load this app even before the user gave explicit consent. We recommend always leaving this "false".', 'stein-cookies-with-ease' ); ?></label>
			</p>


             <p>
				<label><input type="checkbox" name="klaro_onlyonce" value="1" <?php checked( $klaro_onlyonce, 1 ); ?> /><?php esc_html_e( 'Only Once. If "onlyOnce" is set to true, the app will only be executed once regardless how often the user toggles it on and off.', 'stein-cookies-with-ease' ); ?></label>
			</p>

            <hr class="cookieswithease-divider" />

            <p>
				<label for="klaro_description" class="cookieswithease-field-label"><?php esc_html_e('Description', 'stein-cookies-with-ease'); ?></label>
				<textarea id="klaro_description" name="klaro_description" rows="3" style="width:100%;" ><?php echo esc_textarea( get_post_meta( $post->ID, 'klaro_description', true ) ); ?></textarea>
				<span class="description"><?php esc_html_e('Shown to your visitors in the cookie consent banner. Keep it short and easy to understand — this is NOT the place for code.', 'stein-cookies-with-ease'); ?></span>
			</p>

			<?php
			$slug              = get_post_field( 'post_name', get_post() );
			$integration_code  = get_post_meta( $post->ID, 'klaro_integration_code', true );
			$can_edit_code     = current_user_can( 'unfiltered_html' );
			$is_auto_draft     = ( 'auto-draft' === $post->post_status );

			// youtube/vimeo are handled entirely by the "Auto-gate video
			// embeds" setting (gate_video_oembed()) once published - no
			// integration code needed, so unlike other Applications they
			// don't get the generic <script> starting template below (that
			// would suggest pasting script markup here, which is wrong for
			// these two and would also be output site-wide, see the notice
			// further down).
			$auto_gated_slugs = array( 'youtube', 'vimeo' );

			if ( '' !== $integration_code ) {
				$integration_code_value = $integration_code;
			} elseif ( ! $is_auto_draft && '' !== $slug && ! in_array( $slug, $auto_gated_slugs, true ) ) {
				// Prefill a starting template using the real slug, editable in place.
				$integration_code_value = "# Inline scripts:\n"
					. '<' . 'script type="text/plain" data-type="application/javascript" data-name="' . $slug . "\">\n// Your Code...\n</" . "script>\n\n"
					. "# External scripts and resources (img, link, ...):\n"
					. '<' . 'script type="text/plain" data-src="//example.com/script.js" data-name="' . $slug . '"></' . 'script>';
			} else {
				// Freshly created, never saved (no real slug yet to prefill
				// with), or one of the auto-gated slugs above.
				$integration_code_value = '';
			}
			?>

            <hr class="cookieswithease-divider" />

            <p>
				<label for="klaro_integration_code" class="cookieswithease-field-label"><?php esc_html_e('Integration Code', 'stein-cookies-with-ease'); ?></label>
				<textarea id="klaro_integration_code" name="klaro_integration_code" rows="8" style="width:100%; font-family:monospace;" <?php echo $can_edit_code ? '' : 'readonly="readonly"'; ?> ><?php echo esc_textarea( $integration_code_value ); ?></textarea>
				<?php if ( ! $can_edit_code ) : ?>
					<span class="description cookieswithease-field-warning"><strong><?php esc_html_e('Read-only:', 'stein-cookies-with-ease'); ?></strong> <?php esc_html_e('Your user account lacks the "unfiltered_html" capability (e.g. on multisite or with DISALLOW_UNFILTERED_HTML), so the integration code cannot be changed here. The saved value is kept as it is.', 'stein-cookies-with-ease'); ?></span><br />
				<?php endif; ?>
				<span class="description">
					<?php esc_html_e('This code is output automatically on your site whenever consent applies. It must use type="text/plain" and match this Application\'s data-name so Stein\'s Cookies with Ease can gate it. See the example here:', 'stein-cookies-with-ease'); ?>
					<?php if ( $is_auto_draft ) : ?>
						<?php esc_html_e('Save this Application once, then reopen it to get a starting template with the correct identifier.', 'stein-cookies-with-ease'); ?>
					<?php endif; ?>
				</span>
				<?php
				// Always shown, independent of the textarea's actual content
				// above (which some Applications - e.g. youtube/vimeo, see
				// $auto_gated_slugs - deliberately leave empty) so there is
				// always a concrete example to look at.
				$example_slug = ( '' !== $slug ) ? $slug : 'your-app-slug';
				?>
				<pre class="cookieswithease-integration-example"><code>&lt;script type=&quot;text/plain&quot; data-type=&quot;application/javascript&quot; data-name=&quot;<?php echo esc_html( $example_slug ); ?>&quot;&gt;
// Your Code...
&lt;/script&gt;

&lt;script type=&quot;text/plain&quot; data-src=&quot;//example.com/script.js&quot; data-name=&quot;<?php echo esc_html( $example_slug ); ?>&quot;&gt;&lt;/script&gt;</code></pre>
			</p>
			<p class="description cookieswithease-field-warning">
				<strong><?php esc_html_e('Note:', 'stein-cookies-with-ease'); ?></strong>
				<?php esc_html_e('This code is output on every single page of your site, not just where you use this Application - there is no way to limit it to specific pages here. For an Application you only use on certain pages (e.g. a Google Maps embed on your contact page, or a video only shown on one post), leave this field empty and instead place the code directly on that page (Custom HTML block), or, for YouTube/Vimeo, use the "Auto-gate video embeds" setting under Settings instead.', 'stein-cookies-with-ease'); ?>
			</p>

        <?php

    }

    /**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function save_metabox( $post_id, $post ) {
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['cookieswithease_metabox_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cookieswithease_metabox_nonce'] ) ) : '';
        $nonce_action = 'cookieswithease_save_metabox_' . $post_id;

        // Check if nonce is set (empty() instead of the previously ineffective isset() check).
        if ( empty( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

		/* Ok to save */

		if ( isset( $_POST['klaro_purposes'] ) ) { // Input var okay.
			update_post_meta( $post_id, 'klaro_purposes', sanitize_text_field( wp_unslash( $_POST['klaro_purposes'] ) ) ); // Input var okay.
		}

		$klaro_default_state = ( isset( $_POST['klaro_default_state'] ) && '1' === $_POST['klaro_default_state'] ) ? 1 : 0; // Input var okay.
		update_post_meta( $post_id, 'klaro_default_state', esc_attr( $klaro_default_state ) );

		$klaro_required = ( isset( $_POST['klaro_required'] ) && '1' === $_POST['klaro_required'] ) ? 1 : 0; // Input var okay.
		update_post_meta( $post_id, 'klaro_required', esc_attr( $klaro_required ) );

		$klaro_optout = ( isset( $_POST['klaro_optout'] ) && '1' === $_POST['klaro_optout'] ) ? 1 : 0; // Input var okay.
		update_post_meta( $post_id, 'klaro_optout', esc_attr( $klaro_optout ) );

		$klaro_onlyonce = ( isset( $_POST['klaro_onlyonce'] ) && '1' === $_POST['klaro_onlyonce'] ) ? 1 : 0; // Input var okay.
		update_post_meta( $post_id, 'klaro_onlyonce', esc_attr( $klaro_onlyonce ) );

		if ( isset( $_POST['klaro_description'] ) ) { // Input var okay.
			update_post_meta( $post_id, 'klaro_description', sanitize_textarea_field( wp_unslash( $_POST['klaro_description'] ) ) ); // Input var okay.
		}

		// Only users with the 'unfiltered_html' capability may change the raw
		// integration code; for everyone else the stored value stays untouched.
		if ( current_user_can( 'unfiltered_html' ) && isset( $_POST['klaro_integration_code'] ) ) { // Input var okay.
			// Deliberately NOT run through sanitize_text_field()/wp_kses() or
			// any other content filter: this field exists to hold raw
			// <script>/<iframe> markup that output_integration_codes() later
			// echoes verbatim on the frontend, so filtering it here would
			// strip the very tags the admin intended to save. This is safe
			// because save_metabox() only reaches this point after the
			// nonce + 'edit_post' capability checks above have passed
			// (this CPT requires 'manage_options', see
			// register_custom_post_type()), exactly like an established
			// "Custom Header/Footer Code" plugin. Do NOT add wp_kses_post()
			// or similar here -- it would silently break this feature.
			update_post_meta( $post_id, 'klaro_integration_code', wp_unslash( $_POST['klaro_integration_code'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw code by design, gated by the unfiltered_html check above.
		}

    }
}
