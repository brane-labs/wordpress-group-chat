<?php
/**
 * Plugin Name:       WP Group Chat
 * Plugin URI:        https://brane.app/wordpress-group-chat
 * Description:       Add a group chat to your site so visitors talk to each other and keep coming back. Install, set up, save.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Brane Labs
 * Author URI:        https://brane.app/wordpress-group-chat
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       wp-group-chat
 *
 * @package WPGroupChat
 */

// No direct access. Every file in this plugin repeats this: a web server
// misconfigured to serve PHP source, or to execute a file outside WordPress,
// is not a hypothetical.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGC_VERSION', '1.0.0' );
define( 'WPGC_FILE', __FILE__ );
define( 'WPGC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPGC_URL', plugin_dir_url( __FILE__ ) );

/**
 * The single option row everything is stored in.
 *
 * One row rather than an option per field, so a read is one query and a save is
 * atomic. WordPress sanitises it through a single callback on the way in.
 */
define( 'WPGC_OPTION', 'wpgc_settings' );

/**
 * Where the loader is served from.
 *
 * A constant rather than a setting on purpose. It is one fixed public address,
 * and a text field for it would only ever be a way for a site to end up loading
 * something else. A developer working against a different deployment can define
 * WPGC_EMBED_ORIGIN in wp-config.php; nothing in the admin UI can
 * change it.
 */
if ( ! defined( 'WPGC_EMBED_ORIGIN' ) ) {
	define( 'WPGC_EMBED_ORIGIN', 'https://embed.brane.chat' );
}

require_once WPGC_DIR . 'includes/class-wpgc-settings.php';
require_once WPGC_DIR . 'includes/class-wpgc-admin.php';
require_once WPGC_DIR . 'includes/class-wpgc-embed.php';

/**
 * Boot the plugin.
 */
function wpgc_init() {
	WPGC_Settings::register();

	if ( is_admin() ) {
		WPGC_Admin::register();
	} else {
		WPGC_Embed::register();
	}
}
add_action( 'plugins_loaded', 'wpgc_init' );

/**
 * A direct link to the settings screen from the plugins list.
 */
function wpgc_action_links( $links ) {
	$settings = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=wp-group-chat' ) ),
		esc_html__( 'Settings', 'wp-group-chat' )
	);
	array_unshift( $links, $settings );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpgc_action_links' );
