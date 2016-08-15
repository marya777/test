// initialize variables


(function($){

	$(document).on('pageinit',function() {

		$("#tw_auth_btn").on('tap' , function(event) {
			var $target = $(event.currentTarget);
			var link = $target.attr("href");
			window.location = link;

			return false;
		});

		var query_default = {
			type		: 'get',
			url			: et_globals.ajaxURL,
			data : {
				action		: 'et-mobile-fetch-ad',
				paged		: 1,
				ad_location	: et_globals.ad_location || [],
				ad_category : et_globals.ad_cat || [],
				product_cat : et_globals.ad_cat || [],
				s			: ''
			},
			beforeSend	:	function () {
			    		// $('#inview').unbind('inview');
			    		$.mobile.loading( 'show', {
							text: et_globals.loading_text,
							textVisible: true,
							theme: 'c',
							html: ""
						});
			    	}

		};

		var seller_query = {
			type		: 'get',
			url			: et_globals.ajaxURL,
			data : {
				action		: 'ce-load-more-seller',
				paged		: 1,
				content		: {
					name : '' ,
					paged : 1
				}
				// ad_location	: et_globals.ad_location || [],
				// ad_category : et_globals.ad_cat || [],
				// s			: ''
			},
			beforeSend	:	function () {
			    		// $('#inview').unbind('inview');
			    		$.mobile.loading( 'show', {
							text: et_globals.loading_text,
							textVisible: true,
							theme: 'c',
							html: ""
						});
			    	}

		};

		var review_query = {
			type		: 'get',
			url			: et_globals.ajaxURL,
			data : {
				action		: 'ce-load-more-reviews',
				paged		: 1,
				author		: '',
				type		: 'all'
				// ad_location	: et_globals.ad_location || [],
				// ad_category : et_globals.ad_cat || [],
				// s			: ''
			},
			beforeSend	:	function () {
			    		// $('#inview').unbind('inview');
			    		$.mobile.loading( 'show', {
							text: et_globals.loading_text,
							textVisible: true,
							theme: 'c',
							html: ""
						});
			    	}
		}

		var no_data	=	false;
		var no_seller	=	false;
		var blog_page = 1;
		var timer = null;

		/**
		 * jquery mobile
		*/
		// if(hideLeftPush != null)		
		$( '.hideLeftPush' ).on('tap', function(event) {
			var menuLeft 		= $( '.cbp-spmenu-s1' ),
				showLeftPush 	= $( '.showLeftPush' ),
				hideLeftPush 	= $( '.hideLeftPush' ),
				headerLeftPush 	= $( '.headerLeftPush' ),
				body = document.body;

			$(this).toggleClass('active' );
			$('body').toggleClass('cbp-spmenu-push-toright' );
			menuLeft.toggleClass('cbp-spmenu-open' );
			hideLeftPush.toggleClass('disabled' );

		});

		// if(headerLeftPush != null)		
		$( '.headerLeftPush' ).on ( 'tap' , function( event ) {
			var $target			= $(event.currentTarget);
			var menuLeft 		= $( '.cbp-spmenu-s1' ),
				hideLeftPush 	= $( '.hideLeftPush' ),
				headerLeftPush 	= $( '.headerLeftPush' ),
				body = document.body;

			$(this).toggleClass('active' );
			$('body').toggleClass('cbp-spmenu-push-toright' );
			menuLeft.toggleClass('cbp-spmenu-open' );

			headerLeftPush.toggleClass('disabled' );

		});

		/**
		 * change tab in single ad
		*/
		$(".tabs .ui-tabs").on('tap',function(){

			var index = $(this).index();
			$(".tabs .ui-tabs").removeClass("tab-active");
			$(this).addClass("tab-active");

			$(".content-tabs .tab-cont").fadeOut(50);
			$(".content-tabs .tab-cont").eq(index).fadeIn(100);
			$visibility = $("#tab_gmap").css('visibility');

			if($visibility == 'hidden')
				$("#tab_gmap").css('visibility','visible');

		});

		$('.open-mail').on('tap', function () {
			$(".tabs .ui-tabs").removeClass("tab-active");
			$(".content-tabs .tab-cont").fadeOut(50);
			$('.mail').addClass('tab-active');
			$('.mail-form').fadeIn(100);
		});
		/**
		 * toggle cat, location in search option
		*/
		$('.category-items li a').on('tap', function () {
			$(this).toggleClass('active');
		});

		/**
		 * live search
		*/
		$('.txt_search').on('keyup', function () {
			if ( timer !== null ){
				clearTimeout(timer);
			}
			timer = setTimeout(function(){
				$.searchAd();
			}, 300);
		});

		/**
		 * search ad
		*/
		$('.search-button').on('tap' , function() {
			$.searchAd();
			$(".search .search-btn").removeClass('tapped');
		});

		/**
		 * single ad contact seller
		*/
		$('form#contact-seller').on('submit', function(e){
			e.preventDefault();
			var data	=	$(this).serialize();

			$.ajax({
				type : 'post',
				data : data,
				url : et_globals.ajaxURL,
				beforeSend : function () {
					$.mobile.loading( 'show', {
						text: et_globals.loading_text,
						textVisible: true,
						theme: 'c',
						html: ""
					});
				},
				success : function (res) {
					$.mobile.loading('hide');
					if(res.status) {
						alert(res.msg);
					}else {
						alert(res.msg);
					}

					if ( timer !== null ){
						clearTimeout(timer);
					}
					timer = setTimeout(function(){
						$.mobile.loading('hide');
					}, 2000);

				}
			})
			//ce-send-seller-message
		});

		/**
		 * bind eview event to load more ads in home
		*/
		mobileInview();

		/**
		 * bind eview event to load more ads in template author
		*/
		$('#author-inview').bind('inview', function(event, isVisible) {
	      	if (!isVisible) { console.log('!isVisible'); return; }
	    	if(no_data) { return; }

	    	query_default.data.author = $('#author-inview').attr('data-author');
	    	query_default.success	=	function (res) {
	    		// hide loading
	    		$.mobile.loading('hide');
	    		if(!res.success) {
	    			no_data	=	true;
	    			return;
	    		}else {
		    		/**
		    		 * trigger flag when reach the max page, prevent load no data
		    		*/
		    		if(res.the_last)  no_data	=	true;
		    		//fill data in list view
		    		$.mobileFillData(res)
		    		/**
		    		 * increase page offset
		    		*/
		    		query_default.data.paged	+= 1;
	    		}
	    	};

	    	$.ajax(query_default);
		});

		/**
		 * bind eview event to load more ads in template author
		*/
		$('#review-inview').bind('inview', function(event, isVisible) {
	      	if (!isVisible) { console.log('!isVisible'); return; }
	    	if(no_data) { return; }

	    	review_query.data.author 	= $('#review-inview').attr('data-author');
	    	review_query.data.type 		= $('#review-inview').attr('data-review');
	    	review_query.success	=	function (res) {
	    		// hide loading
	    		$.mobile.loading('hide');
	    		if(!res.success) {
	    			no_data	=	true;
	    			return;
	    		}else {
		    		/**
		    		 * trigger flag when reach the max page, prevent load no data
		    		*/
		    		if(res.the_last)  no_data	=	true;
		    		//fill data in list view
		    		$.mobileFillReviews(res.data)
		    		/**
		    		 * increase page offset
		    		*/
		    		review_query.data.paged	+= 1;
	    		}
	    	};

	    	$.ajax(review_query);
		});


		/**
		 * load more seller in sellers list
		*/
		$('#seller-list-inview').bind('inview', function(event, isVisible) {
	      	if (!isVisible) { console.log('!isVisible'); return; }
	    	if( no_seller ) { return; }

	    	// seller_query.data.author = $('#author-inview').attr('data-author');
	    	seller_query.success	=	function (res) {
	    		// hide loading
	    		$.mobile.loading('hide');
	    		if(!res.status) {
	    			no_seller	=	true;
	    			return;
	    		}else {
		    		/**
		    		 * trigger flag when reach the max page, prevent load no data
		    		*/
		    		if(res.the_last)  no_seller	=	true;
		    		_.templateSettings = {
					    evaluate    : /<#([\s\S]+?)#>/g,
						interpolate : /\{\{(.+?)\}\}/g,
						escape      : /<#-([\s\S]+?)#>/g,
					};
		    		var template	=	_.template( $('#ce_seller_template').html() );
		    		//fill data in list view
		    		for(var i = 0; i < res.data.length ; i++) {
						var item 		=	res.data[i];
		    			$('.seller-list').append( template(item) );
		    			// $('.latest-list').listview('refresh');
		    			// return;
		    		}

		    		$('.seller-list').listview('refresh');

		    		/**
		    		 * increase page offset
		    		*/
		    		seller_query.data.paged	+= 1;	
		    		seller_query.data.content.paged += 1;
	    		}
	    	};

	    	$.ajax(seller_query);
		});


		/**
		 * Add function auto hint search in input search location
		 */
	    $('#load-more-post').bind('inview', function (event , isVisible ) {
	    	if (!isVisible) { console.log('!isVisible'); return; }
	    	if( no_data ) { return; }

	    	event.preventDefault ();
			var $target			=	$(event.currentTarget),
				$template		=	$target.parents('.button-more').find('input#template'),
				$list_payment	=	$('.list-blog ul'),
				page			=	blog_page + 1;


			$.ajax ({
				url : et_globals.ajaxURL,
				type : 'post',
				data : {
					page			: page,
					action			: 'et-mobile-load-more-post',
					template_value	: $template.val(),
					template		: $template.attr('name')
				},
				beforeSend : function () {
					blog_page ++ ;
					$.mobile.showPageLoadingMsg();
				},
				success : function (response) {
					$.mobile.hidePageLoadingMsg();
					if(response.success) {
						$list_payment.append (response.data);

						if( blog_page >= response.total ){
							no_data =  true;
							// console.log(blog_page);
						}

					}else {

						no_data = true
						blog_page--;

					}
				}
			});
	    });

		/**
		 * bind event to inview image
		*/
		$('img.wp-post-image').bind('inview', function(event, isVisible) {
			if (!isVisible) { return; }

			var img = $(this);
			// Show a smooth animation
			//img.css('opacity', 0);
			img.animate({ opacity: 1 }, 500);
			// Change src
			img.attr('src', img.attr('data-src'));

			// Remove it from live event selector
			img.removeAttr('data-src');
			img.trigger('create');
		});

		$.searchAd	=	function () {
			/*console.log('search trigger');*/
			var $list	=	$('.latest-list');
			no_data	=	false;
			query_default.data.ad_category = [];
			query_default.data.ad_location = [];
			query_default.data.s =	$('#txt_search').val();
			/**
			 * reset paged to 0
			*/
			query_default.data.paged	=	0;

			$('.category-items li a.active').each(function(){
				var tax		=	$(this).attr('data-tax'),
					slug	=	$(this).attr('data');
				query_default.data[tax].push(slug);
			})

			//$('.latest-list').html('');
			query_default.success	=	function (res) {
	    		$.mobile.loading('hide');
	    		if(!res.success) {
	    			$('.latest-list').html('');
	    			no_data	=	true;
	    			$list.append('<li class="no-result list-divider ui-li ui-li-static ui-btn-up-c ui-first-child"><h2>'+res.msg+ '</h2></li>');
	    			return;
	    		} else {

		    		if(res.the_last)  no_data	=	true;
		    		$('.latest-list').empty();

		    		$.mobileFillData(res);
		    		query_default.data.paged	+= 1;

		    		if( $('.page404').length > 0 ) {
			    		$('.inview').attr('id', 'inview');
			    		$('.page404').remove();
			    		/**
						 * bind eview event to load more ads in home
						*/
						mobileInview();
			    	}
			    }
	    	};

	    	$.ajax(query_default);


	   //  	query_default.data.paged	=	1;
		}

		$.mobileFillData	=	function (res) {
			// console.log('fill data');

    		for(var i = 0; i < res.data.length ; i++) {

				var item 		=	res.data[i],
					featured	=	parseInt(item[et_globals._et_featured]);
				_.templateSettings = {
				    evaluate    : /<#([\s\S]+?)#>/g,
					interpolate : /\{\{(.+?)\}\}/g,
					escape      : /<#-([\s\S]+?)#>/g,
				};
				var template	=	_.template( $('#ce_mobile_ad_template').html() );
				if(item.template_id) {
					if( $('#ce_mobile_ad_template_'+item.template_id).length > 0 )
						var template	=	_.template( $('#ce_mobile_ad_template_'+item.template_id).html() );
				}
				/**
    			 * not append list divider if is author
    			*/
    			if( typeof query_default.data.author == 'undefined') {
		    		if( featured == 1  && $('.featured-divider').length < 1 ) {
				        $('.latest-list').append('<li class="list-divider featured-divider"><h2>'+res.featured+'</h2></li>');
				    }
				    if( featured != 1 && $('.latest-divider').length < 1 ) {
				        $('.latest-list').append('<li class="list-divider latest-divider"><h2>'+res.latest+'</h2></li>');
				    }
    			}

    			$('.latest-list').append( template(item) );
    			// $('.latest-list').listview('refresh');
    			// return;
    		}

    		$('.latest-list').listview('refresh');
		}

		$.mobileFillReviews = function (data) {
			_.templateSettings = {
				    evaluate    : /<#([\s\S]+?)#>/g,
					interpolate : /\{\{(.+?)\}\}/g,
					escape      : /<#-([\s\S]+?)#>/g,
			};

			var template	=	_.template( $('#review_item_template').html() );

			for(var i = 0; i < data.length ; i++) {

				var item 		=	data[i];

    			$('.list-reviews').append( template(item) );

    		}

    		$('.list-reviews').trigger('create');
		}



		/**
		 * inview load more ad
		*/
		function mobileInview () {
			/**
			 * bind eview event to load more ads in home
			*/
			$('.inview').bind('inview', function(event, isVisible) {
		      	if (!isVisible) { return; }
		    	if(no_data) { return; }

		    	query_default.success	=	function (res) {
		    		// hide loading
		    		$.mobile.loading('hide');
		    		if(!res.success) {
		    			$("li.no-result").show();
		    			no_data	=	true;
		    			return;
		    		}
		    		$('.no-result').hide();
		    		/**
		    		 * trigger flag when reach the max page, prevent load no data
		    		*/
		    		if(res.the_last)  no_data	=	true;
		    		//fill data in list view
		    		$.mobileFillData(res)
		    		/**
		    		 * increase page offset
		    		*/
		    		query_default.data.paged	+= 1;

		    	};
		    	// query_default	=	_.extend ({success : function () { } })
		    	$.ajax(query_default);
			});
		}


		/**
		 * Loc viet headroom. header slide up and down
		*/
		var height_1= $('.ui-page .aminate-header').height();
		var lastScrollTop = 0;
		$(window).scroll(function(){
			var st = $(this).scrollTop();
			if (st > lastScrollTop && st > 90){
				// scrolling down
				if($('.ui-page .aminate-header').data('size') === 'big')
				{
					$('.ui-page .aminate-header').data('size','small');
					$('.ui-page .aminate-header').stop().animate({top:'-' + 90 +'px',opacity: '0'},100, 'easeOutExpo');
				}
			}
			else
			{
				// scrolling up
				if($('.ui-page .aminate-header').data('size') === 'small')
				{
					$('.ui-page .aminate-header').data('size','big');
					$('.ui-page .aminate-header').stop().animate({top:'0',opacity: '1'},600, 'easeOutExpo');
				}
			}
			lastScrollTop = st;
		});
		/**
		 * end headroom by Loc
		*/
	} );

} )(jQuery);