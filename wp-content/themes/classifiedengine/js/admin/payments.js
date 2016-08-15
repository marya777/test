(function($){

// var peopleView = new PeopleView({ collection: peopleCollection });
// $(document.body).append(peopleView.render().el);
	CE.Models.Order = Backbone.Model.extend({
		defaults: {
			method: '',
			icon   : '',
			title  : ''

		}
	});
	CE.Views.OrderItem = Backbone.View.extend({
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

		template : _.template('<div class="method">{{method}}</div> ' +
			'<div class="content"> <span class="price font-quicksand">{{ price }}</span>  {{ icon }}	 ' + 
			'<a target="_blank" href="{{ link }}" class="ad ad-name">{{ post_title }}</a>' +
		' ({{ plan }}) by <a target="_blank" href="{{ author_url }}" class="company">{{ author_name }}</a>' +
		'<div class="note">Payment status : <span class="status">{{ status }}</span>{{ note }}</div>' +
			'</div>'),
		initialize: function(){

			this.blockUi = new CE.Views.BlockUi();
		},
		render : function(){
			this.$el.append( this.template(this.model.toJSON()) )
			return this;
		}

	});

	CE.Views.Payment_Settings = Backbone.View.extend({
		el :'div#engine_setting_content',
		events : {
			'click .et-main-left a' : 'selectPayment',
			'click button#load-more' : 'loadMoreOrder',
			'keyup input.search-ads': 'searchAd'
		},
		initialize : function(){
			this.page = 1;

			this.blockUI = new CE.Views.BlockUi({
				image : et_globals.imgURL + '/loading_big.gif'
			});
			this.loadingBtn = new CE.Views.LoadingButton({
				el : this.$el.find('button.load-more')
			}) ;
			this.container 		= this.$el.find('ul.list-payment');
			this.btnLoadMore 	= this.$el.find('button.load-more')

		},
		page : null,

		selectPayment : function(event){
			var view 	= this;
				this.page =1;
			var payment = $(event.currentTarget).attr('rel');
			view.btnLoadMore.removeClass('is-search');
			$(event.currentTarget).parents('.processor-list').find('a').removeClass('active');
			$(event.currentTarget).addClass('active');
			view.loadOrder( payment, null, this.page, {
					beforeSend : function(){
						this.page ++;
						view.blockUI.block($('.list-payment'))
					},
					success : function( res ){

						view.blockUI.unblock();

						view.container.html('');
						if(res.success){
							_.each( res.data, function(order){

								var item = new CE.Views.OrderItem({
									model : new CE.Models.Order( order )
								});

								if(typeof item.model.get('ID') !='undefined')
									view.container.append( item.render().$el );
								else
									view.container.append(item.model.get('html'));

									});

							if(res.total <= 1)
								view.btnLoadMore.hide();
							else
								view.btnLoadMore.show();
						}	else  {

							view.container.append(res.msg);
							view.btnLoadMore.hide();
						}

					}
				});

			return false;
		},

		loadMoreOrder : function(event){
			var page = this.page +1 ;
			event.preventDefault ();
			var view = this,
				button 		= 	$(event.currentTarget),
				loadingBtn 	= new CE.Views.LoadingButton({ el : $(button)}),
				search = '';
			if($(event.currentTarget).hasClass('is-search'))
				search = $("input.search-ads").val();
			var payment = $('ul.processor-list').find('.active').attr('rel');
			view.loadOrder(payment, search, page,{
				beforeSend : function(){
					view.page ++ ;
					loadingBtn.loading();
				},
				success 	: function(res){
					loadingBtn.finish();

					if(res.total <= view.page )
			 			view.btnLoadMore.hide();

			 		_.each( res.data, function(order){
						var item = new CE.Views.OrderItem({
							model : new CE.Models.Order( order)
						});

						if(typeof item.model.get('ID') !='undefined')
							view.container.append( item.render().$el );
						else
							view.container.append(item.model.get('html'));

					});
			 	}
			});
			return false;
		},

		searchAd : function(event){
			var app 	= this,
				title 	= $(event.currentTarget).val(),
				page 	= 1,
				payment = 'all';

			app.loadOrder( payment, title, page, {
				beforeSend : function(){ 
					app.blockUI.block($('.list-payment')); 
				},
				success : function( res ){
					app.blockUI.unblock();
					app.container.html('');
					app.btnLoadMore.addClass('is-search');

					if(res.success){
						if(res.total <= 1)
		 					app.btnLoadMore.hide();
					} else {
						app.btnLoadMore.hide();
						app.container.append(res.msg);
					}
					_.each( res.data, function(order){
						var item = new CE.Views.OrderItem({
							model : new CE.Models.Order( order)
						});
						if(typeof item.model.get('ID') !='undefined')
							app.container.append( item.render().$el );
						else
							app.container.append(item.model.get('html'));
					});
					if(res.total > res.page)
						app.btnLoadMore.show();
					else
						app.btnLoadMore.hide();


				}
			});
		},

		loadOrder: function( payment,search, page, params){
			var params = $.extend( {
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'et-load-payments',
					payment : payment,
					page 	: page,
					search  : search
				},
				beforeSend: function(){},
				success: function(){}
			}, params );

			$.ajax(params);
		}


	});
	$(document).ready(function(){
		new CE.Views.Payment_Settings();
	});
}(jQuery));