<?php

defined( 'ABSPATH' ) || exit;

class CookiesWithEase_Embeds
{

	public function __construct()
	{

		add_filter( 'embed_oembed_html', array( $this, 'gate_video_oembed' ), 10, 4 );

	}

	/**
	 * Which oEmbed providers auto-gating recognizes, and the URL pattern
	 * used to tell them apart. The array key doubles as the Application
	 * slug (post_name / data-name) each provider is gated against.
	 */
	private $gated_oembed_providers = array(
		'youtube' => '#^https?://(www\.)?(youtube\.com|youtu\.be)/#i',
		'vimeo'   => '#^https?://(www\.)?vimeo\.com/#i',
	);

	/**
	 * Auto-gates YouTube/Vimeo embeds (block editor URL paste, the core
	 * embed blocks, and the [embed] shortcode all end up here) behind
	 * consent, without requiring the "type=text/plain" markup to be pasted
	 * into every page by hand. Opt-in via the "Auto-gate video embeds"
	 * setting, and only takes effect once a published Application for that
	 * provider exists - otherwise there's no consent purpose to gate it
	 * against, and we leave WordPress' normal embed HTML untouched.
	 */
	public function gate_video_oembed( $html, $url, $attr, $post_id ) {

		if ( empty( $html ) || false === strpos( $html, '<iframe' ) ) {
			return $html;
		}

		$options = get_option( 'cookieswithease_options' );
		if ( empty( $options['auto_gate_video_embeds'] ) ) {
			return $html;
		}

		$slug = null;
		foreach ( $this->gated_oembed_providers as $provider_slug => $pattern ) {
			if ( preg_match( $pattern, $url ) ) {
				$slug = $provider_slug;
				break;
			}
		}

		if ( null === $slug || ! $this->is_application_published( $slug ) ) {
			return $html;
		}

		return preg_replace_callback(
			'/<iframe\s+([^>]*?)src="([^"]*)"([^>]*)>/i',
			function ( $matches ) use ( $slug ) {
				// Already gated (e.g. filter running twice on cached oEmbed HTML).
				if ( false !== strpos( $matches[1] . $matches[3], 'data-name=' ) ) {
					return $matches[0];
				}
				return '<iframe ' . $matches[1] . 'type="text/plain" data-src="' . esc_url( $matches[2] ) . '" data-name="' . esc_attr( $slug ) . '"' . $matches[3] . '>';
			},
			$html
		);
	}

	/**
	 * Whether a published Application with this slug (post_name) exists.
	 * Cached per request/slug since embed_oembed_html can run repeatedly
	 * on the same page (once per embedded URL).
	 */
	private function is_application_published( $slug ) {
		static $cache = array();
		if ( isset( $cache[ $slug ] ) ) {
			return $cache[ $slug ];
		}
		$posts = get_posts( array(
			'post_type'      => 'cookieswithease_app',
			'name'           => $slug,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		$cache[ $slug ] = ! empty( $posts );
		return $cache[ $slug ];
	}

}
