(function ($) {

	CE.Views.PaymentSettings = Backbone.View.extend({
		el : 'div#setting-payment',
		events : {
			'submit form#payment_plans_form' 				: 	'submitPaymentForm',
			'click ul.menu-currency a'  					: 	'changeCurrency'	,
			'focusout .cash-message textarea'				: 	'updateCashPaymentSetting',
			'click .add-new-currency'						: 	'showAddNewCurrencyForm',
			'click #add-new-currency'						:	'addNewCurrency',
			'click #engine-currency-form .button-enable a'	:	'currencyAlignChange',
			'change #engine-currency-form #currency_icon' 	: 	'currencyIconChange',
			'click .desc .payment'							: 	'togglePaymentSetting',
			'change .payment-item' 							: 	'onChangePayment',	

		},

		initialize : function () {
			var appView	= this;

			new CE.Views.PaymentPlanList ();
			this.loading = new CE.Views.BlockUi();

		},
		updateCashPaymentSetting : function(event){

			var element 	=	$(event.currentTarget),
				name 	=	'cash-message',
				value 	=	tinymce.EditorManager.get('et_cash_message').getContent (),
				icon	=	element.closest('.cash-message').next (),
				payment	=	element.parents('.payment-setting'),
				button	=	payment.prev ('.payment'),
				view 	= this;

			$.ajax ( {
				url  : et_globals.ajaxURL,
				type : 'post',
				data : {
					action 	: 'save-api-option',
					content : {
						name 	:  name,
						value	:  value
					}
				},
				beforeSend : function(){
					 icon.attr ('data-icon','');
					 icon.html ('<img src="'+et_globals.imgURL+'/loading.gif" />');

				},
				success : function(response){
					view.loading.finish();
					if( response.success ) {
						icon.html ('');
						icon.removeClass('color-error')
						icon.attr ('data-icon','3');
					} else {
						icon.html ('');
						icon.addClass('color-error')
						icon.attr ('data-icon','!');
						button.find('a.active').html ('');
					}
				}
			});
		},


		onChangePayment : function(event){
			var element = $(event.currentTarget);
			var name 	= $(event.currentTarget).attr('name'),
				value 	= $(event.currentTarget).val(),
				payment	=	element.parents('.payment-setting'),
				button	=	payment.prev ('.payment'),
				icon	=	element.next ('span'),
				view 	= this;	
				
					this.updateApiOption(name, value ,{
						beforeSend: function(){
							view.loading.block($(event.currentTarget));

						},
						success: function(resp){
							view.loading.unblock();
							icon.html ('');
							button.find('.message').html ('');
							if( value == '') {
									icon.addClass('color-error');
									element.addClass('color-error');
									icon.attr ('data-icon','!');
								} else {
									icon.removeClass('color-error');
									element.removeClass('color-error');
									icon.attr ('data-icon','3');
							}
							if( !resp.success ) {
								button.find('a.active').removeClass ('selected');
								button.find('.message').html (resp.msg);
								button.find('.message').show ();
								icon.addClass('color-error');
								element.addClass('color-error');
								icon.attr ('data-icon','!');
							}								
						}
					});
				

		},

		updateApiOption: function(name, value, params){
			var params = $.extend( {
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'save-api-option',
					content: {
						name: name,
						value: value,
					}
				},
				beforeSend: function(){},
				success: function(){}
			}, params );

			$.ajax(params);
		},		
		showAddNewCurrencyForm : function  (event) {
			$('.show-new-currency').show ();
			$('#currency_name').focus ();
		},

		addNewCurrency	:	function (event) {
			event.preventDefault ();
			var $form	=	$(event.currentTarget).parents('form#engine-currency-form');
			$form.find('input').each (function (event) {
				if($(this).val() === '') {
					$(this).addClass('color-error');
					$(this).focus ();
				} 
			});
			var text	=	$form.find('#currency_name').val(),
				icon	=	$form.find('#currency_icon').val(),
				code	=	$form.find('#currency_code').val(),
				align	=	$form.find('a.selected').attr('rel');
				
			$.ajax ({
				url	:	et_globals.ajaxURL,
				type:	'post',
				data:	{
					'code' : code,
					'text' : text,
					'icon' : icon,
					'align': align,
					'action' : 'et-add-new-currency'
				},
				beforeSend: function () {

				},
				success : function (response) {
					if(response.success) {
						var li	=	document.createElement('li');
						if(align == 'right') {
							var money =	code+' '+icon;
						} else {
							var money =	icon+' '+code;
						}
						$('.menu-currency').find ('a.active').removeClass('active');
						li.innerHTML = '<a href="#et-change-currency" class="select-currency  active" title="'+text+'" rel="'+code+'">'+money+' </a>';
						$('.menu-currency').append (li);

						$form.find('input').val('');
					}
				}
			});
		},

		currencyAlignChange	: function (event) {
			event.preventDefault ();
			var $target	=	$(event.currentTarget),
				icon = '$';
			$('form#engine-currency-form').find('a.selected').removeClass('selected');
			$target.addClass('selected');
			if($('#currency_icon').val() !== '') {
				icon 	=	$('#currency_icon').val();
	 		} 
	 		if($target.attr('rel') == 'left') {
				$('.currency_text').html ('<sup>'+icon+'</sup>1000,000');
			} else {
				$('.currency_text').html ('1000,000<sup>'+icon+'</sup>');
			}
		},

		currencyIconChange : function (event) {
			event.preventDefault ();
			var currencyIcon	=	$(event.currentTarget).val();
			$('.currency_text').find('sup').html (currencyIcon);
		},
	
		changeCurrency 	: function (event) {
			
			var $li 		=	$(event.currentTarget),
				action		=	$li.attr('href'),
				container 	= $li.parent(),
				blockUI 	= new CE.Views.BlockUi(),
				view 		= this;
			action			=	action.replace ('#', '');

			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	  :  action,
					new_value :  $li.attr('rel')
				},
				beforeSend : function(){
					blockUI.block(container);
					// update view
					$container	=	$li.parents('ul');
					$container.find('.active').removeClass('active');
					$li.addClass('active');
				},
				success : function(response){
					blockUI.finish();
				}
			});		
			return false;
		},

		togglePaymentSetting : function (event) {
			// event.preventDefault();
			// var $target	=	$(event.currentTarget);
			// $target.parents('.item').find('.payment-setting').slideToggle();
		}
		,
		// event handle: Submit Payment form
		submitPaymentForm : function(event){
			event.preventDefault();

			var form = $(event.target);
			var button = form.find('.engine-submit-btn');
			var buttonTitle = button.find('span:not(.icon)');
			var list_container = $('#payment_lists');
			var view = this;
			var loading = new CE.Views.LoadingButton({el : button});

			if ( form.valid() ){
				var  price = form.find('input[name=payment_price]').val(),
					featured  = form.find('input[type=checkbox][name=payment_featured]').is(':checked') ? 1 : 0;
				var model = new CE.Models.PaymentPlan({
					title: form.find('input[name=payment_name]').val(),
					_regular_price: price ,
					et_price : price,
					et_duration: form.find('input[name=payment_duration]').val(),

					_et_featured: featured,
					et_featured: featured,


					et_number_posts: form.find('input[name=payment_quantity]').val(),
					et_description : form.find('input[name=payment_desc]').val()
				}),
					loading = new CE.Views.LoadingButton({el : button});

				model.add({
					beforeSend : function(){
						loading.loading();
					},
					success : function(){
						loading.finish();
						form.find('input').val('');
					}
				});
				
			}
		}
		
	});

	/**
	 * payment plan list view
	*/
	CE.Views.PaymentPlanList = Backbone.View.extend({
		el : 'ul.pay-plans-list',
		initialize: function(){
			var view = this;
			view.views = [];
			view.collection = new CE.Collections.PaymentPlans( JSON.parse( $('#payment_plans_data').html() ) );
			view.$el.find('li').each(function(index){
				var $this = $(this);
				view.views.push( new CE.Views.PaymentPlanItem({
					model : view.collection.models[index],
					el : $this
				}) );
			});

			this.collection.bind('remove', this.removeView, this );
			this.collection.bind('add', this.addView, this );

			pubsub.on('je:setting:paymentPlanAdded', this.addView, this);

			// sort payment plan
			$('.sortable').sortable({
				axis: 'y',
				handle: 'div.sort-handle'
			});

			// payment plan sorting
			$('ul.pay-plans-list').bind('sortupdate', function(e, ui){
				view.updatePaymentOrder();
			});

		},

		updatePaymentOrder : function(){
			var order = $('ul.pay-plans-list').sortable('serialize');
			var params = ajaxParams;
			params.data = {
				action: 'et_sort_payment_plan',
				content : {
					order: order
				}
			};
			params.beforeSend = function(){

			}
			params.success = function(data){

			}
			$.ajax(params);
		},

		add : function(model){
			this.collection.add(model);
		},
		removeView : function(model){
			var thisView = this;
			var viewToRemove = _.filter( thisView.views, function(vi){ 
				return vi.model.get('id') == model.get('id');
			})[0];

			_.without(thisView.views, viewToRemove);

			viewToRemove.fadeOut();
		},
		addView : function(model){

			var view = new CE.Views.PaymentPlanItem({model: model});
			this.views.unshift( view );

			view.render().$el.hide().prependTo( this.$el ).fadeIn();
		}
	});

	/**
	 * payment plan list item
	*/
	CE.Views.PaymentPlanItem		= Backbone.View.extend({
		tagName : 'li',
		className : 'item',
		events : {
			'click a.act-edit' : 'editPlan',
			'click a.act-del' : 'removePlan'
		},
		initialize: function(){

			this.model.bind('updated', this.render, this );
			this.model.bind('detroy', this.fadeOut, this);
			this.model.bind('remove', this.fadeOut, this);
		},
		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		template : _.template("<div class='sort-handle'></div><span>{{ title }}<# if( _et_featured == 1) { #> <em class='icon-text'>^</em><# } #></span> {{ backend_text }}" +
			"<div class='actions'>" +
				"<a href='#' title='Edit' class='icon act-edit' rel='id' data-icon='p'></a> " +
				"<a href='#' title='Delete' class='icon act-del' rel='id' data-icon='D'></a>" +
			"</div>"),
		render : function(){
			this.$el.html( this.template(this.model.toJSON()) ).attr('data', this.model.id ).attr('id', 'payment_' + this.model.id);
			return this;
		},

		blockItem : function(){
			this.blockUi = new CE.Views.BlockUi();
			this.blockUi.block(this.$el);
		},

		unblockItem: function(){
			this.blockUi.unblock();
		},

		editPlan : function(event){
			event.preventDefault();

			if ( this.editForm && this.$el.find('.engine-payment-form').length > 0 ){
				this.editForm.closeForm(event);
			}
			else{
				this.editForm = new CE.Views.PaymentEditForm({ model: this.model, parent: this.$el });
			}

		},

		removePlan : function(event){
			// ask user if he really want to delete
			if ( !confirm(et_globals.confirm_delete_plan) ) return false;
			
			event.preventDefault();
			var view = this;

			// call delete request
			this.model.remove({
				beforeSend: function(){
					view.blockItem();
				},
				success: function(resp){
					view.unblockItem();
				}
			});
		},

		fadeOut : function(){
			this.$el.fadeOut(function(){ $(this).remove(); });
		}
	});

	//	=============================================
	//	View Payment Edit Form
	//	=============================================
	CE.Views.PaymentEditForm = Backbone.View.extend({
		tagName : 'div',
		events : {
			'submit form.edit-plan' : 'savePlan',
			'click .cancel-edit' : 'cancel'
		},
		template : '', //_.template( $('#template_edit_form').html() ),
		render : function(){
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},
		initialize : function(options){
			// apply template for view
			_.templateSettings = {
			    evaluate    : /<#([\s\S]+?)#>/g,
				interpolate : /\{\{(.+?)\}\}/g,
				escape      : /<#-([\s\S]+?)#>/g,
			};
			
			if ( $('#template_edit_form').length > 0 )
				this.template = _.template( $('#template_edit_form').html() );
			this.options	=	options;
			this.model.bind('update', this.closeForm, this);
			this.appear();
		},

		appear : function(){
			this.render().$el.hide().appendTo( this.options.parent ).slideDown();
		},

		savePlan : function(event) {
			event.preventDefault();
			var form = this.$el.find('form');
			var view = this;
			if(form.valid()) {
				var price = form.find('input[name=price]').val(),
					featured = form.find('input[type=checkbox][name=featured]').is(':checked') ? 1 : 0;
				this.model.set({
					title: form.find('input[name=title]').val(),
					_regular_price: price,
					et_price : price,

					et_duration: form.find('input[name=duration]').val(),

					_et_featured: featured,
					et_featured: featured,

					et_number_posts: form.find('input[name=quantity]').val(),
					et_description: form.find('input[name=payment_desc]').val(),
				});
				this.model.save(this.model.toJSON(), {
					beforeSend : function(){
						view.loading = new CE.Views.LoadingButton({el : form.find('.add_payment_plan') });
						view.loading.loading();
					},
					success : function(){
						view.loading.finish();
						view.closeForm();
					}
				});
			}
		},
		cancel : function(event){
			event.preventDefault();
			this.closeForm();
		},
		closeForm : function(){
			this.$el.slideUp( 500, function(){ $(this).remove(); });
		}
	});

	/**
	 * payment plan model
	*/

	CE.Models.PaymentPlan = Backbone.Model.extend({
		initialize : function(){},
		parse : function(resp){
			if ( resp.data ){
				resp.data.id	=	resp.data.ID;
				return resp.data;
			}
		},

		remove : function(options){
			this.sync('delete', this, options);
		},

		add : function(options){
			this.sync('add', this, options);
		},

		sync	: function(method, model, options) {
			options	= options || {};
			var success	= options.success || function(resp){ };
			var beforeSend	= options.beforeSend || function(){ };
			var params		= _.extend(ajaxParams, options);
			var thisModel	= this;
			var action	= 'et_sync_paymentplan';

			if ( options.data ){
				params.data = options.data;
			}
			else {
				params.data = model.toJSON();
			}

			params.success = function(resp) {
				thisModel.set( thisModel.parse(resp) );
				switch( method ){
					case 'add':
						pubsub.trigger('je:setting:paymentPlanAdded', thisModel, resp);
						break;
					case 'delete':
						pubsub.trigger('je:setting:paymentPlanRemoved', thisModel, resp);
						thisModel.trigger('remove');
						//thisModel.destroy();
						break;
					case 'update':
						thisModel.trigger('updated');
						pubsub.trigger('je:setting:paymentPlanUpdated', thisModel, resp);
						break;
					default :
						pubsub.trigger('je:setting:paymentPlanSynced', thisModel, resp);
						break;
				}
				success(resp);
			};

			params.beforeSend = function(){
				beforeSend();
			};

			//params.method	= method;
			params.data = jQuery.param( {method : method, action : action, content : params.data });

			return jQuery.ajax(params);
		}
	});
	

	CE.Collections.PaymentPlans	= Backbone.Collection.extend({
		model : CE.Models.PaymentPlan,
		initialize : function () {

		}

	});

	var SettingRouter = Backbone.Router.extend({
		routes :{	
			'section/:section' : 'openSection'
		},

		openSection: function(section){
			var target 	= $('a[href="#section/' + section + '"]'),
				content = $('#' + section);

			$('.inner-menu a.section-link').removeClass('active');
			$('.et-main-main').hide();
			target.addClass('active');
			content.show();
		}
	});

	CE.Views.UpdateSettings	=	Backbone.View.extend({
		el : 'div#setting-update',
		events : {
			'keyup #license_key' 								: 'keyupLicenseKey',
			'change #license_key' 								: 'changeLicenseKey'
		},
		initialize : function () { },
		keyupLicenseKey: function(e){
			var view = this,
				input = $(e.currentTarget),
				value = input.val();
			if (this.previousValue != value){
				this.previousValue = value;
				if (this.timing) clearTimeout(this.timing);
				this.timing = setTimeout(function(){ view.update_license(value) }, 3000);
			}
		},

		changeLicenseKey : function(e){
			var view = this,
				input = $(e.currentTarget),
				value = input.val();
			if (this.timing) clearTimeout(this.timing);
			this.update_license(value);
		},

		update_license : function(value){
			var loading 		= $('.license-field'),
				loading_url 	= et_globals.imgURL + '/loading.gif',
				icon			= $('<span class="icon"></span>').append( $('<img src="' + loading_url + '">') );
			$.ajax({
					url: et_globals.ajaxURL,
				type: 'POST',
				data: {
					action 	: 'et-update-license-key',
					key 	: value
				},
				beforeSend: function(){
					loading.append(icon);
					// show the loading image
				},
				success: function(resp){
					// receive response from server
					icon.find('img').remove();
					icon.attr('data-icon', '3');
					setTimeout(function(){ $(icon).fadeOut('normal', function(){ $(this).remove(); }) }, 2000);
				}
			})
		}
	});

	CE.Views.LanguageSettings = Backbone.View.extend({
		el : 'div#setting-language',
		events : {
			'click button#save-language' 		: 'saveLanguage',
			'click ul.list-language a'  		: 'changeLanguage',
			'change select#base-language'	    : 'loadTranslationForm'	,
			'keyup  #new-language-name'			: 'handleNewLangForm',
			'click  .add-lang'					: 'showAddnewLangForm',
			'change #language-list textarea'	: 'markLanguageChange'
		},

		initialize : function () {
			$('textarea.autosize').attr('row',1).autosize();
		},

		changeLanguage : function (event) {
			event.preventDefault();
			var $li 	=	$(event.currentTarget);
			var action		=	$li.attr('href');
			action			=	action.replace ('#', '');
			var blockUi 	= new CE.Views.BlockUi();
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : { 
					action 	  :  action,
					new_value :  $li.attr('rel')
				},
				beforeSend : function(){
					blockUi.block($(event.currentTarget));
				},
				success : function(response){
					blockUi.unblock();					
					// update view
					if( response) {
						$container	=	$li.parents('ul');
						$container.find('.active').removeClass('active');
						$li.addClass('active');
						window.location.reload();
					}				
				}
			});
			
		},
		
		markLanguageChange : function (event) {
			event.preventDefault ();
			var target	=	$(event.currentTarget);

			target.addClass('changed');

			var container	=	target.parents('.form-item');

			container.find('input').addClass('changed');

		},

		saveLanguage : function (event) {
			event.preventDefault ();

			// prevent sending if button are disable
			if ( this.isSaveingLanguage ) return false;
			var button 		= 	$(event.currentTarget);

			var form 		=	$('#setting-language').find ('form#language-list'),
				lang_name	=	$('#setting-language').find('select#base-language').val (),
				view 		= 	this;

			var title 		= 	button.html(),
				data		= 	'',
				loadingBtn 	= new CE.Views.LoadingButton({ el : $(button)});

			form.find('.changed').each (function () {
				data 	= 	data + $(this).attr('name')+'='+encodeURIComponent($(this).val())+'&';
			});


			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : 
					data + 'action=et-save-language&lang_name='+lang_name
				,
				beforeSend : function(){
					this.isSaveingLanguage = true;
					loadingBtn.loading();
				},
				success : function(reponse){
					loadingBtn.finish();
					// update view
					this.isSaveingLanguage = false;
				}
			});
		},

		showAddnewLangForm : function  (event) {
			var $current	=	$(event.currentTarget);
			var container 	= $current.parent();
			$current.fadeOut(300, function(){
				container.find('.input-new-lang').fadeIn(300).focus();
			});
		},

		handleNewLangForm: function(e){
			var input = $(e.currentTarget),
				containter = input.parent(),
				button = containter.find('button');

			if ( e.which == 13 ){ // save the new lang
				this.addNewLanguage(input.val());
			} else if ( e.which == 27){ // escape, cancel the new lang form
				this.closeAddLang();
			}
		},

		closeAddLang : function(){
			$('.list-language li.new-language input').val('').fadeOut(300, function(){
				$('.list-language li.new-language button').fadeIn(300);
			});
		},

		// event add new language 
		addNewLanguage : function(name) {
			if ( name == '' ) return false;

			var $container	=	$('#setting-language').find('ul.list-language'),
				lang_name	=	name,
				blockUi 	= new CE.Views.BlockUi(),
				view 		= this;

			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : {
					lang_name : lang_name,
					action    : 'et-add-new-lang'
				},
				beforeSend : function(){
					blockUi.block($('.list-language li.new-language div.lang-field-wrap')); // block the input field
				},
				success : function(reponse){
					blockUi.unblock();
					// update view translation
					if(reponse.success) {					
						//$container.find('.active').removeClass('active');
						$container.find('li.new-language').before('<li><a class="actives" href="et-change-language" rel="' + reponse.lang_name + '">'+reponse.lang_name+'</a></li>');
						$('#base-language').append('<option value="'+reponse.lang_name+'">'+reponse.lang_name+'</option>');
						// hide the form
						$('.list-language li.new-language button').show();
						$('.list-language li.new-language input').val('').hide();
					}
				}
			});
			
		},

		loadTranslationForm : function (event) {
			if ( $(event.currentTarget).val() == '' ) return false;
			var $form 	=	$('#setting-language').find('form#language-list');
			var lang_name	=	$(event.currentTarget).val();
			$.ajax ({
				url  : et_globals.ajaxURL,
				type : 'post',
				data : {
					lang_name : lang_name,
					action    : 'et-load-translation-form'
				}
				,
				beforeSend : function(){
					$form.html('<img src="'+et_globals.imgURL+'/loading.gif" />');
				},
				success : function(reponse){
					// update view translation
					if(reponse.success) {
						$form.html (reponse.data);

						// var _bulletsCenter = 15;	//	number of bullets in the middle, should always be an odd number
						// var _bulletsSide = 2;	//	number of bullets left and right
						// $('#language-list').carouFredSel({
						// 	items: 1,
						// 	pagination: '#pager',
						// 	auto :  {
						// 		play : false
						// 	},
						// 	scroll : {
						// 		duration : 500
						// 	},
							
						// 	width : "100%",
						// 	height : "variable"
						// });

						// $('#language-list').bind('updatePageStatus.cfs', function() {
						// 	var _bullets = $('#pager').children();
						// 	_bullets.removeClass( 'hidden' ).removeClass( 'ellipsis' );

						// 	var _pagesTotal = $(this).children().length;
						// 	var _pagesCurrent = $(this).triggerHandler( 'currentPosition' );
							
						// 	if (_pagesTotal > _bulletsCenter + (_bulletsSide * 2) + 2) {

						// 		//	1 2 3 |4| 5 6 7 8 .. 14 15	
						// 		if (_pagesCurrent < Math.floor(_bulletsCenter / 2) + _bulletsSide + 2) {
						// 			var start = _bulletsSide + _bulletsCenter + 1;
						// 			var end = _pagesTotal - _bulletsSide - 1;
						// 			hideBullets( _bullets, start, end );
						// 			_bullets.eq( end ).addClass( 'ellipsis' );
								
						// 		//	1 2 .. 8 9 10 |11| 12 13 14 15	
						// 		} else if (_pagesCurrent > _pagesTotal - (Math.ceil(_bulletsCenter / 2) + _bulletsSide) - 2) {
						// 			var start = _bulletsSide + 1;
						// 			var end = _pagesTotal - (_bulletsSide + _bulletsCenter + 1);
						// 			hideBullets( _bullets, start, end );
						// 			_bullets.eq( start - 1 ).addClass( 'ellipsis' );
							
						// 		//	1 2 .. 6 7 |8| 9 10 .. 14 15	
						// 		} else {
						// 			var start = _bulletsSide + 1;
						// 			var end = _pagesCurrent - Math.floor(_bulletsCenter / 2);
						// 			hideBullets( _bullets, start, end );
						// 			_bullets.eq( start - 1 ).addClass( 'ellipsis' );
									
						// 			var start = _pagesCurrent + Math.ceil(_bulletsCenter / 2);
						// 			var end = _pagesTotal - _bulletsSide - 1;
						// 			hideBullets( _bullets, start, end );
						// 			_bullets.eq( end ).addClass( 'ellipsis' );
						// 		}
						// 	}
						// }).trigger( 'updatePageStatus.cfs' );

					} else {

					}
				}
			});	
		}
	});
	
	CE.Views.Settings	=	Backbone.View.extend({
		el : 'div#engine_setting_content',
		initialize : function () {

			// setup router
			this.router  = new SettingRouter();
			Backbone.history.start();

			/**
			 * init sub view 
			*/
			new CE.Views.PaymentSettings();
			new CE.Views.UpdateSettings();
			new CE.Views.LanguageSettings();
			new CE.Views.AdTax();

			this.loading = new CE.Views.BlockUi();

			// branding settings
			// File branding 
			this.uploaderIDs	= ['website_logo','mobile_icon'];
			this.uploaderThumbs	= ['large','thumbnail'];
			this.uploaders		= [];

			var blockUi = new CE.Views.BlockUi(),
				cbBeforeSend = function(ele){
					button = $(ele).find('.image');
					blockUi.block(button);
				},
				cbSuccess = function(){
					blockUi.unblock();
				};

			// loop through the array to init uploaders
			for( i=0; i<this.uploaderIDs.length; i++ ){
				// get the container of the target
				$container	= this.$('#' + this.uploaderIDs[i] + '_container');				

				this.uploaders[this.uploaderIDs[i]]	= new CE.Views.File_Uploader({
					el					: $container,
					uploaderID			: this.uploaderIDs[i],

					thumbsize			: this.uploaderThumbs[i],
					multipart_params	: {
						_ajax_nonce	: $container.find('.et_ajaxnonce').attr('id'),					
						action		: 'et-change-branding',
						imgType		: this.uploaderIDs[i]
					},
					filters         : [
                    	{ title : 'Image Files', extensions : 'jpg,jpeg,gif,png,ico' }
                	],

					cbUploaded	: function(up,file,res){
						if(res.success){
							$('#'+this.container).parents('.desc').find('.error').remove();
						} else {
							$('#'+this.container).parents('.desc').append('<div class="error">'+res.msg+'</div>');
						}
					},
					beforeSend	: cbBeforeSend,
					success		: cbSuccess
				});
			}
			// end file branding
			$('.select-style select').styleSelect();

		} ,

		events: {			
			'change .option-item' 								: 'onChangeOption',					
			'click .desc .payment'								: 'togglePaymentSetting',
			'click #setting-mail-template .reset-default'		: 'resetDefaultMailTemplate',			
			'click .payment a.deactive' 						: 'deactiveOption',
			'click .payment a.active'   						: 'activeOption',
			'focusout .payment-setting textarea'				: 'updateHeadLine',

		},

		updateHeadLine : function (event) {        
			event.preventDefault();
            var view    = this,
                element = $(event.currentTarget),
                id      = $(event.currentTarget).attr('id'),
                icon    = $(element).closest('.wp-editor-wrap').next(),
            	ed   	= tinyMCE.get(id);
            
            var mail    = element.hasClass('ce-mail'),
            	name    = $(event.currentTarget).attr('name'),
            	lang    = element.hasClass('autosize');
            
            if(name =='et_cash_message')
                    return false;
            // check if language text--> false
            if(lang && id !='google_analytics' )
                    return false;
                // in case do not tiny_mce                        
                        if(typeof  ed === 'undefined' && ed != null){
                                var new_value = $(event.currentTarget).val();                                        
                        } else {                            
                                var new_value = $('#'+id).val();                              
                        }                                
                        if(mail){                                 
                                /*
                                * update mail template 
                                */                                
                                view.updateMailTemplate (name, new_value , { 
                                        beforeSend: function(){
                                                view.loading.block($(event.currentTarget).parents('.form-item'));
                                        }, success: function(){
                                                view.loading.unblock();                                               
                                                if(new_value.length == 0){
                                                        icon.addClass('color-error')
                                                        icon.attr ('data-icon','!');                                                      
                                                } else {
                                                        icon.removeClass('color-error')
                                                        icon.attr ('data-icon','3');
                                                }                                              
                                        } 
                                });
                        } else {
                                
                                view.updateOption (name, new_value , { 
                                        beforeSend: function(){
                                                view.loading.block($(event.currentTarget).parents('.form-item'));
                                        }, success: function(){
                                                icon = $(element).next();
                                                view.loading.unblock();
                                                if(new_value.length == 0){
                                                        icon.addClass('color-error')
                                                        icon.attr ('data-icon','!');                                                       
                                                } else {                                                        
                                                        icon.removeClass('color-error')
                                                        icon.attr ('data-icon','3');
                                                }
                                        } 
                                } );
                        }
        },

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
					action 	 : 'et-disable-option',
					gateway :  payment.attr('rel')
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
					action 	: 'et-enable-option',
					gateway :  payment.attr('rel'),
					label	:  payment.attr ('title')
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
					action: 'save-option',
					content: {
						name: name,
						value: value,
					}
				},
				beforeSend: function(){},
				success: function(){}
			}, params );

			$.ajax(params);
		},

		updateMailTemplate: function(name, value, params){
			var params = $.extend( {
				url: et_globals.ajaxURL,
				type: 'post',
				data: {
					action: 'save-mail-template',
					content: {
						name: name,
						value: value,
					}
				},
				beforeSend: function(){},
				success: function(){}
			}, params );

			$.ajax(params);
		},

		updatePaymentOption: function(name, value, params){
			var params = $.extend( {
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'save-payment-status',
					content: {
						name: name,
						value: value,
					}
				},
				beforeSend: function(){},
				success: function(){}
			}, params );

			$.ajax(params);
		},
		
		
		resetDefaultMailTemplate : function (event) {
			event.preventDefault ();
			var view = this;			
			var $target 	=	$(event.currentTarget),
				$textarea	=	$target.parents('.mail-template').find('textarea'),
				id 			=   $textarea.attr ('id'),
				mail_type	=	$textarea.attr ('name'),
				action 		=	'et-reset-mail-template';

			$.ajax ({
				url : et_globals.ajaxURL,
				type : 'post',
				data : {
					type : mail_type,
					action : action 
				},
				beforeSend : function (event) {
					//view.loading.block($target.parents('.mail-template').find('textarea'));
				},
				success : function ( response) {
					//view.loading.unblock();
					$textarea.val (response.msg);
					var ed 			=	tinyMCE.EditorManager.get(id);
					if(typeof ed !== 'undefined' && ed !== null)
						ed.setContent (response.msg);
					else 
						$('#'+id).val(response.msg);
					var icon = $target.parents('.mail-template').find('span.icon ');
					if(response.msg.length == 0){
							icon.addClass('color-error')
							icon.attr ('data-icon','!');
						} else {
							icon.removeClass('color-error')
							icon.attr ('data-icon','3');
					}
				}
			});
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
				//}
		},


		togglePaymentSetting : function (event) {
			event.preventDefault();
			var $target	=	$(event.currentTarget);
			$target.parents('.item').find('.payment-setting').slideToggle();
		}

		

	});
	
	function hideBullets( bullets, start, end ) {
		for ( var a = start; a < end; a++ ) {
			bullets.eq( a ).addClass( 'hidden' );
		}
	}

	$(document).ready(function ($){
		new CE.Views.Settings ();
	});
	

})(jQuery)