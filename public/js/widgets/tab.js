/**
 * © Seltor.ru 2013
 * Author: Anton Zelenski zelibobla@gmail.com
 *
 * Extend bootstrap tabs to ajax content loading
 *
 * dependencies (order of attaching has meaning):
 *	 underscore-1.4.3
 *	 jquery-1.9.0
 *	 backbone-0.9.10
 *	 bootstrap-2.2.2
 */
var TabModel = Backbone.Model.extend({
	defaults: { url: '',
				href: '',
				body: 'Loading...' },
	initialize: function( options ){
		this.url = options.url;
		this.href = options.href;
	}
});
var TabView = Backbone.View.extend({
	model: TabModel,
	templates: {
		loader: '<div class="progress progress-striped active form-loader"><div class="bar" style="width:100%"></div></div>',
	},
	/**
	* retrieve data from binded tag attributes and stay idle
	* @return void
	*/
	initialize: function(){
		var self = this,
			a = this.$el.find( 'a' );
		a.on( 'click', function(){ self.trigger( 'click' ) });
	},
	/**
	* pull tab containment
	* @return void
	*/
	render: function(){
		var container = $( this.$el.find( 'a' ).attr( 'href' ) ),
			self = this;
		this.$el.addClass( 'active' );
		container.addClass( 'active' );
		container.addClass( 'in' );
		container.html( this.templates.loader );
		this.model.fetch({
			success: function(){
				self.updateContainer();
				self.trigger( 'load', { id: self.model.get( 'href' ) } );
			},
			error: function(){
				self.model.set( 'body', 'Error' );
				self.updateContainer();
			}
		});
	},
	/**
	* display retrieved tab containment
	* @return void
	*/
	updateContainer: function(){
		var container = $( this.$el.find( 'a' ).attr( 'href' ) );
		container.html( this.model.get( 'body' ) );
	},
	/**
	* hide tab containment
	* @return void
	*/
	hide: function(){
		var container = $( this.$el.find( 'a' ).attr( 'href' ) );
		this.$el.removeClass( 'active' );
		container.empty();
	}
});
/**
* tabs – is a kind of collection of TabView objects
* in constructor it looks for an DOM elements matching to selector and bind on it TabView object (extends Backbone.View)
* any view can be accessed by tabs[ href ], thus href – is a necessary attribute of any DOM element which in Backbone.View binded
*/
var Tabs = Backbone.View.extend({
	initialize: function( options ){
		if( 'undefined' == typeof options ||
			'undefined' == typeof options.selector )
			throw 'Can\'t run Tab class with undefined selector to bind it to';

		if( 0 == $( options.selector ).length ){
		 	if( options.debug )
				console.log( 'Tab class: no elements matches specified selector; exit' );
			return;
		}

		var buttons = $( options.selector ).find( 'li' );
		if( 0 == buttons.length ){
		 	if( options.debug )
				console.log( 'Tab class: under specified selector there is no any anchor element; exit' );
			return;
		}

		/* instantiate tabs (views and models) */
		var self = this,
			tabs = {};
		$.each( buttons, function( index, li ){
			var a = $( li ).find( 'a' ),
				href = $( a ).attr( 'href' );
			if( 'undefined' == typeof href )
				throw 'Can\'t bind tab view to anchor with undfined href';
			var container = $( href );
			if( 0 == container.length )
				throw 'Anchor href references to not existing element ' + href;
			var	tab_model = new TabModel({ url: container.attr( 'url' ),
			 							   href: href }),
				tab_view = new TabView({ el: li,
										 model: tab_model });
			tabs[ href ] = tab_view;
			tab_view.on( 'click', function(){
				var exception = this.model.get( 'href' );
				$.each( tabs, function( href, tab_view ){
					if( href == exception )
					 	tab_view.render();
					else
						tab_view.hide();
				});
			});
			tab_view.on( 'load', function( event ){
				self.trigger( 'load', event );
			});
		});
				
		/* programmatically click the tab from location url */
		if( null !== ( res = /#(.*)/.exec( window.location.href ) ) )
			$( 'a[ href=' + res[ 0 ] + ']' ).click();
		else {
			/* programmatically click active tab */
			var active = $( options.selector ).find( 'li.active' );
			if( 1 != active.length )
				var active = $( options.selector ).find( 'li' )[ 0 ];
			active.find( 'a' ).click();	
		}
			
	}
});