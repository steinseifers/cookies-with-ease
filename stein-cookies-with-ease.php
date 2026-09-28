<?php
	/*
	Plugin Name: Stein's Cookies with Ease
	Description: A simple, privacy-friendly cookie consent manager based on Klaro!. Gate third-party scripts and embeds behind visitor consent, manage them as Applications, and style the banner with built-in color schemes.
	Version: 1.0.0
	Requires at least: 5.8
	Requires PHP: 7.4
	Author: Steinseifers Kommunikationswerkstatt
	Author URI: https://steinseifers.de
	Text Domain: stein-cookies-with-ease
	License: GPLv2 or later
	License URI: https://www.gnu.org/licenses/gpl-2.0.html

	Stein's Cookies with Ease bundles the Klaro! consent management JavaScript library
	(js/cookieswithease-klaro.min.js) from https://github.com/kiprotect/klaro,
	licensed under BSD-3-Clause. See licenses/BSD-3-CLAUSE-klaro.txt for
	the full license text and copyright notice.
	*/

defined('ABSPATH') or die("Cannot access pages directly.");

// Single source of truth for the version; the plugin header above is text only.
define( 'COOKIESWITHEASE_VERSION', '1.0.0' );
define( 'COOKIESWITHEASE_FILE', __FILE__ );
define( 'COOKIESWITHEASE_DIR', plugin_dir_path( __FILE__ ) );

require_once COOKIESWITHEASE_DIR . 'includes/helpers.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-activator.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-post-type.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-settings.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-metabox.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-embeds.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-shortcodes.php';
require_once COOKIESWITHEASE_DIR . 'includes/class-frontend.php';

register_activation_hook( COOKIESWITHEASE_FILE, array( 'CookiesWithEase_Activator', 'activate' ) );

if ( is_admin() ) {

	new CookiesWithEase_Settings();
	new CookiesWithEase_Post_Type();
	new CookiesWithEase_Metabox();

} else {

	new CookiesWithEase_Frontend();
	new CookiesWithEase_Embeds();
	new CookiesWithEase_Shortcodes();

}
