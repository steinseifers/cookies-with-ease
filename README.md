# Stein's Cookies with Ease

A free, open-source, self-hosted cookie consent manager for WordPress. No external consent cloud, no tracking of your visitors by a third-party vendor — everything runs on your own WordPress installation.

Stein's Cookies with Ease is a free & open-source tool that provides an intuitive, user-friendly way to manage consent on your website. It is easy to use and configure, lightweight, and compatible with all modern browsers — simple, unobtrusive, and optimized for mobile as well as desktop.

Stein's Cookies with Ease can manage both inline and external scripts as well as static tracking elements like images or stylesheet links.

> This project is a security-hardened, modernized fork of the abandoned [Klaro Consent Manager](https://wordpress.org/plugins/klaro-consent-manager/) plugin (v1.1.7). See [Credits](#credits) below.

## Is this the right tool for you?

Stein's Cookies with Ease is **not** an automatic, scan-your-whole-site-and-block-everything solution like Complianz, Cookiebot, or CookieYes. It does not detect third-party scripts on its own and does not ship built-in integrations for specific plugins (Google Analytics, ad networks, page builders, …).

Instead, it gives you the building blocks (a lightweight, self-hosted consent banner plus the `type="text/plain"` mechanism described below) and expects **you** to configure it: for every script, embed, or tracking pixel you want to gate behind consent, you manually create an Application in the admin and rewrite that script's markup to use `type="text/plain"`. A plugin or embed only becomes "compatible" if its output can be adjusted this way — there is no automatic compatibility layer with other plugins.

If you want a fully automatic, hands-off compliance suite, look at Complianz or a similar service instead. If you're comfortable with a bit of manual, one-time setup per script in exchange for a small, self-hosted plugin with no external dependencies, Stein's Cookies with Ease is for you.

## Features

- A tool to help you implement opt-in consent handling in line with GDPR/ePrivacy requirements (no legal guarantee, see [Important](#important))
- Customizable cookie message using `.po` file (multi-language support)
- Redirects users to a modal for more cookie information and to toggle individual scripts
- Custom link to your Privacy Policy page
- Option to refuse deletion of functional cookies
- Option to review and edit user consent
- Option to reset the user consent
- Style the consent banner with your own CSS (Klaro exposes CSS custom properties for theming), or pick one of three built-in color schemes (Light, Dark, Dark Blue) and optionally hide the cookie graphic — no CSS required
- Optimized for mobile as well as desktop browsers
- Can manage: inline and external scripts, images, stylesheets, links
- Allow users to have control of what scripts are loaded
- Shortcode to review and reset consent from the privacy policy page or any post/page
- Control which page types load the consent banner via the `cookieswithease_disable_klaro` filter (front page, blog, pages, tags, categories, posts, archives, search, 404) — no settings-page checkboxes, just a filter in your own code
- Ships with draft Application templates for common services (Google Analytics, Google Tag Manager, Facebook Pixel, YouTube, Vimeo, Google Maps) so you don't have to start from a blank form — created as **drafts** on activation, reviewed and published by you, never enabled automatically. For Google Analytics, Google Tag Manager, and Facebook Pixel, the draft already contains the ready-to-use `type="text/plain"` code with the standard integration pattern — you only need to drop in your own ID
- Optional auto-gating for YouTube and Vimeo embeds — turn on "Auto-gate video embeds" in Settings and pasted URLs or the embed blocks are gated behind consent automatically, no manual `type="text/plain"` markup needed

## Installation

1. Download or clone this repository into `wp-content/plugins/stein-cookies-with-ease/`, then activate it like any other plugin.
2. Go to **Stein's Cookies with Ease → Settings** and change the settings to suit your needs.
3. Go to **Stein's Cookies with Ease → Applications** — you'll find a handful of draft entries for common services already there (Google Analytics, Google Tag Manager, Facebook Pixel, YouTube, Vimeo, Google Maps). Open the ones you actually use, review the purpose and settings, and publish them; delete the ones you don't need. For anything else, create a new Application, enter a name and description, set the purpose (e.g. "Analytics"), and publish.
4. Scroll to the bottom of the newly created Application and copy the generated code. Replace your inline script's `<script>` tag with it, leaving external script tags as they are. See [Example: Google Analytics 4](#example-google-analytics-4) below.
5. Optionally add `[cookieswithease type="review"]` or `[cookieswithease type="reset"]` to your privacy policy page (or any other page) so visitors can review or reset their consent later.

## Example: Google Analytics 4

The code generated for an Application looks something like this:

```html
<script type="text/plain" data-type="application/javascript" data-name="google-analytics">
// Your Google Analytics code, all except the script tags, which we replace with this one…
</script>
```

Leave your external script tag as-is and replace only the inline script with the snippet above, so you end up with something like this (replace `G-XXXXXXXXXX` with your own measurement ID):

```html
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script type="text/plain" data-type="application/javascript" data-name="google-analytics">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>
```

Note that the external `gtag.js` loader in this example is not gated by consent on its own; if you need it blocked until consent is given as well, gate it too, using `type="text/plain"` with `data-type="application/javascript"` and `data-src` instead of `src`.

## How it works

Stein's Cookies with Ease is built on top of [Klaro!](https://github.com/kiprotect/klaro), the well-tested, BSD-3-Clause-licensed consent management JavaScript library. The plugin provides the WordPress-native admin UI (settings, Applications as posts, metaboxes) around it and generates the Klaro configuration for your site automatically.

Each Application you create becomes one entry in the consent banner, grouped by purpose, so visitors can give or withhold consent per service rather than an all-or-nothing choice.

There is no server-side output buffering or script rewriting involved — the blocking happens entirely in the browser, using a standard HTML behavior:

1. You mark up the script you want to gate with `type="text/plain"` instead of its real type (e.g. `<script type="text/plain" data-type="application/javascript" data-name="google-analytics">…</script>`). Browsers never execute a `<script>` tag with an unrecognized `type`, so it simply sits inert in the page.
2. On page load, the bundled Klaro library scans the DOM for elements carrying `data-name="<application-slug>"`, matching them against the Applications you defined in the admin.
3. Once a visitor consents to that Application, Klaro builds a new `<script>` element, copies over the `data-*` attributes, and sets its `type`/`src` to the real values from `data-type`/`data-src` — then swaps it in for the placeholder. Only at that point does the browser actually run the script. The same swap happens for `src`/`href` on `<img>`/`<link>` elements.
4. WordPress's only role in this is building the list of Applications (name, purpose, default state, …) via `WP_Query` and handing it to the front end as the Klaro configuration through `wp_localize_script()` — the actual gating logic all runs client-side after that.

## One Application per service, not per page

Some services you gate run on every single page (Google Analytics, Google Tag Manager) — one Application, one snippet in `wp_footer`, done. Others — a YouTube video, a Google Maps embed — only show up on the handful of pages that actually embed them. That doesn't change how many Applications you need: it's still **one published Application per service**, not per page or per embed.

The Application is what defines the consent *purpose* Klaro shows in the banner/modal (title, description, category), and the visitor's choice is remembered once, site-wide, in a single cookie. Accept YouTube on one page and you won't be asked again on another page that also embeds a YouTube video.

### YouTube and Vimeo: auto-gated, no manual markup

For YouTube and Vimeo specifically, you don't need to touch page content by hand at all:

1. Publish the matching draft Application ("YouTube" / "Vimeo", both seeded on activation).
2. Turn on **Auto-gate video embeds** in Stein's Cookies with Ease → Settings.
3. Paste video URLs or use the embed blocks as usual. The plugin hooks into WordPress's own `embed_oembed_html` filter and rewrites the resulting `<iframe>` to `type="text/plain"` behind the scenes, for every page, automatically — including embeds you already added before turning the setting on.

If the matching Application isn't published, or the setting is off, embeds are left completely untouched (normal WordPress behavior) — nothing breaks, it just isn't gated.

### Everything else without native WordPress oEmbed support (Google Maps, etc.)

WordPress only auto-embeds URLs from providers with a registered oEmbed handler (YouTube, Vimeo, Twitter, …). Google Maps and most other embeds don't have one, so there's no filter to hook into automatically. For those, publish the matching Application (Google Maps is seeded as a draft too) and paste its `type="text/plain"` snippet into a Custom HTML block, same as for scripts.

## Screenshots

1. Cookie consent banner on the front end.
2. Plugin settings page in the WordPress admin.

## Security

This fork includes a number of hardening fixes over the original plugin, most notably:

- CSRF protection (nonce + capability check) on the settings reset action, replacing an unauthenticated global handler
- Output escaping on all dynamically generated front-end styles
- Color fields sanitized with `sanitize_hex_color()` instead of generic text sanitization
- PHP 8 compatibility fixes (safe array access, modern WordPress APIs)
- The raw integration code of an Application can only be edited and is only output if its author has the `unfiltered_html` capability (not the case for administrators on multisite or with `DISALLOW_UNFILTERED_HTML`)

On uninstall (deleting the plugin via the Plugins screen), the plugin removes its settings, all Applications (including drafts) and its transients. Export your Applications first if you want to keep them.

If you find a security issue, please open a private security advisory on this repository rather than a public issue.

## External services

Stein's Cookies with Ease itself does not connect to any external service and does not send any data anywhere: it makes no remote requests (no phone-home, no update checks, no telemetry), and the consent banner, its scripts and its styles are all served from your own site. The only external resources that can ever be loaded are the third-party services you choose to gate behind consent.

For convenience, the plugin ships draft Application templates for a few common services (they are only examples). A template does nothing until you publish it and replace its placeholder ID (measurement ID, container ID, pixel ID, embed parameters) with your own. Once you have done so, the respective service is contacted from your visitors' browsers only AFTER the visitor has given consent for that Application. Before consent (or after it is withdrawn), the code stays inert (`type="text/plain"`) and no request is made. When a request is made, the service receives the data a browser always transmits (such as the visitor's IP address, browser and device information, the referrer and the page being visited), plus whatever the service itself collects and sets via cookies. You, as the site owner, are responsible for choosing which services to enable and for informing your visitors accordingly.

### Google Tag Manager

Tag management service by Google that loads further marketing and analytics scripts configured in your own container. Template: "Google Tag Manager". After consent, the browser loads `https://www.googletagmanager.com/gtm.js` and sends the container ID, IP address, browser and device information and the visited page to Google; the tags in your container may transmit further data.

- Terms of use: <https://marketingplatform.google.com/about/analytics/tag-manager/use-policy/>
- Google Terms of Service: <https://policies.google.com/terms>
- Privacy Policy: <https://policies.google.com/privacy>

### Google Analytics (gtag.js)

Web analytics service by Google. Template: "Google Analytics". After consent, the browser loads `https://www.googletagmanager.com/gtag/js` and sends your measurement ID, IP address, browser and device information, the visited page and visitor interactions (e.g. page views) to Google Analytics.

- Terms of Service: <https://marketingplatform.google.com/about/analytics/terms/de/>
- Google Terms of Service: <https://policies.google.com/terms>
- Privacy Policy: <https://policies.google.com/privacy>

### Meta (Facebook) Pixel

Analytics and advertising measurement tool by Meta Platforms. Template: "Facebook Pixel". After consent, the browser loads `https://connect.facebook.net/en_US/fbevents.js` and sends your pixel ID, IP address, browser and device information, the visited page and the events you configure (e.g. page views) to Meta.

- Terms of Service: <https://www.facebook.com/legal/terms>
- Meta Business Tools Terms: <https://www.facebook.com/legal/technology_terms>
- Privacy Policy: <https://www.facebook.com/privacy/policy/>

### Google Maps (Embed)

Map embed by Google. Template: "Google Maps" (an iframe pointing to `https://www.google.com/maps/embed`). After consent, the iframe is loaded and the browser sends the IP address, browser and device information and the embedding page to Google; Google may set cookies.

- Google Maps Terms: <https://www.google.com/intl/en/help/terms_maps/>
- Google Terms of Service: <https://policies.google.com/terms>
- Privacy Policy: <https://policies.google.com/privacy>

### YouTube and Vimeo (optional auto-gating)

If you enable "Auto-gate video embeds", embedded YouTube and Vimeo videos are replaced by a consent placeholder and only loaded from youtube.com / vimeo.com after consent, at which point the provider receives the IP address, browser and device information and the embedding page.

- YouTube Terms of Service: <https://www.youtube.com/t/terms> - Google Privacy Policy: <https://policies.google.com/privacy>
- Vimeo Terms of Service: <https://vimeo.com/terms> - Vimeo Privacy Policy: <https://vimeo.com/privacy>

## Important

Stein's Cookies with Ease is a tool to help you implement consent handling. It is not legal advice and does not make your website GDPR-compliant by itself. Activating this plugin does not guarantee that your website is successfully meeting its responsibilities and obligations under GDPR. Individual organizations should assess their unique responsibilities and ensure extra measures are taken to meet any obligations required by law, based on a data protection impact assessment (DPIA).

## Credits

Stein's Cookies with Ease is a fork of **Klaro Consent Manager** by m.r.d.a, originally published on WordPress.org (version 1.1.7).

It bundles the **Klaro!** consent management JavaScript library, version 0.7.21, from [kiprotect/klaro](https://github.com/kiprotect/klaro) (unmodified minified build, `js/cookieswithease-klaro.min.js`; the matching source is available at [github.com/kiprotect/klaro](https://github.com/kiprotect/klaro) under the release tag `v0.7.21`), licensed under BSD-3-Clause. See [`licenses/BSD-3-CLAUSE-klaro.txt`](licenses/BSD-3-CLAUSE-klaro.txt) for the full license text and copyright notice.

## License

Stein's Cookies with Ease itself is licensed under [GPLv2 or later](LICENSE). The bundled Klaro! library retains its original BSD-3-Clause license (see above).
