<?php

defined( 'ABSPATH' ) || exit;

/**
 * Draft Applications seeded on plugin activation, see CookiesWithEase_Activator.
 *
 * @return array Slug => title/description/purpose/snippet.
 */

return ( static function () {

	// Built from parts so the markup stays plain text in this file (not a script output).
	$st = '<' . 'script';
	$et = '</' . 'script>';

	return array(
		'google-analytics'   => array(
			'title'       => 'Google Analytics',
			'description' => __( 'Google Analytics is a web analytics service offered by Google that tracks and reports website traffic.', 'cookies-with-ease' ),
			'purpose'     => 'statistics',
			'snippet'     => "{$st} type=\"text/plain\" data-src=\"https://www.googletagmanager.com/gtag/js?id=YOUR-MEASUREMENT-ID\" data-name=\"google-analytics\">{$et}\n\n{$st} type=\"text/plain\" data-type=\"application/javascript\" data-name=\"google-analytics\">\nwindow.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', 'YOUR-MEASUREMENT-ID');\n{$et}",
		),
		'google-tag-manager' => array(
			'title'       => 'Google Tag Manager',
			'description' => __( 'Google Tag Manager is a tag management system that loads other marketing and analytics scripts on your behalf.', 'cookies-with-ease' ),
			'purpose'     => 'statistics',
			'snippet'     => "{$st} type=\"text/plain\" data-type=\"application/javascript\" data-name=\"google-tag-manager\">\n(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\nj=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n})(window,document,'script','dataLayer','YOUR-CONTAINER-ID');\n{$et}",
		),
		'facebook-pixel'     => array(
			'title'       => 'Facebook Pixel',
			'description' => __( 'Facebook Pixel is an analytics tool that measures the effectiveness of advertising by understanding the actions visitors take on your website.', 'cookies-with-ease' ),
			'purpose'     => 'advertising',
			'snippet'     => "{$st} type=\"text/plain\" data-type=\"application/javascript\" data-name=\"facebook-pixel\">\n!function(f,b,e,v,n,t,s)\n{if(f.fbq)return;n=f.fbq=function(){n.callMethod?\nn.callMethod.apply(n,arguments):n.queue.push(arguments)};\nif(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';\nn.queue=[];t=b.createElement(e);t.async=!0;\nt.src=v;s=b.getElementsByTagName(e)[0];\ns.parentNode.insertBefore(t,s)}(window, document,'script',\n'https://connect.facebook.net/en_US/fbevents.js');\nfbq('init', 'YOUR-PIXEL-ID');\nfbq('track', 'PageView');\n{$et}",
		),
		'youtube'            => array(
			'title'       => 'YouTube',
			'description' => __( 'Embedded YouTube videos can set cookies and load third-party scripts from Google once played.', 'cookies-with-ease' ),
			'purpose'     => 'functionality',
			// No snippet: unlike the script-based Applications above, YouTube
			// only shows up on specific pages, not site-wide - and with the
			// "Auto-gate video embeds" setting, it doesn't need one at all
			// (see gate_video_oembed()). A snippet here would still be
			// output on every single page via output_integration_codes(),
			// not just the pages that actually embed a video.
		),
		'google-maps'        => array(
			'title'       => 'Google Maps',
			'description' => __( 'Embedded Google Maps can set cookies and load third-party scripts from Google.', 'cookies-with-ease' ),
			'purpose'     => 'functionality',
			'snippet'     => "<iframe type=\"text/plain\" data-src=\"https://www.google.com/maps/embed?pb=YOUR-EMBED-PARAMETERS\" data-name=\"google-maps\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen loading=\"lazy\"></iframe>",
		),
		'vimeo'              => array(
			'title'       => 'Vimeo',
			'description' => __( 'Embedded Vimeo videos can set cookies and load third-party scripts once played.', 'cookies-with-ease' ),
			'purpose'     => 'functionality',
			// No snippet - see the comment on 'youtube' above, same reasoning.
		),
	);

} )();
