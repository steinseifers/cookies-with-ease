=== Cookies with Ease ===
Contributors: steinseifer
Tags: cookies, consent, cookie, EU law, gdpr
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, self-hosted cookie consent manager based on Klaro. A tool to help you implement consent handling for GDPR/ePrivacy - not legal advice.

== Description ==
Cookies with Ease is a free & open-source tool that provides an intuitive, user-friendly way to manage consent on your website. It is easy to use and configure, lightweight, and compatible with all modern browsers - simple, unobtrusive, and optimized for mobile as well as desktop.

Cookies with Ease can manage both inline and external scripts as well as static tracking elements like images or stylesheet links.

This project is a security-hardened, modernized fork of the abandoned Klaro Consent Manager plugin (v1.1.7). See Credits below.

= Is this the right plugin for you? =
Cookies with Ease is NOT an automatic, scan-your-whole-site-and-block-everything solution like Complianz, Cookiebot, or CookieYes. It does not detect third-party scripts on its own and does not ship built-in integrations for specific plugins (Google Analytics, ad networks, page builders, ...).

Instead, it gives you the building blocks (a lightweight, self-hosted consent banner plus the `type="text/plain"` mechanism described below) and expects you to configure it: for every script, embed, or tracking pixel you want to gate behind consent, you manually create an Application in the admin and rewrite that script's markup to use `type="text/plain"`. A plugin or embed only becomes "compatible" if its output can be adjusted this way - there is no automatic compatibility layer with other plugins.

If you want a fully automatic, hands-off compliance suite, look at Complianz or a similar service instead. If you're comfortable with a bit of manual, one-time setup per script in exchange for a small, self-hosted plugin with no external dependencies, Cookies with Ease is for you.

= Features =
* A tool to help you implement opt-in consent handling in line with GDPR/ePrivacy requirements (no legal guarantee, see "Important" below)
* Customizable cookie message using .po file (multi-language support)
* Redirects users to a modal for more cookie information and to toggle individual scripts
* Custom link to your Privacy Policy page
* Option to refuse deletion of functional cookies
* Option to review and edit user consent
* Option to reset the user consent
* Style the consent banner with your own CSS (Klaro exposes CSS custom properties for theming), or pick one of three built-in color schemes (Light, Dark, Dark Blue) and optionally hide the cookie graphic - no CSS required
* Optimized for mobile as well as desktop browsers
* Can manage: inline and external scripts, images, stylesheets, links
* Allow users to have control of what scripts are loaded
* Shortcode to review and reset consent from the privacy policy page or any post/page
* Control which page types load the consent banner via the `cookieswithease_disable_klaro` filter (front page, blog, pages, tags, categories, posts, archives, search, 404) - no settings-page checkboxes, just a filter in your own code
* Ships with draft Application templates for common services (Google Analytics, Google Tag Manager, Facebook Pixel, YouTube, Vimeo, Google Maps) so you don't have to start from a blank form - created as drafts on activation, reviewed and published by you, never enabled automatically. For Google Analytics, Google Tag Manager, and Facebook Pixel, the draft already contains the ready-to-use `type="text/plain"` code with the standard integration pattern - you only need to drop in your own ID
* Optional auto-gating for YouTube and Vimeo embeds - turn on "Auto-gate video embeds" in Settings and pasted URLs or the embed blocks are gated behind consent automatically, no manual `type="text/plain"` markup needed

= Example: Google Analytics 4 =
The code generated for an Application looks something like this:

```
<script type="text/plain" data-type="application/javascript" data-name="google-analytics">
// Your Google Analytics code, all except the script tags, which we replace with this one...
</script>
```

Leave your external script tag as-is and replace only the inline script with the snippet above, so you end up with something like this (replace `G-XXXXXXXXXX` with your own measurement ID):

```
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script type="text/plain" data-type="application/javascript" data-name="google-analytics">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>
```

Note that the external `gtag.js` loader in this example is not gated by consent on its own; if you need it blocked until consent is given as well, gate it too, using `type="text/plain"` with `data-type="application/javascript"` and `data-src` instead of `src`.

