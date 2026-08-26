# WP Group Chat
Embed a free group chat for all of your visitors to engage in, and grow your community and retention.

## What it does

Gives your visitors somewhere to talk to each other. A chat button appears in the
corner of every page, and anyone can join without leaving the site: they sign in
with a six digit code sent to their email.

Fill in the form, save. That is the whole setup.

The chat is **not downloaded on page load**. The plugin adds a small loader that
draws the button and waits; the chat itself arrives only when a visitor presses
it. A visitor who never presses it downloads nothing.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later
- A Crowd you administer, from the Brane app

## Setting up

1. Install the Brane app on your phone, from [get.brane.app](https://get.brane.app).
2. Sign in, then create a Crowd. That Crowd is the group chat your visitors will
   see.
3. Run it from the app: photo, description, who has joined, removing anyone you
   need to. Everything about the chat is managed there, not in WordPress.
4. Copy your Crowd ID into **Settings → WP Group Chat** and save.

### Your Crowd ID

The last part of your Crowd's link. Share the Crowd from the app and you get a
link like `brane.im/c/northside-runners`, where the ID is `northside-runners`.

Pasting the whole link into the field works too: the ID is taken from the end of
it.

The app can also email you the full instructions. Open your Crowd, then the menu,
then **Embed on Website**. It only ever sends to the address on your own account,
and you need to be an admin of the Crowd.


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
add_filter( 'wpgc_show', function ( $show ) {
	if ( is_page( 'checkout' ) ) {
		return false;
	}
	return $show;
} );
```

## For developers

- `wpgc_show` — filter whether the loader is output on the current
  request.
- `WPGC_EMBED_ORIGIN` — define in `wp-config.php` to point at a
  different deployment. Deliberately not a setting: it is one fixed public
  address, and a text field for it would only ever be a way for a site to load
  something else.

Settings are stored in a single option, `wpgc_settings`, sanitised
through one allowlist on save. Removed on uninstall, kept on deactivate.

## Building a release

```bash
./bin/build-zip.sh
```

Produces `dist/wp-group-chat.zip`, containing the files in a `wp-group-chat/`
folder, with dev files excluded per `.distignore`.

⚠️ **Check that ZIP, not a GitHub source download.** WordPress derives a plugin's
slug from its directory, and the Plugin Check tool derives the expected text
domain from that slug. A "Download ZIP" gives a folder named after the branch
(`wordpress-group-chat-main`), so the checker reports a text domain mismatch on
every translated string and a trademark warning on the "slug". None of that is a
problem with the code, only with the name of the folder it was checked in.

## The Plugin Check trademark warning

The checker reports one warning that is expected and has been accepted:

> The plugin name includes a restricted term... contains the restricted term
> "wp" which cannot be used at all in your plugin name.

`TRADEMARK_SLUGS` in the checker holds both `wordpress` and `wp`, and neither
ends in `-`, which means neither may appear anywhere in a plugin name. Its source
comments the entry as `'wp', // it's allowed, but shows a warning.` So the name is
permitted and the warning is permanent.

There is no suffix escape: `FOR_USE_EXCEPTIONS` contains only `woocommerce`, so
"... for WordPress" is not an allowed pattern here and would be flagged the same
way. A name containing neither term is the only fully clean option, and that
trade was made deliberately in favour of the name.

## Roadmap

See [`docs/ROADMAP.md`](docs/ROADMAP.md). The short version: 1.1.0 covers hiding
the branding in the chat panel for Crowds on a plan that includes it, and that has to be
decided by the service rather than by a checkbox here.

## Licence

GPL-2.0-or-later.
