(function($){

	CE.Models.CategoryTax	=	CE.Models.Tax.extend ({
		initialize : function () {
			this.action = 'et_sync_'+et_globals.ce_ad_cat;
		}
	});

	CE.Models.LocationTax	=	CE.Models.Tax.extend ({
		initialize : function () {
			this.action = 'et_sync_ad_location';
		}
	});
	/**
	 * Resume Category view
	*/
	CE.Views.CategoryTaxItem =  CE.Views.TaxItem.extend ({
		initialize : function (){
			CE.Views.TaxItem.prototype.initialize.call();
			this.confirm_html = 'temp_'+et_globals.ce_ad_cat+'_delete_confirm';
			this.tax_name	  = 'resume_category';
		},
		render : function(){
			this.$el.append( this.template(this.model.toJSON()) ).addClass('category-item tax-item').attr('id', 'cat_' + this.model.get('id'));
			return this;
		},

		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		template: _.template('<div class="container"> \
							<div class="sort-handle"></div> \
						<div class="controls controls-2"> \
							<a class="button act-open-form" rel="{{ id }}" title=""> \
								<span class="icon" data-icon="+"></span> \
							</a> \
							<a class="button act-del" rel="{{ id }}"> \
								<span class="icon" data-icon="*"></span> \
							</a> \
						</div> \
						<div class="input-form input-form-2"> \
							<input class="bg-grey-input tax-name" rel="{{ id }}" type="text" value="{{ name }}"> \
						</div> \
					</div>'),
		sub_template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		sub_template : _.template('<li class="form-sub-tax disable-sort" id="tax_{{ id }}"> \
						<div class="container">\
							<!--	<div class="sort-handle"></div>  --> \
							<div class="controls controls-2">\
								<a class="button act-add-sub" title=""> \
									<span class="icon" data-icon="+"></span> \
								</a>\
							</div>\
							<div class="input-form input-form-2"> \
								<form action="" class="" data-tax="'+et_globals.ce_ad_cat+'">\
									<input type="hidden" name="parent" value="{{id}}">\
									<input class="bg-grey-input new-tax" name="name" type="text" placeholder="Enter category name"> \
								</form> \
							</div> \
						</div>\
					</li>'),
	});

	/**
	 * Resume Category view
	*/
	CE.Views.LocationTaxItem =  CE.Views.TaxItem.extend ({
		initialize : function (){
			CE.Views.TaxItem.prototype.initialize.call();
			this.confirm_html = 'temp_ad_location_delete_confirm';
			this.tax_name	  = 'ad_location';

		},
		render : function(){
			this.$el.append( this.template(this.model.toJSON()) ).addClass('category-item tax-item').attr('id', 'loc_' + this.model.get('id'));
			return this;
		},
		template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		template: _.template('<div class="container"> \
							<div class="sort-handle"></div> \
						<div class="controls controls-2"> \
							<a class="button act-open-form" rel="{{ id }}" title=""> \
								<span class="icon" data-icon="+"></span> \
							</a> \
							<a class="button act-del" rel="{{ id }}"> \
								<span class="icon" data-icon="*"></span> \
							</a> \
						</div> \
						<div class="input-form input-form-2"> \
							<input class="bg-grey-input tax-name" rel="{{id}}" type="text" value="{{ name }}"> \
						</div> \
					</div>'),

		sub_template : _.templateSettings = {
		    evaluate    : /<#([\s\S]+?)#>/g,
			interpolate : /\{\{(.+?)\}\}/g,
			escape      : /<#-([\s\S]+?)#>/g,
		},

		sub_template : _.template('<li class="form-sub-tax disable-sort" id="tax_{{ id }}"> \
						<div class="container">\
							<!--	<div class="sort-handle"></div>  --> \
							<div class="controls controls-2">\
								<a class="button act-add-sub" title=""> \
									<span class="icon" data-icon="+"></span> \
								</a>\
							</div>\
							<div class="input-form input-form-2"> \
								<form action="" class="" data-tax="ad_location">\
									<input type="hidden" name="parent" value="{{id}}">\
									<input class="bg-grey-input new-tax" name="name" type="text" placeholder="Enter location name"> \
								</form> \
							</div> \
						</div>\
					</li>'),
	});


	/**
	 * backend tax view extended
	*/
	CategoryView	=	CE.Views.BackendTax.extend({
		initialize: function(){
			this.initTax ();
			this.initView ();
		},	

		initView : function () {
			var appView =	this,
				tax_type	=	this.$el.find('.list-job-input').attr('data-tax');

			$('div#ad-category .tax-sortable').nestedSortable({
				handle: '.sort-handle',
				items: 'li',
				toleranceElement: '.sort-handle',
				listType : 'ul',
				placeholder : 'ui-sortable-placeholder',
				dropOnEmpty : false,
				cancel : '.disable-sort',
				update : function(event, ui){
	            	appView.sortTax(event, ui, 'et_sort_'+et_globals.ce_ad_cat);
	            }
			});
		},

		initTax : function () {
			// this function should be override by children classs
			var tax_type	=	this.$el.find('.list-job-input').attr('data-tax'), view = this;
			_.each( this.$el.find('.list-job-input li.tax-item'), function(item){
				var $this = $(item);
				var jobLoc = {
					id : $this.find('.act-del').attr('rel'),
					name : $this.find('input[type=text]').val()
				}
				//var itemView	=	view.factory(tax_type,jobLoc, item) ;
				var itemView = new CE.Views.CategoryTaxItem( {model : new CE.Models.CategoryTax(jobLoc), el : item, confirm_html : 'temp_'+et_globals.ce_ad_cat+'_delete_confirm' } );
			} );
		}
	});

	/**
	 * backend tax view extended
	*/
	LocationView	=	CE.Views.BackendTax.extend({
		initialize: function(){
			this.initTax ();
			this.initView ();
		},

		initView : function () {
			var appView =	this,
				tax_type	=	this.$el.find('.list-job-input').attr('data-tax');

			$('div#ad-location .tax-sortable').nestedSortable({
				handle: '.sort-handle',
				items: 'li',
				toleranceElement: '.sort-handle',
				listType : 'ul',
				placeholder : 'ui-sortable-placeholder',
				dropOnEmpty : false,
				cancel : '.disable-sort',
				update : function(event, ui){
	            	appView.sortTax(event, ui, 'et_sort_ad_location');
	            }
			});
		},

		initTax : function () {
			// this function should be override by children classs
			var tax_type	=	this.$el.find('.list-job-input').attr('data-tax'), view = this;
			_.each( this.$el.find('.list-job-input li.tax-item'), function(item){
				var $this = $(item);
				var jobLoc = {
					id : $this.find('.act-del').attr('rel'),
					name : $this.find('input[type=text]').val()
				}
				//var itemView	=	view.factory(tax_type,jobLoc, item) ;
				var itemView = new CE.Views.LocationTaxItem( {model : new CE.Models.LocationTax(jobLoc), el : item, confirm_html : 'temp_ad_location_delete_confirm' } );
			} );
		}
	});

	CE.Views.AdTax = Backbone.View.extend ({
		el : 'div#classified_content',
		initialize : function () {
			var view = this;
			this.loading = new CE.Views.BlockUi();
			var position =	new CategoryView({el : $('div#ad-category') , action : 'et_sort_'+et_globals.ce_ad_catss }); //note 1.8.4 change
			var available =	new LocationView({el : $('div#ad-location') , action : 'et_sort_ad_location' }); // no 1.8.4 change

			CE.TaxFactory.registerTaxModel(et_globals.ce_ad_cat, CE.Models.CategoryTax);
			CE.TaxFactory.registerTaxModel('ad_location', CE.Models.LocationTax);

			CE.TaxFactory.registerTaxItem('ad_location', CE.Views.LocationTaxItem);
			CE.TaxFactory.registerTaxItem(et_globals.ce_ad_cat, CE.Views.CategoryTaxItem);

		}
	});

}(jQuery));