= How it works =
Cookies with Ease is built on top of Klaro! (https://github.com/kiprotect/klaro), the well-tested, BSD-3-Clause-licensed consent management JavaScript library. The plugin provides the WordPress-native admin UI (settings, Applications as posts, metaboxes) around it and generates the Klaro configuration for your site automatically.

Each Application you create becomes one entry in the consent banner, grouped by purpose, so visitors can give or withhold consent per service rather than an all-or-nothing choice.

There is no server-side output buffering or script rewriting involved - the blocking happens entirely in the browser, using a standard HTML behavior:

1. You mark up the script you want to gate with `type="text/plain"` instead of its real type (e.g. `<script type="text/plain" data-type="application/javascript" data-name="google-analytics">...</script>`). Browsers never execute a `<script>` tag with an unrecognized `type`, so it simply sits inert in the page.
2. On page load, the bundled Klaro library scans the DOM for elements carrying `data-name="<application-slug>"`, matching them against the Applications you defined in the admin.
3. Once a visitor consents to that Application, Klaro builds a new `<script>` element, copies over the `data-*` attributes, and sets its `type`/`src` to the real values from `data-type`/`data-src` - then swaps it in for the placeholder. Only at that point does the browser actually run the script. The same swap happens for `src`/`href` on `<img>`/`<link>` elements.
4. WordPress's only role in this is building the list of Applications (name, purpose, default state, ...) via `WP_Query` and handing it to the front end as the Klaro configuration through `wp_localize_script()` - the actual gating logic all runs client-side after that.

= One Application per service, not per page =
Some services you gate run on every single page (Google Analytics, Google Tag Manager) - one Application, one snippet in `wp_footer`, done. Others - a YouTube video, a Google Maps embed - only show up on the handful of pages that actually embed them. That doesn't change how many Applications you need: it's still one published Application per service, not per page or per embed.

The Application is what defines the consent purpose Klaro shows in the banner/modal (title, description, category), and the visitor's choice is remembered once, site-wide, in a single cookie. Accept YouTube on one page and you won't be asked again on another page that also embeds a YouTube video.

= YouTube and Vimeo: auto-gated, no manual markup =
For YouTube and Vimeo specifically, you don't need to touch page content by hand at all:

1. Publish the matching draft Application ("YouTube" / "Vimeo", both seeded on activation).
2. Turn on "Auto-gate video embeds" in Cookies with Ease / Settings.
3. Paste video URLs or use the embed blocks as usual. The plugin hooks into WordPress's own `embed_oembed_html` filter and rewrites the resulting `<iframe>` to `type="text/plain"` behind the scenes, for every page, automatically - including embeds you already added before turning the setting on.

If the matching Application isn't published, or the setting is off, embeds are left completely untouched (normal WordPress behavior) - nothing breaks, it just isn't gated.

= Everything else without native WordPress oEmbed support (Google Maps, etc.) =
WordPress only auto-embeds URLs from providers with a registered oEmbed handler (YouTube, Vimeo, Twitter, ...). Google Maps and most other embeds don't have one, so there's no filter to hook into automatically. For those, publish the matching Application (Google Maps is seeded as a draft too) and paste its `type="text/plain"` snippet into a Custom HTML block, same as for scripts.

= Important =
Cookies with Ease is a tool to help you implement consent handling. It is not legal advice and does not make your website GDPR-compliant by itself. Activating this plugin does not guarantee that your website is successfully meeting its responsibilities and obligations under GDPR. Individual organizations should assess their unique responsibilities and ensure extra measures are taken to meet any obligations required by law, based on a data protection impact assessment (DPIA).

== Security ==
This fork includes a number of hardening fixes over the original plugin, most notably:

* CSRF protection (nonce + capability check) on the settings reset action, replacing an unauthenticated global handler
* Output escaping on all dynamically generated front-end styles
* Color fields sanitized with sanitize_hex_color() instead of generic text sanitization
* PHP 8 compatibility fixes (safe array access, modern WordPress APIs)
* The raw integration code of an Application can only be edited and is only output if its author has the `unfiltered_html` capability (not the case for administrators on multisite or with `DISALLOW_UNFILTERED_HTML`)

On uninstall (deleting the plugin via the Plugins screen), the plugin removes its settings, all Applications (including drafts) and its transients. Export your Applications first if you want to keep them.

If you find a security issue, please open a private security advisory on the GitHub repository rather than a public support topic.

== Installation ==
1. Install like any other plugin (upload the plugin folder to `wp-content/plugins/cookies-with-ease/` or install it via Plugins / Add New), then activate it.
2. Go to Cookies with Ease / Settings and change the settings to suit your needs.
3. Go to Cookies with Ease / Applications - you'll find a handful of draft entries for common services already there (Google Analytics, Google Tag Manager, Facebook Pixel, YouTube, Vimeo, Google Maps). Open the ones you actually use, review the purpose and settings, and publish them; delete the ones you don't need. For anything else, create a new Application, enter a name and description, set the purpose (e.g. "Analytics"), and publish.
4. Scroll to the bottom of the newly created Application and copy the generated code. Replace your inline script's `<script>` tag with it, leaving external script tags as they are. See "Example: Google Analytics 4" above.
5. Optionally add `[cookieswithease type="review"]` or `[cookieswithease type="reset"]` to your privacy policy page (or any other page) so visitors can review or reset their consent later.

== Screenshots ==

1. Cookie consent banner on the front end.
2. Plugin settings page in the WordPress admin.

== Credits ==
Cookies with Ease is a fork of "Klaro Consent Manager" by m.r.d.a, originally published on WordPress.org (version 1.1.7).

It bundles the Klaro! consent management JavaScript library, version 0.7.21, from kiprotect/klaro (https://github.com/kiprotect/klaro), licensed under BSD-3-Clause (unmodified minified build, `js/cookieswithease-klaro.min.js`; the matching source is available at https://github.com/kiprotect/klaro under the release tag `v0.7.21`). See `licenses/BSD-3-CLAUSE-klaro.txt` in the plugin folder for the full license text and copyright notice.

== License ==
Cookies with Ease itself is licensed under GPLv2 or later (see the LICENSE file in the plugin folder). The bundled Klaro! library retains its original BSD-3-Clause license (see above).

== Changelog ==
= 1.0.0 =
* Fork/rewrite based on Klaro Consent Manager 1.1.7: rebranding to Cookies with Ease, CSRF hardening of the settings reset (nonce + capability check), color fields switched to sanitize_hex_color(), output escaping in the frontend stylesheet, PHP 8 compatibility (isset() safeguards, wp_reset_postdata(), get_current_screen()), modernized register_setting() signature, removal of dead code and of the unfinished "cookie list" feature, added BSD-3-Clause license notice for the bundled Klaro library, added draft Application templates for common services (Google Analytics, Google Tag Manager, Facebook Pixel, YouTube, Vimeo, Google Maps) seeded on activation.
* Added translations for es_ES, fr_FR, it_IT, nl_NL, pl_PL, pt_PT, sv_SE (alongside the existing de_DE and hr_HR).
* Reset link ([cookieswithease type="reset"]) now reliably reloads the page and cleans up straggler cookies (e.g. Google Analytics' _ga_* cookie, which a third-party script can re-write during the reload itself) instead of silently doing nothing on error.
* Removed the default "Custom Fields" metabox from the Application post type (it duplicated the plugin's own metabox with unfiltered raw fields).
* Added optional auto-gating for YouTube and Vimeo embeds via the "Auto-gate video embeds" setting, hooking into WordPress' own embed_oembed_html filter - no manual type="text/plain" markup needed for these two providers.
* Styled Klaro's per-embed "do you want to load external content?" placeholder (previously unstyled) to match the video player it stands in for - dark, edge-to-edge, sized and positioned exactly like the gated iframe - and unified its two accept buttons into a single one that always remembers the choice.
* The YouTube and Vimeo draft Applications no longer come with a pre-filled Integration Code snippet (it would otherwise be output on every single page via output_integration_codes(), not just pages with an actual embed - the auto-gate setting makes it unnecessary for these two anyway). Added a warning next to the Integration Code field explaining it is always site-wide, and that situational Applications (Maps, videos, etc.) may be better left without one.
* The raw Integration Code of an Application can only be edited and is only output if its author has the `unfiltered_html` capability. Seeded draft Applications are now assigned to the activating user (or the first administrator if there is none, e.g. WP-CLI).
* Uninstalling the plugin now removes its settings, all Applications (including drafts) and its transients.
* Removed the unneeded jQuery dependency from the front end, fixed various escaping and sanitizing findings, and fixed an invalid `<select>` tag in the Application metabox.
* Moved the code from a single file into `includes/` (settings, post type, metabox, embeds, shortcodes, front end, activator) without functional changes.
* Added Croatian (hr_HR) strings that were still missing, so all nine translations are complete.
