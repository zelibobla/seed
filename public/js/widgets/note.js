/**
 * © Jamydays.ru 2013
 * Author: Anton Zelenski zelibobla@gmail.com
 *
 * The class to retrieve notes from server and pulish it at frontend
 *
 * dependencies (order of attaching has meaning):
 *	 underscore-1.4.3
 *	 jquery-1.9.0
 *	 backbone-0.9.10
 *	 bootstrap-2.2.2
 */
var NoteModel = Backbone.Model.extend({
	defaults: { body: 'Loading...',
				type: 'neutral',
				id: null,
				subject: '',
				is_active: true },
	initialize: function( options ){
		this.set( options );
	},
	countDown: function(){
		var self = this;
		setTimeout( function(){
			self.set( 'is_active', false );
			self.save();
		}, 3000 );
	}
});
var NoteView = Backbone.View.extend({
	tagName: 'li',
	template: _.template( '<div class="alert"><%=body%><button type="button" class="close">&times;</button></div>' ),
	events: {
		'click .close': 'disappear',
	},
	render: function( parent ){
		this.$el.html( this.template( this.model.toJSON() ) );
		parent.append( this.el );
		this.model.countDown();
		this.model.on( 'change:is_active', function(){
			this.disappear();
		}, this );
	},
	disappear: function(){
		this.$el.fadeOut();
	}
});
var NotesModel = Backbone.Collection.extend({
	model: NoteModel,
	initialize: function( options ){
		this.url = options.url;
	}
});
var Notes = Backbone.View.extend({
	tagName: 'ul',
	className: 'notes',
	initialize: function( options ){
		if( 'undefined' == typeof options ||
			'undefined' == typeof options.url )
			throw 'Can\'t run Note class with undefined url';

		var notes_model = new NotesModel({ url: options.url });
		this.collection = notes_model;
		this.on( 'act', function(){ this.collection.fetch() }, this );
		this.collection.on( 'reset', this.render, this );
	},
	render: function(){
		$( document.body ).append( this.el );
		this.collection.each( function( note, index ){
			var view = new NoteView({ model: note });
			view.render( this.$el );
		}, this );

	}
	
});
