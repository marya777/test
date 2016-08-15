(function($){
	CE.Views.Overviews = Backbone.View.extend({
		el : 'div#engine_setting_content',
		events : {
			'click a.act-approve' : 'approveAd',
			'click a.act-reject' : 'rejectAd',
			'change select#time_limit' : 'changeTimeLimit',
			'click a#archive' : 'archived_job'
		},
		templates : {},

		initialize : function(){

			this.templates = {};
			pubsub.on('ce:notification', this.showNotice,this);
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};

			this.templates	= new _.template(
				'<div class="notification autohide {{ type }}-bg">' +
					'<div class="main-center">' +
						'{{ msg }}' +
					'</div>' +
				'</div>'
			);
			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments );
			this.modal_reject = new CE.Views.Modal_Reject();
		},

		approveAd : function(event){
			var	view 		= this,
				id 			= $(event.currentTarget).attr('rel');
			this.blockUi = this.blockUi?this.blockUi:{};
			this.blockUi[id] 	= new CE.Views.BlockUi();
			var ad = new CE.Models.Ad({
				id :id,
				ID : id,
				update_type : 'change_status',
				post_status :'publish'
 			});
			// ad.set('ID',id);
			ad.save('','',{
					beforeSend: function(){
						view.blockUi[id].block('li#ad_' + id);
					},
					success: function(model, res){
						view.blockUi[id].unblock();

						var type = 'error';
						if(res.success)
							type ='success';
						pubsub.trigger('ce:notification', {
							msg			: res.msg,
							notice_type	: type,
						});
						$("li#ad_"+id).remove();
					}
			} );
			return false;
		},

		rejectAd : function(event){

			var title 	=  $(event.currentTarget).closest('li').find('a.ad-name').html(),
				id 		= $(event.currentTarget).attr('rel');

			var ad = new CE.Models.Ad({
				id :id,
				ID : id,
				post_title : title

 			});

			this.modal_reject.onReject(ad);


		},

		changeTimeLimit: function(event){

			var $this = $(event.target);
			var value = $this.val();
			var view = this;
			var params = _.extend( ajaxParams, {
				data : {
					within : value,
					action : 'et_overview_filter_stats'
				},
				beforeSend : function(){
					$this.parent().after(et_globals.loadingImg);
				},
				success : function(resp){
					stats = resp.data;
					$('.et-main-header').find('img.loading').remove();
					if ( resp.success ){
						var symbol = $('#stats_revenue sup').clone();
						// change stats
						$('#stats_pending_ads').html( stats.pending_ads );
						$('#stats_active_ads').html( stats.active_ads );
						$('#stats_revenue').html( symbol[0].outerHTML + stats.revenue );
						$('#stats_applications').html( stats.applications );
					}
				}
			} );

			return $.ajax(params);
		},
		showNotice	: function(params){
			var view = this;
			// remove existing notification
			$('div.notification').remove();

			var notification	= $(view.templates({msg:params.msg,type:params.notice_type}));

			if( $('#wpadminbar').length !== 0 ){
				notification.addClass('having-adminbar');
			}

			notification.hide().prependTo('body')
				.fadeIn('fast')
				.delay(1000)
				.fadeOut(3000, function(){
					$(this).remove();
				});
		},

		archived_job: function(event){

			event.preventDefault();
			if (!$(event.currentTarget).hasClass('disabled')){
				var blockUi = new CE.Views.BlockUi();
				var num	=	parseInt( $('#expired_jobs .number').html() );

				var j = 0;
				for (var i = 0; i < num; i += 10 ) {
					j	=	j+1;
					var params = {
						url : et_globals.ajaxURL,
						type : 'post',
						data : {
							action : 'et_archive_expired_ads',
							paged : j
						},
						beforeSend: function(){
							blockUi.block($('#expired_jobs'));
						},
						success : function(resp){
							if (resp.success){
								blockUi.unblock();
								$('#expired_jobs').fadeOut('normal', function(){ $(this).remove() });
							}
							else 
								alert(resp.msg);
						}
					}
					$.ajax(params);
				};

			}
		}

	});

	CE.Views.Modal_Reject	= CE.Views.Modal_Box.extend({
		el		: '#reject-ad',
		events	: {
			'submit form.reject-ad'			: 'submitReject'
		},

		initialize	: function(){

			CE.Views.Modal_Box.prototype.initialize.apply(this, arguments );

			var that		= this;
		},
		onReject : function (model) {
			this.model = model;
			this.openModal();

			this.$el.find('input[name=id]').val(model.get('ID'));
			this.$el.find('span.post_name').text(model.get('post_title'));
		},

		submitReject : function (event) {
			event.preventDefault();
			this.loadingBtn = new CE.Views.LoadingButton({el: this.$('form button.btn-primary')});
			this.blockUi 			= new CE.Views.BlockUi();
			var	view 	= this,
				form 	= $(event.target);
				id 		= $(form).find('input[name=id]').val(),
				message	= $(form).find('textarea[name=message]').val();
			this.model.reject({
					beforeSend: function(){
						view.loadingBtn.loading();
					},
					success: function(model, res){
						view.loadingBtn.finish();
						var type = 'error';
						if(res.success)
							type ='success';

						pubsub.trigger('ce:notification', {
							msg			: et_overview.reject_ad,
							notice_type	: type,
						});
						view.closeModal();

						$("li#ad_"+res.data.ID).remove();
					} 
			} );
			return false;
		}
	});
	$(document).ready(function(){
		new CE.Views.Overviews();
	})

})(jQuery);