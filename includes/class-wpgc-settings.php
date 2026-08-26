<?php
/**
 * Settings storage, defaults and sanitising.
 *
 * @package WPGroupChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the option row: what its fields are, what they default to, and what a
 * value has to look like to be allowed in.
 *
 * Everything here is an allowlist. Each field is either matched against a fixed
 * set, or against a pattern, and anything that does not match falls back to the
 * default rather than being stored and escaped later. That ordering matters: a
 * value that never enters the database cannot be output wrongly by some future
 * template that forgets to escape.
 */
class WPGC_Settings {

	/** Themes the chat actually honours. */
	const THEMES = array( 'light', 'dark' );

	/** Which side of the page the button sits on. */
	const POSITIONS = array( 'right', 'left' );

	/** Kept short deliberately: these are a button label and one line of copy. */
	const MAX_LABEL_LENGTH   = 60;
	const MAX_WELCOME_LENGTH = 140;

	/**
	 * Register the option with WordPress.
	 *
	 * `register_setting` is what gives the form its nonce and capability check
	 * via `settings_fields()`, so the save path is WordPress's rather than ours.
	 */
	public static function register() {
		register_setting(
			'wpgc',
			WPGC_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * The shipped defaults.
	 *
	 * `enabled` is false so installing the plugin never changes a site on its
	 * own: nothing appears until somebody has entered a Crowd and chosen to turn
	 * it on.
	 */
	public static function defaults() {
		return array(
			'enabled'   => false,
			'crowd'     => '',
			'theme'     => 'light',
			'primary'   => '',
			'secondary' => '',
			'label'     => '',
			'position'  => 'right',
			'welcome'   => '',
		);
	}

	/**
	 * The current settings, with any missing field filled from the defaults.
	 *
	 * Merged rather than trusted as-is, so an option row written by an older
	 * version cannot produce an undefined-index notice after an upgrade.
	 */
	public static function get() {
		$stored = get_option( WPGC_OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Whether there is enough here to render anything.
	 */
	public static function is_active() {
		$settings = self::get();
		return ! empty( $settings['enabled'] ) && '' !== $settings['crowd'];
	}

	/**
	 * Sanitise a submitted settings array.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array Values safe to store.
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		return array(
			'enabled'   => ! empty( $input['enabled'] ),
			'crowd'     => self::sanitize_crowd( $input['crowd'] ?? '' ),
			'theme'     => self::sanitize_choice( $input['theme'] ?? '', self::THEMES, $defaults['theme'] ),
			'primary'   => self::sanitize_hex_colour( $input['primary'] ?? '' ),
			'secondary' => self::sanitize_hex_colour( $input['secondary'] ?? '' ),
			'label'     => self::sanitize_line( $input['label'] ?? '', self::MAX_LABEL_LENGTH ),
			'position'  => self::sanitize_choice( $input['position'] ?? '', self::POSITIONS, $defaults['position'] ),
			'welcome'   => self::sanitize_line( $input['welcome'] ?? '', self::MAX_WELCOME_LENGTH ),
		);
	}

	/**
	 * A Crowd identifier.
	 *
	 * ⚠️ A strict allowlist, not an escape. This value becomes a path segment in
	 * the chat's address, so it is reduced to the characters an identifier can
	 * actually contain and anything else is dropped. Somebody who pastes a whole
	 * URL, or a name with spaces and punctuation, gets the recognisable part of
	 * it rather than a stored value that has to be defended everywhere it is
	 * later used.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_crowd( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		$value = strtolower( trim( wp_unslash( $value ) ) );

		// A pasted link is a common and understandable mistake: keep the last
		// path segment, which is where the identifier is.
		if ( false !== strpos( $value, '/' ) ) {
			$parts = array_filter( explode( '/', $value ) );
			$value = (string) end( $parts );
		}

		$value = preg_replace( '/[^a-z0-9_-]/', '', $value );
		return substr( (string) $value, 0, 100 );
	}

	/**
	 * One of a fixed set of values, or the default.
	 *
	 * @param mixed  $value    Raw value.
	 * @param array  $allowed  Permitted values.
	 * @param string $fallback Value to use when the input is not permitted.
	 * @return string
	 */
	public static function sanitize_choice( $value, array $allowed, $fallback ) {
		$value = is_scalar( $value ) ? strtolower( trim( (string) $value ) ) : '';
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * A hex colour, normalised to `#rrggbb`, or empty.
	 *
	 * Empty rather than a guess when it does not parse: no colour means the chat
	 * keeps its own, which always looks intentional. A half-understood value
	 * would not.
	 *
	 * Three-digit shorthand is expanded here because the chat itself only reads
	 * the six and eight digit forms, and `#fc0` in a colour field is a
	 * reasonable thing for somebody to type.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_hex_colour( $value ) {
		$value = is_scalar( $value ) ? strtolower( trim( (string) $value ) ) : '';
		$value = ltrim( $value, '#' );

		if ( preg_match( '/^[0-9a-f]{3}$/', $value ) ) {
			$value = $value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2];
		}

		return preg_match( '/^[0-9a-f]{6}$/', $value ) ? '#' . $value : '';
	}

	/**
	 * A single line of plain text, length capped.
	 *
	 * @param mixed $value      Raw value.
	 * @param int   $max_length Maximum characters to keep.
	 * @return string
	 */
	public static function sanitize_line( $value, $max_length ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		$value = sanitize_text_field( wp_unslash( $value ) );
		// Multibyte-aware where the extension is available, so a cap does not
		// slice a character in half.
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}
		return substr( $value, 0, $max_length );
	}
}
