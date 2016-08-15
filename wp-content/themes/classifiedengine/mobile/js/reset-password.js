(function($){
	$(document).on('pageinit' , function () {
		$("form.reset_password").on('submit',function(e){
			e.preventDefault();
			var user_key 	= $(e.currentTarget).find('input[name=user_key]').val(),
			user_login 		= $(e.currentTarget).find('input[name=user_login]').val(),
			user_new_pass 	= $(e.currentTarget).find('input[name=user_new_pass]').val(),
			user_pass_again = $(e.currentTarget).find('input[name=user_pass_again]').val();
			if(user_new_pass != user_pass_again){
				alert('Password is not equal');
				return false;
			}

			$.ajax({
					url 	: et_globals.ajaxURL,
					type 	: 'post',
					contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
					data	: {
						action		: 'et_reset_password',
						user_login 	: user_login,
						user_key 	: user_key,
						user_pass 	: user_new_pass,
						user_pass_again: user_pass_again,
					},
					beforeSend : function() {
						$.mobile.showPageLoadingMsg();
					},
					success : function (response) {
						$.mobile.hidePageLoadingMsg();
						if(response.success){
							alert(response.msg);
							//setTimeout(function() {window.location.reload();}, 1000);
							
						} else {
						
							alert(response.msg);
						}

						
					}
				});
			return false;
		});
	});
})(jQuery);