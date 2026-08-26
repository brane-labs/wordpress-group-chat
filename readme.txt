=== WordPress Group Chat ===
Contributors: branelabs
Tags: chat, community, group chat, live chat, messaging
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Add a group chat to your site so visitors talk to each other and keep coming back. Install, set up, save.

== Description ==

Give your visitors somewhere to talk to each other. A chat button appears in the
corner of every page, and anyone can join in without leaving your site: they sign
in with a six digit code sent to their email.

A site people talk on is a site people come back to. Install the plugin, fill in
the form, save.

**The chat is not downloaded on page load.** The plugin adds a small loader that
draws the button and waits. The chat itself arrives only when a visitor presses
it, so a visitor who never presses it downloads nothing.

= Set up in one screen =

* Crowd ID
* Light or dark theme, which visitors can then switch for themselves
* A main and a second colour, which tint the theme rather than replacing it
* Button position, bottom left or bottom right
* The button label, read out by screen readers
* A welcome line above the sign-in field

Nothing appears on your site until you tick "Show the chat button on this site",
so you can fill everything in first.

= Keeping the chat off certain pages =

Site-wide in this version. To exclude pages, use the `wpgc_show`
filter.

== Installation ==

1. Install and activate the plugin.
2. Go to Settings > WordPress Group Chat.
3. Enter your Crowd ID and tick "Show the chat button on this site".

To find your Crowd ID, open your Crowd in the Brane app, then the menu, then
"Embed on Website". You need to be an admin of the Crowd. The settings screen has
the same instructions behind "Where do I find this?".

== Frequently Asked Questions ==

= Will this slow my site down? =

The chat is not loaded until a visitor presses the button. Until then the plugin
has added a button and a stylesheet.

= Do visitors need an account? =

They sign in with a six digit code sent to their email, in the chat panel,
without leaving your page.

= Can I use my own colours? =

Yes. The colours tint the theme rather than replacing it, so text and surfaces
stay legible whatever you choose.

= Can I remove the branding from the chat? =

Not in this version. It is planned, for Crowds on a plan that includes it.

= Does it work with caching plugins? =

The loader is enqueued as a normal script, so caching and optimisation plugins
handle it like any other.

== Changelog ==

= 1.0.0 =
* First release.
