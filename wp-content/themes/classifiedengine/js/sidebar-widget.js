(function ($) {
jQuery(document).ready (function ($) {

	CE.Models.TextWidget	=	Backbone.Model.extend({
		defaults : {
			title : '',
			text : '', 
			i 	 : '-1'
		}
	});

	CE.Views.TextSidebar = Backbone.View.extend({
		
		el : $('div#static-text-sidebar'),

		events : {
			'click a.add-more' 				: 'add_more'
		},

		initialize : function () {
			that	=	this;

			/**
			 * initialize and bind event to html render by php
			*/
			$('.widget_text').each ( function () {
				var container	=	$(this),
					title		=	container.find('.widget-title').html(),
					text		=	container.find('.textwidget').html(),
					id			=	container.attr('id');
										
					if(id !== undefined ) {
						var i	=	id.split("-");
						if($('.add-more').length > 0 )
							container.append(
									'<div class="btn-widget edit-remove">' +
									'<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon="p"></span></a>' +
									'<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon="#"></span></a>' +
								'</div>'
							);
						var widget		=	new CE.Models.TextWidget({ i : i[1], text : text,title : title });
						var textwidget	=	new CE.Views.TextWidget({model : widget, el : container });
					}
			});

			$(".sortable" ).sortable({
		        connectWith: ".sortable",
		        axis :"y" ,
		        cursor: "move",
		        cursorAt: { left: 5 },
		        opacity : 0.7,
		        stop: function( event, ui ) {
		        	var $item	=	ui.item,
		        		widgets	=	$item.parents('.sortable').sortable( "toArray" ),
		        		blockUI = 	new CE.Views.BlockUi();
		        	$.ajax ({
		        		type : 'POST',
		        		url : et_globals.ajaxURL ,
		        		data : {
		        			sidebar : $item.parents('.sortable').attr('id'),
		        			action : 'ce-sort-sidebar-widget',
		        			widget : widgets
		        		},
		        		beforeSend : function () {
		        			blockUI.block ($item.parents('.sortable'));
		        		},

		        		success : function (res) {
		        			blockUI.unblock();
		        		}

		        	});
		        } ,
		       handle : '.sort-handle'
		    });

		},
		
		add_more : function ( event ) {
			event.preventDefault ();
			this.addOne (event);
		},

		addOne : function (event) {
			event.preventDefault();
			var target	=	$(event.currentTarget),
				view 	=	this,
				blockui =	new CE.Views.BlockUi();
			$.ajax({
				type : 'get',
				url : et_globals.ajaxURL,
				data : {
					action : 'ce-request-widgetid',
					widget : 'text', 
					sidebar : $('.sidebar').attr('id')
				},
				beforeSend : function () { blockui.block(target);},
				success : function (res) {
					blockui.unblock();
					if(typeof res.i != 'undefined') {
						var	model	=	new CE.Models.TextWidget();
							
						model.set('i', res.i);

						var widget	=	new CE.Views.TextWidget ({model : model });
						view.$('.sidebar').append( widget.renderEdit().el );

					}
				}
			});
			
		}

	});
	

	CE.Views.TextWidget = Backbone.View.extend ({
	
		tagName : 'div',
		className : 'widget_text widget-area',

		events	: {

			'click a.edit'				: 'editWidget',

			'click a.remove'			: 'removeWidget',
			'submit form'				: 'saveWidget'
		},

		text : false,
		edit_template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		edit_template : _.template(

			'<form id="save-widget-text"><div style="display:none;" class="widget-data" >' +
			'<input class="title" name="widget-text[{{ i }}][title]" value="{{ title }}" />' +
	  		'<textarea cols="8" rows="5" class="text" name="widget-text[{{ i }}][text]" >{{ text }}</textarea>' +

	  		'<input type="hidden" name="widget-id" class="widget-id" value="text-{{i}}">' +
			'<input type="hidden" name="id_base" class="id_base" value="text">'+
			'<input type="hidden" name="widget-width" class="widget-width" value="400">'+
			'<input type="hidden" name="widget-height" class="widget-height" value="350">'+
			'<input type="hidden" name="widget_number" class="widget_number" value="{{ i }}">' +
			'<input type="hidden" name="multi_number" class="multi_number" value="">' +
			'<input name="add_new" value="111" type="hidden"> <button class="btn btn-primary">'+et_globals.save+' </button> </div> </div>'
		),

		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		template : _.template (
				'<span class="arrow-right"></span><div class="sort-handle"></div><div class="widget-title customize_heading">{{ title }}</div>' +
				'<div class="textwidget">{{ text }}</div>' +
				'<div class="btn-widget edit-remove">' +
					'<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon="p"></span></a>' +
					'<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon="#"></span></a>' +
				'</div>'
			),

		initialize : function() {			
		},

		editWidget : function ( event ) {
			event.preventDefault();
			var $target =	$(event.currentTarget),
				$widget =	$target.parents('.widget_text');

			this.renderEdit();

			//tinyMCE.execCommand('mceAddControl', false,'static-text');
			$widget.addClass('editting');
		},

		cancelWidget : function  ( event ) {

			event.preventDefault ();

		},

		removeWidget : function (event) {

			event.preventDefault ();
			var $target	=	$(event.currentTarget);
			this.$el.append( this.edit_template( this.model.toJSON() ));
			var str	=	this.$('form').serialize();
			str += '&delete_widget=1';
			this.saveData (str);

			this.remove();

		},

		render : function  () {
			this.$el.html(this.template( this.model.toJSON() ));		
			return this;
		},

		renderEdit : function () {
			this.$el.html( this.edit_template( this.model.toJSON() )).slideDown(200).find('.widget-data').show(300);
			return this;
		},

		/**
		 * submit widget
		*/
		saveWidget : function (event) {

			event.preventDefault();
			var $target	=	$(event.currentTarget);
			var str	=	$target.serialize();
			
			this.saveData(str);

			var title	=	$target.find('.title').val();
			var text	=	$target.find('.text').val();

			this.model.set('title' , title);
			this.model.set('text' , text);
			this.render();
			
		},
		/**
		 * save widget data
		*/
		saveData : function (str) {			
			$.ajax({
				type : 'post',
				data : str + '&action=save-widget&sidebar='+$('.sidebar').attr('id')+'&savewidgets='+$('#savewidgets').val(),
				url  : et_globals.ajaxURL			
			});
		}
	});


	new CE.Views.TextSidebar();

});
})(jQuery);