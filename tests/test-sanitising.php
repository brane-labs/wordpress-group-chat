<?php
/**
 * Runs the sanitisers and the tag rewriting outside WordPress.
 *
 * A plain script rather than a PHPUnit suite: it needs no WordPress test
 * install, so it runs anywhere PHP does, which is what gets it run at all.
 *
 *   php tests/test-sanitising.php
 *
 * @package WordPressGroupChat
 */

// Enough of WordPress to load the classes under test.
define( 'ABSPATH', __DIR__ );
define( 'WPGC_OPTION', 'wpgc_settings' );
define( 'WPGC_EMBED_ORIGIN', 'https://embed.brane.chat' );

$GLOBALS['test_option'] = array();

function register_setting( $group, $name, $args = array() ) {}
function get_option( $name, $default = false ) {
	return $GLOBALS['test_option'] ? $GLOBALS['test_option'] : $default;
}
function wp_parse_args( $args, $defaults ) {
	return array_merge( $defaults, array_intersect_key( $args, $defaults ) );
}
function wp_unslash( $value ) {
	return is_string( $value ) ? stripslashes( $value ) : $value;
}
function sanitize_text_field( $value ) {
	$value = strip_tags( (string) $value );
	$value = preg_replace( '/[\r\n\t]+/', ' ', $value );
	return trim( preg_replace( '/\s+/', ' ', $value ) );
}
function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function apply_filters( $hook, $value ) {
	return $value;
}
function add_action() {}
function add_filter() {}

require_once __DIR__ . '/../includes/class-wpgc-settings.php';
require_once __DIR__ . '/../includes/class-wpgc-embed.php';

$failures = 0;
$checks   = 0;

function check( $label, $actual, $expected ) {
	global $failures, $checks;
	$checks++;
	if ( $actual === $expected ) {
		return;
	}
	$failures++;
	printf(
		"  FAIL  %s\n          expected: %s\n          actual:   %s\n",
		$label,
		var_export( $expected, true ),
		var_export( $actual, true )
	);
}

echo "Crowd ID\n";
check( 'plain id', WPGC_Settings::sanitize_crowd( 'northside-runners' ), 'northside-runners' );
check( 'trims and lowercases', WPGC_Settings::sanitize_crowd( '  Makaveli  ' ), 'makaveli' );
check( 'keeps underscores', WPGC_Settings::sanitize_crowd( 'my_crowd_2' ), 'my_crowd_2' );
// A pasted link is the likeliest wrong input, so it is recovered rather than rejected.
check( 'recovers from a pasted url', WPGC_Settings::sanitize_crowd( 'https://embed.brane.chat/c/makaveli' ), 'makaveli' );
check( 'recovers with a trailing slash', WPGC_Settings::sanitize_crowd( 'https://embed.brane.chat/c/makaveli/' ), 'makaveli' );
// The important ones: nothing that could change the meaning of a URL or a tag
// survives, because this value ends up in both.
check( 'strips path traversal', WPGC_Settings::sanitize_crowd( '../../etc/passwd' ), 'passwd' );
check( 'strips quotes and brackets', WPGC_Settings::sanitize_crowd( 'a" onload="x' ), 'aonloadx' );
// Anything with a slash goes through the pasted-link branch first, so this is
// reduced to the last segment and then allowlisted. The branch only ever
// narrows the input, so it is safe for values that were never a link.
check( 'strips a script tag', WPGC_Settings::sanitize_crowd( '<script>alert(1)</script>' ), 'script' );
check( 'strips an event handler with a slash', WPGC_Settings::sanitize_crowd( '"><img src=x onerror=1>' ), 'imgsrcxonerror1' );
check( 'strips a query string', WPGC_Settings::sanitize_crowd( 'crowd?a=1&b=2' ), 'crowda1b2' );
check( 'strips spaces', WPGC_Settings::sanitize_crowd( 'my crowd' ), 'mycrowd' );
check( 'empty stays empty', WPGC_Settings::sanitize_crowd( '' ), '' );
check( 'non-scalar is empty', WPGC_Settings::sanitize_crowd( array( 'x' ) ), '' );
check( 'length capped', strlen( WPGC_Settings::sanitize_crowd( str_repeat( 'a', 500 ) ) ), 100 );

