/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */
var checkboxDependant = function(){
	if( $( '#is_photographer' ).is( ':checked' ) )
		$( '.dependant' ).show();
	else
		$( '.dependant' ).hide();
}

$( document ).on( 'ready', function(){
	
	/* init ajax tabs */
	tabs = new Tabs({ selector: '.nav-tabs.ajax' });
	tabs.on( 'load', function( event ){
		if( '#profile_tab' == event.id ){
			core.forms.rebind();
			core.forms[ 'profile' ].on( 'render', function(){
				checkboxDependant( $( '#is_photographer' ), $( '.dependant' ) );
				$( '#is_photographer' ).bind( 'change', checkboxDependant );
				
				/* bind image uploader */
				$( '#fileupload' ).fileupload({
					url: '/profile/image',
					acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
					maxFileSize: 2000000, // 2MB
					dataType: 'json',
					fail: function(){
						core.note.trigger( 'act' );
						$( '#progress' ).hide();
						$( '.fileinput-button' ).show();
					},
					submit: function(){
						$( '#progress' ).show();
						$( '.fileinput-button' ).hide();
					},
					done: function( e, data ){
						core.note.trigger( 'act' );
						$( '#progress' ).hide();
						$( '.fileinput-button' ).show();
						$( '#profile_pic' ).attr( 'src', data.result.full_filename + '?_=' + Math.floor( Math.random() * 1000000 ) ).show();
						$( '#delete_photo' ).show();
						$( '#photo' ).val( data.result.filename );
					},
					progressall: function( e, data ){
						var progress = parseInt( data.loaded / data.total * 100, 10 );
						$( '#progress .bar' ).css( 'width', progress + '%' );
					}
				});
				$( '#delete_photo' ).click( function( event ){
					event.preventDefault();
					$( '#profile_pic' ).hide();
					$( '#photo' ).val( '' );
					$( '#delete_photo' ).hide();
				});

				/* bind select2 */
				var select2 = $( '#city' ).select2( core.city_select2_options ).data( 'select2' );
				select2.onSelect = ( function( fn ){
					return function( data, options ){
						$( '#city_id' ).val( data.id );
						return fn.apply( this, arguments )
					};
				})( select2.onSelect );




			});
			core.forms[ 'profile' ].on( 'act', function(){
				core.note.trigger( 'act' );
			});
		} else if( '#trips_tab' == event.id ){
			var trips = new Trips({ selector: '#trips_tab' });
		}
	});

});
