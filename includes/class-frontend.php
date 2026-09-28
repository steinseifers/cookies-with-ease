<?php

defined( 'ABSPATH' ) || exit;

class CookiesWithEase_Frontend
{

	/**
	 * Whether klaro_script() has run on the current request, i.e. whether
	 * Klaro is actually loaded on this page. Set by klaro_script() itself
	 * and read by output_integration_codes() so the per-Application
	 * integration code is only printed when Klaro (and thus its opt-in
	 * gating) is present, without duplicating klaro_disable_at()'s
	 * page-type conditions.
	 */
	private $klaro_loaded = false;

	public function __construct()
	{

		add_action( 'wp_enqueue_scripts', array( $this, 'klaro_disable_at' ) );
		add_action( 'wp_footer', array( $this, 'output_integration_codes' ), 100 );
		add_filter( 'body_class', array( $this, 'add_theme_body_classes' ) );

	}

	/**
	 * Adds body classes for the selected color scheme and the "hide cookie
	 * graphic" option, so the actual styling stays in css/klaro-custom.css
	 * as plain, maintainable CSS instead of PHP-generated style strings.
	 */
	public function add_theme_body_classes( $classes ) {

		$klaro_options = get_option( 'cookieswithease_options' );

		if ( ! empty( $klaro_options['hide_cookie_icon'] ) ) {
			$classes[] = 'cookieswithease-hide-icon';
		}

		$scheme = isset( $klaro_options['color_scheme'] ) ? $klaro_options['color_scheme'] : 'light';
		if ( in_array( $scheme, array( 'dark-black', 'dark-blue' ), true ) ) {
			$classes[] = 'cookieswithease-scheme-' . $scheme;
		}

		return $classes;
	}

	public function klaro_script() {

		$this->klaro_loaded = true;

		wp_register_script('cookieswithease-config', plugins_url( 'js/cookieswithease-config.js', COOKIESWITHEASE_FILE ), array(), COOKIESWITHEASE_VERSION, true);
		wp_enqueue_script( 'cookieswithease-config' );

		wp_register_script('cookieswithease-klaro', plugins_url( 'js/cookieswithease-klaro.min.js', COOKIESWITHEASE_FILE ), array(), COOKIESWITHEASE_VERSION, true);
		wp_enqueue_script( 'cookieswithease-klaro' );

		// Klaro 0.7.x ships its own stylesheet with a different DOM/class
		// structure than the 2018 bundle this plugin used to style manually.
		// The old backend color settings and the PHP-generated <style> tag
		// that used to override them have been removed; site owners can
		// theme the banner via Klaro's CSS custom properties in their own
		// stylesheet instead.
		wp_register_style( 'cookieswithease-klaro', plugins_url( 'css/klaro.min.css', COOKIESWITHEASE_FILE ), array(), COOKIESWITHEASE_VERSION );
		wp_enqueue_style( 'cookieswithease-klaro' );

		// Custom visual theme for the consent notice (white card, rounded
		// corners, cookie illustration, stacked buttons) on top of Klaro's
		// own stylesheet - loaded after it via the dependency so our rules
		// win without needing !important.
		wp_register_style( 'cookieswithease-klaro-custom', plugins_url( 'css/klaro-custom.css', COOKIESWITHEASE_FILE ), array( 'cookieswithease-klaro' ), COOKIESWITHEASE_VERSION );
		wp_enqueue_style( 'cookieswithease-klaro-custom' );

		wp_localize_script( 'cookieswithease-config', 'cookieswithease_config', $this->get_config() );

	}

	/**
	 * Assembles the config array handed to cookieswithease-config.js as the
	 * global "cookieswithease_config" object. The key order here is the key order of the
	 * resulting JSON.
	 */
	private function get_config() {

		$klaro_options = get_option( 'cookieswithease_options' );

		$mustConsent = isset($klaro_options['must_consent']) ? $klaro_options['must_consent'] : false;
		$klaroCookieExpires = isset($klaro_options['klaro_cookie_expires']) ? $klaro_options['klaro_cookie_expires'] : '365';

		return array_merge(
			array(
				'klaroID' => isset( $klaro_options['klaro_id_selector'] ) ? $klaro_options['klaro_id_selector'] : 'klaro',
				'klaroPrivacyLink' => isset( $klaro_options['klaro_privacy_link'] ) ? $klaro_options['klaro_privacy_link'] : '',
				'klaroCookieExpires' => $klaroCookieExpires,
			),
			$this->get_strings(),
			array(
				'allAppsJson' => $this->get_apps(),
				'mustConsent' => $mustConsent,
			)
		);

	}

