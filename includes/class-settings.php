<?php

defined( 'ABSPATH' ) || exit;

class CookiesWithEase_Settings
{

    private $options;



	public function __construct()
	{

		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_init', array( $this, 'klaro_default_options' ) );
		add_action( 'admin_init', array( $this, 'handle_reset_request' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'klaro_admin_script' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( COOKIESWITHEASE_FILE ), array( $this, 'klaro_settings_link' )  );



    }

	public function klaro_settings_link($links){
		$settings_link = '<a href="edit.php?post_type=cookieswithease_app&page=cookies-with-ease-settings">' . esc_html__( 'Settings', 'cookies-with-ease' ) . '</a>';
		array_unshift($links, $settings_link);
	return $links;
	}





    public function klaro_admin_script($hook) {
		$screen = get_current_screen();
    if ( $hook == 'post.php' && $screen->post_type != 'cookieswithease_app' ) {
        return;
    }

		wp_register_script('cookieswithease-admin', plugins_url('/js/cookieswithease-admin.js', COOKIESWITHEASE_FILE), array( 'jquery' ), COOKIESWITHEASE_VERSION, true);
		wp_enqueue_script('cookieswithease-admin');

		wp_register_style('cookieswithease-admin-style', plugins_url('/css/admin.css', COOKIESWITHEASE_FILE), array(), COOKIESWITHEASE_VERSION );
	    wp_enqueue_style('cookieswithease-admin-style');
	}

	public function add_plugin_page(){
		// This page will be under "Applications"

		add_submenu_page(
			'edit.php?post_type=cookieswithease_app',
			__( 'Settings Admin', 'cookies-with-ease' ),
			__( 'Settings', 'cookies-with-ease' ),
			'manage_options',
			'cookies-with-ease-settings',
			array( $this, 'create_admin_page' )
		);



	}

	public function create_admin_page(){
		// Set class property
		$this->options = get_option( 'cookieswithease_options');
		?>
		<div id="cookieswithease" class="wrap">

			<h2><?php esc_html_e('Cookies with Ease', 'cookies-with-ease'); ?></h2>
            <p><?php esc_html_e("Add Cookies with Ease cookie consent manager.", 'cookies-with-ease'); ?></p>

            <div class="main-content">

			<form method="post" action="options.php">
				<?php
				settings_fields( 'cookieswithease_option_group' );
				do_settings_sections( 'klaro-settings' );
				submit_button();
				?>
			</form>

			<form method="post" action="">
				<?php wp_nonce_field( 'cookieswithease_reset_action', 'cookieswithease_reset_nonce' ); ?>
				<input name="reset" class="button button-secondary confirm" type="submit" value="<?php echo esc_attr__( 'Reset to default settings', 'cookies-with-ease' ); ?>" >
				<input type="hidden" name="action" value="reset" />
			</form>

			</div>


            <div class="main-sidebar">

            <h3><?php esc_html_e('Plugin info','cookies-with-ease'); ?></h3>

			<div class="inner">
				<ul>
					<li><strong><?php esc_html_e('Author:','cookies-with-ease'); ?></strong> <a href="https://steinseifers.de" target="_blank" rel="noopener noreferrer">Steinseifers Kommunikationswerkstatt</a></li>
					<li><strong><?php esc_html_e('Version:','cookies-with-ease'); ?></strong> <?php echo esc_html( COOKIESWITHEASE_VERSION ); ?></li>
					<li><strong><?php esc_html_e('Credits:','cookies-with-ease'); ?></strong> <?php esc_html_e('Based on Klaro (kiprotect), BSD-3-Clause', 'cookies-with-ease'); ?> &ndash; <a href="https://github.com/kiprotect/klaro" target="_blank" rel="noopener noreferrer">github.com/kiprotect/klaro</a></li>

				</ul>
			</div>

			</div>
        </div>
		<?php
	}

	public function page_init()
	{
		register_setting(
			'cookieswithease_option_group',
			'cookieswithease_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'type'              => 'array',
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'setting_section_id',
			'',
			array( $this, 'print_section_info' ),
			'klaro-settings'

		);





		add_settings_field(
			'klaro_id_selector',
			__("Element ID and Cookie Name", 'cookies-with-ease'),
			array( $this, 'klaro_id_selector_callback' ),
			'klaro-settings',
			'setting_section_id'
		);

		add_settings_field(
			'klaro_privacy_link',
			__("Privacy Policy Link", 'cookies-with-ease'),
			array( $this, 'klaro_privacy_link_callback' ),
			'klaro-settings',
			'setting_section_id'
		);


		add_settings_field(
			'klaro_zindex',
			__("Z-index", 'cookies-with-ease'),
			array( $this, 'klaro_zindex_callback' ),
			'klaro-settings',
			'setting_section_id'
		);
		add_settings_field(
			'klaro_cookie_expires',
			__("Cookie expires after", 'cookies-with-ease'),
			array( $this, 'klaro_cookie_expires_callback' ),
			'klaro-settings',
			'setting_section_id'
		);



		add_settings_field(
			'must_consent',
			__("Must Consent", 'cookies-with-ease'),
			array( $this, 'must_consent_callback' ),
			'klaro-settings',
			'setting_section_id'
		);

		add_settings_field(
			'hide_cookie_icon',
			__("Cookie Graphic", 'cookies-with-ease'),
			array( $this, 'hide_cookie_icon_callback' ),
			'klaro-settings',
			'setting_section_id'
		);

		add_settings_field(
			'color_scheme',
			__("Color Scheme", 'cookies-with-ease'),
			array( $this, 'color_scheme_callback' ),
			'klaro-settings',
			'setting_section_id'
		);

		add_settings_field(
			'auto_gate_video_embeds',
			__("Auto-gate video embeds", 'cookies-with-ease'),
			array( $this, 'auto_gate_video_embeds_callback' ),
			'klaro-settings',
			'setting_section_id'
		);


		// Note: the old "Disable at" page-type checkboxes and per-page/post
		// exception fields were removed in favor of the cookieswithease_disable_klaro
		// filter (see klaro_disable_at()) -- controlling this from code is
		// more flexible than a fixed set of checkboxes ever was.



	}
/**
* Sanitize each setting field as needed
*
* @param array $input Contains all settings fields as array keys
*/
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['klaro_id_selector'] ) )
			$new_input['klaro_id_selector'] = sanitize_text_field( $input['klaro_id_selector'] );

		if( isset( $input['klaro_privacy_link'] ) )
			$new_input['klaro_privacy_link'] = sanitize_text_field( $input['klaro_privacy_link'] );

		if( isset( $input['klaro_zindex'] ) )
			$new_input['klaro_zindex'] = absint( $input['klaro_zindex'] );

		if( isset( $input['klaro_cookie_expires'] ) )
			$new_input['klaro_cookie_expires'] = absint( $input['klaro_cookie_expires'] );

		if( isset( $input['must_consent'] ) )
			$new_input['must_consent'] = sanitize_text_field( $input['must_consent'] );

		if( isset( $input['hide_cookie_icon'] ) )
			$new_input['hide_cookie_icon'] = sanitize_text_field( $input['hide_cookie_icon'] );

		if( isset( $input['color_scheme'] ) && in_array( $input['color_scheme'], array( 'light', 'dark-black', 'dark-blue' ), true ) )
			$new_input['color_scheme'] = $input['color_scheme'];

		if( isset( $input['auto_gate_video_embeds'] ) )
			$new_input['auto_gate_video_embeds'] = sanitize_text_field( $input['auto_gate_video_embeds'] );

		return $new_input;
	}

	/**
	 * Liefert die Werksvoreinstellungen. Wird sowohl bei der Erstinstallation
	 * als auch beim (abgesicherten) Reset ueber handle_reset_request() genutzt.
	 */
	private function get_default_options() {
		return array(

				'klaro_id_selector' => 'klaro',
				'klaro_privacy_link' => '/privacy',
				'klaro_zindex' => '99990',
				'klaro_cookie_expires' => '365',
				'color_scheme' => 'light',
		);

	}

	public function klaro_default_options() {

		if ( get_option('cookieswithease_options') == false ) {
			update_option( 'cookieswithease_options', $this->get_default_options() );
		}

	}

	/**
	 * Verarbeitet den "Reset to default settings"-Button. Bewusst aus dem
	 * ungebundenen admin_init-Kontext des Originals herausgeloest: der
	 * Handler wirkt nur, wenn er auf der eigenen Settings-Seite (page=
	 * cookies-with-ease-settings) mit gueltigem Nonce und manage_options-Capability
	 * aufgerufen wird - andernfalls stirbt check_admin_referer() oder es
	 * wird stillschweigend zurueckgekehrt. Danach folgt ein Redirect
	 * (POST-Redirect-GET), damit ein Neuladen der Seite keinen erneuten
	 * Reset ausloest.
	 */
	public function handle_reset_request() {
		if ( ! isset( $_POST['reset'] ) ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) || 'cookies-with-ease-settings' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		check_admin_referer( 'cookieswithease_reset_action', 'cookieswithease_reset_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'cookies-with-ease' ) );
		}

		update_option( 'cookieswithease_options', $this->get_default_options() );

		wp_safe_redirect( add_query_arg( array( 'post_type' => 'cookieswithease_app', 'page' => 'cookies-with-ease-settings' ), admin_url( 'edit.php' ) ) );
		exit;
	}


	public function print_section_info()
	{
		// Intentionally empty: the section has no description.
    }

	public function klaro_id_selector_callback()
	{
		printf(
			'<p class="description"><input type="text" size="18" id="klaro_id_selector" class="mystickyinput" name="cookieswithease_options[klaro_id_selector]" value="%s" /> ',
			isset( $this->options['klaro_id_selector'] ) ? esc_attr( $this->options['klaro_id_selector']) : ''
		);

		 echo '</p>';
	}

	public function klaro_privacy_link_callback()
	{
		printf(
			'<p class="description"><input type="text" size="18" id="klaro_privacy_link" class="mystickyinput" name="cookieswithease_options[klaro_privacy_link]" value="%s" /> ',
			isset( $this->options['klaro_privacy_link'] ) ? esc_attr( $this->options['klaro_privacy_link']) : ''
		);

		 echo esc_html__('Put a link to your privacy policy here (relative or absolute). Put shortcode [cookieswithease type="review"] and [cookieswithease type="reset"] to your privacy page so visitor can review, update and reset their consent.', 'cookies-with-ease');
		 echo '</p>';

	}

	public function klaro_zindex_callback()
	{
		printf(
			'<p class="description"><input type="number" min="0" max="2147483647" step="1" id="klaro_zindex" name="cookieswithease_options[klaro_zindex]" value="%s" /></p>',
			isset( $this->options['klaro_zindex'] ) ? esc_attr( $this->options['klaro_zindex']) : ''
		);
	}

	public function klaro_cookie_expires_callback()
	{
		printf(
		'<p class="description">'
		);
		printf(
		' <input type="number" class="small-text" min="0" step="1" id="klaro_cookie_expires" name="cookieswithease_options[klaro_cookie_expires]" value="%s" />',
			isset( $this->options['klaro_cookie_expires'] ) ? esc_attr( $this->options['klaro_cookie_expires']) : ''
		);
		echo esc_html__("days", 'cookies-with-ease');
		echo '</p>';
	}



	public function must_consent_callback()
	{

		printf(
			'<p class="description"><input id="%1$s" name="cookieswithease_options[must_consent]" type="checkbox" %2$s /> ',
			'must_consent',
			checked( isset( $this->options['must_consent'] ), true, false )
		);
		echo esc_html__("If Must Consent is set to true, Klaro will directly display the consent manager modal and not allow the user to close it before having actively consented or declines the use of third-party apps.", 'cookies-with-ease');
		echo '</p>';


	}

	public function hide_cookie_icon_callback()
	{
		printf(
			'<p class="description"><input id="%1$s" name="cookieswithease_options[hide_cookie_icon]" type="checkbox" %2$s /> ',
			'hide_cookie_icon',
			checked( isset( $this->options['hide_cookie_icon'] ), true, false )
		);
		echo esc_html__("Hide the cookie graphic shown above the consent banner and modal.", 'cookies-with-ease');
		echo '</p>';
	}

	public function color_scheme_callback()
	{
		$current = isset( $this->options['color_scheme'] ) ? $this->options['color_scheme'] : 'light';

		$schemes = array(
			'light'      => array(
				'label' => __( 'Light', 'cookies-with-ease' ),
				'swatch' => '#ffffff',
			),
			'dark-black' => array(
				'label' => __( 'Dark', 'cookies-with-ease' ),
				'swatch' => '#1a1a1a',
			),
			'dark-blue'  => array(
				'label' => __( 'Dark Blue', 'cookies-with-ease' ),
				'swatch' => '#1e3a5f',
			),
		);

		echo '<div class="cookieswithease-scheme-pill">';
		foreach ( $schemes as $value => $scheme ) {
			printf(
				'<label class="cookieswithease-scheme-option%1$s"><input type="radio" name="cookieswithease_options[color_scheme]" value="%2$s" %3$s /><span class="cookieswithease-scheme-swatch" style="background-color:%4$s;"></span><span class="cookieswithease-scheme-label">%5$s</span></label>',
				( $current === $value ) ? ' is-active' : '',
				esc_attr( $value ),
				checked( $current, $value, false ),
				esc_attr( $scheme['swatch'] ),
				esc_html( $scheme['label'] )
			);
		}
		echo '</div>';
		echo '<p class="description">' . esc_html__( 'Pick a built-in color scheme for the consent banner, or leave it on Light and override the colors yourself with custom CSS.', 'cookies-with-ease' ) . '</p>';
	}

	public function auto_gate_video_embeds_callback()
	{
		printf(
			'<p class="description"><input id="%1$s" name="cookieswithease_options[auto_gate_video_embeds]" type="checkbox" %2$s /> ',
			'auto_gate_video_embeds',
			checked( isset( $this->options['auto_gate_video_embeds'] ), true, false )
		);
		echo esc_html__( 'Automatically gate YouTube and Vimeo videos embedded via the block editor (URL paste or the embed block) behind consent, without editing each page by hand. Requires a published "youtube"/"vimeo" Application (see the draft templates created on activation) - the video only loads once the visitor consents to it.', 'cookies-with-ease' );
		echo '</p>';
	}




}
