(function ($) {
CE.Views.AdvancedSettings	=	Backbone.View.extend({
		el : 'div#advanced_settings',

		events: {			
			'change .option-item' 								: 'onChangeOption',							
			'click .payment a.deactive' 						: 'deactiveOption',
			'click .payment a.active'   						: 'activeOption'
		},

		initialize : function () {
			this.loading = new CE.Views.BlockUi();
		} ,

		deactiveOption	: function (event) {
			event.preventDefault ();
			var payment	=	$(event.currentTarget),
				icon	=	payment.parents('.payment').find('a.icon'),
				view 	= this,
				loadingView = new CE.Views.LoadingEffect(),
				blockUI = new CE.Views.BlockUi(),
				container 	= $(event.currentTarget).parent(),
				enableBtn = container.children('a.active');

			if (container.hasClass('disabled')) return false;

			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	 : 'ce-disable-setting',
					setting  :  payment.attr('rel'),
					value	 : 0
				},
				beforeSend : function(){
					blockUI.block(payment);
					container.addClass('disabled');
					payment.addClass('selected');
					enableBtn.removeClass('selected');
				},
				success : function(response){
					blockUI.unblock();
					container.removeClass('disabled');
					if( response.success == true) {
						//change display
					} else {
						enableBtn.addClass('selected');
						payment.removeClass('selected');
					}
				}
			});
			return false;
		},

		activeOption 	: function  (event) {
			event.preventDefault();
			var payment	=	$(event.currentTarget),
				icon_container	=	payment.parents('.payment'),
				icon 			= 	icon_container.find('a.icon'),
				view 	= this,
				loadingView = new CE.Views.LoadingEffect(),
				container 	= $(event.currentTarget).parent(),
				blockUI = new CE.Views.BlockUi(),
				disableBtn = container.children('a.deactive');

			if (container.hasClass('disabled')) return false;
			
			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'ce-enable-setting',
					setting :  payment.attr('rel'),
					value	:  1
				},
				beforeSend : function(){
					blockUI.block(payment);
					container.addClass('disabled');
					payment.addClass('selected');
					disableBtn.removeClass('selected');
				},
				success : function(response){
					blockUI.unblock();
					container.removeClass('disabled');
					if( response.success == true) {
						icon_container.find('.message').hide ();
					} else {
						disableBtn.addClass('selected');
						payment.removeClass('selected');
						icon_container.find('.message').html (response.msg);
						payment.parents('.item').find('.payment-setting').show();
					}
				}
			});
			return false;
		},

		
		
		updateOption: function(name, value, params){
			var params = $.extend( {
				url: et_globals.ajaxURL,
				type: 'post',
				data: {
					action	: 'ce-save-setting',
					setting	: name,
					value	: value,
				},
				beforeSend: function(){},
				success: function(){}
			}, params );

			$.ajax(params);
		},

		onChangeOption: function(event){
			var element = $(event.currentTarget);
			var name 	= $(event.currentTarget).attr('name'),
				value 	= $(event.currentTarget).val(),
				view 	= this;
				view.url = false;
				if(element.hasClass('url'))
					view.url = true;

				this.updateOption(name, value, {
					beforeSend: function(){
						view.loading.block($(event.currentTarget));
					},
					success: function(resp){
						view.loading.unblock();
						$(event.currentTarget).removeClass('color-error');
						var icon = element.next();
						icon.html ('');

						if(view.url){
							var url = $(event.currentTarget).val();
							if(url.indexOf("http:") == -1 && url.indexOf('https:') == -1 && url.length > 0 ){
							 	var new_url = 'http://' + url;
							    $(event.currentTarget).val(new_url);
							}
						}
						if(value.length == 0){
							icon.addClass('color-error')
							icon.attr ('data-icon','!');
						} else {
							icon.removeClass('color-error')
							icon.attr ('data-icon','3');
						}

					}
				});

		}

	});

	new CE.Views.AdvancedSettings ();
})(jQuery);