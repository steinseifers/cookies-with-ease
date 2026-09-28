// The following global variables (option) are the interface from PHP
// (see wp_localize_script() in cookies-with-ease.php) - unchanged.
var klaroID = option.klaroID,
	klaroPrivacyLink = option.klaroPrivacyLink,
	klaroCookieExpires = option.klaroCookieExpires,
	allAppsJson = option.allAppsJson,
	mustConsent = option.mustConsent,
    consentNoticeDescription = option.consentNoticeDescription,
	consentNoticelearnMore = option.consentNoticelearnMore,
	consentNoticechangeDescription = option.consentNoticechangeDescription,
	consentModalDescription = option.consentModalDescription,
	consentModalTitle = option.consentModalTitle,
	consentModalPrivacyPolicyText = option.consentModalPrivacyPolicyText,
	consentModalPrivacyPolicyName = option.consentModalPrivacyPolicyName,
	klaroPoweredBy = option.klaroPoweredBy,
	klaroOK = option.klaroOK,
	klaroDecline = option.klaroDecline,
	klaroSave = option.klaroSave,
	klaroClose = option.klaroClose,
	klaroAcceptAll = option.klaroAcceptAll,
	klaroAcceptSelected = option.klaroAcceptSelected,
	appPurpose = option.appPurpose,
	appPurposes = option.appPurposes,
	appDisableAllTitle = option.appDisableAllTitle,
	appDisableAllDescription = option.appDisableAllDescription,
	appOptOut = option.appOptOut,
	appOptOutDescription = option.appOptOutDescription,
	appRequired = option.appRequired,
	appRequiredDescription = option.appRequiredDescription,
	purposesAnalytics = option.purposesAnalytics,
	purposesSecurity = option.purposesSecurity,
	purposesAdvertising = option.purposesAdvertising,
	purposesStyling = option.purposesStyling,
	purposesStatistics = option.purposesStatistics,
	purposesFunctionality = option.purposesFunctionality,
	purposesOther = option.purposesOther,
	contextualConsentDescription = option.contextualConsentDescription,
	contextualConsentAccept = option.contextualConsentAccept;

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
			poweredBy: klaroPoweredBy,
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

var klaroConfig = {

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

};

// Helper functions are encapsulated in their own namespace to avoid unnecessarily
// polluting the global scope. klaroConfig/klarotranslations remain deliberately
// global, as they are expected by the bundled Klaro library.
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

// Rueckwaertskompatibler globaler Alias, da die Cookies with Ease-Shortcode-Ausgabe
// (Reset-Link) deleteACookie() weiterhin direkt als globale Funktion aufruft.
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
