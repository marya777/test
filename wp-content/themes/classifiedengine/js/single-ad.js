(function ($){
// A List of People
// View for all people
CE.Views.AdRelated = Backbone.View.extend({
	tagName: 'div',
	initialize : function(){
		this.collection.bind('remove', this.removeView, this );

	},

	render: function() {

		this.collection.each(function(Ad) {
			var appView = new CE.Views.AdItem({ model: Ad });
			this.$el.append(appView.render().el);
		}, this);

		return this;
	}

});

// The View for a Person
CE.Views.AdItem = Backbone.View.extend({
	tagName: 'div',
	className :'col-md-4 item-product related-items',


	//template: _.template($('#personTemplate').html() ),
	template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
	},
	template :_.template("<p class='img'><img src='{{ url_thumb }}' /> </p><p class='intro-product'><a href='{{ link }}'><span class='title'>{{ title }}</span><br /> <span class='name'>{{ location }}</span> <br/> <span class='price'>{{ price }} </span></a> </p>"),
	initialize : function(){
		this.model.bind('remove', this.fadeOut, this);
	},

	render: function() {
		// var html=_.template(rowTemplate,this.model.toJSON());
		this.$el.html( this.template(this.model.toJSON()) );
		return this;
	},
	fadeOut : function(){
		this.$el.fadeOut(function(){ $(this).remove(); });
	}
});


CE.Ads_Related = Backbone.View.extend({
	el: '#listing-related',
	events : {
		'click ul.dropdown-menu li' : 'selectListingRrelated',
		//'click .contronl-listing  button.btn123' : 'btnListingRelated'
	},


	initialize : function(){
		// this.collection = new CE.Collections.Ads(JSON.parse($('#listing_data').html())) ;
		this.loading = new CE.Views.BlockUi();
		this.initCarousel();

	},

	initCarousel : function () {
		var width	=	$(window).width(),
			visible	=	4;
		if(width <= 980) {
			visible	=	3;
		} 

		if(width <= 680) {
			visible	=	2;
		} 

		$("#ad_carousel").carouFredSel({
			circular 	: false,
			// responsive  : true,
			infinite    : false,
			cookie   	: false,
			auto    	: false, 
			width 		: '100%',
			// response 	: true,
			items : {
				visible : visible,
			},
			scroll  : {
				items 	: visible
			},
			align : 'left',
			prev    : {
				button  : ".prev-related"
			},
			next    : {
				//onBefore : view.nexPage(),
				button  : ".next-related"

			}
		});

		$( window ).resize(function() {
			var width	=	$(window).width(),
			visible	=	4;
			if(width <= 980) {
				visible	=	3;
			}

			if(width <= 680) {
				visible	=	2;
			}
			$("#ad_carousel").carouFredSel({
				circular 	: false,
				// responsive  : true,
				infinite    : false,
				cookie   	: false,
				auto    	: false,
				width 		: '100%',
				// response 	: true,
				items : {
					visible : visible
				},
				scroll  : {
					items 	: visible
				},
				align : 'left',
				prev    : {
					button  : ".prev-related"
				},
				next    : {
					//onBefore : view.nexPage(),
					button  : ".next-related"

				}
			});
		});
	},

	render: function() {

		this.collection.each(function(Ad) {
			var appView = new CE.Views.AdItem({ model: Ad });
			this.$el.append(appView.render().el);
		}, this);

		return this;
	},


	selectListingRrelated : function (event){

		if($(event.currentTarget).hasClass('selected'))
			return ;
		var $target		= $(event.currentTarget)	,
			type 		= $target.attr('rel'),
			view 		= this,
			contaner 	= $target.closest(".main-center").find('#listing_container'),
			id 			= this.model.get('id'),
			paged 		= 1;

		$target.parents().find("li").removeClass("selected");
		$target.addClass('selected');
		$target.closest(".dropdown-left").find('button span.select').html(type);

		this.loadAdsRelated(
			id, 
			type, 
			paged ,{
			beforeSend: function(){
				view.loading.block(contaner);
			},
			success: function(res) {
				view.loading.finish();
				if(res.success){
					$("div#ad_carousel").html('');
					$('#ad_carousel').trigger('destroy');
					var count = parseInt(res.found_posts);
					for (var i = 0 ; i < res.data.length  ; i++ ) {
						var item	=	res.data[i];
						var template	=	$('#ce-single-related');
						if(item.template_id) {
							var template	=	$('#ce-single-related_'+ item.template_id);
						}

	            		var template_e   = _.template( template.html() );
	            		$('#ad_carousel').append ( template_e(item) );
					};

					if(i < 5){
						view.$el.find('div.contronl-listing').css('visibility','hidden');
					}
					view.initCarousel();
				}
			}
		});

	},

	loadAdsRelated: function(id, type, page, params){
		var params = $.extend( {
			url: et_globals.ajaxURL,
			type: 'post',
			data: {
				action: 'et-listing-related',
				content: {
					paged 	: page,
					id 		: id,
					type 	: type
				}
			},
			beforeSend: function(){},
			success: function(){}
		}, params );

		$.ajax(params);
	}
});


CE.Views.Single_Ad	=	Backbone.View.extend({
	el : 'body.single-classified',

	events : {
		'click .button-event li.edit' 	 		: 'onEdit',
		'click .button-event li.archive' 		: 'archiveAd',
		'click .button-event li.approve' 		: 'approveAd',
		'click .button-event li.reject' 		: 'rejectAd',
		'click .button-event li.toggleFeature'  : 'toggleFeature',
		'click .btn-zoom'						: 'toggleSlider',
		'click #bx-pager img'					: 'slideTo',
		'click a#add_fovorite' 					: 'addFavorite',

	},

	initialize : function () {
		var ad		=	JSON.parse($('#single_ad_data').html()),
			view	=	this;

		this.model			=	new CE.Models.Ad (ad);
		this.editModalView	=	new CE.Views.Modal_Edit_Ad();
		this.rejectModal 	= 	new CE.Views.Modal_Reject();

		pubsub.on('ce:afterEditAd' , this.afterEdit , this );
		pubsub.on('ce:afterRejectAd' , this.afterRejectAd , this );

		this.options	=	{ beforeSend : function () {view.beforeSend()}, success : function (model, res) {view.success(model, res)} };

		if( $('.heading-message').length > 0 ) {

			this.template	=	 _.template($('#single-ad-button').html());
			this.model.on('change',this.renderControl, this );

			this.heading_template	=	_.template($('#single-ad-heading-msg').html());
			$('.heading-message').html(this.heading_template( this.model.toJSON())).slideDown(200) ;

		}
		this.initSlide();

		new CE.Ads_Related({model : this.model });

	},

	initSlide : function () {
		$('.bg-slide-listing').animate({'opacity':'1'},500);
		$(".slide-listing").carouFredSel({
                // responsive : true,
                direction : 'right',
                // pagination : '#bx-pager',
                align : 'center',
                circular: false,
                auto : false,
                //height : 'variable',
                scroll : {
                    fx : 'uncover',
                    easing : "linear",
                    duration        : 500,
                    timeoutDuration : 1000
                },
                item : {
                    //height : 'variable',
                    visible : 1
                },
                next : {
                    // button : '.next-slide',
                    onBefore : function (data) {
                        // console.log(data);
                        var id  =   data.items.visible.eq(0).attr('id');
                        var old =   parseInt(data.items.old.eq(0).attr('id')) + 1;

                        if( id == '11' && old  < 4 ) {
                            $('.contronl-slide').trigger('prev', 1);
                        } else
                        $('.contronl-slide').trigger('next', 1);


                    }

                },
                prev : {
                    // button : '.prev-slide',
                    onBefore : function (data) {

                        var id  =   data.items.visible.eq(0).attr('id');
                        var old =   parseInt(data.items.old.eq(0).attr('id')) + 1;

                        if( id == '0' && old <= $('#bx-pager a').length ) {
                            $('.contronl-slide').trigger('next', 1);
                        } else
                            $('.contronl-slide').trigger('prev', 1);
                        // console.log('prev');
                    }
                }
            });

            $('.contronl-slide').carouFredSel ({
                // responsive : true,
                synchronise: ['#images', false, true],
                infinite : true,
                items : 6,
                scroll  : {
                    items           : 3,
                    duration        : 400,
                    timeoutDuration : 0,
                    easing          : "linear"
                },
                auto    : false,
                // prev    : ".next-slide",
                // next    : ".prev-slide",
                height : 85,
                width : "100%",
                align : 'center',
                next : {
                    button : '.next-slide',
                },
                prev : {
                    button : '.prev-slide',
                }
            }).parent().css("margin", "auto 0");

	},

	beforeSend : function () {
		if(typeof this.blockUi === 'undefined')
			this.blockUi	=	new CE.Views.BlockUi();
		this.blockUi.block ( this.$el.find('.single-ad-title .button-event') );
	},

	success : function (model, res) {
		this.blockUi.unblock();
		if(res.success) {
			pubsub.trigger('ce:notification', {notice_type : 'success' , msg : res.msg});
		} else {
			pubsub.trigger('ce:notification', {notice_type : 'error' , msg : res.msg})
		}
	},

	onEdit : function (e) {
		e.preventDefault();
		this.editModalView.onEdit(this.model);
	}, 
	afterEdit : function (res) {
		if(res.success) {
			window.location.reload();
		}
	},
	archiveAd : function (e) {
		e.preventDefault();
		this.model.expire(this.options);
	},
	rejectAd : function (e) {
		var view = this;
        e.preventDefault();
       // pubsub.trigger('ce:ad:onReject',this.model);
       	//this.model.reject({ beforeSend : function () {view.rejectModal.onReject(view.model.get('ID'));view.beforeSend()}, success : function (model, res) {view.success(model, res)} })
    	this.model.set('is_single',true);
        this.rejectModal.onReject(this.model);
        //this.model.reject(this.options);
	},
	afterRejectAd : function(model,res){
		this.model = model;
		this.renderControl();
	},

	approveAd : function (e) {
		e.preventDefault();
		this.model.approve(this.options);
	},

	toggleFeature : function (e) {
		e.preventDefault();
		var $target	=	$(e.currentTarget);
		if($target.hasClass('featured')) {
			this.model.toggleFeatured( 0,this.options );
		} else {
			this.model.toggleFeatured( 1,this.options );
		}

	},

	toggleSlider : function (e) {
		var $target	=	$(e.currentTarget);
		if($target.hasClass('in')) {

            // $('.bg-slide-listing').animate({'width':'1051px'},500);
            var top	=	'595px';
            /**
             * if have thumnails change top length
            */
            if($('.bg-slide-thumbnails').length > 0 ) top	=	'680px';
            $('.seller-profile').parents('.col-md-4').animate({'margin-top': top },400 , function () {

            	$target.removeClass('in',500).find('.fa').addClass('fa-compress').removeClass('fa-expand');
	            $(".slide-listing").addClass('out');

	            $('.slide-listing-wraper').find('img').css('opacity', 0);

            	$('.bg-slide-listing').animate({'width':'1051px'},500);
	            $('.bg-slide-thumbnails').animate({'width':'1051px','max-width':'1051px'},500);

	            var temp    =   560;
	            $('.caroufredsel_wrapper ul li').css('height', 563).each(function() {
	                var height = $(this).find('img').height();
	                height  =   parseInt(height);
	                if(temp < height ) temp = height;
	                $(this).animate({'width':'1051px','height': '563px'}, 500);
	            });

	            $('.slide-thumbnails .caroufredsel_wrapper').animate({'margin-left' : '130px'});
	            $(".slide-listing").trigger("configuration", [ "height", temp ] );

	            $('.slide-listing-wraper').find('img').each(function() {
	            	var src = $(this).attr('data-full');
	            	$(this).attr('src', src);

	            });
	            $('.slide-listing-wraper img').animate({'opacity' : 1 }, 1000);
            });


        }

        else {

        	$('.slide-listing-wraper').find('img').css('opacity', 0);

            $('.bg-slide-listing').animate({'width':'712px'},500);
            $('.bg-slide-thumbnails').animate({'width':'712px','max-width':'712px'},500);
            $('.caroufredsel_wrapper ul li').animate({'width':'712px','height':'302px'},500);

             $('.slide-thumbnails .caroufredsel_wrapper').animate({'margin-left' : '0px'});

            $('.seller-profile').parents('.col-md-4').animate({'margin-top':'0px'},500);

            $(".slide-listing").trigger("configuration", [ "height", 302 ] );
            $target.addClass('in', 1000).find('.fa').removeClass('fa-compress').addClass('fa-expand');
            $(".slide-listing").removeClass('out');

            $('.slide-listing-wraper').find('img').each(function() {
            	var src = $(this).attr('data-normal');
            	$(this).attr('src', src);
            });
            $('.slide-listing-wraper img').animate({'opacity' : 1 }, 1000);
        }
	},

	slideTo : function (e) {
		e.preventDefault();
		var $target	=	$(e.currentTarget),
        	$a  	=   $target.parents('a');

        $('#bx-pager a').removeClass('active');
        $a.addClass('active');
        $(".slide-listing").trigger('slideTo' , [ parseInt($a.attr('data-slide-index')), true]);

	},

	renderControl : function () {

		var html	=	this.template(this.model.toJSON());
		$('ul.button-event').html(html);

		if( $('.heading-message').length > 0 ) {
			var html	=	this.heading_template(this.model.toJSON());
			$('.heading-message').html(html);
		}

	},

	addFavorite : function (event){
		var $target = $(event.currentTarget),
			view 	= this,
			ad_id 	= $target.attr('rel'),
			type 	= ($target.hasClass('active') == false ) ? 'remove' : 'add' ,
			logged  = $target.hasClass('logged');
			view.blockUi	=	new CE.Views.BlockUi();
		if(!logged){
			pubsub.trigger('et:request:auth',this);
			// setTimeout(function(){
			// 	$(window).scrollTop(0);
			// },300);
			return false;
		}

		var t = $.ajax({
				url		: et_globals.ajaxURL,
				type   	: 'get',
				data   	: { id : ad_id , action : 'et-update-favorite', type :type },
				beforeSend : function () {
					view.blockUi.block ( $target );
				},
				success : function (res) {
					$target.toggleClass('active');
					setTimeout(function(){
						view.blockUi.unblock();
					},300);

				}
		});
		return false;
	}


});
	CE.Views.Comments = Backbone.View.extend({
		el :'#ad_comment',
		events : {
			//'submit .comment-form' : 'submitComment',
			'click p.must-log-in a' 		: 'triggerPopupLogin',
			'click a.comment-reply-login' 	: 'triggerPopupLogin',
			'click .comment-reply-title' 	: 'togleFormComment',
			'click .comment-reply-link' 	: 'showCommentForm',
			'click #reCaptcha a.btn-reload' 	: 'reloadImgDefault',
		},
		initialize : function(){

			pubsub.on('et:response:auth', this.afterLogin, this);
			$(this.el).find("form").validate({
			 	rules: {
			 		author 		: 'required',
				    comment 	: 'required',
	    			email : {
	    				required: true,
	    				email 	: true,
	    			},
	    			recaptcha_response_field : 'required',
	    		}
		    });

			this.count = 0;
		},
		showCommentForm : function(event){
			$("form#commentform").show();
		},
		togleFormComment : function(event){

			var $target = $(event.currentTarget),
				view 	= this;

			$("form#commentform").show();
			$target.find("span").toggleClass('fa-arrow-down');
			$target.find("span").toggleClass('fa-arrow-up');
			console.log($target.find("form"));
			if($target.find("span").hasClass('fa-arrow-down'))
					$("form#commentform").hide();

		},

		afterLogin : function(data, status, jqXHR){
			if(data.status)
				location.reload();
		},
		reloadImgDefault : function(){
			Recaptcha.reload();
			return false;
		},



		triggerPopupLogin : function (e){
			e.preventDefault();
			pubsub.trigger('et:request:auth',this);
			setTimeout(function(){
				$(window).scrollTop(0);
			},300);
			return false;

		}
	});

	$(document).ready(function(){

		if($(("div#ad_comment").length > 0 )) {
			new CE.Views.Comments;
		}
		jQuery("a.item-slider").fancybox({
			'titlePosition' :'inside',
		});

	});

})(jQuery);
