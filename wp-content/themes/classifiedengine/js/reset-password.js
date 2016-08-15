(function ($) {
jQuery(document).ready(function($){

CE.Views.ResetPassword = Backbone.View.extend({
	el : $('#page_reset_password'),

	events : {
		'submit form#reset_password' : 'resetPassword'
	},

	initialize: function(){
		this.validator = this.$('form#reset_password').validate({
			rules : {
				user_new_pass : "required",
				user_pass_again : {
					required : true,
					equalTo : '#user_new_pass'
				}
			}
		});

		pubsub.on('et:response:reset_password', this.afterResetPassword, this);
	},

	resetPassword : function(e){		

		e.preventDefault();
		var form = this.$('form#reset_password');
		var loadingBtn = new CE.Views.LoadingButton({el : form.find('#submit_profile')});
		if ( this.validator.form() ){
			CE.app.auth.setUserName(form.find('input[name=user_login]').val());
			CE.app.auth.setUserKey(form.find('input[name=user_key]').val());
			CE.app.auth.setPass(form.find('input[name=user_new_pass]').val());
			CE.app.auth.doResetPassword({
				beforeSend : function(){
					loadingBtn.loading();
				},
				success : function(){
					loadingBtn.finish();
				}
			});
		}
	},

	afterResetPassword : function(resp){
		var type = 'error';
		if(resp.success)
			type = 'success';

		pubsub.trigger('ce:notification', {notice_type : type , msg : resp.msg});

		if ( resp.success && resp.data.redirect_url ){			
			window.location = resp.data.redirect_url;
		}
	}

});

$("document").ready(function(){
	new CE.Views.ResetPassword();
});


});
})(jQuery);