	/**
	 * Returns the translated UI strings for the consent notice and modal.
	 */
	private function get_strings() {

		$consentNoticeDescription = __("We collect and process your personal information for the following purposes: {purposes}. ", 'stein-cookies-with-ease');
		$consentNoticelearnMore = __("Learn more", 'stein-cookies-with-ease');
		$consentNoticechangeDescription = __("There were changes since your last visit, please update your consent.", 'stein-cookies-with-ease');
		$consentModalDescription = __("Here you can see and customize the information that we collect about you.", "stein-cookies-with-ease");
		$consentModalTitle = __("Information that we collect", "stein-cookies-with-ease");
		$consentModalPrivacyPolicyText = __("To learn more, please read our {privacyPolicy}. ", "stein-cookies-with-ease");
		$consentModalPrivacyPolicyName = __("privacy policy", "stein-cookies-with-ease");
		$klaroOK = __("OK", "stein-cookies-with-ease");
		$klaroDecline = __("Decline", "stein-cookies-with-ease");
		$klaroSave = __("Save", "stein-cookies-with-ease");
		$klaroClose = __("Close", "stein-cookies-with-ease");
		$klaroAcceptAll = __("Accept all", 'stein-cookies-with-ease');
		$klaroAcceptSelected = __("Accept selected", 'stein-cookies-with-ease');
		$appPurpose = __("Purpose", "stein-cookies-with-ease");
		$appPurposes = __("Purposes", "stein-cookies-with-ease");
		$appDisableAllTitle = __("Toggle all apps", "stein-cookies-with-ease");
		$appDisableAllDescription = __("Use this switch to enable/disable all apps.", "stein-cookies-with-ease");
		$appOptOut = __("(opt-out)", "stein-cookies-with-ease");
		$appOptOutDescription = __("This app is loaded by default (but you can opt out)", "stein-cookies-with-ease");
		$appRequired = __("(always required)", "stein-cookies-with-ease");
		$appRequiredDescription = __("This application is always required", "stein-cookies-with-ease");
		$purposesAnalytics = __("Analytics", "stein-cookies-with-ease");
		$purposesSecurity = __("Security", "stein-cookies-with-ease");
		$purposesAdvertising = __("Advertising", "stein-cookies-with-ease");
		$purposesStyling = __("Styling", "stein-cookies-with-ease");
		$purposesStatistics = __("Statistics", "stein-cookies-with-ease");
		$purposesOther = __("Other", "stein-cookies-with-ease");
		$purposesFunctionality = __("Functionality", "stein-cookies-with-ease");

		// Per-embed inline consent (the "do you want to load external content
		// from X?" placeholder shown in place of a gated iframe until the
		// visitor consents to that one Application). Klaro renders two
		// buttons here by default - a temporary "accept once" one, and an
		// "accept and remember" one that only appears once the visitor has
		// already been through the main consent notice/modal at least once.
		// We give both the same label so visitors only ever see one action;
		// cookieswithease-config.js also passes both under the same Klaro
		// translation keys, and css/klaro-custom.css hides the temporary
		// button via CSS whenever the persistent one is also present.
		$contextualConsentDescription = __("Do you want to load external content supplied by {title}?", "stein-cookies-with-ease");
		$contextualConsentAccept = __("Yes", "stein-cookies-with-ease");

		return array(
			'consentNoticeDescription' => $consentNoticeDescription,
			'consentNoticelearnMore' => $consentNoticelearnMore,
			'consentNoticechangeDescription' => $consentNoticechangeDescription,
			'consentModalDescription' => $consentModalDescription,
			'consentModalTitle' => $consentModalTitle,
			'consentModalPrivacyPolicyText' => $consentModalPrivacyPolicyText,
			'consentModalPrivacyPolicyName' => $consentModalPrivacyPolicyName,
			'klaroOK' => $klaroOK,
			'klaroDecline' => $klaroDecline,
			'klaroSave' => $klaroSave,
			'klaroClose' => $klaroClose,
			'klaroAcceptAll' => $klaroAcceptAll,
			'klaroAcceptSelected' => $klaroAcceptSelected,
			'appPurpose' => $appPurpose,
			'appPurposes' => $appPurposes,
			'appDisableAllTitle' => $appDisableAllTitle,
			'appDisableAllDescription' => $appDisableAllDescription,
			'appOptOut' => $appOptOut,
			'appOptOutDescription' => $appOptOutDescription,
			'appRequired' => $appRequired,
			'appRequiredDescription' => $appRequiredDescription,
			'purposesAnalytics' => $purposesAnalytics,
			'purposesSecurity' => $purposesSecurity,
			'purposesAdvertising' => $purposesAdvertising,
			'purposesStyling' => $purposesStyling,
			'purposesStatistics' => $purposesStatistics,
			'purposesFunctionality' => $purposesFunctionality,
			'purposesOther' => $purposesOther,
			'contextualConsentDescription' => $contextualConsentDescription,
			'contextualConsentAccept' => $contextualConsentAccept,
		);

	}

