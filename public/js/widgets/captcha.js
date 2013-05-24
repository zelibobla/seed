/**
 * © Jamydays.ru 2013
 * Author: Anton Zelenski zelibobla@gmail.com
 *
 * The class to handle captcha refresh
 *
 * dependencies (order of attaching has meaning):
 *	 underscore-1.4.3
 *	 jquery-1.9.0
 *	 backbone-0.9.10
 *	 bootstrap-2.2.2
 */
var CaptchaModel = Backbone.Model.extend({
	defaults: { id: null,
				src: '' },
	initialize: function( options ){
		this.set( options );
	}
});
var Captcha = Backbone.View.extend({
	loader: '<div class="progress progress-striped active captcha-loader"><div class="bar"></div></div>',
	events: {
		'click': 'refresh',
	},
	initialize: function( options ){
		if( 'undefined' == typeof options ||
			'undefined' == typeof options.selector )
			throw 'Unable to run Captcha class with undefined {url:, selector:, src:} params';
		
		this.$el = $( options.selector );
		if( 0 == this.$el.length )
			throw 'Unable bind Captcha class to nonexisting element: "' + options.selector + '"';
		
		var url = this.$el.attr( 'href' );
		if( 'undefined' == typeof url )
			throw 'Can\'t retrieve href from binded element: "' + options.selector +'"';
		
		var container = this.$el.closest( '.controls' );
		this.id = container.find( '#captcha-id' ),
		this.img = container.find( 'img' );
		if( 'undefined' == typeof container ||
		 	'undefined' == typeof this.id ||
			'undefined' == typeof this.img )
			throw 'Can\'t find container with neighbour elements or any of these $( "#captcha-id" ), $( "img" ) neighbours';
			
		this.model = new CaptchaModel({
			id: this.id.val(),
			src: this.img.attr( 'src' )
		});
		this.model.url = url;
		var self = this;
		this.model.on( 'change', function(){ self.render() });
		
	},
	render: function(){
		this.id.val( this.model.get( 'id' ) );
		this.img.attr( 'src', this.model.get( 'src' ) );
		this.img.show();
		this.loader.remove();
	},
	refresh: function( event ){
		event.preventDefault();
		this.img.hide().after( this.loader );
		this.loader = $( '.captcha-loader' );
		this.model.fetch();
	},
});
