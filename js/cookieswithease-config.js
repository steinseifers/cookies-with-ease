// Everything below runs inside an IIFE so that no generic names leak into the
// global scope. The only globals are window.cookieswithease_config (the data
// handed over from PHP, see wp_localize_script() in includes/class-frontend.php),
// window.CookiesWithEase (helpers) and window.klaroConfig, whose name is
// expected by the bundled Klaro library (it looks up the config by this name).
( function () {

var klaroID = cookieswithease_config.klaroID,
	klaroPrivacyLink = cookieswithease_config.klaroPrivacyLink,
	klaroCookieExpires = cookieswithease_config.klaroCookieExpires,
	allAppsJson = cookieswithease_config.allAppsJson,
	mustConsent = cookieswithease_config.mustConsent,
    consentNoticeDescription = cookieswithease_config.consentNoticeDescription,
	consentNoticelearnMore = cookieswithease_config.consentNoticelearnMore,
	consentNoticechangeDescription = cookieswithease_config.consentNoticechangeDescription,
	consentModalDescription = cookieswithease_config.consentModalDescription,
	consentModalTitle = cookieswithease_config.consentModalTitle,
	consentModalPrivacyPolicyText = cookieswithease_config.consentModalPrivacyPolicyText,
	consentModalPrivacyPolicyName = cookieswithease_config.consentModalPrivacyPolicyName,
	klaroOK = cookieswithease_config.klaroOK,
	klaroDecline = cookieswithease_config.klaroDecline,
	klaroSave = cookieswithease_config.klaroSave,
	klaroClose = cookieswithease_config.klaroClose,
	klaroAcceptAll = cookieswithease_config.klaroAcceptAll,
	klaroAcceptSelected = cookieswithease_config.klaroAcceptSelected,
	appPurpose = cookieswithease_config.appPurpose,
	appPurposes = cookieswithease_config.appPurposes,
	appDisableAllTitle = cookieswithease_config.appDisableAllTitle,
	appDisableAllDescription = cookieswithease_config.appDisableAllDescription,
	appOptOut = cookieswithease_config.appOptOut,
	appOptOutDescription = cookieswithease_config.appOptOutDescription,
	appRequired = cookieswithease_config.appRequired,
	appRequiredDescription = cookieswithease_config.appRequiredDescription,
	purposesAnalytics = cookieswithease_config.purposesAnalytics,
	purposesSecurity = cookieswithease_config.purposesSecurity,
	purposesAdvertising = cookieswithease_config.purposesAdvertising,
	purposesStyling = cookieswithease_config.purposesStyling,
	purposesStatistics = cookieswithease_config.purposesStatistics,
	purposesFunctionality = cookieswithease_config.purposesFunctionality,
	purposesOther = cookieswithease_config.purposesOther,
	contextualConsentDescription = cookieswithease_config.contextualConsentDescription,
	contextualConsentAccept = cookieswithease_config.contextualConsentAccept;

// Klaro 0.7.21 nests all translation strings per language and renamed the
// "app" section to "service" (matching the "apps" -> "services" rename in
// the config below). We keep feeding it our own PHP-translated strings
// (WordPress gettext already produced the right language), just reshaped
// into the schema this Klaro version expects. lang stays 'en' further down
// so Klaro always looks up strings under this "en" object rather than
// trying to load its own bundled translations for the site's language.
function klaroTranslations() {

	return {
		en: {
			privacyPolicy: {
				text: consentModalPrivacyPolicyText,
				name: consentModalPrivacyPolicyName,
			},
			consentModal: {
				title: consentModalTitle,
				description: consentModalDescription,
			},
			consentNotice: {
				title: consentModalTitle,
				description: consentNoticeDescription,
				changeDescription: consentNoticechangeDescription,
				learnMore: consentNoticelearnMore,
			},
			ok: klaroOK,
			decline: klaroDecline,
			save: klaroSave,
			close: klaroClose,
			acceptAll: klaroAcceptAll,
			acceptSelected: klaroAcceptSelected,
			service: {
				disableAll: {
					title: appDisableAllTitle,
					description: appDisableAllDescription,
				},
				optOut: {
					title: appOptOut,
					description: appOptOutDescription,
				},
				required: {
					title: appRequired,
					description: appRequiredDescription,
				},
				purposes: appPurposes,
				purpose: appPurpose,
			},
			purposes: {
				analytics: { title: purposesAnalytics },
				security: { title: purposesSecurity },
				advertising: { title: purposesAdvertising },
				styling: { title: purposesStyling },
				statistics: { title: purposesStatistics },
				functionality: { title: purposesFunctionality },
				other: { title: purposesOther },
			},
			// Per-service title override. Without this, Klaro's per-embed
			// consent notice (the "do you want to load external content
			// from {title}?" placeholder) derives the displayed name from
			// the service's slug itself (capitalizing only the first
			// letter, e.g. "youtube" -> "Youtube") instead of using the
			// real Application title ("YouTube") we already have.
			...Object.fromEntries(
				allAppsJson.map(function (app) {
					return [app.name, { title: app.title }];
				})
			),
			// Klaro shows two buttons here by default: a temporary "accept
			// once" one (always available) and a persistent "accept always"
			// one (only once the visitor has been through the main consent
			// notice/modal at least once). Giving both the same label means
			// visitors only ever read one action - css/klaro-custom.css
			// hides the temporary button via CSS whenever the persistent
			// one is also present, so only the persistent one remains.
			contextualConsent: {
				description: contextualConsentDescription,
				acceptOnce: contextualConsentAccept,
				acceptAlways: contextualConsentAccept,
			},
		},
	};
}

var klaroConfig = window.klaroConfig = {

	version: 1,
    elementID: klaroID,
    cookieName: klaroID,
    cookieExpiresAfterDays: klaroCookieExpires,
	// DEFINE LANG AS EN SO LET IT ALLWAYS THINKS ITS ENGLISH, TRANSLATION IS ALREADY SERVED ANYWAY USING PLUGIN PO AND MO FILES
	lang: 'en',
    privacyPolicy: klaroPrivacyLink,
    mustConsent: mustConsent,
    translations: klaroTranslations(),
	// New in Klaro 0.7.x: without this, the small consent notice's "ok"
	// button only accepted required/default-true services, which looked
	// broken (clicking "ok" silently did nothing for opt-in-only services
	// like Google Analytics). acceptAll gives it a real, distinct "accept
	// all" action alongside "decline" - see the discussion that led here.
	acceptAll: true,
	hideDeclineAll: false,
	// Show the initial consent notice as a centered, page-overlaying modal
	// instead of Klaro's default small corner box - avoids colliding with
	// other fixed-position UI (e.g. a theme's scroll-to-top button).
	noticeAsModal: true,
	// Klaro 0.7.x renamed "apps" to "services".
	services: allAppsJson,
	// Klaro's own "Powered by Klaro!" attribution is not rendered.
	disablePoweredBy: true,

};

// Helper functions are encapsulated in their own namespace to avoid unnecessarily
// polluting the global scope. window.klaroConfig remains deliberately global,
// as it is expected by the bundled Klaro library.
window.CookiesWithEase = window.CookiesWithEase || {};

window.CookiesWithEase.deleteACookie = function () {
    var cookies = document.cookie.split("; ");
    for (var c = 0; c < cookies.length; c++) {
        var d = window.location.hostname.split(".");
        while (d.length > 0) {
            var cookieBase = encodeURIComponent(cookies[c].split(";")[0].split("=")[0]) + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; domain=' + d.join('.') + ' ;path=';
            var p = location.pathname.split('/');
            document.cookie = cookieBase + '/';
            while (p.length > 0) {
                document.cookie = cookieBase + p.join('/');
                p.pop();
            }
            d.shift();
        }
    }
};

var deleteACookie = window.CookiesWithEase.deleteACookie;

// Reset-Link-Handler: laeuft jeden Schritt einzeln in try/catch, damit ein Fehler
// in einem Schritt (z.B. klaro.getManager()) nicht die folgenden Schritte
// verhindert - insbesondere den Reload, der vorher bei einem Fehler ausblieb
// und den Link stattdessen nur zu href="#" springen liess.
//
// Cookies wie Google Analytics' _ga_* koennen trotz sofortigem deleteACookie()
// ueberleben: das Drittanbieter-Skript ist auf der aktuellen Seite noch aktiv
// und schreibt seinen Cookie oft ueber einen eigenen unload/visibilitychange-
// Handler noch einmal, genau waehrend location.reload() navigiert - also NACH
// unserem Loeschversuch. Da nach dem Reset kein Consent mehr vorliegt, laedt
// das Drittanbieter-Skript auf der neu geladenen Seite gar nicht erst, daher
// reicht ein zweiter, verzoegerter Aufraeum-Durchlauf dort zuverlaessig aus.
window.CookiesWithEase.resetConsent = function () {
	try {
		deleteACookie();
	} catch (e) {}
	try {
		klaro.getManager().resetConsent();
	} catch (e) {}
	try {
		sessionStorage.setItem( 'cookieswithease_reset_pending', '1' );
	} catch (e) {}
	location.reload();
	return false;
};

// Zweiter Aufraeum-Durchlauf nach einem Reset (siehe Kommentar oben).
(function () {
	try {
		if ( sessionStorage.getItem( 'cookieswithease_reset_pending' ) === '1' ) {
			sessionStorage.removeItem( 'cookieswithease_reset_pending' );
			deleteACookie();
		}
	} catch (e) {}
})();

} )();
