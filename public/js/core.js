/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

var core = {};

$( document ).on( 'ready', function(){
	
	/* init Form backbone view */
	core.forms = new Forms({ selector: '.form' });
	
	/* init Notes backbone view */
	core.note = new Notes({ url: '/voice' });
	core.note.trigger( 'act' );
	
	/* describe special behaviour of login form after it is rendered */
	if( 'undefined' != typeof core.forms[ 'signin' ] )
		core.forms[ 'signin' ].on( 'render', function(){
			$( '#recover_link' ).click( function( event ){
				event.preventDefault();
				core.forms.hide();
				$( '#recover' ).click();
			});
		});

	/* bind any form `act` event to cause Notes `act` event */
	core.forms.on( 'act', function(){ core.note.trigger( 'act' ) });
});
