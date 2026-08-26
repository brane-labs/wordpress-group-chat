<?php
/**
 * Runs the sanitisers and the tag rewriting outside WordPress.
 *
 * A plain script rather than a PHPUnit suite: it needs no WordPress test
 * install, so it runs anywhere PHP does, which is what gets it run at all.
 *
 *   php tests/test-sanitising.php
 *
 * @package BraneCrowdChat
 */

// Enough of WordPress to load the classes under test.
define( 'ABSPATH', __DIR__ );
define( 'BRANE_CROWD_CHAT_OPTION', 'brane_crowd_chat_settings' );
define( 'BRANE_CROWD_CHAT_EMBED_ORIGIN', 'https://embed.brane.chat' );

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

require_once __DIR__ . '/../includes/class-brane-crowd-chat-settings.php';
require_once __DIR__ . '/../includes/class-brane-crowd-chat-embed.php';

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
check( 'plain id', Brane_Crowd_Chat_Settings::sanitize_crowd( 'northside-runners' ), 'northside-runners' );
check( 'trims and lowercases', Brane_Crowd_Chat_Settings::sanitize_crowd( '  Makaveli  ' ), 'makaveli' );
check( 'keeps underscores', Brane_Crowd_Chat_Settings::sanitize_crowd( 'my_crowd_2' ), 'my_crowd_2' );
// A pasted link is the likeliest wrong input, so it is recovered rather than rejected.
check( 'recovers from a pasted url', Brane_Crowd_Chat_Settings::sanitize_crowd( 'https://embed.brane.chat/c/makaveli' ), 'makaveli' );
check( 'recovers with a trailing slash', Brane_Crowd_Chat_Settings::sanitize_crowd( 'https://embed.brane.chat/c/makaveli/' ), 'makaveli' );
// The important ones: nothing that could change the meaning of a URL or a tag
// survives, because this value ends up in both.
check( 'strips path traversal', Brane_Crowd_Chat_Settings::sanitize_crowd( '../../etc/passwd' ), 'passwd' );
check( 'strips quotes and brackets', Brane_Crowd_Chat_Settings::sanitize_crowd( 'a" onload="x' ), 'aonloadx' );
// Anything with a slash goes through the pasted-link branch first, so this is
// reduced to the last segment and then allowlisted. The branch only ever
// narrows the input, so it is safe for values that were never a link.
check( 'strips a script tag', Brane_Crowd_Chat_Settings::sanitize_crowd( '<script>alert(1)</script>' ), 'script' );
check( 'strips an event handler with a slash', Brane_Crowd_Chat_Settings::sanitize_crowd( '"><img src=x onerror=1>' ), 'imgsrcxonerror1' );
check( 'strips a query string', Brane_Crowd_Chat_Settings::sanitize_crowd( 'crowd?a=1&b=2' ), 'crowda1b2' );
check( 'strips spaces', Brane_Crowd_Chat_Settings::sanitize_crowd( 'my crowd' ), 'mycrowd' );
check( 'empty stays empty', Brane_Crowd_Chat_Settings::sanitize_crowd( '' ), '' );
check( 'non-scalar is empty', Brane_Crowd_Chat_Settings::sanitize_crowd( array( 'x' ) ), '' );
check( 'length capped', strlen( Brane_Crowd_Chat_Settings::sanitize_crowd( str_repeat( 'a', 500 ) ) ), 100 );

echo "Colours\n";
check( 'six digit', Brane_Crowd_Chat_Settings::sanitize_hex_colour( '#F4C32F' ), '#f4c32f' );
check( 'without hash', Brane_Crowd_Chat_Settings::sanitize_hex_colour( 'F4C32F' ), '#f4c32f' );
check( 'three digit expands', Brane_Crowd_Chat_Settings::sanitize_hex_colour( '#fc0' ), '#ffcc00' );
// rgb() is what somebody copies out of a design tool, and it is NOT accepted by
// the chat, so it must not be stored as though it were.
check( 'rgb() is rejected', Brane_Crowd_Chat_Settings::sanitize_hex_colour( 'rgb(244, 195, 47)' ), '' );
check( 'colour name is rejected', Brane_Crowd_Chat_Settings::sanitize_hex_colour( 'red' ), '' );
check( 'css injection is rejected', Brane_Crowd_Chat_Settings::sanitize_hex_colour( '#fff;}body{display:none' ), '' );
check( 'four digits rejected', Brane_Crowd_Chat_Settings::sanitize_hex_colour( '#abcd' ), '' );
check( 'empty stays empty', Brane_Crowd_Chat_Settings::sanitize_hex_colour( '' ), '' );

