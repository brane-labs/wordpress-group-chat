<?php
/**
 * Plugin Name:       Brane Crowd Chat
 * Plugin URI:        https://brane.app/embed/
 * Description:       Add your Brane Crowd to your site as a chat button. Fill in the form, save, done.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Brane
 * Author URI:        https://brane.app/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       brane-crowd-chat
 * Domain Path:       /languages
 *
 * @package BraneCrowdChat
 */

// No direct access. Every file in this plugin repeats this: a web server
// misconfigured to serve PHP source, or to execute a file outside WordPress,
// is not a hypothetical.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BRANE_CROWD_CHAT_VERSION', '1.0.0' );
define( 'BRANE_CROWD_CHAT_FILE', __FILE__ );
define( 'BRANE_CROWD_CHAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRANE_CROWD_CHAT_URL', plugin_dir_url( __FILE__ ) );

/**
 * The single option row everything is stored in.
 *
 * One row rather than an option per field, so a read is one query and a save is
 * atomic. WordPress sanitises it through a single callback on the way in.
 */
define( 'BRANE_CROWD_CHAT_OPTION', 'brane_crowd_chat_settings' );

/**
 * Where the loader is served from.
 *
 * A constant rather than a setting on purpose. It is one fixed public address,
 * and a text field for it would only ever be a way for a site to end up loading
 * something else. A developer working against a different deployment can define
 * BRANE_CROWD_CHAT_EMBED_ORIGIN in wp-config.php; nothing in the admin UI can
 * change it.
 */
if ( ! defined( 'BRANE_CROWD_CHAT_EMBED_ORIGIN' ) ) {
	define( 'BRANE_CROWD_CHAT_EMBED_ORIGIN', 'https://embed.brane.chat' );
}

require_once BRANE_CROWD_CHAT_DIR . 'includes/class-brane-crowd-chat-settings.php';
require_once BRANE_CROWD_CHAT_DIR . 'includes/class-brane-crowd-chat-admin.php';
require_once BRANE_CROWD_CHAT_DIR . 'includes/class-brane-crowd-chat-embed.php';

/**
 * Boot the plugin.
 */
function brane_crowd_chat_init() {
	Brane_Crowd_Chat_Settings::register();

	if ( is_admin() ) {
		Brane_Crowd_Chat_Admin::register();
	} else {
		Brane_Crowd_Chat_Embed::register();
	}
}
add_action( 'plugins_loaded', 'brane_crowd_chat_init' );

/**
 * Load translations.
 */
function brane_crowd_chat_load_textdomain() {
	load_plugin_textdomain( 'brane-crowd-chat', false, dirname( plugin_basename( BRANE_CROWD_CHAT_FILE ) ) . '/languages' );
}
add_action( 'init', 'brane_crowd_chat_load_textdomain' );

/**
 * A direct link to the settings screen from the plugins list.
 */
function brane_crowd_chat_action_links( $links ) {
	$settings = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=brane-crowd-chat' ) ),
		esc_html__( 'Settings', 'brane-crowd-chat' )
	);
	array_unshift( $links, $settings );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'brane_crowd_chat_action_links' );
