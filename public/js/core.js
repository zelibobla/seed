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
	if( 'undefined' != typeof core.forms[ 'login_trigger' ] )
		core.forms[ 'login_trigger' ].on( 'render', function(){
			$( '#recover_link' ).click( function( event ){
				event.preventDefault();
				core.forms.hide();
				$( '#recover_trigger' ).click();
			});
			var captcha = new Captcha({ selector: '#refresh_captcha' });
		});

	/* bind any form `act` event to cause Notes `act` event */
	core.forms.on( 'act', function(){ core.note.trigger( 'act' ) });
	
	/* bind datepicker */
	core.datepicker_options = {
		format: 'dd-mm-yyyy',
		onRender: function( date ){
			var now = new Date();
			return date.valueOf() < now.valueOf() ? 'disabled' : '';
		}
	};
	$( '.datepicker' ).datepicker( core.datepicker_options );
	$( '#from' ).datepicker().on( 'changeDate', function( event ){
		$( '#from' ).datepicker( 'hide' );
		$( '#to' ).focus();
	});
	$( '.add-on' ).bind( 'click', function( event ){
		$( event.target ).next().focus();
	});
	
	/* bind select2 */
	core.city_select2_options = {
		initSelection : function( element, callback ) {
			var data = { id: element.val(), text: element.val() };
			callback(data);
		},
		minimumInputLength: 2,
		ajax: {
			url: "/city",
			data: function( term, page ){
				return {
					q: term,
					page_limit: 10
				};
			},
		    results: function( data, page ){
		        return { results: data.result };
		    }
		},
	};
	if( 0 != $( '#where' ).length ){
		var select2 = $( '#where' ).select2( core.city_select2_options ).data( 'select2' );
		select2.onSelect = ( function( fn ){
			return function( data, options ){
				$( '#where' ).val( data.text );
				$( '#city_id' ).val( data.id );
				return fn.apply( this, arguments )
			};
		})( select2.onSelect );
	}
	
	core.translator = {};
	core.translator[ 'month_0_short' ] = 'янв';
	core.translator[ 'month_1_short' ] = 'фев';
	core.translator[ 'month_2_short' ] = 'мар';
	core.translator[ 'month_3_short' ] = 'апр';
	core.translator[ 'month_4_short' ] = 'мая';
	core.translator[ 'month_5_short' ] = 'июн';
	core.translator[ 'month_6_short' ] = 'июл';
	core.translator[ 'month_7_short' ] = 'авг';
	core.translator[ 'month_8_short' ] = 'сен';
	core.translator[ 'month_9_short' ] = 'окт';
	core.translator[ 'month_10_short' ] = 'ноя';
	core.translator[ 'month_11_short' ] = 'дек';
});
