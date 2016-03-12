/**
 * modalEffects.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
jQuery( document ).ready( function() {
	 
	 
	var ModalEffects = (function() {

		function init() {

			var overlay = document.querySelector( '.md-overlay' );

			[].slice.call( document.querySelectorAll( '.md-trigger' ) ).forEach( function( el, i ) {

				var modal = document.querySelector( '#' + el.getAttribute( 'data-modal' ) ),
					close = modal.querySelector( '.md-close' );

				function removeModal( hasPerspective ) {
					classie.remove( modal, 'md-show' );

					if( hasPerspective ) {
						classie.remove( document.documentElement, 'md-perspective' );
					}
				}

				function removeModalHandler() {					
					jQuery('html').css('background' , '#f1f1f1');
					removeModal( classie.has( el, 'md-setperspective' ) ); 
				}

				el.addEventListener( 'click', function( ev ) {
					var height = jQuery(window).height();
					jQuery('.ia-iframe-container').css('height', height * 0.90 | 0);
					classie.add( modal, 'md-show' );
					
					overlay.removeEventListener( 'click', removeModalHandler );
					overlay.addEventListener( 'click', removeModalHandler );

					if( classie.has( el, 'md-setperspective' ) ) {
						setTimeout( function() {
							classie.add( document.documentElement, 'md-perspective' );
						}, 25 );
					}
					
					//jQuery('html').css('background' , '#000');
				});

				close.addEventListener( 'click', function( ev ) {
					ev.stopPropagation();
					removeModalHandler();
				});

			} );

		}

		init();

	})();
});