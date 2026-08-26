<?php
/**
 * Front-end output.
 *
 * @package BraneCrowdChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Puts the chat loader on the front end of the site.
 *
 * The loader is enqueued as a script rather than echoed into the footer, so it
 * behaves like every other script on the site: caching and optimisation plugins
 * can see it, other code can dequeue it, and its position in the page is
 * WordPress's business rather than ours.
 *
 * The chat itself is NOT downloaded on page load. The loader draws a button and
 * waits; the chat arrives when a visitor presses it. That is worth knowing
 * before judging the plugin on page weight.
 */
class Brane_Crowd_Chat_Embed {

	const HANDLE = 'brane-crowd-chat';

	/**
	 * Hook up, but only when there is something to show.
	 */
	public static function register() {
		if ( ! Brane_Crowd_Chat_Settings::is_active() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'script_loader_tag', array( __CLASS__, 'add_attributes' ), 10, 2 );
	}

	/**
	 * Enqueue the loader.
	 *
	 * No version query string: the address is fixed and the file is versioned by
	 * whoever serves it, so appending one would only fragment caching.
	 */
	public static function enqueue() {
		/**
		 * Filter whether the chat appears on the current request.
		 *
		 * Gives a site a way to keep the chat off particular pages without
		 * needing a setting for every possible rule.
		 *
		 * @param bool $show Whether to output the loader.
		 */
		if ( ! apply_filters( 'brane_crowd_chat_show', true ) ) {
			return;
		}

		wp_enqueue_script(
			self::HANDLE,
			BRANE_CROWD_CHAT_EMBED_ORIGIN . '/embed.js',
			array(),
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			true
		);
	}

	/**
	 * Add the configuration to the script tag.
	 *
	 * Done through `script_loader_tag` rather than by writing the tag ourselves
	 * because WordPress owns the tag. `defer` is added here too: it matters
	 * enough to be explicit rather than left to the enqueue strategy, since a
	 * loader that blocks the parser is the thing that gets a plugin uninstalled.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public static function add_attributes( $tag, $handle ) {
		if ( self::HANDLE !== $handle ) {
			return $tag;
		}

		$attributes = '';
		foreach ( self::attributes() as $name => $value ) {
			$attributes .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		// Inserted before the closing bracket of the opening tag, so whatever
		// else WordPress or another plugin put there is preserved.
		return preg_replace( '/(<script\b[^>]*)(>)/', '$1' . $attributes . ' defer$2', $tag, 1 );
	}

	/**
	 * The data attributes for the current settings.
	 *
	 * Only non-empty values are included: an empty attribute is not the same as
	 * an absent one to the loader, and absent means "use your own default",
	 * which is what an untouched field should mean.
	 *
	 * @return array<string,string>
	 */
	public static function attributes() {
		$settings = Brane_Crowd_Chat_Settings::get();

		$attributes = array( 'data-crowd' => $settings['crowd'] );

		foreach ( array(
			'data-theme'     => $settings['theme'],
			'data-primary'   => $settings['primary'],
			'data-secondary' => $settings['secondary'],
			'data-label'     => $settings['label'],
			'data-position'  => $settings['position'],
			'data-welcome'   => $settings['welcome'],
		) as $name => $value ) {
			if ( '' !== $value && null !== $value ) {
				$attributes[ $name ] = $value;
			}
		}

		return $attributes;
	}

	/**
	 * The tag as text, for showing somebody what the plugin is putting on their
	 * page. Read by the settings screen; never used to output anything.
	 *
	 * @return string
	 */
	public static function preview_snippet() {
		$parts = array( sprintf( 'src="%s/embed.js"', BRANE_CROWD_CHAT_EMBED_ORIGIN ) );
		foreach ( self::attributes() as $name => $value ) {
			$parts[] = sprintf( '%s="%s"', $name, $value );
		}
		$parts[] = 'defer';

		return '<script ' . implode( ' ', $parts ) . '></script>';
	}
}
