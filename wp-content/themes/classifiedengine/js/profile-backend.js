(function ($){
	CE.Views.Backend_Profile	=	Backbone.View.extend({
		el : 'body',
		initialize : function () {			
			var $profile_thumb		= this.$('#profile_thumb_container');			
			var blockUi = new CE.Views.BlockUi();

			if($profile_thumb.length > 0 ){

				this.profileUpload	= new CE.Views.File_Uploader({
					el					: $profile_thumb,
					uploaderID			: 'profile_thumb',
					thumbsize			: 'large',
					multipart_params	: {
						_ajax_nonce	: $profile_thumb.find('.et_ajaxnonce').attr('id'),
						profile_id 	: $profile_thumb.find('input#profile_id').val(),
						action		: 'et-avatar-upload',
						imgType		: 'user_avatar'
					},
					cbUploaded		: function(up,file,res){
						if(res.success){
						console.log('success test');				
							// var attach_id = res.data.attach_id;					
							// $profile_thumb.find("input#attach_id").val(attach_id);

							//that.job.author.set('slider_thumb',res.data,{silent:true});
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
		}
	});
	$(document).ready(function(){
		new CE.Views.Backend_Profile;
	});

})(jQuery);
