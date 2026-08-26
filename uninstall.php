<?php
/**
 * Removes the plugin's stored settings.
 *
 * Runs on delete, not on deactivate: deactivating is often temporary and losing
 * a configuration because somebody toggled the plugin off would be its own bug.
 *
 * @package WPGroupChat
 */

// Only ever reached by WordPress's uninstall routine.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wpgc_settings' );

// Multisite: the option lives per site, so each one has to be cleared.
if ( is_multisite() ) {
	$wpgc_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $wpgc_site_ids as $wpgc_site_id ) {
		switch_to_blog( $wpgc_site_id );
		delete_option( 'wpgc_settings' );
		restore_current_blog();
	}
}