echo "Fixed choices\n";
check( 'theme dark', Brane_Crowd_Chat_Settings::sanitize_choice( 'dark', Brane_Crowd_Chat_Settings::THEMES, 'light' ), 'dark' );
check( 'theme case insensitive', Brane_Crowd_Chat_Settings::sanitize_choice( 'DARK', Brane_Crowd_Chat_Settings::THEMES, 'light' ), 'dark' );
// The loader's own comment offers `paper`, but the chat only honours light and
// dark, so offering it here would be a setting that silently does nothing.
check( 'paper falls back to light', Brane_Crowd_Chat_Settings::sanitize_choice( 'paper', Brane_Crowd_Chat_Settings::THEMES, 'light' ), 'light' );
check( 'junk falls back', Brane_Crowd_Chat_Settings::sanitize_choice( 'x"><script>', Brane_Crowd_Chat_Settings::THEMES, 'light' ), 'light' );
check( 'position left', Brane_Crowd_Chat_Settings::sanitize_choice( 'left', Brane_Crowd_Chat_Settings::POSITIONS, 'right' ), 'left' );

echo "Text lines\n";
check( 'plain text', Brane_Crowd_Chat_Settings::sanitize_line( 'Chat with us', 60 ), 'Chat with us' );
check( 'tags stripped', Brane_Crowd_Chat_Settings::sanitize_line( 'Hi <script>alert(1)</script>', 60 ), 'Hi alert(1)' );
check( 'newlines collapsed', Brane_Crowd_Chat_Settings::sanitize_line( "one\ntwo", 60 ), 'one two' );
check( 'length capped', strlen( Brane_Crowd_Chat_Settings::sanitize_line( str_repeat( 'a', 200 ), 60 ) ), 60 );

echo "Whole-form sanitising\n";
$clean = Brane_Crowd_Chat_Settings::sanitize(
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
check( 'non-array input is defaults', Brane_Crowd_Chat_Settings::sanitize( 'nonsense' ), Brane_Crowd_Chat_Settings::defaults() );

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
$attributes = Brane_Crowd_Chat_Embed::attributes();
check( 'crowd present', $attributes['data-crowd'], 'makaveli' );
check( 'theme present', $attributes['data-theme'], 'dark' );
check( 'colour present', $attributes['data-primary'], '#f4c32f' );
// An empty attribute is not the same as an absent one to the loader.
check( 'empty colour omitted', isset( $attributes['data-secondary'] ), false );
check( 'empty label omitted', isset( $attributes['data-label'] ), false );
// data-env must never be emitted: which service the chat talks to is not the
// host page's decision.
check( 'no env attribute', isset( $attributes['data-env'] ), false );

$tag     = "<script src='https://embed.brane.chat/embed.js' id='brane-crowd-chat-js'></script>\n";
$rewrite = Brane_Crowd_Chat_Embed::add_attributes( $tag, 'brane-crowd-chat' );
check( 'keeps the existing id', false !== strpos( $rewrite, "id='brane-crowd-chat-js'" ), true );
check( 'adds the crowd', false !== strpos( $rewrite, 'data-crowd="makaveli"' ), true );
check( 'adds defer', false !== strpos( $rewrite, 'defer>' ), true );
check( 'still one tag', substr_count( $rewrite, '<script' ), 1 );
check( 'other handles untouched', Brane_Crowd_Chat_Embed::add_attributes( $tag, 'jquery' ), $tag );

printf( "\n%d checks, %d failures\n", $checks, $failures );
exit( $failures > 0 ? 1 : 0 );
