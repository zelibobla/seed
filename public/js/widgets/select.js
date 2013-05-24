/**
 * © Jamydays.ru 2013
 * Author: Anton Zelenski zelibobla@gmail.com
 *
 * The classes to handle select options dependands
 *
 * dependencies (order of attaching has meaning):
 *	 underscore-1.4.3
 *	 jquery-1.9.0
 *	 backbone-0.9.10
 *	 bootstrap-2.2.2
 */
var OptionModel = Backbone.Model.extend({
	defaults: { label: 'noname',
	 			value: '' },
	initialize: function( options ){
		this.set( options );
	}
});
var Option = Backbone.View.extend({
	tagName: 'option',
	initialize: function( options ){
		if( 'undefined' == typeof options ||
			'undefined' == typeof options.node )
			throw 'Can\'t run Option class with undefined node';
		this.node = options.node;
	},
	render: function(){
		this.node.append( this.el );
		this.$el.attr({ 'value': this.model.get( 'value' ),
		 				'label': this.model.get( 'label' ) });
	}
})
var SelectCollection = Backbone.Collection.extend({
	model: OptionModel,
	initialize: function( models, options ){
		if( 'undefined' == typeof options ||
			'undefined' == options.url )
			throw 'Can\'t initialize SelectCollection with undefined url option';
		this.url = options.url + '?parent_id=' + options.parent.val();		
	}
});
var Select = Backbone.View.extend({
	tagName: 'select',
	templates: {
		loader: '<div class="progress progress-striped active select-loader"><div class="bar"></div></div>',
	},
	selectors:{
		loader: '.select-loader',
	},
	initialize: function( options ){
		if( 'undefined' == typeof options ||
			'undefined' == typeof options.url )
			throw 'Can\'t run Select class with undefined url';
		this.url = options.url;
		if( 'undefined' == typeof options.parent ||
		 	!options.parent.length )
			throw 'Can\'t run Select class with undefined parent';
		this.parent = options.parent;
		if( 'undefined' == typeof options.node ||
		 	!options.node.length )
			throw 'Can\'t run Select class with undefined node';
		this.setElement( options.node );
		this.parent.on( 'change', $.proxy( function(){ this.render() }, this ) );
		this.render();
	},
	render: function(){
		this.$el.hide().after( this.templates.loader );
		this.$el.empty();
		this.model = new SelectCollection( false, { url: this.url, parent: this.parent });
		var self = this;
		this.model.fetch({
			success: function(){
				$( self.selectors.loader ).remove();
				self.$el.show();
				self.model.each( function( element ){
					var option = new Option({ model: element, node: self.$el });
					option.render();
				});
				self.trigger( 'act' );
				self.$el.on( 'change', $.proxy( function(){ self.remember() }, this ) );
				if( 'undefined' != typeof $( 'body' ).data( self.$el.attr( 'id' ) ) ){
					self.$el.val( $( 'body' ).data( self.$el.attr( 'id' ) ) );
				} else {
					self.remember();
				}
			},
			error: function(){
				self.trigger( 'act' );
			}
		});
	},
	remember: function(){
		$( 'body' ).data( this.$el.attr( 'id' ), this.$el.val() );
	},
});