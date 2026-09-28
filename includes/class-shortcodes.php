<?php

defined( 'ABSPATH' ) || exit;

class CookiesWithEase_Shortcodes
{

	public function __construct()
	{

		add_shortcode( 'cookieswithease', array( $this, 'klaro_func' ) );

	}

	public function klaro_func( $atts ) {
		$type = '';
		$a = shortcode_atts( array(
			'type' => $type,
		), $atts );

  		if ($a['type'] == 'review' ) {
    		return '<a href="#" onclick="return klaro.show(klaroConfig, true)">'. esc_html__('Review and update','cookies-with-ease').'</a>';
  		}

		else if ($a['type'] == 'reset' ) {
			return '<a href="#" onclick="return CookiesWithEase.resetConsent()">'. esc_html__('Reset your consents','cookies-with-ease').'</a>';
	  	}
	}

}