	/**
	 * Returns the Applications (cookieswithease_app posts) in the shape
	 * Klaro expects for its "apps" list.
	 */
	private function get_apps() {

		$query = new WP_Query( array( 'post_type' => 'cookieswithease_app' ) );

		$jsondata = array();

		while ( $query->have_posts() ) : $query->the_post();

		$slug = get_post_field( 'post_name', get_post() );

		$default_checked  = ! cookieswithease_meta_flag( get_the_ID(), 'klaro_default_state' );
		$required_checked = cookieswithease_meta_flag( get_the_ID(), 'klaro_required' );
		$optout_checked   = cookieswithease_meta_flag( get_the_ID(), 'klaro_optout' );
		$onlyonce_checked = cookieswithease_meta_flag( get_the_ID(), 'klaro_onlyonce' );


		$klaro_purpose = get_post_meta(get_the_ID(), 'klaro_purposes', false);


		$jsondata[] = array(

    		'name' => $slug,
			'title' => get_the_title(),
			'default' => $default_checked,
			'required' => $required_checked,
			'optOut' => $optout_checked,
			'onlyOnce' => $onlyonce_checked,
			'purposes' => $klaro_purpose,
    		'description' => get_post_meta( get_the_ID(), 'klaro_description', true ),

		);
		endwhile;

		wp_reset_postdata();

		return $jsondata;

	}


	public function klaro_disable_at() {

		if ( is_front_page() ) {
			$context = 'front_page';
		} elseif ( is_home() ) {
			$context = 'blog';
		} elseif ( is_page() ) {
			$context = 'page';
		} elseif ( is_tag() ) {
			$context = 'tag';
		} elseif ( is_category() ) {
			$context = 'category';
		} elseif ( is_single() ) {
			$context = 'single';
		} elseif ( is_archive() ) {
			$context = 'archive';
		} elseif ( is_search() ) {
			$context = 'search';
		} elseif ( is_404() ) {
			$context = '404';
		} else {
			$context = '';
		}

		/**
		 * Filters whether Klaro (the consent script/banner) should be
		 * disabled on the current request. Replaces the old "Disable at"
		 * settings-page checkboxes and per-page/post exception fields --
		 * control this from code instead, e.g. in a small mu-plugin:
		 *
		 *     add_filter( 'cookieswithease_disable_klaro', function( $disable, $context ) {
		 *         if ( 'page' === $context && is_page( 'imprint' ) ) {
		 *             return true; // never show the banner on the imprint page
		 *         }
		 *         return $disable;
		 *     }, 10, 2 );
		 *
		 * @param bool   $disable Whether to disable Klaro on this request. Default false (always enabled).
		 * @param string $context One of 'front_page', 'blog', 'page', 'tag', 'category', 'single', 'archive', 'search', '404', or '' for anything else.
		 */
		$disable = apply_filters( 'cookieswithease_disable_klaro', false, $context );

		if ( ! $disable ) {
			$this->klaro_script();
		}
	}

	/**
	 * Outputs the per-Application integration code (klaro_integration_code
	 * post meta) of every published Application in the footer, so users can
	 * manage the actual <script>/<iframe> tags for a third-party service
	 * directly from the Application's admin screen instead of a separate
	 * header/footer-code plugin.
	 *
	 * The value is echoed RAW, without esc_html()/wp_kses() or any other
	 * filtering. This is intentional, not an oversight: the tags rely on
	 * type="text/plain" (Klaro's own opt-in gating mechanism) to stay inert
	 * until consent is given, so escaping them would break the feature
	 * entirely. Writing this meta field is only ever possible through
	 * save_metabox(), which is gated behind the 'manage_options' capability
	 * and a nonce check -- exactly like WordPress' own "Custom HTML" widget
	 * or any established header/footer-code plugin. There is no code path
	 * that lets an unprivileged user or a site visitor influence this
	 * value, so raw output here is not an XSS risk.
	 *
	 * Additionally, the code is only output if the Application's author holds
	 * the 'unfiltered_html' capability (not available to administrators on
	 * multisite or with DISALLOW_UNFILTERED_HTML), mirroring core's
	 * handling of raw HTML in post content.
	 */
	public function output_integration_codes() {

		// Only output anything if Klaro was actually loaded on this
		// request -- reuses klaro_disable_at()'s existing page-type
		// conditions (via the flag klaro_script() sets) instead of
		// duplicating that logic here.
		if ( ! $this->klaro_loaded ) {
			return;
		}

		$query = new WP_Query( array(
			'post_type'      => 'cookieswithease_app',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		while ( $query->have_posts() ) : $query->the_post();

			$integration_code = get_post_meta( get_the_ID(), 'klaro_integration_code', true );

			// Only output code whose author is allowed to publish unfiltered
			// HTML (capability 'unfiltered_html'); see save_metabox().
			if ( '' !== $integration_code && user_can( (int) get_post_field( 'post_author', get_the_ID() ), 'unfiltered_html' ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw output, see method docblock.
				echo $integration_code;
			}

		endwhile;

		wp_reset_postdata();
	}

}
