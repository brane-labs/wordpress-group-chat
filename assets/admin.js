/*
 * Settings screen behaviour: the help panel, and a colour swatch.
 *
 * Progressive enhancement only. Every field is a plain form control that works
 * with this file absent, and nothing here validates anything — the server
 * sanitises on save regardless of what happens in the browser.
 */
( function () {
	'use strict';

	function wireHelpPanel() {
		var toggle = document.querySelector( '.wpgc-help-open' );
		var panel = document.getElementById( 'wpgc-help' );
		if ( ! toggle || ! panel ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var open = panel.hasAttribute( 'hidden' );
			if ( open ) {
				panel.removeAttribute( 'hidden' );
			} else {
				panel.setAttribute( 'hidden', '' );
			}
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	}

	/*
	 * Shows the colour beside the field as it is typed. Purely so somebody
	 * notices a mistyped value before saving; the stored value is whatever the
	 * server accepts.
	 */
	function wireColourSwatches() {
		var fields = document.querySelectorAll( '.wpgc-colour' );
		Array.prototype.forEach.call( fields, function ( field ) {
			var swatch = document.createElement( 'span' );
			swatch.className = 'wpgc-swatch';
			field.parentNode.insertBefore( swatch, field.nextSibling );

			var paint = function () {
				var value = field.value.trim();
				if ( value && value.charAt( 0 ) !== '#' ) {
					value = '#' + value;
				}
				// Only the forms the server will accept get painted, so the
				// swatch and the saved result agree.
				swatch.style.background = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test( value ) ? value : 'transparent';
			};

			field.addEventListener( 'input', paint );
			paint();
		} );
	}

	function start() {
		wireHelpPanel();
		wireColourSwatches();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
