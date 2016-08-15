(function($) {

	CE.Views.AdList = Backbone.View.extend({
		initialize: function() {

			var view = this;
			this.listenTo(this.collection, 'add', this.addOne);
			this.listenTo(this.collection, 'unshift', this.addOne);
			this.listenTo(this.collection, 'remove', this.removeOne);

			this.listenTo(this.collection, 'all', this.addAll);

			//this.collection.fetch();
			this.list_view = [];
			var i = 0;
			this.collection.each(function(ad, index, coll) {

				var el = view.$('div.col-md-4:eq(' + index + ')');
				if (el.length !== 0) {
					var itemView = new CE.Views.AdItem({
						el: el,
						model: ad
					});
					view.list_view[index] = itemView;
				}

			});

		},

		render: function() {

		},

		addOne: function(ad, col, options) {

			var itemView = new CE.Views.AdItem({
				model: ad
			}),
				$itemEl = itemView.render().$el.hide(),
				$existingItems = this.$el.find('div.item-product'),

	            index       = ( options && 'at' in options) ? options.at : $existingItems.length ,
	            position    =  $existingItems.eq(index);

	        if ( this.list_view.length === 0 || position.length == 0 ){
	            $itemEl.appendTo(this.$el).fadeIn('slow') ;
	        }
	        else{
	            $itemEl.insertBefore(position).fadeIn('slow');
	        }


			//this.$el.prepend ( itemView.render().el );
			this.list_view.splice(index, 0, itemView);
		},

		removeOne: function(ad, col, options) {
			// remove the ad item view from the array listView
			var itemView = this.list_view.splice(options.index, 1);

			if (itemView.length > 0) {
				itemView[0].$el.fadeOut('slow', function() {
					itemView[0].remove().undelegateEvents();

					// after hiding the removed ad, publish this event to add the ad to the correct collection
					pubsub.trigger('ce:ad:afterRemoveAdView', ad);
				});
			}
		},

		addAll: function() {

		}

	});


	CE.Views.AdItem     =   Backbone.View.extend({
	    tag : 'div',
	    className : 'item-product col-md-4',
	    //template: _.template($('#ad-item-template').html()),
	    events : {
	        'click .button-event .approve'          : 'approveAd',
	        'click .button-event .reject'           : 'rejectAd',
	        'click .button-event .archive'          : 'archiveAd',
	        'click .button-event .edit'             : 'editAd',
	        //'click .button-event .remove'          	: 'removeAd',
	        'click .button-event .toggle-feature'   : 'toggleFeatured'
	    },

	    initialize : function () {
	        if(this.model) {
	            this.model.on('change',this.render,this);
	        }

	        this.blockUi    =   new CE.Views.BlockUi();
	        _.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};

	        this.template   =   $('#ad-item-template');

	        if( this.model.get('template_id') ) {
	        	this.template	= $('#ad-item-template_'+this.model.get('template_id') );
	        }

	        if (this.template.length>0)
	            this.template   = _.template(this.template.html());

	        // pubsub.on ('ce:ad:beforeSend', this.beforeSend);
	        // pubsub.on ('ce:ad:success', this.success);
	        var view    =   this;
	        this.options    =   {beforeSend : function () {view.beforeSend()}, success : function (model, res) {view.success(model, res)} };

	    },

	    render : function () {
	        this.$el.html(this.template(this.model.toJSON()));
	        this.$el.addClass(this.className);
	        $('.tooltips').tooltip({
      			selector: "a[data-toggle=tooltip]"
 			});
	        return this;
	    },

	    beforeSend : function () {
	        if(typeof this.blockUi === 'undefined')
	            this.blockUi    =   new CE.Views.BlockUi();
	        this.blockUi.block ( this.$el );
	    },

	    success : function (model, res) {
	        this.blockUi.unblock();
	        if(res.success) {
	            pubsub.trigger('ce:notification', {notice_type : 'success' , msg : res.msg});
	        } else {
	            pubsub.trigger('ce:notification', {notice_type : 'error' , msg : res.msg})
	        }

	    },

	    approveAd : function (event) {
	        event.preventDefault();
	        var view    =   this;
	        // var options  =   {beforeSend : function () {view.beforeSend()}, success : function (model, res) {view.success(model, res)} };
	        this.model.approve(this.options);
	        //this.remove();
	    },

	    rejectAd : function (event) {
	        event.preventDefault();
	        var ad = this;
	        pubsub.trigger('ce:ad:onReject',this.model);
	        // var options  =   {beforeSend : function () {view.beforeSend()}, success : function (model, res) {view.success(model, res)} };
	        //this.model.reject(this.options);
	    },

	    archiveAd : function (event) {
	        event.preventDefault();
	        // var view     =   this;
	        this.model.expire(this.options);
	        //this.remove();
	    },
	    removeAd : function (event) {
	    	var view = this;
	       var ad 		= new CE.Models.Ad({
				id :this.model.get('ID'),
				ID : this.model.get('ID'),
				update_type : 'change_status',
				post_status :'draft'
 			});
			ad.save('','',{
					beforeSend: function(){
						//view.loadingBtn.loading();
					},
					success: function(model, res){
						//view.loadingBtn.finish();
						var type = 'error';
						if(res.success){
							type ='success';
							//view.closeModal();
						}
						// for remove ad from list pending ad
						pubsub.trigger('ce:ad:afterRemovetAd',model,res);

					}
			} );
			return false;
	    },

	    editAd : function (event) {

	        event.preventDefault();
	        if(!this.model.has('id')){
	            this.model.set('id',this.model.get('id'),{silent:true});
	        }

	        pubsub.trigger('ce:ad:onEdit', this.model);
	    },

	    toggleFeatured : function (event) {
	        event.preventDefault();

	        if(this.$el.find('span.icon-featured').length > 0 )
	            this.model.toggleFeatured(0,this.options);
	        else
	            this.model.toggleFeatured(1,this.options);
	    }

	});


	CE.Models.keyword = Backbone.Model.extend({
		label: function() {
			return this.get("name");
		}
	});
	CE.Collections.AutoComplete = Backbone.Collection.extend({
		model: CE.Models.keyword
	});



	CE.Views.SearchForm = Backbone.View.extend({
		el: 'div#search_form',
		events: {
			'click .search-home button.show-cat': 'dropdownCat',
			'change .filter-location' 		: 'selectLocation',
			'click .show-box-category li' 	: 'selectCat',
			'click .dropdown-search li' 	: 'dropdownSearch',
			'keypress input#s' 				: 'keypressSearch',
			'submit form#search-form' 		: 'submitSearch',
			'click .button-search' 			: 'triggerSearch'
		},

		initialize: function() {


			$('.scrollbar1').tinyscrollbar({
				sizethumb: 165
			});
			$('.scrollbar2').tinyscrollbar({
				sizethumb: 165
			});
			this.$("select#search_location").chosen({width : '204px'});;

		},

		keypressSearch : function(event){
			if ( event.keyCode == 13 ) {
				$("form#search-form").submit();
				return false;
			}
			return true;
		},

		dropdownCat: function(e) {
			e.preventDefault();

			var $target = $(e.currentTarget);
			if ($target.hasClass('opened')) {

				$('.category-search-dropdown .scrollbar2').animate({
					'top': '-250px'
				}, 500, function() {
					$('.search-home .button-show').css({
						'border-bottom-right-radius': '3px'
					}).removeClass('opened');
				});

			} else {
				$target.css({
					'border-bottom-right-radius': '0px'
				}).addClass('opened');
				$('.category-search-dropdown .scrollbar2').animate({
					'top': '0px'
				}, 500);
				$('.category-search-dropdown').animate({
					'top': '40px',
					'z-index': '900'
				}, 0);

			}

		},


		selectLocation: function(e) {

			e.preventDefault();
			var $target = $(e.currentTarget),
			 	location = $target.val();



			this.$el.find('input#location').val(location);
			 this.$el.find('input#location').attr('name','location');
			//var title = $target.text();
			//$('.dropdown-left').find('span.select').text(title);
			this.setCookie('et_location', location, 30);
		},

		selectCat: function(e) {
			e.preventDefault();
			var $target = $(e.currentTarget),
				title = $target.text(),
				ad_category = $target.attr('data-slug');

			if (typeof ad_category !== 'undefined') {
				this.$el.find('input#ad_cat').val(ad_category);
				// this.$el.find('input#ad_cat').attr('name', 'ad_cat');
			} else {
				this.$el.find('input#ad_cat').removeAttr('name');
			}

			$('.show-box-category').find('span.select').text(title);
			$('.category-search-dropdown .scrollbar2').animate({
				'top': '-250px'
			}, 500);
			$('.search-home .button-show').css({
				'border-bottom-right-radius': '3px'
			}).removeClass('opened');

			$('.search-category li').removeClass('check-all').find('.icon').remove();
			$target.addClass('check-all').prepend('<span class="icon" data-icon="3"></span>');
		},

		dropdownSearch: function(e) {
			e.preventDefault();
			var title = $(e.currentTarget).text();
			$('.dropdown-search').find('.select').text(title);
		},

		triggerSearch: function() {
			//if($('#s').val() == '')  $('#s').val(' ');


			this.$('form#search-form').submit();
		},

		submitSearch: function(e) {
			//if($('#s').val() == '')  $('#s').val(' ');
			var fcat = this.$el.find('input#ad_cat'),
				//flocation = this.$el.find('input#location'),
				s = this.$el.find('input#s');

			if (fcat.val().length == 0)
				fcat.removeAttr('name');
			// if (flocation.val().length == 0)
			// 	flocation.removeAttr('name');
			if (s.val().length == 0)
				s.removeAttr('name');

			return true;
		},

		setCookie: function(c_name, value, exdays) {
			var exdate = new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
			document.cookie = c_name + "=" + c_value + ';domain=http://localhost/framework/forumengine;path=/';

			$.ajax({
				data: {
					action: 'ce_set_cookie',
					name: c_name,
					value: value
				},
				type: 'post',
				url: et_globals.ajaxURL,
				success: function(res) {
					if (c_name == 'et_location') {
						window.location.href = res.url;
					}
				}
			});

		}

	});

	CE.Views.App = Backbone.View.extend({
		el: 'body',
		templates: {},

		events: {
			'click .icon-view li': 'changeListView',
			'hover  .item-product': 'showButtonEvent',
			'mouseleave .item-product': 'hideButtonEvent',
			'submit form.info-seller ': 'sendMessage',
			'click .info-seller .submit-review': 'onSendReview',
			'click .section-categories .view-all': 'expandCatList',
			'click .section-categories .fa': 'showSubCat', 
			'click .close' : 'closeModal',
			'click .dropdown-toggle' : 'toggleFilter'
		},

		initialize: function() {
			// init default settings for validator plugin
			new CE.Views.SearchForm();
			/**
			 * setup tooltip for button event
			 */

			$('.tooltips').tooltip({
				selector: "a[data-toggle=tooltip]"
			});

			//$('.header-top a').addClass('ajax-nav');

			$('.sf-menu a').click(function() {
				$('.sf-menu li').removeClass('current-menu-item current-menu-parent');
				$(this).parents('li').addClass('current-menu-item');
			});
			/**
			 * menu callback
			 */
			$('ul.sf-menu').superfish({
				cssArrows: false,
				animation: {
					opacity: 'show'
				},
				animationOut: {
					opacity: 'hide'
				},
				delay: 700,
				speed: 'fast',
				speedOut: 'fast'
			});

			/**
			 * catch event click slide down/slide up child categories in categories list
			 */
			$(".border-bottom").click(function() {
				var $li = $(this).parents('li');

				$(this).parents('.nav-list').find('.menu-child').slideUp()
					.end().find('li').removeClass('clicked')
					.end().find('.fa').removeClass('fa-arrow-down').addClass('fa-arrow-right');

				$li.addClass('clicked');
				if (!$li.hasClass('active')) {
					$li.addClass('active');
					$li.find('.menu-child').slideDown('200');
					$li.find('.fa').removeClass('fa-arrow-right').addClass('fa-arrow-down');
				} else {
					$li.removeClass('active');
				}

				$(this).parents('.nav-list').find('li').each(function() {
					if (!$(this).hasClass('clicked')) {
						$(this).removeClass('current-cat active');
					}

				});
			});
			/**
			 * show sub when item is active
			 */
			$('.nav-list li.clicked').find('.menu-child').slideDown().end()
				.find('.fa').removeClass('fa-arrow-right').addClass('fa-arrow-down');

			/**
			 * use script to fix carousel pagination width
			 */
			var width_paginate = $('.paginate').width();
			$('.bx-prev').css({
				'right': width_paginate + 45 + 'px'
			});

			$.validator.setDefaults({

				// prevent the form to submit automatically by this plugin
				// so we need to apply handler manually
				onsubmit: false,
				onfocusout: function(element, event) {
					if (!this.checkable(element) && element.tagName.toLowerCase() === 'textarea') {
						this.element(element);
					} else if (!this.checkable(element) && (element.name in this.submitted || !this.optional(element))) {
						this.element(element);
					}
				},
				validClass: "valid", // the classname for a valid element container
				errorClass: "message", // the classname for the error message for any invalid element
				errorElement: 'div', // the tagname for the error message append to an invalid element container

				// append the error message to the element container
				errorPlacement: function(error, element) {
					$(element).closest('div').append(error);
				},

				// error is detected, addClass 'error' to the container, remove validClass, add custom icon to the element
				highlight: function(element, errorClass, validClass) {
					var $container = $(element).closest('div');
					if (!$container.hasClass('error')) {
						$container.addClass('error').removeClass(validClass)
							.append('<span class="icon" data-icon="!"></span>');
					}
				},

				// remove error when the element is valid, remove class error & add validClass to the container
				// remove the error message & the custom error icon in the element
				unhighlight: function(element, errorClass, validClass) {
					var $container = $(element).closest('div');
					if ($container.hasClass('error')) {
						$container.removeClass('error').addClass(validClass);
					}
					$container.find('div.message').remove()
						.end()
						.find('span.icon').remove();
				}
			});

			this.header = new CE.Views.Header();
			this.auth = new CE.Models.Auth();
			// this.mes 	= new CE.Views.Send_Message();

			// init current user model
			var current_user_data = $('#current_user_data').html();
			if ( !! current_user_data) {
				current_user_data = JSON.parse(current_user_data);
				//current_user_data.id	=	current_user_data.ID;
			}


			this.currentUser	= new CE.Models.Seller(current_user_data);
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};

			this.templates.notification	= new _.template(
				'<div class="notification autohide {{type}}-bg">' +
					'<div class="main-center">' +
						'{{ msg }}' +
					'</div>' +

				'</div>'
			);

			// event handler to show notification on top of the page
			pubsub.on('ce:notification', this.showNotice, this);

			// event handler for when receiving response from server after requesting login/register
			pubsub.on('et:response:auth', this.handleAuth, this);

			// // event handler for when receiving response from server after requesting new password
			// pubsub.on('je:response:request_reset_password', this.handleRequestResetPassword, this);

			// // event handler for when receiving response from server after reseting password
			// pubsub.on('je:response:reset_password', this.handleResetPassword, this);

			// event handler for when receiving response from server after requesting logout
			pubsub.on('et:response:logout', this.handleLogout, this);

			// // render button in header
			this.currentUser.on('change:id', this.header.updateAuthButtons, this.header);

			/**
			 * bind event to inview image
			 */
			$('img[data-src]').live('inview', function(event, isVisible) {
				if (!isVisible) {
					return;
				}

				var img = $(this);

				// Show a smooth animation
				// img.css('opacity', 0);
				img.load(function() {
					img.animate({
						opacity: 1
					}, 500);
				});

				// Change src
				img.attr('src', img.attr('data-src'));

				// Remove it from live event selector
				//img.removeAttr('data-src');
			});

		},

		closeModal : function(event){
            $('body').removeClass('modal-open');
        },

		showButtonEvent: function(event) {
			/**
			 * list view span 12 always show button event
			 */
			if ($('body').hasClass('body-list-view')) return false;
			/**
			 * toggle button events if is grid view
			 */
			var $target = $(event.currentTarget);
			$target.find('.button-event').css({}).fadeIn(100);
		},

		hideButtonEvent: function(event) {
			/**
			 * list view span 12 always show button event
			 */
			if ($('body').hasClass('body-list-view')) return false;
			/** 
			 * toggle button events if is grid view
			 */
			var $target = $(event.currentTarget);
			$target.find('.button-event').css({})
				.fadeOut(100);
		},
		// open modal send message
		sendMessage: function(event) {


			if ($('#send-message').length > 0 && (typeof this.modal_send_msg === 'undefined' || !(this.modal_send_msg instanceof CE.Views.Modal_Message))) {
				this.modal_send_msg = new CE.Views.Modal_Message();

			}
			var form 		=   $(event.target),
				ad_id 		= 	$(form).find('input#ad_id').val(),
				seller_id 	= 	$(form).find('input#seller_id').val(),
				seller_name = 	$(form).find('span.seller-name').text();
				this.modal_send_msg.onSend(seller_id,seller_name,ad_id);
				 var html = $("#reCaptcha").html();
                $(".captcha-append").html(html);
			return false;

		},
		// open modal review
		onSendReview: function(event) {

			if (typeof this.currentUser.get('ID') !== 'undefined') { // require use login to submit review
				if ($('#send-review').length > 0 && (typeof this.modal_review === 'undefined' || !(this.modal_review instanceof CE.Views.Modal_Review))) {
					this.modal_review = new CE.Views.Modal_Review();

				}

				var $target = $(event.target),
					// email 		= 	$(form).find('span.seller-email').text(),

					seller_id = $target.attr('data-id'),
					seller_name = $target.attr('data-name');

				this.modal_review.onSend(seller_id, seller_name);

				return false;
			} else {
				// request modal login
				$('#requestLogin').click();
				pubsub.trigger('ce:notification', {
					msg: $('.review-message').val(),
					notice_type: 'error'
				});

			}
		},

		expandCatList: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget);
			$target.parents('ul').find('li.hide').slideDown(100).removeClass('hide');
			$target.remove();
		},

		showSubCat: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget);
			$target.parents('li').find('li.child').slideDown(100).show().end().find('i').remove();
			$target.remove();
		},

		toggleFilter : function(event) {
			var $target = $(event.currentTarget),
				$parent = $target.parents('.dropdown-search');
			$parent.find('.dropdown-menu').slideToggle(200);
		},

		// event handler: for custom event: "custom:notification"
		// show the notice on top of the page
		showNotice: function(params) {

			// remove existing notification
			$('div.notification').remove();

			var notification = $(this.templates.notification({
				msg: params.msg,
				type: params.notice_type
			}));

			if ($('#wpadminbar').length !== 0) {
				notification.addClass('having-adminbar');
			}

			notification.hide().prependTo('body')
				.fadeIn('fast')
				.delay(1000)
				.fadeOut(3000, function() {
					$(this).remove();
				});
		},

		handleAuth: function(resp, status, jqXHR) {
			var notice_type;

			// check if authentication is successful or not
			if (resp.status || resp.success) {
				pubsub.trigger('ce:notification', {
					msg: resp.msg,
					notice_type: 'success'
				});

				var data = resp.data

				if (et_globals.is_single_job) {
					if (data.is_admin) {
						window.location.reload();
					}
				} else

				// if this is not job posting page, reload
				if (et_globals.page_template !== 'page-post-ad.php') {
					//window.location.reload();
				}

				this.currentUser.set(resp.data);

				if (typeof data.redirect_url !== 'undefined') {
					window.location.href = data.redirect_url;
				}

			} else {
				pubsub.trigger('ce:notification', {
					msg: resp.msg,
					notice_type: 'error'
				});
			}
		},

		handleLogout: function(data, status, jqXHR) {

			this.currentUser.clear();
			pubsub.trigger('ce:notification', {
				msg: data.msg,
				notice_type: 'success'
			});
			if (et_globals.page_template !== 'page-post-ad.php') {
				window.location.href = et_globals.homeURL;
			}
		},

		handleRequestResetPassword: function(data, status, jqXHR) {
			pubsub.trigger('je:notification', {
				notice_type: data.success ? 'success' : 'error',
				msg: data.msg
			});
		},

		handleResetPassword: function(data, status, jqXHR) {
			pubsub.trigger('je:notification', {
				notice_type: data.success ? 'success' : 'error',
				msg: data.msg
			});
		},


		/**
		 * toggle list view change
		 */
		changeListView: function(e) {
			e.preventDefault();
			var $target = $(e.currentTarget);
			if ($target.hasClass('grid')) {
				$('body').addClass('body-grid-view');
				$('body').removeClass('body-list-view');
				this.setCookie('ce_list_view', 'grid', 30);
				$('.item-product').find('img').each(function() {
					var src = $(this).attr('data-grid');
					$(this).css('opacity', 0).animate({
						opacity: 1
					}, 500).attr('src', src);
					$(this).attr('data-src' , src);
				});
			} else {
				$('body').addClass('body-list-view');
				$('body').removeClass('body-grid-view');
				this.setCookie('ce_list_view', 'list', 30);
				$('.item-product').find('img').each(function() {
					var src = $(this).attr('data-list');
					$(this).css('opacity', 0).animate({
						opacity: 1
					}, 500).attr('src', src).attr('data-src' , src);
					$(this).attr('data-src' , src);
				});
			}
		},

		setCookie: function(c_name, value, exdays) {
			var exdate = new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
			document.cookie = c_name + "=" + c_value + ';domain=http://localhost/framework/forumengine;path=/';

			$.ajax({
				data: {
					action: 'ce_set_cookie',
					name: c_name,
					value: value
				},
				type: 'post',
				url: et_globals.ajaxURL
			});

		},

		getCookie: function(c_name) {
			var c_value = document.cookie;
			var c_start = c_value.indexOf(" " + c_name + "=");
			if (c_start == -1) {
				c_start = c_value.indexOf(c_name + "=");
			}
			if (c_start == -1) {
				c_value = null;
			} else {
				c_start = c_value.indexOf("=", c_start) + 1;
				var c_end = c_value.indexOf(";", c_start);
				if (c_end == -1) {
					c_end = c_value.length;
				}
				c_value = unescape(c_value.substring(c_start, c_end));
			}
			return c_value;
		}

	});

	CE.Views.Header = Backbone.View.extend({


		el  : 'div.header-top',
		modal_login	: {},
		modal_register : {},
		modal_forgot_pass : {},
		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		templates	: {
			'login'	: '<li><a id="requestLogin" class="login-modal header-btn bg-btn-header" href="#login"><span class="icon" data-icon="U"></span></a></li>',
			'auth'	: _.template ('	<span class="profile-icon">' +
									'<a   href="{{profile_url}}" class="bg-btn-header btn-header" title="Account"><span data-icon="U" class="icon"></span></a></span>' +
									'<span id ="requestLogout" class="quite-icon"><a href="' + et_globals.logoutURL + '"><span data-icon="Q" class="icon"></span></a></span>' )

		},
		events: {
			'click span#requestLogout'	: 'doLogout',
			'click a#requestLogin' 		: 'doLogin',
			'click a#requestRegister'	: 'doRegister'
		},

		initialize: function() {
			if (typeof this.loginModal === 'undefined' && $('#loginModal').length > 0)
				this.loginModal = new CE.Views.Modal_Login();
			_.bindAll(this,'doLogout','doLogin','doRegister');
		},

		updateAuthButtons: function() {
			if (!CE.app.currentUser.isNew()) {
				this.$('div.icon-account').html(this.templates.auth(CE.app.currentUser.attributes));
			} else {
				this.$('div.icon-account').html(this.templates.login);
			}
			pubsub.trigger('afterUserChange', CE.app.currentUser.isNew());
		},

		doLogout: function(e) {
			e.preventDefault();
			pubsub.trigger('et:request:logout');
			CE.app.auth.doLogout();
			return false;
		},

		doLogin: function(e) {
			e.preventDefault();
			pubsub.trigger('et:request:auth');
			//this.loginModal.openModal();
			var html = $("#reCaptcha").html();
			$("#reCaptchaLogin").html(html);
			return false;

		},

		afterLogin: function(data, status, jqXHR) {
			// change the title 'loading' of the button
			// check if authentication is successful or not
			if (data.status) {
				this.loginModal.closeModal();
				//var loginModal	= new CE.Views.Modal_Login();
				//loginModal.closeModal();
				if (typeof data.redirect_url !== 'undefined')
					window.location.href = data.redirect_url;
				else
					window.location.reload();
			} else {
				// display error here
			}

		},


		doRegister: function(e) {
			e.preventDefault();
			pubsub.trigger('je:request:register');
		}

	});

	// Modal Login
	CE.Views.Modal_Login	= CE.Views.Modal_Box.extend({
		el		: '#loginModal',
		events	: {
			'submit form.form-login' 		: 'doLogin',
			'submit form.form-register' 	: 'doRegister',
			'click button.close'			: 'closeModal',
			'click a.forgot-password-link' 	: 'doGetPassword',
			'submit form.forgot-password' 	: 'getPassword',
			'click .signin-request' 		: 'requestSignin',
			'click .register-request' 		: 'requestRegister',
			'click .captcha-append a.btn-reload' 	: 'reloadImg',
			'click #reCaptcha a.btn-reload' : 'reloadImgDefault',
			//'click .facebook_auth_btn' 		: 'facebookLogin',

		},



		initialize: function() {

			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments);

			var that = this;

			pubsub.on('et:response:auth', this.afterLogin, this);
			pubsub.on('et:request:auth', this.openModalLogin, this);



			this.bind('waiting', this.waiting, this);
			this.bind('endWaiting', this.endWaiting, this);

			// pubsub.on('et:request:requestResetPassWaiting');
			// pubsub.trigger('et:response:request_reset_password', this, status, jqXHR);

			//_.bindAll(this);

			if (typeof this.validator === 'undefined') {
				this.validator = this.$('form.form-login').validate({
					rules: {
						inputEmail: {
							required: true
						},
						inputPassword: "required"
					}
				});

				this.vldgetpass = this.$('form.forgot-password').validate({
					rules: {
						user_login: {
							required: true,
							//email: true
						}

					}

				});	

				this.validatorRegister	=	this.$('form.form-register').validate({


					rules: {
						user_login: 'required',
						password: "required",
						repeat_password: {
							equalTo: "form.form-register #password"
						},
						user_email: {
							required: true,
							email: true,
							remote: et_globals.ajaxURL + '?action=et_email_check_used'
						}
					}
				});

			}

			this.loadingBtn = new CE.Views.LoadingButton({el: this.$('form button.btn-primary')});

		},
		facebookLogin : function(event){
			event.preventDefault();
			if ( FB ){
				FB.login(function(response) {
					if (response.authResponse) {
						access_token = response.authResponse.accessToken; //get access token
						user_id = response.authResponse.userID; //get FB UID

						FB.api('/me', function(response) {
							user_email = response.email; //get user email
							// you can store this data into your database

							var params = {
								url 	: et_globals.ajaxURL,
								type 	: 'post',
								data 	: {
									action: 'et_facebook_auth',
									content: response
								},
								beforeSend: function(){
								},
								success: function(resp){
									if ( resp.success ){
										//window.location = resp.data.redirect_url;
										location.reload(true);
									} else if ( resp.msg ) {
										if(typeof resp.data.social !== 'undefined' && resp.data.social == 'facebook'){
											window.location.href = resp.data.url;
										} else {
											alert(resp.msg);
										}
									}
								},
								complete: function(){
									//$('#facebook_auth_btn').loader('unload');
								}
							}
							$.ajax(params);

						});

					} else {
						//user hit cancel button
						console.log('User cancelled login or did not fully authorize.');
					}
				}, {
					scope: 'email,user_about_me'
				});
			}
		},

		reloadImg : function(){
			var html = $("#reCaptcha").html();
			$(".captcha-append").html(html);
			return false;
		},

		reloadImgDefault : function(){
			Recaptcha.reload();

			return false;
		},

		setOptions: function(options) {
			this.options = _.extend(options, this.options);
		},

		waiting: function() {
			this.loadingBtn.loading();
		},

		endWaiting: function() {
			this.loadingBtn.finish();
		},

		requestSignin: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget);

			var rel = $target.attr('rel');
			$('.modal-body').hide();
			$('.register-header').hide();
			$('.signin-header').show();
			$('#' + rel).show();
		},

		requestRegister: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget);
			var rel = $target.attr('rel');
			$('.modal-body').hide();

			$('.signin-header').hide();
			$('.register-header').show();

			$('#'+rel).show();

			this.reloadImg();

		},

		openModalLogin: function() {

			$('#register-body').hide();
			$('#login-body').show();

			$('.register-header').hide();
			$('.signin-header').show();

			this.$el.removeClass('modal-forgotpass');
			this.$el.find('.container-form-login').show().end()
				.find('.container-forgot-password').hide().end();

			$('.register-request').removeClass('active');
			// $('#register-body').hide();

			this.openModal();
			//this.initValidator();
		},

		afterLogin: function(data, status, jqXHR) {
			// change the title 'loading' of the button
			this.trigger('endWaiting');

			// check if authentication is successful or not
			if (data.status) {
				this.closeModal();
			} else {
				// display error here
			}
		},
		/**
		 * login
		 */
		doLogin: function(event) {
			event.preventDefault();

			// get the submitted form & its id
			var $target = this.$(event.currentTarget),
				$container = $target.closest('form'),
				form_type = $target.attr('id'),
				view = this;
			var options = this.options;


			if (this.validator.form()) {
				view.trigger('waiting');
				// update the auth model before submiting form
				CE.app.auth.setUserName($target.find('input[name=username]').val());
				CE.app.auth.setEmail($target.find('input[name=username]').val());
				CE.app.auth.setPass($target.find('input[name=password]').val());
				CE.app.auth.set('recaptcha_challenge_field', $target.find('input#recaptcha_challenge_field').val());
				CE.app.auth.set('recaptcha_response_field', $target.find('input#recaptcha_response_field').val());
				CE.app.auth.doAuth('login', options);
			}
		},
		/**
		 * register user
		 */
		doRegister: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget),
				view = this;
			if (this.validatorRegister.form()) {

				CE.app.auth = new CE.Models.Seller();

				this.$('#register-body').find('input, textarea, select').each(function() {
					CE.app.auth.set($(this).attr('name'), $(this).val());
				})
				CE.app.auth.set('user_pass', $target.find('input#password').val());
				var loading	=	new CE.Views.LoadingButton({el :$target.find('button.btn-primary') });

				CE.app.auth.save('', '', {
					beforeSend: function() {
						loading.loading();
					},

					success: function(model, res) {
						loading.finish();

						if(res.success) {
							window.location.reload();
						} else {
							if( typeof Recaptcha.reload === 'function')
								Recaptcha.reload();

						}
					}

				});
			}
		},

		doGetPassword: function() {

			var app = this;
			app.loadingBtn = new CE.Views.LoadingButton({
				el: this.$('form.forgot-password button.btn-primary')
			});
			app.$el.addClass('modal-forgotpass');
			app.$el.find('.container-form-login').hide();
			app.$el.find('.container-forgot-password').show();

		},
		getPassword: function(event) {
			var app = this;
			if (!app.vldgetpass.form())
				return false
			var user_login = $(event.currentTarget).find('input[name=user_login]').val();
			CE.app.auth.setEmail(user_login);
			CE.app.auth.doRequestResetPassword({
				beforeSend: function() {
					app.loadingBtn.loading();
				},
				success: function(res) {

					if (res.success) {
						app.closeModal();
						pubsub.trigger('ce:notification', {
							notice_type: 'success',
							msg: res.msg
						});
					} else {
						pubsub.trigger('ce:notification', {
							notice_type: 'error',
							msg: res.msg
						});
					}
					app.loadingBtn.finish();

				}


			})


			return false;
		}

	});



	CE.Views.AdCarousel = Backbone.View.extend({
		action: 'ce_request_thumb',
		events: {
			'hover .catelory-img-upload': 'hoverCarousel',
			'mouseleave .catelory-img-upload': 'unhoverCarousel',
			'click .image-item .delete ': 'removeCarousel',
			'click input.set-featured ': 'setFeatured'
		},

		initialize: function() {			
			this.imgTitle = et_globals.carouselTitle;
			this.removeText = et_globals.removeCarousel;

			this.setupView();
			pubsub.on('et:response:auth', this.handleAuth, this);
			/**
             * setup ae carousel template
             */
            if ($('#ce_carousel_template').length > 0) {
                this.template = _.template($('#ce_carousel_template').html());
            }
		},


		handleAuth : function (resp, status, jqXHR) {			
			if( resp.status){
				this.carousel_uploader.config.multipart_params._ajax_nonce = resp.ajaxnonce;
			}

			if (resp.success) {
				this.carousel_uploader.config.multipart_params._ajax_nonce = resp.data.ajaxnonce;
			}

		},

		setModel: function(model) {
			this.model = model;
		},

		setupView: function() {
			var that = this,
				$carousel = this.$el,
				i = 0,
				j = 0;

			this.carousels = [];
			this.carousels = this.model.get('et_carousels') || [];
			this.featured_image = this.model.get('featured_image') || '';

			this.blockUi = new CE.Views.BlockUi();
			that.maxFileUpload = et_globals.ce_config.number_of_carousel;
			that.numberOfFile = this.carousels.length;


			/**
			 * clear the list
			 */
			this.$('#image-list').find('li.image-item').remove();
			/**
			 * get model image and init view
			 */
			var items = []
			$.each(this.carousels, function(index, item) {
				items.push(item);
			});

			$.ajax({
				url: et_globals.ajaxURL,
				type: 'get',
				data: {
					item: items,
					action: that.action
				},
				beforeSend: function() {
					$('#carousel_browse_button').show();
				},
				success: function(res) {
					if (res.success) {

						$.each(res.data, function(index, item) {
							if (typeof item.thumbnail !== 'undefined') {
								if (typeof item.thumbnail !== 'undefined') {
	                                var $ul = $('#image-list');
	                                if (item.attach_id === that.model.get('featured_image')) item.is_feature = true;
	                                var li = that.template(item);
	                                $ul.prepend(li);
                            	}

							}
						});
						/**
						 * hide add button when file >= maxfile
						 */
						if (res.data.length >= that.maxFileUpload) {
							$('#carousel_browse_button').hide('slow');
						}
					}
				}
			});
			if (typeof this.carousel_uploader === 'undefined')
				this.carousel_uploader = new CE.Views.File_Uploader({
					el: $carousel,
					//runtimes: 'gears,html5,flash,silverlight,browserplus,html4',
					uploaderID: 'carousel',
					thumbsize: 'company-logo',
					multi_selection: true,
					multipart_params: {
						_ajax_nonce: $carousel.find('.et_ajaxnonce').attr('id'),
						action: 'et-carousel-upload',
						imgType: 'ad_carousels',
						author: that.model.get('post_author')
					},

					cbUploaded: function(up, file, res) {
						if (res.success) {
							var $ul = $('#image-list');
	                        var li = that.template(res.data);
	                        $ul.prepend(li);
	                        // update carousel list item
	                        //carousel_list = carousel_list+','+res.data.attach_id;
	                        that.carousels.push(res.data.attach_id);
	                        //$('.carousel-list').find('#carousels').val(carousel_list);
	                        that.model.set('et_carousels', that.carousels);

						} else {
							pubsub.trigger('je:notification', {
								msg: res.msg,
								notice_type: 'error'
							});
						}
					},


				cbAdded : function ( up,  files ) {
					var max_files		=	that.maxFileUpload;
					//var carousels		=	that.model.get('et_carousels') || [];
					that.numberOfFile	=	$('.catelory-img-upload').length;

					j	=	that.numberOfFile;
					i	=	that.numberOfFile;
					if ( files.length > (max_files - that.numberOfFile) ) {
		              	//alert('You are allowed to add only ' + max_files + ' files.');
		              	alert( 'You are allowed to add only ' + (max_files - that.numberOfFile) + ' files.');
		            }


						plupload.each(files, function(file) {

							if (files.length > (max_files - that.numberOfFile)) {
								//alert('You are allowed to add only ' + max_files + ' files.');

								up.removeFile(file);
								//alert('You are allowed to add only ' + max_files - that.numberOfFile + ' files.');
							} else {
								i++;
							}

						});

						that.numberOfFile = i;

						if (that.numberOfFile >= max_files) {
							$('#carousel_browse_button').hide('slow');
						}
					},

					beforeSend: function(element) {
						// pubsub.trigger ('ce:carousels:uploading');
						that.model.set('uploadingCarousel', true);
						that.blockUi.block($('#carousel_browse_button'));
					},


					success: function() {
						// pubsub.trigger ('ce:carousels:finished');
						j++;
						if (j == that.numberOfFile) {
							that.model.set('uploadingCarousel', false);
						}

						var featured = that.$el.find('span.featured');

						if (featured.length == 0) {
							var last = that.$el.find('.catelory-img-upload:last');
							last.addClass('featured');
							that.model.set('featured_image', last.attr('id'));
						}

						that.blockUi.unblock();

					}
				});
		},

		removeCarousel: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget),
				$span = $target.parents('.catelory-img-upload'),
				id = $span.attr('id');

			var carousels = this.carousels;
			carousels = $.grep(carousels, function(a) {
				return a != id;
			});

			this.model.set('et_carousels', carousels);

			$.ajax({
				type: 'post',
				url: et_globals.ajaxURL,
				data: {
					action: 'ce_remove_carousel',
					id: id
				},
				beforeSend: function() {

				},
				success: function() {
					$('#carousel_browse_button').show('slow');
				}
			});

			$span.remove();
			this.numberOfFile = this.numberOfFile - 1;
		},

		setFeatured: function(event) {
			var $target = $(event.currentTarget);

			this.model.set('featured_image', $target.attr('data-id'));
			$('.catelory-img-upload').removeClass('featured');
			$target.parents('.catelory-img-upload').addClass('featured');
		},

		hoverCarousel: function(event) {
			var $target = $(event.currentTarget);
			$target.find('img').animate({
				'opacity': '0.5'
			}, 200);
			$target.find('.delete').animate({
				'opacity': '1'
			}, 200);
		},

		unhoverCarousel: function(event) {
			var $target = $(event.currentTarget);
			$target.find('img').animate({
				'opacity': '1'
			}, 200);
			$target.find('.delete').animate({
				'opacity': '0'
			}, 200);
		}

	});

	/**
	 * view controls ad categories list
	 */
	CE.Views.AdCategoryList = Backbone.View.extend({
		el: '.search-category',
		//template : 
		events: {
			'change .search-category input': 'searchCat',
			'click .ui-autocomplete li': 'toggleCat'
		},

		initialize: function() {
			this.max_cat = et_globals.ce_config.max_cat || '';
			this.setupView();
		},

		setModel: function(model) {
			var cats = [],
				view = this;

			this.model = model;
			this.cat = this.model.get('category') || [];

			view.$el.find('li').removeClass('selected');
			$.each(this.cat, function(index, item) {
				cats.push(item.term_id);
				view.$el.find('li[data-id="' + item.term_id + '"]').addClass('selected');
			});

			this.cat = cats;
			this.model.set(et_globals.ce_ad_cat, this.cat);

		},

		setupView: function() {
			var view = this;
			this.cat = this.model.get('category') || [];
			this.categories = JSON.parse($('#ce_categories').html());

			$.widget("custom.catcomplete", $.ui.autocomplete, {
				_renderMenu: function(ul, items) {
					var that = this,
						currentCategory = "";
					$.each(items, function(index, item) {
						if (item.category != currentCategory) {
							if ($.inArray(item.id, view.cat) == -1)
								ul.append("<li data-id='" + item.id + "' class='ui-autocomplete-category'><span class='category-root'><span class='icon-circle'></span>" + item.category + "</span></li>");
							else
								ul.append("<li data-id='" + item.id + "' class='ui-autocomplete-category selected'><span class='category-root'><span class='icon-circle'></span>" + item.category + "</span></li>");

							currentCategory = item.category;
						}

						if (item.label != item.category)
							that._renderItemData(ul, item);
					});

				},
				_renderItem: function(ul, item) {
					if ($.inArray(item.id, view.cat) == -1)
						return $("<li>").addClass('ui-menu-item').css('padding-left', item['css'])
							.append('<span class="category-root"><span class="icon-circle"></span>' + item.label + '</span>').attr('data-id', item.id)
							.appendTo(ul);
					else
						return $("<li>").addClass('ui-menu-item selected')
							.append('<span class="category-root"><span class="icon-circle"></span>' + item.label + '</span>').attr('data-id', item.id)
							.appendTo(ul);
				}

			});

			$("#category").catcomplete({
				minLength: 0,
				close: function(event, ui) {
					return false;
				},
				source: this.categories,
				appendTo: '#auto-complete-list',
				select: function(event, ui) {
					return false;
				}
			});

			$('#category').catcomplete('search');

			var cats = [];
			view.$el.find('li').removeClass('selected');
			$.each(this.cat, function(index, item) {
				cats.push(item.term_id);
				view.$el.find('li[data-id="' + item.term_id + '"]').addClass('selected');
			});

			this.cat = cats;
			this.model.set(et_globals.ce_ad_cat, this.cat);
		},

		searchCat: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget),
				$list = $('ul.category-items');
			var keyword = $target.val();
		},

		toggleRootCat: function(event) {
			var $target = $(event.currentTarget);

			if ($target.hasClass('selected-root')) {
				$target.removeClass('selected-root');
				/**
				 * remove cat from model
				 */
				this.removeCat($target.parents('li').attr('data-id'));

			} else {
				$target.addClass('selected-root');
				/**
				 * add cat to model
				 */
				this.addCat($target.parents('li').attr('data-id'));

			}
		},

		toggleCat: function(event) {
			var $target = $(event.currentTarget);

			// $('#category').focus();

			if ($target.hasClass('selected')) {
				$target.removeClass('selected');
				/**
				 * remove cat from model
				 */
				this.removeCat($target.attr('data-id'));


			} else {
				if (this.max_cat != '' && this.max_cat <= this.cat.length) {
					pubsub.trigger('ce:notification', {
						notice_type: 'error',
						msg: et_globals.max_cat_msg
					});
					return;
				}
				$target.addClass('selected');
				/**
				 * add cat to model
				 */
				this.addCat($target.attr('data-id'));

			}
		},

		addCat: function(cat) {

			if ($.inArray(cat, this.cat) == -1) {
				this.cat.push(cat);
				this.model.set('product_cat', this.cat);
				this.model.set('ad_category', this.cat);
			}

			$('#category').parents('.controls').removeClass('error');
			pubsub.trigger('ce:ad:addCat', {
				catId: cat,
				model: this.model
			});
		},

		removeCat: function(cat) {
			this.cat = $.grep(this.cat, function(a) {
				return cat != a;
			});
			this.model.set('product_cat', this.cat);
			this.model.set('ad_category', this.cat);
			
			if (this.cat.length == 0) {
				$('#category').parents('.controls').addClass('error');
			}
			pubsub.trigger('ce:ad:removeCat', {
				catId: cat,
				model: this.model
			});
		}

	});

	// Modal Edit Job
	CE.Views.Modal_Edit_Ad = CE.Views.Modal_Box.extend({
		el: '#modal_edit_ad',
		events: {
			'submit form.edit-ad-form'   : 'submitAd',
			'blur input#et_full_location': 'gecodeMap',
			'keydown  input#_regular_price' 	 : 'pressTabToLocation',
			'keydown  input#et_price' 	 : 'pressTabToLocation',
		},

		gecodeMap: function(event) {
			var address = $(event.currentTarget).val();
			//gmaps = new GMaps
			GMaps.geocode({
				address: address,
				callback: function(results, status) {
					if (status == 'OK') {
						var latlng = results[0].geometry.location;
						$('#et_location_lat').val(latlng.lat());
						$('#et_location_lng').val(latlng.lng());
					}
				}
			});
		},

		initialize: function() {

			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments);

			var that = this;
			_.bindAll(this,'gecodeMap');

			this.adFormValidate();
			$('#ad_location').chosen({width : '460px'});

		},

		adFormValidate: function() {
			/**
			 * form validate
			 */
			var	ad_require_fields 	= et_globals.ad_require_fields,
				required_price 		= $.inArray(et_globals.regular_price, ad_require_fields)== -1 ? false : true,
				required_full_local	= $.inArray('et_full_location',ad_require_fields)== -1 ? false : true;
			if($('#_regular_price').length > 0 ) {
				$("form.edit-ad-form").validate({
					rules: {
						post_title: "required",
						et_full_location	:{ required : required_full_local  },
						ad_category: "required",
						post_content: "required",
						_regular_price : {
							required: required_price,
							isMoney : required_price,
						}
					}
				});
			}else {
				$("form.edit-ad-form").validate({
					rules: {
						post_title: "required",
						et_full_location	:{ required : required_full_local  },
						ad_category: "required",
						post_content: "required",
						et_price : {
							required: required_price,
							isMoney : required_price,
						}
					}
				});
			}
			
		},

		onEdit: function(model) {

			var view = this;
			this.model = model;

			setTimeout(function() {
				if (typeof tinyMCE !== 'undefined') {
					tinymce.EditorManager.execCommand('mceAddEditor', true, "post_content");
					var content = view.model.get('post_content');
					content = content.replace(/\n\n/g, "</p><p>");
					tinymce.EditorManager.get('post_content').setContent(content);
				}

				view.$el.find('input[type=text],input[type=hidden],input[type=checkbox],textarea,select').each(function() {
					var name = $(this).attr('name');
					if (typeof name !== 'undefined' && typeof view.model.get(name) !== 'undefined') {
						$(this).val(view.model.get(name));
					}

				});
				// danng add
				view.$el.find('input[type=radio]').each(function() {
					var name = $(this).attr('name');
					var value = view.model.get(name);
					if(value != '')
						$("input[value=" + value + "]").prop("checked", true);
				});
				// end danng



				var location = view.model.get('location');
				if (typeof location[0] !== 'undefined'){
					//view.$('#ad_location').val(location[0].term_id);
					$('#ad_location').chosen().change();
					setTimeout(function(){
						//view.$('#ad_location').val(location[0].term_id).change();
						$("#ad_location").val(location[0].term_id).trigger("chosen:updated");
					},500);

				}

				if (typeof view.carousels === 'undefined') {
					view.carousels = new CE.Views.AdCarousel({
						el: $('#gallery_container'),
						model: model
					});
				} else {
					view.carousels.setModel(model);
					view.carousels.setupView();
				}



				if (typeof view.categoryView === 'undefined') {
					view.categoryView = new CE.Views.AdCategoryList({
						model: view.model
					});
				} else {
					view.categoryView.setModel(view.model);
					view.categoryView.setupView();
				}

				pubsub.trigger('ce:ad:afterSetupFields', view.model);

				view.openModal();
			}, 100)


		},

		submitAd: function(e) {
			var view = this;
			e.preventDefault();

			view.$el.find('input,textarea,select').each(function() {
				view.model.set($(this).attr('name'), $(this).val());
			});
			//danng add
			var temp = new Array();
			view.$el.find('input[type=checkbox]:checked').each(function() {
				var name = $(this).attr('name');
				if (jQuery.inArray(name, temp) == -1)
					temp.push(name);
			});

			for (var i = 0; i < temp.length; i++) {
				var key = temp[i];
				temp[key] = new Array()
				view.$el.find('input[name="' + key + '"]:checked').each(function() {
					var name = $(this).attr('name');
					temp[key].push($(this).val());
				});
				this.model.set(key, temp[key]);
			}

			view.$el.find('input[type=radio]:checked').each(function() {
				view.model.set($(this).attr('name'), $(this).val());
			});
			// end danng

			var loading = new CE.Views.LoadingButton({
				el: $(e.currentTarget).find('.btn-primary')
			});
			if ($("form.edit-ad-form").valid() && !this.model.get('uploadingCarousel') && this.model.get(et_globals.ce_ad_cat).length > 0) {
				this.model.save('', '', {
					beforeSend: function() {
						loading.loading();
					},
					success: function(model, res) {
						loading.finish();
						if (res.success) {
							view.closeModal();
						}
						pubsub.trigger('ce:afterEditAd', res);
					}
				});
			} else {
				pubsub.trigger('ce:notification', {
					notice_type: 'error',
					'msg': et_globals.require_fields
				});
			}
		},

		pressTabToLocation : function(event){
			if(event.which == 9){
				$("a.chosen-single").trigger("mousedown");
				setTimeout(function() {$(".chosen-search input").focus();}, 100);

			}

		}

	});


	/**
	 * contact seller modal view, render view to help user can contact seller
	 */
	CE.Views.Modal_Message = CE.Views.Modal_Box.extend({
		el: '#send-message',
		events: {
			'submit form.send-message': 'submitMessage',
			'click .captcha-append a.btn-reload' 	: 'reloadImg',
		},

		initialize: function() {

			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments);

			var that = this;
			this.formvalid = this.$('form.send-message').validate({
				rules: {
					user_email: {
						email: true,
						required: true
					}

				}

			});
			//_.bindAll(this);

		},


		onSend : function (seller_id,name,ad_id) {

			// this.model	=	model;			

			this.$el.find('input[name=reset]').trigger('click');
			this.$el.find('span.response').text('');
			this.$el.find('input[name=seller_id]').val(seller_id);
			this.$el.find('span.seller-name').text(name);
			this.$el.find('input[name=ad_id]').val(ad_id);

			this.openModal();

			return false;
		},

		submitMessage: function(event) {

			event.preventDefault();

			if(!$('form.send-message').valid() )
			return false;	
			var view				=	this,
				form 				=   $(event.target),				
				user_email 			= 	$(form).find('input[name=user_email]').val(),
				seller_id 			= 	$(form).find('input[name=seller_id]').val(),
				first_name 			= 	$(form).find('input[name=first_name]').val(),
				last_name 			= 	$(form).find('input[name=last_name]').val(),
				phone 				= 	$(form).find('input[name=phone_number]').val(),
				message 			= 	$(form).find('textarea[name=message]').val(),
				ad_id 				= 	$(form).find('input[name=ad_id]').val(),
				recaptcha_challenge = 	$(form).find('input[name=recaptcha_challenge_field]').val(),
				recaptcha_response 	= 	$(form).find('input[name=recaptcha_response_field]').val(),
				button 				= 	$(form).find('button.btn');

			if(view.$el.find('button.btn-primary').hasClass('disable'))

				return false;
			var loading = new CE.Views.LoadingButton({
				el: button
			});

			var params = $.extend({
				url: et_globals.ajaxURL,
				type: 'post',
				data: {
					action: 'ce-send-seller-message',
					// content: {

						email_user  	: user_email,
						seller_id		: seller_id,
						ad_id 			: ad_id,
						message 		: message,
						first_name 		: first_name,
						last_name 		: last_name,
						phone			: phone,
						recaptcha_challenge : recaptcha_challenge,
						recaptcha_response  : recaptcha_response,

					// }
				},
				beforeSend: function() {
					loading.loading();
					button.addClass('disable');				
				},
				success: function(res) {
					// pubsub.trigger('ce:response:afterlogin', data, status, jqXHR);
					var type = 'error';
					if (res.status){
						type = 'success';
						view.closeModal();
					} else {
						if(typeof Recaptcha !== 'undefined'){							
							Recaptcha.reload();
							view.reloadImg();
						}
					}
					pubsub.trigger('ce:notification', {
						msg: res.msg,
						notice_type: type
					});
					button.removeClass('disable');
					loading.finish();
				}
			}, params);

			$.ajax(params);
			return false;
		},

		reloadImg : function(){
			var html = $("#reCaptcha").html();			
			$(".captcha-append").html(html);
						
			return false;
		},

	});


	/**
	 * modal review seller
	 */
	CE.Views.Modal_Review = CE.Views.Modal_Box.extend({
		el: '#send-review',
		seller_id: [],
		events: {
			'submit form.send-message': 'submitReview'
			/*,
			'keypress input#review_article '	: 'addArticle'*/
		},

		initialize: function() {
			// var view =	this;
			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments);

			var that = this;
			this.formvalid = this.$('form.submit-review').validate({
				rules: {
					comment_content: 'required',
					review_article: 'required'
				}

			});
			//_.bindAll(this);
			that.articles = {};

			$("input#review_article").autocomplete({
				// minLength: 2,
				source: function(request, response) {
					var term = request.term;
					if (term in that.articles) {
						response(that.articles[term]);
						$('.ui-autocomplete').css('z-index', '1041');
						return;
					}

					$.getJSON(
						et_globals.ajaxURL, {
							action: 'et_fetch_ad_by_seller',
							author: that.seller_id
							/*,
							//s		: term*/
						},
						function(data, status, xhr) {
							that.articles[term] = data;
							response(data);
						}
					);
				},
				minLength: 0,
				appendTo: '#et-complete-control',
				select: function(event, ui) {
					$('#review_comment_post_ID').val(ui.item.id);
				}
			}).focus(function() {
				$(this).autocomplete("search", this.value);
			});
			if ($('.author').length > 0)
				$.getJSON(
					et_globals.ajaxURL, {
						action: 'et_fetch_ad_by_seller',
						author: that.seller_id
						/*,
						//s		: term*/
					},
					function(data, status, xhr) {
						that.articles[data.id] = data;
						//response( data );
					}
				);
		},

		onSend : function ( id, name ) {

			// this.model	=	model;			
			this.seller_id = id;
			this.seller_name = name;

			this.$el.find('input[name=reset]').trigger('click');
			this.$el.find('span.response').text('');

			this.$el.find('span.seller-name').text(name);
			this.openModal();

			return false;
		},
		/**
		 * submit review
		 */
		submitReview: function(e) {
			e.preventDefault();
			var view = this,
				data = {
					comment_author: this.seller_id
				};

			this.$el.find('input,textarea,select').each(function() {
				data[$(this).attr('name')] = $(this).val();
			});

			if ($('form.submit-review').valid()) {
				$.ajax({
					type: 'post',
					url: et_globals.ajaxURL,
					data: {
						action: 'et-review-sync',
						content: data
					},
					success: function(res) {
						if (res.success) {
							pubsub.trigger('ce:notification', {
								msg: res.msg,
								notice_type: 'success'
							});
							/**
							 * close modal
							 */
							view.closeModal();
						} else {
							pubsub.trigger('ce:notification', {
								msg: res.msg,
								notice_type: 'error'
							});
						}
					}

				});
			}
		}

	});

	/**
	 * modal reject : render reject ad view help admin can reject an ad, and send seller a message
	 */
	CE.Views.Modal_Reject = CE.Views.Modal_Box.extend({
		el: '#reject-ad',
		events: {
			'submit form.reject-ad': 'submitReject'
		},

		initialize: function() {

			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments);

			var that = this;

			//_.bindAll(this);


		},

		
		onReject : function (model) {
			this.model	=	model;
			this.openModal();
			this.$el.find('input[name=id]').val(model.get('ID'));
			this.$el.find('span.post_name').text(model.get('post_title'));
		},

		submitReject: function(event) {
			event.preventDefault();
			this.blockUi = new CE.Views.BlockUi();
			var view = this,
				form = $(event.target);
			id = $(form).find('input[name=id]').val(),
			message = $(form).find('textarea[name=message]').val();
			this.loadingBtn = new CE.Views.LoadingButton({
				el: this.$('form button.btn-primary')
			});

			var ad = new CE.Models.Ad({
				id: id,
				ID: id,
				message: message,
				update_type: 'change_status'
			});

			ad.set('message', message);

			ad.save('post_status', 'reject', {
				beforeSend: function() {
					view.loadingBtn.loading();
				},
				success: function(model, res) {
					view.loadingBtn.finish();
					var type = 'error';
					if (res.success) {
						type = 'success';
						view.closeModal();
					}
					// for remove ad from list pending ad 
					pubsub.trigger('ce:ad:afterReject', model, res);
					// for render header in single-job
					pubsub.trigger('ce:afterRejectAd', model, res);

					pubsub.trigger('ce:notification', {
						msg: res.msg,
						notice_type: type
					});
				}
			});
			return false;
		}
	});

	// $('.modal').on('hide.bs.modal', function(){
	// 	$('body').removeClass('modal-open');
	// })

})(jQuery);