// declare everything inside this object

(function ($){

	CE.Views.AdLisProfile     =   Backbone.View.extend({
		sync : function(){
		},
	    initialize : function () {
	        var view = this;
	        this.listenTo(this.collection, 'add', this.addOne);
	        this.listenTo(this.collection, 'remove', this.removeOne);

	        this.listenTo(this.collection, 'all', this.addAll);

	        //this.collection.fetch();
	        this.list_view  =   [];
	        var i = 0;
	        _.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};

	        this.collection.each(function( ad, index, coll ) {

	            var el  = view.$( 'li.ce-ad-item:eq(' + index + ')' );
	            if( el.length !== 0 ){
	                var itemView    =   new CE.Views.AdItemProfile ({el : el, model : ad });
	                view.list_view[index]	=	itemView;
	            }

	        });

	        view.paged		=	1;
	        if( $('#favorite_item_template').length > 0 )
	        this.template 	=	_.template($('#favorite_item_template').html());
			$('#inview').bind('inview' , function (event , isVisible) {
				view.inView(event , isVisible);
			} );

	    },

		inView : function (event , isVisible) {
			var view 	=	this;
			if (!isVisible) { return; }
			var view	=	this,
				user_id 	= 	$('input[name=profile_id]').val();
			var params = $.extend( {
				url: et_globals.ajaxURL,
				type: 'post',
				data: {
					action: 'ce-load-more-favorites',
					content: {
						user_id : user_id,
						paged   : view.paged
					}
				},
				beforeSend: function(){
					view.paged ++;
				},
				success: function(res){
					//view.blockUI.unblock();
					var $seller_list	=	$('#list_favorites');
					if( !res.status ) {
						$('#inview').hide();
					} else {

						var sellers	=	res.data;

						_.each(sellers, function (element ) {
							$seller_list.append(view.template(element));
						});

						if(res.total_pages == view.paged) $('#inview').hide();
					}
				}
			}, params );

			$.ajax(params);

		},

	    render : function () {

	    },

	    addOne : function ( ad, col, options) {

	        var itemView        =   new CE.Views.AdItemProfile ({model: ad }),
	            $itemEl         =   itemView.render().$el.hide(),
	            $existingItems  =   this.$el.find('li.ce-ad-item');

	            index       = (options && 'at' in options) ? options.at : $existingItems.length,
	            position    =  $existingItems.eq(index);

	        // insert the view at the correct position, same index in collection
	        if ( this.list_view.length === 0 || position.length === 0 ){
	            $itemEl.appendTo(this.$el).fadeIn('slow') ;
	        }
	        else{
	            $itemEl.insertBefore(position).fadeIn('slow');
	        }
	        this.list_view.splice( index, 0, itemView );
	    },

	    removeOne : function (ad, col, options ) {
	        // remove the ad item view from the array listView
	        var itemView = this.list_view.splice( options.index, 1 );

	        if( itemView.length > 0 ) {
	            itemView[0].$el.fadeOut('slow',function(){
	                itemView[0].remove().undelegateEvents();

	                // after hiding the removed ad, publish this event to add the ad to the correct collection
	                pubsub.trigger('ce:ad:afterRemoveAdView', ad);
	            });
	        }
	    },

	    addAll : function () {

	    }

	});
	/**
	 * seller listing view
	*/
	CE.Views.AdItemProfile     =   Backbone.View.extend({
	    tag : 'li',
	    className : 'ce-ad-item',
	    //template: _.template($('#ad-item-template').html()),
	    events : {
	        'click .button-event .approve'          : 'approveAd',
	        'click .button-event .reject'           : 'rejectAd',
	        'click .button-event .archive'          : 'archiveAd',
	        'click .button-event .edit'             : 'editAd',
	        'click .button-event .delete'          	: 'removeAd',
	        'click .button-event .toggle-feature'   : 'toggleFeatured'
	    },

	    initialize : function () {
	        if(this.model) {
	            this.model.on('change',this.render,this);

	        }
	       // _.bindAll(this, 'render');
	        this.blockUi    =   new CE.Views.BlockUi();

	        this.template   =   $('#ad-item-template');

	        if (this.template.length>0){
	            this.template   = _.template(this.template.html());
	        }

	        // pubsub.on ('ce:ad:beforeSend', this.beforeSend);
	        // pubsub.on ('ce:ad:success', this.success);
	        var view    =   this;
	        this.options    =   {beforeSend : function () {view.beforeSend()}, success : function (model, res) {view.success(model, res)} };


	    },

	    render : function () {
	        this.$el.html(this.template(this.model.toJSON()));
	        this.$el.addClass(this.className);
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
	       	this.model.expire(this.options);


	    },
	    removeAd : function (event) {

	    	var view = this;
 			var ad 		= new CE.Models.Ad({
				id :this.model.get('ID'),
				ID : this.model.get('ID'),
				update_type : 'change_status',
 			});

 			//var prevStatus    = this.model.get('status');
 			this.options.success = function(resp,msg){

 				view.blockUi.unblock();
		        if(resp.success) {
		            pubsub.trigger('ce:notification', {notice_type : 'success' , msg : resp.msg});
		        } else {
		            pubsub.trigger('ce:notification', {notice_type : 'error' , msg : resp.msg})
		        }
		        pubsub.trigger('ce:ad:afterRemoveAd', resp.data);
 			};
			ad.remove(this.options);

			return false;
	    },

	    editAd : function (event) {

	        event.preventDefault();
	        if(!this.model.has('id')){
	            this.model.set('id',this.model.get('id'),{silent:true});
	            this.model.set('ID',this.model.get('ID'));
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

	CE.Views.Seller_Listing	=	Backbone.View.extend({
		el : 'body',
		events : {

		},

		initialize : function () {

			var listing_ads ='';
			if($('#listing_ads').length > 0)
			listing_ads	=	JSON.parse($('#listing_ads').html());

			pubsub.on('ce:ad:onReject', this.onRejectAd, this);


			this.listing_collection	= 	new CE.Collections.Ads (listing_ads,{ comparator : '' });
			this.listingView		=	new CE.Views.AdLisProfile ({collection : this.listing_collection, el : $('#profile_listing')});

			pubsub.on('ce:ad:afterReject', this.afterRejectAd, this);

			pubsub.on('ce:ad:afterDeleteAd', this.afterDeleteAd, this);

			pubsub.on ('ce:afterEditAd', this.afterEditAd, this );

			pubsub.on('ce:ad:afterArchive', this.afterArchiveAd, this);

			pubsub.on('ce:ad:afterRemoveAd',this.afterRemoveAd,this);

			if( $('#modal_edit_ad').length > 0 && ( typeof this.editModalView === 'undefined' || !(this.editModalView instanceof CE.Views.Modal_Edit_Ad) ) ){
				this.editModalView	= new CE.Views.Modal_Edit_Ad();

				pubsub.on('ce:ad:onEdit', this.onEditAd, this);
				pubsub.on('ce:ad:afterEditAd', this.afterEditAd, this);
				this.rejectAd 	= new CE.Views.Modal_Reject();

			}

		},
		afterRemoveAdView : function (ad) {

		},

		onEditAd : function (model) {
			this.editModalView.onEdit(model);
		},
		afterEditAd : function (res) {
			if(res.success) {
				pubsub.trigger ('ce:notification', {notice_type : 'success' , msg : res.msg});
			} else {
				pubsub.trigger ('ce:notification', {notice_type : 'error' , msg : res.msg});
			}
		},

		afterDeleteAd : function (ad , resp) {
			if(resp.success) {
				this.listing_collection.remove(ad);
			}
		},

		afterArchiveAd : function (ad, resp) {

			if(resp.success) {
				//this.listing_collection.remove(ad);
			}
		},
		afterRemoveAd : function(ad,resp){
		var model = this.listing_collection.where({ ID: ad.ID })
		this.listing_collection.remove(model);

		}

	});
	/* View seller */
	//End view Seller

	CE.Views.Seller_Profile	=	Backbone.View.extend({
		el : 'div#seller_profile',
		events : {
			'submit form.change-password' 	: 'changePassword',
			'submit form.update-profile' 	: 'updateProfile',
			'click a.button-right-bar' 		: 'addTextWidget',
			'change #user_location_id'		: 'updateLocationText'

		},

		initialize : function () {
			this.loading = new  CE.Views.BlockUi();

			this.formvalid = this.$('form.change-password').validate({
				 rules: {
					// simple rule, converted to {required:true}
					old_password : "required",
					user_pass :{
						required : true,
					},
					renew_password : {
						required	: true,
						equalTo 	: '#user_pass',
					}
				}

			});

			this.formProfile = this.$('form.update-profile').validate({
				 rules: {
					// simple rule, converted to {required:true}
					display_name : "required",
					user_email :{
						required : true,
						email 	: true,
					}
				}

			});

			var $profile_thumb		= this.$('#profile_thumb_container');
			var blockUi = new CE.Views.BlockUi();

			if($profile_thumb.length > 0 ){

				this.profileUpload	= new CE.Views.File_Uploader({
					el					: $profile_thumb,
					uploaderID			: 'profile_thumb',
					thumbsize			: 'large',
					multipart_params	: {
						_ajax_nonce	: $profile_thumb.find('.et_ajaxnonce').attr('id'),
						profile_id	: $profile_thumb.find('input#profile_id').val(),
						action		: 'et-avatar-upload',
						imgType		: 'user_avatar'
					},
					cbUploaded		: function(up,file,res){
						if(res.success){
						} else {
							pubsub.trigger('je:notification',{
								msg	: res.msg,
								notice_type	: 'error'
							});
						}
					},
					beforeSend	: function(element){
						blockUi.block($profile_thumb.find('.avatar-thumbs'));
					},
					success : function(){
						blockUi.unblock();

					}
				});
			}

		},

		updateLocationText : function (e) {
			var $target	=	$(e.currentTarget);
			$('#user_location').val($target.find('option:selected').text());
		},

		changePassword: function(event){

			event.preventDefault();
			if(!$('form.change-password').valid() )
			return false;
			var form 		= $(event.target),
				button 		= $(event.currentTarget).find('button'),
				loadingBtn 	= new CE.Views.LoadingButton({ el : $(button)}),
				view 		= this;

			var saveData	=	[];
			form.find('input,textarea,select').each(function() {
				CE.app.currentUser.set ($(this).attr('name'), $(this).val() );
				saveData.push($(this).attr('name'));
			});
			CE.app.currentUser.save('' , '', {
					saveData :saveData,
					beforeSend: function(){
						loadingBtn.loading();
					},
					success: function(model, res){
						loadingBtn.finish();
						var type = 'error';
						if( res.success )
						type = 'success';

						pubsub.trigger('ce:notification',{
							msg			: res.msg,
							notice_type	: type
						});
					}
				} );
			return false;

		},

		updateProfile : function (event){

			if(!$('form.update-profile').valid())
				return false;
			var form 		= $(event.target),
				button 		= $(form).find('button.btn'),
				loadingBtn 	= new CE.Views.LoadingButton({ el : button }),
				view 		= this;	

			CE.app.currentUser.set('type', 'updateprofile');
			var saveData	=	[];

			form.find('input[type=text],input[type=hidden],textarea,select').each(function() {
				CE.app.currentUser.set ( $(this).attr('name'), $(this).val() );

				if(typeof $(this).attr('name') !== 'undefined')
					saveData.push($(this).attr('name'));
			});
			var temp = new Array();
			form.find('input[type=checkbox]:checked').each (function (){
				var name = $(this).attr('name');
				if(jQuery.inArray(name, temp) == -1){
					temp.push(name);
					saveData.push(name);
				}
			});

			for(var i = 0; i < temp.length; i++){
				var key = temp[i];
				temp[key] = new Array()
				view.$el.find('input[name='+key+']:checked').each (function (){
					var name = $(this).attr('name');
					temp[key].push($(this).val());
				});
				CE.app.currentUser.set(key,temp[key]);

			}
			// for radio
			form.find('input[type=radio]:checked').each(function() {
				CE.app.currentUser.set( $(this).attr('name'), $(this).val() );
				saveData.push($(this).attr('name'));
			});


			//loadingBtn.loading();
			CE.app.currentUser.save('' , '', {
				saveData : saveData,
				beforeSend: function(){
					loadingBtn.loading();
				}, success: function(res){
					loadingBtn.finish();
					var type = 'error';

			} } );
			return false;

		}
	});

})(jQuery);
