# Roadmap

## 1.0.0 — shipped

A form instead of a script tag. Crowd ID, theme, two colours, button position
and label, welcome line. Nothing appears on a site until it is explicitly
switched on.

## 1.1.0 — paid rebranding

Removing the Brane branding from the chat panel, for Crowds on a plan that
includes it.

### What "branding" means, concretely

Three separate things, and they need separating because they are not equally
easy to change:

| Where | What it is | Already controllable? |
| --- | --- | --- |
| Sign-in screen | The default welcome line naming Brane | **Yes** — the `Welcome line` field already replaces it |
| Chat panel | A "powered by" credit in the panel's furniture | No |
| Loading screen | The same credit while the chat starts | No |

So a site owner can already replace the one piece of wording that mentions us,
and 1.1.0 is really about the other two.

### ⚠️ The constraint that decides the design

**This cannot be a plugin setting.** A checkbox in WordPress is a checkbox
anybody can tick, and the plugin's source is public — so a setting would make
the paid feature free for anyone who reads this file. It would also be
unenforceable on the other install paths, since the same chat is embedded by a
plain script tag on sites with no WordPress at all.

Whether branding is hidden therefore has to be **decided by the service, for the
Crowd**, and the embed has to obtain that answer as part of loading. The plugin's
role is to say which Crowd it is, which it already does.

That has a useful consequence: 1.1.0 needs **little or no change to this
plugin**. If entitlement travels with the Crowd, an eligible site gets an
unbranded panel from the same script tag it already has, with nothing to
configure and nothing to keep in sync.

### Work, roughly in order

1. **Make the credit conditional in the chat.** Today it is unconditional in the
   panel and the loading screen. It needs to read a flag rather than a constant.
2. **Decide where entitlement lives** and how the chat learns it. It belongs
   with the Crowd, and it must be resolved server-side. A value the host page
   can set is not entitlement.
3. **Make it observable to the Crowd's admin.** Somebody paying for this needs
   to see that it is on, and see it turn off when a plan lapses.
4. **Then, optionally, surface it in this plugin** — a read-only line on the
   settings screen saying whether branding is hidden for this Crowd. Read-only
   is the point: the plugin reports the state, it never sets it.

### Deliberately not in 1.1.0

- **Replacing our branding with the site's own.** A different feature, and a
  larger one: it needs somewhere to put a logo, and rules about what may go in
  the panel. Hiding is a checkbox; substituting is a design.
- **Any client-side entitlement check.** Anything the browser can decide, the
  browser's owner can change.

## Later, unscheduled

- **Per-page control.** 1.0.0 is site-wide, with a `wpgc_show`
  filter for anyone comfortable writing one line of PHP. A visual page/post
  picker is the obvious next step if that filter turns out to be the common
  request rather than the rare one.
- **A block and a shortcode**, for placing the chat inline in a page rather than
  as a floating button.
- **Multiple Crowds on one site**, chosen per page. Worth doing only if asked
  for: the loader deliberately allows one launcher per page, so this is a real
  design change rather than a settings change.
