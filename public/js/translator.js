/**
 * © Anton Zelenski 2013
 * zelibobla@gmail.com
 *
 */
if( 'undefined' == typeof core )
	var core = {};
	
core._ = function(){
	var self = this,
		key ='',
		values = [];

	if( 1 >= arguments.length )
		key = arguments[ 0 ];
	else {
		key = arguments[ 0 ];
		for( i in arguments ){
			if( 0 == i ) continue;
			values.push( arguments[ i ] );
		}
	}

	if( 'undefined' == typeof core.vocabulary ||
		'undefined' == typeof core.vocabulary[ key ] )
		if( 1 >= arguments.length ){
			return key;
		} else
			return vsprintf( key, values );
	else if( 1 >= arguments.length )
		return core.vocabulary[ key ];
	else
		return vsprintf( core.vocabulary[ key ], values );

}

core.vocabulary = {};
localStorage.removeItem( 'vocabulary' );
if( localStorage && localStorage.getItem( 'vocabulary' ) )
	core.vocabulary = JSON.parse( localStorage.getItem( 'vocabulary' ) );
else
	$.ajax({
		url: '/translator',
		success: function( response ){
			core.vocabulary = response;
			if( localStorage )
				localStorage.setItem( 'vocabulary', JSON.stringify( core.vocabulary ) );
		}
	});