echo "Colours\n";
check( 'six digit', WPGC_Settings::sanitize_hex_colour( '#F4C32F' ), '#f4c32f' );
check( 'without hash', WPGC_Settings::sanitize_hex_colour( 'F4C32F' ), '#f4c32f' );
check( 'three digit expands', WPGC_Settings::sanitize_hex_colour( '#fc0' ), '#ffcc00' );
// rgb() is what somebody copies out of a design tool, and it is NOT accepted by
// the chat, so it must not be stored as though it were.
check( 'rgb() is rejected', WPGC_Settings::sanitize_hex_colour( 'rgb(244, 195, 47)' ), '' );
check( 'colour name is rejected', WPGC_Settings::sanitize_hex_colour( 'red' ), '' );
check( 'css injection is rejected', WPGC_Settings::sanitize_hex_colour( '#fff;}body{display:none' ), '' );
check( 'four digits rejected', WPGC_Settings::sanitize_hex_colour( '#abcd' ), '' );
check( 'empty stays empty', WPGC_Settings::sanitize_hex_colour( '' ), '' );

echo "Fixed choices\n";
check( 'theme dark', WPGC_Settings::sanitize_choice( 'dark', WPGC_Settings::THEMES, 'light' ), 'dark' );
check( 'theme case insensitive', WPGC_Settings::sanitize_choice( 'DARK', WPGC_Settings::THEMES, 'light' ), 'dark' );
// The loader's own comment offers `paper`, but the chat only honours light and
// dark, so offering it here would be a setting that silently does nothing.
check( 'paper falls back to light', WPGC_Settings::sanitize_choice( 'paper', WPGC_Settings::THEMES, 'light' ), 'light' );
check( 'junk falls back', WPGC_Settings::sanitize_choice( 'x"><script>', WPGC_Settings::THEMES, 'light' ), 'light' );
check( 'position left', WPGC_Settings::sanitize_choice( 'left', WPGC_Settings::POSITIONS, 'right' ), 'left' );

echo "Text lines\n";
check( 'plain text', WPGC_Settings::sanitize_line( 'Chat with us', 60 ), 'Chat with us' );
check( 'tags stripped', WPGC_Settings::sanitize_line( 'Hi <script>alert(1)</script>', 60 ), 'Hi alert(1)' );
check( 'newlines collapsed', WPGC_Settings::sanitize_line( "one\ntwo", 60 ), 'one two' );
check( 'length capped', strlen( WPGC_Settings::sanitize_line( str_repeat( 'a', 200 ), 60 ) ), 60 );

echo "Whole-form sanitising\n";
$clean = WPGC_Settings::sanitize(
	array(
		'enabled' => '1',
		'crowd'   => 'Makaveli',
		'theme'   => 'dark',
		'primary' => 'f4c32f',
		'label'   => 'Chat',
		'welcome' => 'Say hello',
	)
);
check( 'enabled coerced to bool', $clean['enabled'], true );
check( 'crowd normalised', $clean['crowd'], 'makaveli' );
check( 'colour normalised', $clean['primary'], '#f4c32f' );
// A field absent from the submission must come back as its default, not vanish.
check( 'missing field defaults', $clean['position'], 'right' );
check( 'missing colour empty', $clean['secondary'], '' );
check( 'non-array input is defaults', WPGC_Settings::sanitize( 'nonsense' ), WPGC_Settings::defaults() );

echo "Attributes and tag rewriting\n";
$GLOBALS['test_option'] = array(
	'enabled'   => true,
	'crowd'     => 'makaveli',
	'theme'     => 'dark',
	'primary'   => '#f4c32f',
	'secondary' => '',
	'label'     => '',
	'position'  => 'right',
	'welcome'   => '',
);
$attributes = WPGC_Embed::attributes();
check( 'crowd present', $attributes['data-crowd'], 'makaveli' );
check( 'theme present', $attributes['data-theme'], 'dark' );
check( 'colour present', $attributes['data-primary'], '#f4c32f' );
// An empty attribute is not the same as an absent one to the loader.
check( 'empty colour omitted', isset( $attributes['data-secondary'] ), false );
check( 'empty label omitted', isset( $attributes['data-label'] ), false );
// data-env must never be emitted: which service the chat talks to is not the
// host page's decision.
check( 'no env attribute', isset( $attributes['data-env'] ), false );

$tag     = "<script src='https://embed.brane.chat/embed.js' id='wordpress-group-chat-js'></script>\n";
$rewrite = WPGC_Embed::add_attributes( $tag, 'wordpress-group-chat' );
check( 'keeps the existing id', false !== strpos( $rewrite, "id='wordpress-group-chat-js'" ), true );
check( 'adds the crowd', false !== strpos( $rewrite, 'data-crowd="makaveli"' ), true );
check( 'adds defer', false !== strpos( $rewrite, 'defer>' ), true );
check( 'still one tag', substr_count( $rewrite, '<script' ), 1 );
check( 'other handles untouched', WPGC_Embed::add_attributes( $tag, 'jquery' ), $tag );

printf( "\n%d checks, %d failures\n", $checks, $failures );
exit( $failures > 0 ? 1 : 0 );
