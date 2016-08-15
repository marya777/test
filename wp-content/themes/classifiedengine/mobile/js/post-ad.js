(function($){
	var signUp	=	{
		user : {
			action  				: 'et_seller_sync' ,
			user_login 				: '',
			display_name 			: '',
			user_email				: '',
			user_pass				: '',
			password_again			: '',
			role					: 'seller'
		},

		updateUserData : function ( name, value ) {
			this.user[name]	=	value;
		},

		sync : function () {

			var view = this;

			if( view.user.action == 'et_login' ) {
				view.user.user_email	=	view.user.user_login;
				$.ajax ({
					url : et_globals.ajaxURL,
					type : 'post',
					data : view.user,
					beforeSend : function () {
						$.mobile.showPageLoadingMsg();
					},
					success : function (res) {
						$.mobile.hidePageLoadingMsg();
						if(res.status) {
							$('.authentication-form').remove();
							window.location.reload();
							view.user.ID = res.data.ID;
						} else {
							alert (res.msg);
						}
					}
				});

			}else {

				view.user.id	=	view.user.ID;
				if(view.user.user_pass !== view.user.password_again) {
					alert('Password miss match');
					return;
				}
				var data = {
						content : view.user,
						action  : 'et_seller_sync',
						method  : 'create'
					};
				data = _.extend(data, view.user);
				$.ajax ({
					url : et_globals.ajaxURL,
					type : 'post',
					data : data ,
					beforeSend : function () {
						$.mobile.showPageLoadingMsg();
					},
					success : function (res) {
						$.mobile.hidePageLoadingMsg();
						if(res.success) {
							$('.authentication-form').remove();
							window.location.reload();
						} else {
							if(typeof Recaptcha != 'undefined'){
								Recaptcha.reload();
							}
							alert (res.msg);
						}
					}
				});
			}
		}

	}

	$(document).on('pageinit' , function () {

		/*
		* since : 1.7.3
		* map adreess on mobile
		*/
		$("#et_full_location").on('change',function(event){

			var address = $(event.currentTarget).val();
			if(typeof(GMaps) !== 'undefined' )
				GMaps.geocode({
					address : address,
					callback : function(results, status){
						if (status == 'OK'){
							var latlng = results[0].geometry.location;
								$('#et_location_lat').val(latlng.lat());
								$('#et_location_lng').val(latlng.lng());
						}
					}
				});
			else 
				console.log(' not define gmap');

		});

		if( $('.post_author').length > 0 && $('.post_author').val() != '' ) {
			signUp.user.ID		=	$('.post_author').val();
			signUp.user.action  = 	'et_seller_sync'
		}

		/**
		 * toggle login form
		*/
		$('.open-login').on('tap', function (){
			$('form.register').slideUp();
			$('form.login').slideDown("slow");
		});
		$(".forgot-password").on('tap',function(){
			$('form.login').slideUp();
			$('form.forgot-password').slideDown();
		});

		$("#ad_categories").on ('change', function () {

		 	var max_cat	=	parseInt(et_globals.max_cat);
		 	if(max_cat) {
		 		$(this).find("option:selected");
	            if ($(this).find("option:selected").length > (max_cat -1 ) ) {
	                $(this).find("option:selected:eq(0)").removeAttr("selected");
	            } 
		 	}

		 	$('#'+et_globals.ce_ad_cat).val($(this).val());
	            
        });

		/**
		 * submit register form
		*/
		$('form.register').on ('submit' , function (e) {
			e.preventDefault();

			

			$(this).find('input,input[type=text],input[type=password],input[type=hidden],textarea,select').each(function () {
				var $target = $(this),
					name	= $target.attr('name'),
					value	= $target.val();
				signUp.updateUserData (name, value);
			});

			var temp = new Array();
			$(this).find('input[type=checkbox]:checked').each (function (){
				var name = $(this).attr('name');
				if(jQuery.inArray(name, temp) == -1){
					temp.push(name);					
				}
			});

			for(var i = 0; i < temp.length; i++){
				var key = temp[i];
				temp[key] = new Array()
				$(this).find('input[name='+key+']:checked').each (function (){
					var name = $(this).attr('name');
					temp[key].push($(this).val());
				});
				signUp.updateUserData (key, temp[key]);

			}
			// for radio
			$(this).find('input[type=radio]:checked').each(function() {
				signUp.updateUserData ($(this).attr('name'), $(this).val());
				
			});

			signUp.sync();
		});

		/**
		 * submit login form
		*/
		$('form.login').on ('submit' , function (e) {
			e.preventDefault();
			$(this).find('input').each(function () {
				var $target = $(this),
					name	= $target.attr('name'),
					value	= $target.val();
				signUp.user.action = 'et_login';
				signUp.updateUserData (name, value);
			});
			signUp.sync();
		});

		// forgot password acction.

		$('form.forgot-password').on ('submit' , function (e) {
			e.preventDefault();
			var email = $(e.currentTarget).find('input[name=user_login]').val();
			$.ajax({
					url 	: et_globals.ajaxURL,
					type 	: 'post',
					contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					data	: {
						action		: 'et_request_reset_password',
						user_login : email,
					},
					beforeSend : function() {
						$.mobile.showPageLoadingMsg();
					},
					success : function (response) {
						$.mobile.hidePageLoadingMsg();
						if(response.success){
							alert(response.msg);
							setTimeout(function() {window.location.reload();}, 1000);
							
						} else {
							var msg = response.msg.replace(/<(?:.|\n)*?>/gm, '');


							alert(msg);
						}

						
					}
				});
			
		});


		$('.main-payment').on('click' , function (e) {

			e.preventDefault();

			var $target		=	$(e.currentTarget),
				$container	=	$target.parents('.payment-form'),
				paymentType		=	$target.attr('data-payment');

			if( paymentType != '' ) {
				$.ajax({
					url 	: et_globals.ajaxURL,
					type 	: 'post',
					contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					data	: {
						action		: 'et_payment_process',
						ID			: $container.find('input[name="ad_id"]').val(),
						author		: $container.find('input[name="post_author"]').val(),
						packageID	: $container.find('input[name="et_payment_package"]').val(),
						paymentType	: paymentType,
						coupon_code	: $('#coupon_code').val()
					},
					beforeSend : function() {
						$.mobile.showPageLoadingMsg();
					},
					success : function (response) {
						
						$.mobile.hidePageLoadingMsg();

						if(response.data.ACK ) {
							console.log(response.data.url)
							$('#checkout_form').attr('action',response.data.url);
							if(typeof response.data.extend !== "undefined") {
								$('#checkout_form .payment_info').html('').append(response.data.extend.extend_fields);
								$('#checkout_form .payment_info').trigger('create');
							}	
							$('#payment_submit').click();
						} else {

							alert (response.errors[0]);
						}
					}
				});
			}else {
				alert('Please select a payment processor.');
			}
		});

	});
})(jQuery);