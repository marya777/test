
(function($){
	$(document).ready(function(){
		new CE.Views.BackendSellers();
	});
	var sellerModel = Backbone.Model.extend({
		defaults: {
			display_name: '',
			permalink   : '',
			count_text  : ''

		}
	});
	CE.Views.BackendSeller = Backbone.View.extend({
	tagName : "li",

	events :  {
		'click a.approve_view'	 	: 'approveView',
		'click a.reject_view' 		: 'rejectView'
	},

	className : 'seller-item',

	template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
	},
	template : _.template('<div class="content">' +
			'<a href="{{ permalink }}" class="add">{{ display_name }}</a> <a href="{{permalink }}" class="seller"> - {{ count_text }}</a>' +
		'</div>'),
	initialize: function(){

		this.blockUi = new CE.Views.BlockUi();
	},

	render : function(){
		this.$el.append( this.template(this.model.toJSON()) )

		return this;
	},

	blockItem : function(){
		this.blockUi.block(this.$el);
	},

	unblockItem : function(){
		this.blockUi.unblock();
	},
	/**
	 * approve user view resume
	*/
	approveView : function (e) {
		e.preventDefault();
		var view = this;
		this.model.set('view_resume_status', 'publish');

		this.model.save('view_resume_status', 'publish', {
			beforeSend : function () {
				view.blockItem();
			},
			success : function (model, resp ) {
				view.unblockItem();
				if(resp.success)
					view.$el.remove();
			}
		});
	},
	/**
	 * reject user view reusme
	*/
	rejectView : function (e) {
		e.preventDefault();
		var view = this;
	

		this.model.set('view_resume_status', 'reject');
		this.model.save('view_resume_status', 'reject' ,{
			beforeSend : function () {
				view.blockItem();
			},
			success : function (model, resp ) {
				view.unblockItem();
				if(resp.success)
					view.$el.remove();
			}
		});
	}

});


CE.Views.BackendSellers = Backbone.View.extend({
		el : 'div#seller_setting',

		query_vars : {
			paged : 1
		},

		events : {
			'click .load-more' 			: 'loadMore',
			'keyup #search_seller' 	: 'searchSeller'
		},

		initialize : function(){
			var view	=	this;
			$('.seller-list li.seller-item').each(function(index){
				var $this = $(this);
				var seller = {
					id : $this.attr('data-id'),
					display_name : $this.find('a.ad').html(),
					count_text : $this.find('a.seller').html()
				}

				var view = new CE.Views.BackendSeller({
					model : new sellerModel( seller )
				});
			});

			this.blockUI = new CE.Views.BlockUi({
				image : et_globals.imgURL + '/loading_big.gif'
			});
			this.loadingBtn = new CE.Views.LoadingButton({
				el : this.$el.find('button.load-more')
			}) ;

			var list	=	$('#list_sellers_response').html();

		
		},

		filter : function(args, add){
			var args = _.extend(this.query_vars, args);
			var view = this;

			if ( !add ) 
				add = false;

			$.ajax({
				url : et_globals.ajaxURL,
				data : {
					action : 'ce-backend-search-sellers',
					method : 'read',
					content : args
				},
				beforeSend : function(){
					// block the elements
					if (!add) view.blockUI.block($('.seller-list'))
					else view.loadingBtn.loading();
				},
				success: function(resp, status){

					// unblick elements
					if (!add) view.blockUI.unblock();
					else view.loadingBtn.finish();

					if ( resp.success ){
						// render jobs
						$('ul.seller-list').html('');
						if (resp.data.sellers.length > 0){

							_.each( resp.data.sellers, function(seller){

								var view = new CE.Views.BackendSeller({

									model : new sellerModel( seller.data )
								});

								$('ul.seller-list').append( view.render().$el );

							});

						} else {
							$('ul.seller-list').append( resp.msg );
						}

						// check pageination
						if ( resp.data.pagination.total_page <= resp.data.pagination.paged )
							$('button.load-more').hide();
						else
							$('button.load-more').show();
					}
				}
			});
		},

		loadMore : function(event){
			event.preventDefault();

			this.query_vars.paged++;

			this.filter( this.query_vars, true );
		},

		searchSeller : function(event){
			event.preventDefault();

			var $this = $(event.currentTarget);
			var view = this;

			if ( $('#search_seller').val() == this.query_vars.s ) {
				return false;
			}
			this.query_vars.paged = 1;
			this.query_vars.s = $('#search_seller').val();

			this.timeout = null;

			this.timeout = setTimeout( function(){
				clearTimeout(this.timeout);

				view.filter( view.query_vars, false );
			}, 1000 );
		}
	});
})(jQuery);