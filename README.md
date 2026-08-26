# wordpress-group-chat
Embed a free group chat for all of your visitors to engage in, and grow your community and retention.

## What it does

Fill in your Crowd ID, pick a theme and colours, save. A chat button appears in
the corner of every page. Visitors sign in with a six digit email code without
leaving the site.

The chat is **not downloaded on page load**. The plugin adds a small loader that
draws the button and waits; the chat itself arrives only when a visitor presses
it. A visitor who never presses it downloads nothing.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later
- A Brane Crowd you administer

## Installing

Copy the plugin folder into `wp-content/plugins/`, activate it, then open
**Settings → Brane Crowd Chat**.

Nothing appears on the site until **Show the chat button on this site** is
ticked, so the settings can be filled in without a half-configured chat going
live in the meantime.

## Finding your Crowd ID

In the Brane app, open your Crowd, then the menu, then **Embed on Website**. The
ID is the `data-crowd` value in the line it shows. The same menu can email the
full instructions to the address on your own account.

You need to be an admin of the Crowd to see the option.

## Settings

| Setting | Required | Notes |
| --- | --- | --- |
| Crowd ID | Yes | Letters, numbers and dashes |
| Show the chat | — | Off by default |
| Theme | — | Light or dark. Where the chat *starts*; visitors can switch |
| Main colour | — | Hex, e.g. `#F4C32F`. Empty keeps the chat's own colours |
| Second colour | — | Unread badge and headings. Defaults to the main colour |
| Button position | — | Bottom right or bottom left |
| Button label | — | Read by screen readers. Defaults to "Open chat" |
| Welcome line | — | Shown above the sign-in field |

Colours **tint** the chosen theme rather than replacing it, so surfaces and body
text stay legible whatever colour is chosen.

## Keeping the chat off certain pages

1.0.0 is site-wide. To exclude pages, filter it:

```php
add_filter( 'brane_crowd_chat_show', function ( $show ) {
	if ( is_page( 'checkout' ) ) {
		return false;
	}
	return $show;
} );
```

## For developers

- `brane_crowd_chat_show` — filter whether the loader is output on the current
  request.
- `BRANE_CROWD_CHAT_EMBED_ORIGIN` — define in `wp-config.php` to point at a
  different deployment. Deliberately not a setting: it is one fixed public
  address, and a text field for it would only ever be a way for a site to load
  something else.

Settings are stored in a single option, `brane_crowd_chat_settings`, sanitised
through one allowlist on save. Removed on uninstall, kept on deactivate.

## Roadmap

See [`docs/ROADMAP.md`](docs/ROADMAP.md). The short version: 1.1.0 covers hiding
the Brane branding for Crowds on a plan that includes it, and that has to be
decided by the service rather than by a checkbox here.

## Licence

GPL-2.0-or-later.
