// declare everything inside this object

(function ($){	/**
	 * seller listing view
	*/
	//End view Seller

	CE.Views.List_seller	=	Backbone.View.extend({
		el : 'body',
		events : {
			'keyup #frm_list_seller input' 				: 'searchSeller',
			//'focusout #frm_list_seller input' 	: 'searchSellerAuto',
			'submit form#frm_list_seller'				: 'submitSearch',
			'click .ce-list-locations ul.menu-child li' : 'filterByLocation',
			'click .border-bottom'			  			: 'filterByLocation'
			// 'click .ce-list-locations ul li' 	: 'triggerClickLoction' 
		},

		initialize : function () {

			var view		= this;
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};
			
			this.template 	=	_.template($('#seller_item_template').html());

			view.paged		=	1;

			$('#inview').bind('inview' , function (event , isVisible) {
				view.inView(event , isVisible);
			} );

		},

		inView : function (event , isVisible) {
			var view 	=	this;
			if (!isVisible) { console.log('!isVisible'); return; }
			var view	=	this,
				name 	= 	$("#frm_list_seller").find('input[name=seller_name]').val(),
				local 	= 	$("#frm_list_seller").find('input[name=seller_location]').val();
			console.log(view.paged);
			var params = $.extend( {
				url: et_globals.ajaxURL,
				type: 'post',
				data: {
					action: 'ce-load-more-seller',
					content: {
						name: name,
						local: local,
						paged : view.paged
					}
				},
				beforeSend: function(){
					view.paged ++;
				},
				success: function(res){
					//view.blockUI.unblock();		
					var $seller_list	=	$('#seller-list');
					if( !res.status ) {
						// $seller_list.html('');
						// $seller_list.append('<div class=" no-result col-md-4 col-md-12 item-product"> '  +res.msg + '</div>');
						//$('ul.pagination').html('');
						$('#inview').hide();
					} else {
						var sellers	=	res.data;

						// $seller_list.html('');
						_.each(sellers, function (element ) {
							$seller_list.append(view.template(element));
						});
						console.log(res.total_pages);
						if(res.total_pages == view.paged) $('#inview').hide();
					}
					// $('.sellers-amount').html(res.header_msg);
				}
			}, params );

			$.ajax(params);
		
		}, 

		submitSearch : function (event) {
			event.preventDefault();
			return false;
		},
		searchSellerAuto : function (event){
			var local 	= 	$("#frm_list_seller").find('input[name=seller_location]').val();
			console.log(local.length);
			if(local.length < 1)
				return false;
			this.searchSeller();
		},

		searchSeller : function () {
			var view	=	this,
				name 	= 	$("#frm_list_seller").find('input[name=seller_name]').val(),
				local 	= 	$("#frm_list_seller").find('input[name=seller_location]').val();
			
			view.paged	=	1;
			var params = $.extend( {
				url: et_globals.ajaxURL,
				type: 'post',
				data: {
					action: 'ce-search-seller',
					content: {
						name: name,
						local: local,
					}
				},
				beforeSend: function(){
					//view.blockUi.block($('.btn'));
					//view.blockUI.block($('div#seller-list'))
				},
				success: function(res){
					//view.blockUI.unblock();		
					var $seller_list	=	$('#seller-list');
					if( !res.status ) {
						$seller_list.html('');
						$seller_list.append('<div class=" no-result col-md-4 col-md-12 item-product"> '  +res.msg + '</div>');
						$('ul.pagination').html('');
					} else {
						var sellers	=	res.data;

						$seller_list.html('');
						_.each(sellers, function (element ) {
							$seller_list.append(view.template(element));
						});

						if( res.paginate != '' ) {
							$('ul.pagination').html(res.paginate);	
							$('#inview').show();
						} else {
							$('#inview').hide();
							$('ul.pagination').html('');
						}

					}

					$('.sellers-amount').html(res.header_msg);
				}
			}, params );

			$.ajax(params);
		},

		filterByLocation : function (event){
			event.preventDefault();
			var $target = $(event.currentTarget),
			 	$location_input = $("input#seller_location");

			$location_input.val($target.find('a:first').text());				
			this.searchSeller();	
		}
	});
	

})(jQuery);
