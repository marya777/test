(function($){

	CE.Views.Index = Backbone.View.extend ({

		el : 'body',
		events : {},

		initialize : function () {
			var view		=	this;

			if($('#pending_ads').length > 0 ) {
				var pending_ads	=	JSON.parse($('#pending_ads').html());
				// setup comparator function for pending job collection
				comparator	= function(ad){
					var adDate	= new Date(ad.get('post_date'));
					return -(parseInt(ad.get('job_paid') + "" + adDate.getTime(),10));
				};
				this.pending_collection	= new CE.Collections.Ads (pending_ads, { comparator : comparator });
				this.pendingView	=	new CE.Views.AdList ({collection : this.pending_collection, el : $('#pending_list')});
			}

			if($('#publish_ads').length > 0 ) {
				var publish_ads	=	JSON.parse($('#publish_ads').html());

				this.publish_collection	= new CE.Collections.Ads (publish_ads);
				this.publishView	=	new CE.Views.AdList ({collection : this.publish_collection, el : $('#publish_list')});
			}

			// define events handlers for app view
			pubsub.on('ce:ad:afterRemoveAdView', this.afterRemoveAdView, this);
			/**
			 * trigger after approve an ad
			*/
			pubsub.on('ce:ad:afterApprove', this.afterApproveAd, this);
			/**
			 * trigger on reject an ad, request open modal send reject message
			*/
			pubsub.on('ce:ad:onReject', this.onRejectAd, this);
			/**
			 * trigger when reject ad success
			*/
			pubsub.on('ce:ad:afterReject', this.afterRejectAd, this);
			/**
			 * pubsub trigger after toggle feature sucess
			*/
			pubsub.on('ce:ad:afterToggleFeature', this.afterToggleFeature, this);
			/**
			 * pubsub trigger after archive ad
			*/
			pubsub.on('ce:ad:afterArchive', this.afterArchiveAd, this);


			pubsub.on('ce:ad:afterRemoveAd', this.afterRemoveAd, this);

			pubsub.on ('ce:afterEditAd', this.afterEditAd, this );

			pubsub.on ('ce:afterRejectAd', this.afterRejectAd, this );

			if( $('#modal_edit_ad').length > 0 && ( typeof this.editModalView === 'undefined' || !(this.editModalView instanceof CE.Views.Modal_Edit_Ad) ) ){
				this.editModalView	= new CE.Views.Modal_Edit_Ad();

				pubsub.on('ce:ad:onEdit', this.onEditAd, this);
				pubsub.on('ce:ad:afterEditAd', this.afterEditAd, this);
				this.rejectAd 	= new CE.Views.Modal_Reject();

			}

			this.initInfiniteScroll ();

			this.initReviewInfiniteScroll();

		},


		initInfiniteScroll : function () {
			var view 		=	this;

			view.no_post 	=	false;
			view.scrolled	=	false;
			/**
			 * bind inview de phong viec sidebar qua dai, scroll ko work
			*/
			$('#inview').bind('inview' , function (event , isVisible) {
				view.inView(event , isVisible);
			} );



			if($('#ce_query').length > 0 ) {
				this.query	=	JSON.parse($('#ce_query').html());
				if( typeof this.query.paged === 'undefined')  {
					this.query.paged = 1;
				}
				/**
				 * check inifinite scroll option
				*/
				if( parseInt(et_globals.ce_config.use_infinite_scroll) ) {
					$(window).scroll(function(){
		                if  ( 	$(window).scrollTop() == $(document).height() - $(window).height() 
		                		&& !view.no_post
		                	){
		                   	view.infiniteLoad();
		                   	view.scrolled	=	true;
		                }
		            });
		        }
			}

		},

		inView : function (event , isVisible) {
			var view 			=	this;
			this.inViewVisible	=	true;
			if (!isVisible || view.scrolled ) {
				this.inViewVisible = false; return;
			}
			this.infiniteLoad();

		},

		infiniteLoad : function () {
			var view 	=	this;
			// if( this.inViewVisible ) {
			//$('#inview').show();
			$.ajax({
				//accepts
				type : 'get',
				url  : et_globals.ajaxURL,
				data : this.query, 
				beforeSend : function () {
					view.query.paged++;
				},
				success : function (res) {

					//$('#inview').hide();
					if( res.success ) {
						var publish_list	=	$('#publish_list');
						for(var i=0; i < res.data.length; i++) {
							var item	=	new CE.Models.Ad(res.data[i]);
							view.publish_collection.add(item);
						}

						if( !res.max_page ) {
							$('#inview').remove();
							view.no_post =	true;

						}

					}
					else {
						$('#inview').remove();
						view.no_post =	true;
					}
				}
			});
			//}
		},
		/**
		 * bind event infinite load reviews
		*/
		initReviewInfiniteScroll : function () {
			var view	=	this;
			$('#review-inview').bind('inview' , function (event, isVisible) {
				//var view 			=	this;
				view.inViewVisible	=	true;

				if (!isVisible || view.scrolled ) {
					view.inViewVisible = false; return;
				}
				view.infiniteLoadReviews();
			});

			if($('#reviews_query').length > 0 ) {
				this.reviews_query	=	JSON.parse($('#reviews_query').html());
				if( typeof this.reviews_query.paged === 'undefined')  {
					this.reviews_query.paged = 1;
				}
				/**
				 * check inifinite scroll option
				*/
				if( parseInt(et_globals.ce_config.use_infinite_scroll) ) {
					$(window).scroll(function(){
		                if  ( 	$(window).scrollTop() == $(document).height() - $(window).height() 
		                		&& !view.no_post
		                	){
		                   	view.infiniteLoadReviews();
		                   	view.scrolled	=	true;
		                }
		            });
		        }
			}
		},

		/**
		 * load reviews
		*/
		infiniteLoadReviews : function () {
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};

			var view 		=	this,
				reviewItem	=	_.template($('#review_item_template').html());
			// if( this.inViewVisible ) {
			//$('#inview').show();
			$.ajax({
				//accepts
				type : 'get',
				url  : et_globals.ajaxURL,
				data : this.reviews_query,
				beforeSend : function () {
					view.reviews_query.paged++;
				},
				success : function (res) {

					//$('#inview').hide();
					if( res.success ) {

						for(var i=0; i < res.data.length; i++) {
							var item	=	reviewItem(res.data[i]);
							$('.review-list .row').append(item);

							/**
							 * check mod 2, and add clear both
							*/
							var z	=	$('.list-reviewer-wrapper').length % 2;
							if(z == 0 )	$('.review-list .row').append('<div style="clear:both"></div>');
 						}

 						/**
 						 * remove infinite scroll if reached max page
 						*/
						if( !res.max_page ) {
							$('#review-inview').remove();
							view.no_post =	true;
						}

					}
					else {
						/**
						 * success fale, return infinite scroll
						*/
						$('#review-inview').remove();
						view.no_post =	true;
					}
				}
			});
		},

		afterRemoveAdView : function (ad) {
			if(this.pending_collection.length == 0) {
				$('.pending-title').hide();
			}
		},

		afterEditAd : function (res) {
			if(res.success) {
				pubsub.trigger ('ce:notification', {notice_type : 'success' , msg : res.msg});
			} else {
				pubsub.trigger ('ce:notification', {notice_type : 'error' , msg : res.msg});
			}
		},

		afterApproveAd : function (ad , resp ) {
			if(resp.success) {
				if(this.pending_collection)
					this.pending_collection.remove(ad);
				if(this.publish_collection)
					this.publish_collection.add(ad);
			}
		},

		onRejectAd : function (model) {
			this.rejectAd.onReject(model);
		},

		afterRejectAd : function (ad , resp) {
			if(resp.success) {
				if(this.pending_collection)
				this.pending_collection.remove(ad);
			}
		},

		afterToggleFeature : function (ad) {
			var col	= ad.collection;
			col.remove(ad);
			if(parseInt(ad.get(et_globals._et_featured) ) == 1 )
				this.publish_collection.unshift(ad);
			else
				this.publish_collection.add(ad);
			// this.publish_collection.sort();
		},

		afterArchiveAd : function (ad, resp) {
			if(resp.success) {
				this.publish_collection.remove(ad);
			}
		},

		onEditAd : function (model) {
			this.editModalView.onEdit(model);
		}

	});

})(jQuery);

