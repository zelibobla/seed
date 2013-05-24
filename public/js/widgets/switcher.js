/**
 * © Jamydays.ru 2013
 * Author: Anton Zelenski zelibobla@gmail.com
 *
 * The Jquery plugin to make a button that switches between some action and undo.
 * Plugin should be attached to button form element with specified class name.
 * All necessary data should be located in HTML attribs of this button like this:
 *	<button id="some_unique_id"
			class="join"
 *			inactive_content="Press me!"
 *			active_content="Undo"
 *			active_note="Pressed!"
 *			action_url="/action.php"
 *			undo_url="/undo.php">
 *	</button>
 *
 * Several instances of plugin on one page are available.
 *
 * dependencies:
 *	 jquery-1.5.2
 */

(function( $ ){

		var settings = {};
		var id = null;
		var defaults = {
			'debug'					: true
		};

		var methods = {

			init : function( options ) {

				if( options ) {
					var temp = {};
					$.extend( temp, defaults );
					$.extend( temp, options );
				} else {
					temp = defaults;
				}
				id = this.attr( 'id' );

				settings[ id ] = temp;

				var buttons = this;
				buttons.bind( 'click', methods.process );

				$.each( this, function( index, element ){
					var button = $( element ),
						note_id = button.attr( 'id' ) + '_note_id';
					if( button.hasClass( 'active' ) ){
						button.html( button.attr( 'active_content' ) );
						button.before( '<span id="' + note_id + '">' + button.attr( 'active_note' ) + '</span>' );
					} else {
						button.html( button.attr( 'inactive_content' ) );
					}
				})

			},

			process: function( event ){
				var button = 'undefined' == typeof $( event.target ).attr( 'action_url' )
						   ? $( event.target ).closest( 'button' )
						   : $( event.target ),
					note_id = button.attr( 'id' ) + '_note_id',
					html = button.hasClass( 'active' ) ? button.attr( 'inactive_content' ) : button.attr( 'active_content' ),
					url = button.hasClass( 'active' ) ? button.attr( 'undo_url' ) : button.attr( 'action_url' );
				button.hide().after( '<img src="/css/ajax_loader_bar.gif" id="loader" />' );
				$.ajax({
					url: url,
					success: function( response ){
						$( '#loader' ).remove();
						button.show();
						if( true == $( '#' + note_id ).length ) $( '#' + note_id ).remove();
						if( 'undefined' != typeof response.result &&
						 	true == response.result ){
								button.html( html );
							if( button.hasClass( 'active' ) ){
								button.removeClass( 'active' );
								button.removeClass( 'undo' );
							} else {
								button.before( '<span id="' + note_id + '">' + button.attr( 'active_note' ) + '</span>' );
								button.addClass( 'active' );
								button.addClass( 'undo' );
							}
						} else {
							button.before( '<span id="' + note_id + '">' + translator.error + '</span>' );
						}
					}
				});

			}


		};

		$.fn.switcher = function( method ) {

			// Method calling logic
			if ( methods[ method ] ) {
				return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
			} else if ( typeof method === 'object' || !method ) {
				return methods.init.apply( this, arguments );
			} else {
				$.error( 'Method ' +  method + ' does not exist on jQuery.switcher' );
			}    

	 	};

})( jQuery );