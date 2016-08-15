(function($){
	$(document).ready(function(){
		new CE.Views.Wizard();
	});

	CE.Views.Wizard = Backbone.View.extend({
		el : '#engine_setting_content',
		events : {				
		
			'click button#install_sample_data' 			: 'installSampleData',
			'click button#delete_sample_data'			: 'deleteSampleData',
			'click button#update_database'			: 'update_database',
			'click button#reverse_database' : 'reverse_database'
		},

		initialize : function(){
			// init tinyMCE
			appView =	this;
			// create an array of targets for generating uploaders		
			var blockUis = []; //new CE.Views.BlockUi();		
			
			

		},		

		installSampleData : function(event){
			event.preventDefault();
			if ( $(event.currentTarget).hasClass('disabled') ) return false;

			var block = new CE.Views.BlockUi();
			
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-insert-sample-data'
				},
				beforeSend: function(){
					block.block($(event.currentTarget));
					$( ".btn-language" ).append( "<div class='uploading clear' style='padding-top:8px'>" + et_wizard.wr_uploading + "  </div>");
				},
				success : function(response){
					block.unblock ();					
					$(".btn-language").find(".uploading").hide();
					
					if( response.success  ) {
						$(event.target).after(
							$('<button>').text(et_wizard.delete_sample_data).attr({
								'id'	: 'delete_sample_data',
								'type'	: 'button',
								'class'	: 'primary-button'
							})
						);
						$(event.target).remove();
					}
					else {
						alert(et_wizard.insert_fail);
					}
				},
				error: function(jqXHR, textStatus, errorThrown){					
				}
			});
		},

		deleteSampleData : function(event){
			event.preventDefault();
			if ( $(event.currentTarget).hasClass('disabled') ) return false;
			
			var block = new CE.Views.BlockUi();
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-delete-sample-data'
				},
				beforeSend: function(){
					block.block($(event.currentTarget));
				},
				success : function(response){
					block.unblock ();					
					
					$(event.target).after(
						$('<button>').text(et_wizard.insert_sample_data).attr({
							'id'	: 'install_sample_data',
							'type'	: 'button',
							'class'	: 'primary-button'
						})
					);
					$(event.target).remove();				
				}
			});
		},
		update_database : function(event){
			event.preventDefault();
			if ( $(event.currentTarget).hasClass('disabled') ) return false;
			
			var block = new CE.Views.BlockUi();
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-update-database'
				},
				beforeSend: function(){
					block.block($(event.currentTarget));
				},
				success : function(response){
					block.unblock ();
					alert(response.msg);
				}
			});
		},
		reverse_database :  function(event){
			event.preventDefault();
			if ( $(event.currentTarget).hasClass('disabled') ) return false;
			
			var block = new CE.Views.BlockUi();
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	: 'et-reverse-database'
				},
				beforeSend: function(){
					block.block($(event.currentTarget));
				},
				success : function(response){
					block.unblock ();
					alert(response.msg);
				}
			});
		}
	});
	

})(jQuery);