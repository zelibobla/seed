/**
 * © Jamydays.ru 2013
 * Author: Anton Zelenski zelibobla@gmail.com
 *
 * The class to handle form via ajax
 *
 * dependencies (order of attaching has meaning):
 *	 underscore-1.4.3
 *	 jquery-1.9.0
 *	 backbone-0.9.10
 *	 bootstrap-2.2.2
 */
var FormModel = Backbone.Model.extend({
	defaults: { url: '',
	 			header: 'Some header',
				body: 'Loading...',
				data: {},
				cancel_text: 'Cancel',
				submit_text: 'Submit',
				is_popup: false },
	initialize: function( params ){
		this.url = params.url;
		this.is_popup = params.is_popup;
	}
});
var FormView = Backbone.View.extend({
	templates: {
		backdrop: '<div class="modal-backdrop"></div>',
		container: '<div class="modal"></div>',
		loader: '<div class="progress progress-striped active form-loader"><div class="bar"></div></div>',
		body: _.template( '<div class="modal-header">\
								<button type="button" class="close">&times;</button>\
								<h2><%=header%></h2>\
						   </div>\
						   <div class="modal-body"><%=body%></div>\
						   <div class="modal-footer">\
						   		<span class="wrap-btn-primary"><a href="#" class="btn-primary submit"><%=submit_text%></a></span>\
						   </div>' ),
	},
	selectors: {
		container: '.modal',
		form: '.modal form',
		loader: '.loader',
		backdrop: '.modal-backdrop',
		cancel_button: 'button.close,a.btn.cancel',
		submit_button: 'a.btn-primary.submit',
	},
	/**
	* retrieve data from binded tag attributes and stay idle
	* @return void
	*/
	initialize: function(){
		if( !this.model.get( 'is_popup' ) )
			this.render();
		this.rebindListeners();
	},
	/**
	* bind events to some elements
	* @return this
	*/
	rebindListeners: function(){
		var self = this;
		if( this.model.get( 'is_popup' ) ){
			this.$el.on( 'click', function( event ){ self.render( event ) });
			$( this.selectors.cancel_button ).on( 'click', function(){ self.hide() });
			$( this.selectors.submit_button ).on( 'click', function(){ self.submit() });
		} else {
			$( '#' + this.$el.attr( 'id' ) ).bind( 'submit', function( event ){ self.submit( event ) });
		}
		return this;
	},
	/**
	* display window and background; run data fetch from server
	* @return void
	*/
	render: function( event ){

		if( 'undefined' == typeof this.container ){
			if( this.model.get( 'is_popup' ) ){
				event.preventDefault();
				$( document.body ).append( this.templates.container );
				this.container = $( this.selectors.container )
				if( !$( this.selectors.backdrop ).length )
					$( document.body ).append( this.templates.backdrop );
				this.backdrop = $( this.selectors.backdrop );
				this.container.html( this.templates.loader );
			} else {
				this.container = this.$el.closest( 'div' );
			}
		} else {
			event.preventDefault();
			this.container.show();
			this.backdrop.show();
		}
		var self = this;
		this.model.fetch({
			success: function(){
				self.refresh();
			},
			error: function(){
				self.trigger( 'act' );
				self.hide();
			}
			/* in case of success model.change event will be triggered and handled here */
		});
	},
	/**
	* update window state (binded upper to model change event)
	* @return void
	*/
	refresh: function(){
		var self = this;
		if( this.model.get( 'is_popup' ) ){
			this.container.html( this.templates.body( this.model.toJSON() ) );
			this.form = $( this.selectors.form );
		} else {
			this.container.html( this.model.get( 'body' ) );
			this.form = this.container.find( 'form' );
		}
		this.rebindListeners();
		this.trigger( 'render' );
	},
	/**
	* hide window and background, stay idle
	* @return void
	*/
	hide: function(){
		if( 'undefined' != typeof this.backdrop )
			this.backdrop.hide();
		if( 'undefined' != typeof this.container )
			this.container.hide();
	},
	/**
	* submit form to server, handle server response
	* @return void
	*/
	submit: function( event ){
		if( !this.model.get( 'is_popup' ) )
			event.preventDefault();

		var self = this,
			data = this.form.serialize();

		this.container.html( this.templates.loader );
		$.ajax({
			url: this.model.url,
			data: data,
			type: 'post',
			success: function( response ){
				self.trigger( 'act' );
				if( 'undefined' != typeof response )
					if( 'undefined' != typeof response.reload &&
					 	true == response.reload )
						window.location.reload();
					else if( 'undefined' != typeof response.redirect )
						window.location.href = response.redirect;
				if( !self.model.get( 'is_popup' ) ){
					self.model.set( 'body', response.body );
					self.refresh();
				}
			},
			error: function( jqXHR, status, error ){
				self.trigger( 'act' );
				if( 406 == jqXHR.status )
					self.model.set( $.parseJSON( jqXHR.responseText ) );
				self.refresh();
			}
		});
	}
});
/**
* forms – is a kind of collection of FormView objects
* in constructor it looks for an DOM elements matching to selector and bind on it FormView object (extends Backbone.View)
* any view can be accessed by forms[ id ], thus id – is a necessary attribute of any DOM element which in Backbone.View binded
*/
var Forms = function( options ){
	if( 'undefined' == typeof options ||
		'undefined' == typeof options.selector )
		throw 'Can\'t run Form class with undefined selector to bind it to';
	
	if( 0 == $( options.selector ).length &&
	 	true == options.debug )
		return console.log( 'Form class: no elements matches specified selector; exit' );
	var self = this;


	/**
	* go around all elements on a page and look if there is some new to create a form instance 
	* @return this
	*/
	this.rebind = function(){
		$.each( $( options.selector ), function( index, element ){

			var element_id = $( element ).attr( 'id' );

//			if( 'undefined' != typeof self[ element_id ] ) return;
							
			var is_popup = $( element ).hasClass( 'popup' ),
				url = is_popup ? $( element ).attr( 'href' ) : $( element ).attr( 'action' );
			if( 'undefined' == typeof element_id )
				throw 'Can\'t bind form view to element with undfined id';
			var	form_model = new FormModel({ cancel_text: translator.cancel,
				 							 submit_text: translator.submit,
				 							 is_popup: is_popup,
				 							 url: url }),
				form_view = new FormView({ el: $( element ),
										   model: form_model });
				self[ element_id ] = form_view;
		});
		return this;
	};
	this.rebind();

	/**
	* serial form binding of any event
	* to bind an event on any specified form call forms[ your_id ].on( event_name, function(){} );
	* @param event_name – ( string ) name of desired event
	* @param callback – ( function ) callback to fire on event
	* @return this
	*/
	this.on = function( event_name, callback ){
		$.each( self, function( id, form_view ){
			if( 'function' == typeof form_view ) return;
			form_view.unbind( event_name );
			form_view.on( event_name, callback );
		});
		return self;
	}
	/**
	* serial form hide of all currently shown forms
	* @return this
	*/
	this.hide = function(){
		$.each( self, function( id, form_view ){
			if( 'function' == typeof form_view ) return;
			form_view.hide();
		});
		return self;
	}
}