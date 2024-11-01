=== WP PukiWiki ===
Contributors: kenwakita
Tags: formatting, post, wiki
Requires at least: 1.5
Tested up to: 2.2.2
Version: 0.2
Stable tag: trunk

WP PukiWiki allows the WordPress author to write articles using a light-weight syntax, instead of HTML.

== Description ==

PukiWiki is a PHP-based Wiki.  PukiWiki features headings, quotations, lists of various types, preformatted texts, images, footnotes, hyperlinks, smileys, and many others.

WP PukiWiki allow the WordPress author to use PukiWiki-style Wiki syntax in writing posts and pages.  The screenshots give you some idea.  Visit [**the main site**][WP PukiWiki site] for more information.

[WP PukiWiki site]: http://www.is.titech.ac.jp/~wakita/en/wp-pukiwiki/
	"WP PukiWiki main site"

== Requirements ==

* Working PukiWiki system accessible from the Internet.
* WP PukiWiki: WP PukiWiki uses PHP's Curl API.  Turn it on if this feature is missing on your site.

== Installation ==

1. Setup `PukiWiki` and make it accessible via http from your WordPress hosting server.  `PukiWiki` can be obtained from [PukiWiki official site](http://pukiwiki.sourceforge.jp/?About%20PukiWiki).  If you already have a running `PukiWiki` you can use that site.  WP PukiWiki uses a PukiWiki system to translate Wiki syntax to HTML but WP PukiWiki does not modify PukiWiki at all.  Therefore, it is safe to use your existing PukiWiki system.
1. Obtain and unzip [wp-pukiwiki.zip](http://downloads.wordpress.org/plugin/wp-pukiwiki.zip).
1. Open `wp-pukiwiki.php` with a text editor and modify the definition for `WPPW_URL` so that it points to the URL of your `PukiWiki` site.
1. Upload the `wp-pukiwiki` folder/directory to the `/wp-content/plugins/` directory of your WordPress hosting server.
1. Activate the  plugin.

== Screenshots ==

1. Make a region of your writing enclosed by pseudo pukiwiki tags (&lt;pukiwiki&gt; and &lt;/pukiwiki&gt;) and you can use PukiWiki syntax in writing your documents.
1. Given the above example filled in the WordPress textfield, produced is this page view.  Simple, isn't it?

== Frequently Asked Questions ==

You can find more information from the [**WP PukiWiki main site**](http://www.is.titech.ac.jp/~wakita/en/wp-pukiwiki/).  It offers more detailed installation instructions, [**examples**](http://www.is.titech.ac.jp/~wakita/en/2007/09/10/wp-pukiwiki-sample-page/), manual, and more.
