// declare everything inside this object
var CE = CE || {};

CE.Models        = CE.Models || {};
CE.Collections   = CE.Collections || {};
CE.Views         = CE.Views || {};
CE.Routers       = CE.Routers || {};

// the pub/sub object for managing event throughout the app
CE.pubsub        = CE.pubsub || {};
_.extend(CE.pubsub, Backbone.Events);

// create a shorthand for our pubsub
var pubsub  = pubsub || CE.pubsub;

// create a shorthand for the params used in most ajax request
CE.ajaxParams = {
        type        : 'POST',
        dataType    : 'json',
        url         : et_globals.ajaxURL,
        contentType : 'application/x-www-form-urlencoded;charset=UTF-8'
};
var ajaxParams = CE.ajaxParams;

/*************************************************
//                                              //
//              Classified Engine MODELS                                //
//                                              //
*************************************************/
// Model: Ad
(function ($) {

    $.fn.serializeObject = function(){

    var self = this,
        json = {},
        push_counters = {},
        patterns = {
            "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
            "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
            "push":     /^$/,
            "fixed":    /^\d+$/,
            "named":    /^[a-zA-Z0-9_]+$/
        };


    this.build = function(base, key, value){
        base[key] = value;
        return base;
    };

    this.push_counter = function(key){
        if(push_counters[key] === undefined){
            push_counters[key] = 0;
        }
        return push_counters[key]++;
    };

    $.each($(this).serializeArray(), function(){

        // skip invalid keys
        if(!patterns.validate.test(this.name)){
            return;
        }

        var k,
            keys = this.name.match(patterns.key),
            merge = this.value,
            reverse_key = this.name;

        while((k = keys.pop()) !== undefined){

            // adjust reverse_key
            reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

            // push
            if(k.match(patterns.push)){
                merge = self.build([], self.push_counter(reverse_key), merge);
            }

            // fixed
            else if(k.match(patterns.fixed)){
                merge = self.build([], k, merge);
            }

            // named
            else if(k.match(patterns.named)){
                merge = self.build({}, k, merge);
            }
        }

        json = $.extend(true, json, merge);
    });

    return json;
};

CE.Models.Post        =        Backbone.Model.extend({
    parse : function (res) {
        if (!res.success) {
            pubsub.trigger('ce:notification',{
                msg : res.msg,
                notice_type        : 'error'
            });
            return {};
        }

        if(res.method === 'create'){
            this.set('id', res.data.ID,{silent:true});
            this.set('ID', res.data.ID,{silent:true});
        }

        this.set('the_post_thumbnail' , res.data.the_post_thumbnail);
        /*
        * @since : 1.7.3
        */
        if(typeof res.data.data == 'object')
            this.set('the_post_thumbnail' , res.data.data.the_post_thumbnail);

        return res.data;
    },

    remove : function (options) {
        options = options || {};
        var prevStatus    = this.get('status'),
            success       = (typeof options.success === 'function') ? options.success : false;

        /*options.success = function(model, resp){
                pubsub.trigger('je:job:afterRemoveJob', model, resp, prevStatus);
                if(success){success(model, resp);}
        };*/
        this.sync('delete', this, options);
    },

    sync : function( method, model, options) {
        var params = _.extend({
                        type                : 'POST',
                        dataType            : 'json',
                        url                 : et_globals.ajaxURL,
                        contentType         : 'application/x-www-form-urlencoded;charset=UTF-8',
                        jobseeker_sync        : false
                }, options || {}),
                attrs;        

        method = (options && options.method) ? options.method : method;

        if (method === 'read') {
                params.type     = 'GET';
                params.data    = {id : model.id};
        } else {

            if (!params.data && model && (method === 'create' || method === 'update' || method === 'delete')) {
                /**
                 * get change attributes
                */
                attrs        = _.clone(model.attributes);
                if (options && options.saveData && method !== 'create') {
                        attrs        =        {};
                        _.each(options.saveData, function(element, index){
                                attrs[element]        =        model.attributes[element];
                        });
                        attrs['ID']        =        model.attributes['ID'];
                        attrs['id']        =        model.attributes['id'];
                }
                params.data     = attrs;
            }
        }
        params.data = jQuery.param ({action:model.action,method:method, content: params.data});
        return jQuery.ajax(params);
    }
});

CE.Models.Ad  = CE.Models.Post.extend({

// when having the id, fetch into this object as a company model
    author        : {},

    action      : 'et-product-sync',

    initialize  : function(){
            // bind all functions to this object
            //_.bindAll(this);

            if( this.has('author_id') ){
                    this.author        = new CE.Models.Seller({id:this.get('author_id')});
            }
            else {
                    this.author        = new CE.Models.Seller();
            }
            this.author.on('change', this.updateAdAuthor, this);
    },

    approve : function (options) {
        options = options || {};
        var options     =   _.extend (options, {saveData : ['post_status', 'update_type' ]}),
            success     =   (typeof options.success === 'function') ? options.success : false;

        options.success = function(model, resp){
            pubsub.trigger('ce:ad:afterApprove', model, resp );
            if(success){
                success(model, resp);
            }
        }

        this.set('post_status', 'publish');
        this.set('update_type', 'change_status' );
        this.save( 'post_status', 'publish' ,   options
        );
        
    },

    expire : function (options) {
        options = options || {};
        var options =   _.extend (options, {saveData : ['post_status', 'update_type']}),
            success     =   (typeof options.success === 'function') ? options.success : false;

        options.success = function(model, resp){
            pubsub.trigger('ce:ad:afterArchive', model, resp);
            if(success){
                success(model, resp);
            }
        }
        this.set('post_status', 'archive');
        this.set('update_type', 'change_status' );
        this.save('post_status', 'archive' , options);  
        
    }, 

    reject : function (options) {
        options = options || {};
        var options =   _.extend (options, {saveData : ['post_status', 'update_type']}),
            success     =   (typeof options.success === 'function') ? options.success : false;

        options.success = function(model, resp){
            pubsub.trigger('ce:ad:afterReject', model, resp);
            if(success){
                success(model, resp);
            }
        }

        this.set('post_status', 'reject');
        this.set('update_type', 'change_status' );
        this.save('post_status', 'reject' ,options); 
    
    },

    toggleFeatured : function (value, options) {
        options = options || {};
        var options =   _.extend (options, {saveData : ['et_featured', 'update_type']}),
        success     =   (typeof options.success === 'function') ? options.success : false;

        options.success = function(model, resp){
            pubsub.trigger('ce:ad:afterToggleFeature', model, resp);
            if(success){
                success(model, resp);
            }
        }

        this.set('_et_featured', value );
        this.set('et_featured', value );
        this.set('update_type', 'featured' );
        this.save('et_featured', value , options);
    }

});

CE.Collections.Ads  =   Backbone.Collection.extend({
    model       : CE.Models.Ad,
    comparator   : function(ad){
        var adDate  = new Date(ad.get('post_date'));

        // turn the whole things into a string & turn back into a negative number
        if( et_globals.orderby != 'date' ) {
            if( et_globals.orderby == et_globals._et_featured)
                return -( parseInt(ad.get(et_globals.orderby) + " " + adDate.getTime(),10));
            else {
                return -( parseInt(ad.get(et_globals.orderby) ,10 ) );
            }    
        } else {
            return -( parseInt( adDate.getTime(),10));
        }

    },

    initialize : function () {
        //this.listenTo(model, 'destroy', this.remove);
    }

});

// Model: Authentication
// used for authenticate & authorize the user
CE.Models.Auth     = Backbone.Model.extend({

    params        : {
        type        : 'POST',
        dataType    : 'json',
        url         : et_globals.ajaxURL,
        contentType : 'application/x-www-form-urlencoded;charset=UTF-8'
    },

    setUserName        : function ( value ) {
        this.set({ user_name : value }, {silent: true});
    },
    
    setEmail    : function( value ){
        this.set({ user_email : value }, {silent: true});
    },

    setPass : function(value){
        this.set({user_pass : value}, {silent: true});
    },

    setUserKey : function(value){
            this.set({user_key : value}, {silent : true});
    },

    changePassword : function(options){

        var params  = _.extend({
                        data        : {
                                action                        : 'et_change_pass',
                                user_old_pass        : this.get('user_old_pass'),
                                user_pass                : this.get('user_pass'),
                                user_pass_again        : this.get('user_pass_again')
                        }
                },this.params,options || {});

        params.beforeSend        = function(){
                pubsub.trigger('je:request:waiting');
                if(options && typeof options.beforeSend === 'function'){
                        options.beforeSend();
                }
        };

        params.success        = function(data,status,jqXHR){
                // trigger an event after change password
                pubsub.trigger('je:response:changePassword', data, status, jqXHR);
                if(options && typeof options.success === 'function'){
                        options.success(data,status,jqXHR);
                }
        };
        
        params.error        = function(jqXHR, textStatus, errorThrown){
                // throw a notice
                pubsub.trigger('je:notification', {
                        msg : textStatus,
                        notice_type : 'error'
                });
                if(options && typeof options.error === 'function'){
                        options.error(jqXHR, textStatus, errorThrown);
                }
        };

        return jQuery.ajax(params);
    },

    doAuth  : function(type, options){
        var params;

        if ( type === 'login' ){
                this.unset('user_pass_again', {silent: true});
                type = 'et_login';
        }
        else if (type === 'register'){
                type = 'et_register';
        }
        else {
                return false;
        }

        params  = _.extend({
            data : {
                action      : type,
                user_email  : this.get('user_email'),
                user_pass   : this.get('user_pass'),
                user_login  : this.get('user_login'),
                recaptcha_challenge_field : this.get('recaptcha_challenge_field'),
                recaptcha_response_field  : this.get('recaptcha_response_field'),

            }
        },this.params,options || {});
        
        params.data      =     _.extend(params.data, options);

        params.data.success        =        null;
        params.data.beforeSend        =        null;

        // overwrite before send event
        params.beforeSend  = function(){
            pubsub.trigger('et:request:waiting');
            if(options && typeof options.beforeSend === 'function'){
                    options.beforeSend();
            }
        };

        // overwrite success event
        params.success  = function(data,status,jqXHR){                        
            pubsub.trigger('et:response:auth', data, status, jqXHR);
            
            if(options && typeof options.success === 'function'){
                    
                    options.success(data,status,jqXHR);
            }
            if(!data.success){
                if(typeof Recaptcha.reload === 'function'){
                   $("form.form-login a.btn-reload").trigger("click");
                }
           }
        };

        params.error = function(jqXHR, textStatus, errorThrown){                        
            // throw a notice
            pubsub.trigger('ce:notification', {
                    msg : textStatus,
                    notice_type : 'error'
            });
            if(options && typeof options.error === 'function'){
                    
                    options.error(jqXHR, textStatus, errorThrown);
            }
        };

        return jQuery.ajax(params);
    },

    doLogout    : function(options){
        var params                = _.extend({
                data : {action:'et_logout'}
        },this.params,options || {});

        if(options && typeof options.beforeSend === 'function'){
                params.beforeSend = options.beforeSend;
        }
        params.success  = function(data,status,jqXHR){
                pubsub.trigger('et:response:logout', data, status, jqXHR);
                if(options && typeof options.success === 'function'){
                        options.success(data,status,jqXHR);
                }
        };

        return jQuery.ajax(params);
    },

    doResetPassword : function(options){

        var params = _.extend({
                data : {
                        action: 'et_reset_password',
                        user_login : this.get('user_name'),
                        user_pass : this.get('user_pass'),
                        user_key : this.get('user_key')
                }
        },this.params, options || {});

        params.beforeSend = function(){
                pubsub.trigger('et:request:waiting');
                if(options && typeof options.beforeSend === 'function'){
                        options.beforeSend();
                }
        };

        params.success        = function(data, status, jqXHR){
                pubsub.trigger('et:response:reset_password', data, status, jqXHR);
                if (options && typeof options.success === 'function'){
                        options.success(data, status, jqXHR);
                }
        };

        return jQuery.ajax(params);
    },

    doRequestResetPassword : function(options){

        var params = _.extend({
                data : {
                        action: 'et_request_reset_password',
                        user_login : this.get('user_email')
                }
        },this.params,options || {});

        params.beforeSend = function(){
                pubsub.trigger('et:request:requestResetPassWaiting');
                if(options && typeof options.beforeSend === 'function'){
                        options.beforeSend();
                }
        };

        params.success        = function(data, status, jqXHR){
                pubsub.trigger('et:response:request_reset_password', data, status, jqXHR);
                if (options && typeof options.success === 'function'){
                        options.success(data, status, jqXHR);
                }
        };

        return jQuery.ajax(params);
    }

});


// Model: Seller
CE.Models.Seller  = CE.Models.Auth.extend({

    defaults    : {
		display_name    		: '',
		et_full_location		: '',
		et_avatar				: '',
		et_phone				: ''
	},

    action  	: 'et_seller_sync',
    role        : 'seller',

    initialize        : function(){
        //_.bindAll(this.sync);
        CE.Models.Auth.prototype.initialize.call();
    },

    parse  : function(res){
        if(!res.success){
            pubsub.trigger('ce:notification', { notice_type : 'error', msg : res.msg} );
            return {};
        }
        else {
            pubsub.trigger('ce:notification', { notice_type : 'success', msg : res.msg} );
            return res.data;
        }
    },

    renderListItem : function(){
            return this.itemTemplate(this.toJSON);
    },

    setName        : function(value){
            this.set({display_name: value},{silent:true});
    },

    getName        : function(){
            return this.get('display_name');
    },

    sync	: function(method, model, options) {
		var params = _.extend({
				type        : 'POST',
				dataType    : 'json',
				url         : et_globals.ajaxURL,
				contentType : 'application/x-www-form-urlencoded;charset=UTF-8'
			}, options || {});

		if (method == 'read') {
			params.type = 'GET';
			params.action = model.action;
			params.data = {
				'id' : (model.id) ? model.id : '',
				'login_name' : (model.login_name) ? model.login_name : ''
			};

		}
		if (!params.data && model && (method == 'create' || method == 'update' || method == 'delete')) {
			attrs	= _.clone(model.attributes);
			if (options && options.saveData && method !== 'create') {
				attrs	=	{};
				_.each(options.saveData, function(element, index){
					// render education html
					attrs[element]	=	model.attributes[element];

				});
				attrs['ID']	=	model.attributes['ID'];
				attrs['id']	=	model.attributes['id'];
				params.data	=	attrs;
			}else {
				params.data     = model.toJSON();
			}

			params.action   = model.action;
			
		} 

         // overwrite success event
        params.success  = function(data,status,jqXHR){                        
            pubsub.trigger('et:response:auth', data, status, jqXHR);
            if(options && typeof options.success === 'function'){    
                options.success(data,status,jqXHR);

            }

           if(!data.success){
                if(typeof Recaptcha != 'undefined'){
                   $("a.btn-reload").trigger("click");               
                }
           }
        };
		
		if (params.type !== 'GET') {
			params.processData = false;
		}

		params.data = jQuery.param({action:params.action,method:method,content:params.data});
		
		// Make the request.
		return jQuery.ajax(params);
	}

});

CE.Views.LoadingEffect = Backbone.View.extend({
    initialize : function(){},
    render : function(){
            this.$el.html(et_globals.loadingImg);
            return this;
    },
    finish : function(){
            this.$el.html(et_globals.loadingFinish);
            var view = this;
            setTimeout(function(){
                    view.$el.fadeOut(500, function(){ $(this).remove(); });
            }, 1000);
    },
    remove : function(){
            view.$el.remove();
    }
});

CE.Views.BlockUi = Backbone.View.extend({
    defaults : {
            image : et_globals.imgURL + '/loading.gif',
            opacity : '0.5',
            background_position : 'center center',
            background_color : '#ffffff'
    },

    isLoading : false,

    initialize : function(options){
            //var defaults = _.clone(this.defaults);
            options = _.extend( _.clone(this.defaults), options );

            var loadingImg = options.image;
            this.overlay = $('<div class="loading-blur loading"><div class="loading-overlay"></div><div class="loading-img"></div></div>');
            this.overlay.find('.loading-img').css({
                    'background-image' : 'url(' + options.image + ')',
                    'background-position' : options.background_position
                    });

            this.overlay.find('.loading-overlay').css({
                    'opacity'                        : options.opacity,
                    'filter'                        : 'alpha(opacity=' + options.opacity*100 + ')',
                    'background-color'        : options.background_color
                    });
            this.$el.html( this.overlay );

            this.isLoading = false;
    },

    render : function(){
            this.$el.html( this.overlay );
            return this;
    },

    block: function(element){
            var $ele = $(element);
            // if ( $ele.css('position') !== 'absolute' || $ele.css('position') !== 'relative'){
            //         $ele.css('position', 'relative');
            // }
            this.overlay.css({
                    'position'      : 'absolute',
                    'z-index'       : 2000,
                    'top'           : $ele.offset().top,
                    'left'          : $ele.offset().left,
                    'width'         : $ele.outerWidth(),
                    'height'        : $ele.outerHeight()
            });

            this.isLoading = true;

            this.render().$el.show().appendTo( $('body') );
    },

    unblock: function(){
            this.$el.remove();
            this.isLoading = false;
    },

    finish : function(){
            this.$el.fadeOut(500, function(){ $(this).remove();});
            this.isLoading = false;
    }
});

CE.Views.LoadingButton = Backbone.View.extend({
    dotCount : 3,
    isLoading : false,
    initialize : function(){
            if ( this.$el.length <= 0 ) return false;
            var dom = this.$el[0];
            //if ( this.$el[0].tagName != 'BUTTON' && (this.$el[0].tagName != 'INPUT') ) return false;

            if ( this.$el[0].tagName == 'INPUT' ){
                    this.title = this.$el.val();
            }else {
                    this.title = this.$el.html();
            }

            this.isLoading = false;
    },
    loopFunc : function(view){
            var dots = '';
            for(i = 0; i < view.dotCount; i++)
                    dots = dots + '.';
            view.dotCount = (view.dotCount + 1) % 3;
            view.setTitle(et_globals.loading + dots);
    },
    setTitle: function(title){
            if ( this.$el[0].tagName === 'INPUT' ){
                    this.$el.val( title );
            }else {
                    this.$el.html( title );
            }
    },
    loading : function(){
            //if ( this.$el[0].tagName != 'BUTTON' && this.$el[0].tagName != 'A' && (this.$el[0].tagName != 'INPUT') ) return false;
            this.setTitle(et_globals.loading);
            
            this.$el.addClass('disabled');
            var view                = this;

            view.isLoading        = true;
            view.dots                = '...';
            view.setTitle(et_globals.loading + view.dots);

            this.loop = setInterval(function(){
                    if ( view.dots === '...' ) view.dots = '';
                    else if ( view.dots === '..' ) view.dots = '...';
                    else if ( view.dots === '.' ) view.dots = '..';
                    else view.dots = '.';
                    view.setTitle(et_globals.loading + view.dots);
            }, 500);
    },
    finish : function(){
            var dom                = this.$el[0];
            this.isLoading        = false;
            clearInterval(this.loop);
            this.setTitle(this.title);
            this.$el.removeClass('disabled');
    }
});

// View: Modal Box
CE.Views.Modal_Box   = Backbone.View.extend({
        defaults    : {
                top         : 100,
                overlay     : 0.5
        },
        $overlay    : null,
        events : {
            'click .close' : 'close'
        },

        initialize  : function(){
            // bind all functions of this object to itself
            //_.bindAll(this.openModal);
            // update custom options if having any
            this.options  = $.extend(this.defaults,this.options);
        },

        openModal   : function(){
            var view = this;
            this.$el.modal('show');
            $('body').addClass('modal-open');           
        },

        closeModal   : function(time, callback){
            var modal = this;
            modal.$el.modal('hide');
            $('body').removeClass('modal-open');
            return false;
        }
});

CE.TaxFactory = (function () {

    // Storage for our vehicle types
    var types                 = {};
    var tax_items        = {};
    return {
                getTaxModel: function ( type, data ) {
            var Tax = types[type];

            return (Tax ? new Tax(data) : null);
        },

        registerTaxModel: function ( type, Tax ) {
           // var proto = Tax.prototype;

            // only register classes that fulfill the Tax contract
          //  if ( proto.drive && proto.breakDown ) {
                types[type] = Tax;
           // }

            return CE.TaxFactory;
        },

        getTaxItem: function ( type, data ) {
            var TaxItem = tax_items[type];

            return (TaxItem ? new TaxItem(data) : null);
        },

        registerTaxItem: function ( type, TaxItem ) {
           // var proto = TaxItem.prototype;

            // only register classes that fulfill the TaxItem contract
           // if ( proto.drive && proto.breakDown ) {
                tax_items[type] = TaxItem;
           // }

            return CE.TaxFactory;
        }
    };
})();

/**
 * Model Job Tax
 */
CE.Models.Tax = Backbone.Model.extend({
        initialize        : function(){
                this.action        =        '';
        },
        parse                : function(resp){
                if ( resp.data )
                        return resp.data;
        },
        remove: function(options){
                var params                = _.extend(ajaxParams, options);

                var action        = this.action;

                params.data = _.extend( params.data, this.toJSON() );

                params.data = jQuery.param( {method : 'delete', action : action, content : params.data });

                return jQuery.ajax(params);
        },

        sync                : function(method, model, options) {
                var params                = _.extend(ajaxParams, options);

                var action        = this.action;

                params.data        = model.toJSON();
                //params.method        = method;
                params.data = jQuery.param( {method : method, action : action, content : params.data });

                return jQuery.ajax(params);
        }
});

// ===============================
// Backend Tax Item View
// ===============================
CE.Views.TaxItem = Backbone.View.extend({
    tagName : 'li',
    events : {
            'click a.act-del'                                                        : 'displayReplaceList',
            'click a.act-open-form'                                                : 'openForm',
            'submit .form-sub-tax'                                                : 'addSubTax',
            'click .form-sub-tax a.act-add-sub'                 : 'addSubTax',
            'keyup .form-sub-tax a.act-add-sub'                 : 'keyupSubTax',
            'change input.tax-name'                              : 'updateName',
            'keyup .new-tax'                                                        : 'cancelAddition'
    },
    template : _.templateSettings = {
        evaluate    : /<#([\s\S]+?)#>/g,
        interpolate : /\{\{(.+?)\}\}/g,
        escape      : /<#-([\s\S]+?)#>/g,
    },
    sub_template : _.templateSettings = {
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
                                            <input class="bg-grey-input tax-name" rel="{{id}}" type="text" value="{{name}}"> \
                                    </div> \
                            </div>'),

    sub_template : _.template('<li class="form-sub-tax disable-sort" id="tax_{{ id }}"> \
                                    <div class="container">\
                                            <!--        <div class="sort-handle"></div>  --> \
                                            <div class="controls controls-2">\
                                                    <a class="button act-add-sub" title=""> \
                                                            <span class="icon" data-icon="+"></span> \
                                                    </a>\
                                            </div>\
                                            <div class="input-form input-form-2"> \
                                                    <form action="" class="" data-tax="'+this.tax_name+'">\
                                                            <input type="hidden" name="parent" value="{{id}}">\
                                                            <input class="bg-grey-input new-tax" name="name" type="text" placeholder="Enter category name"> \
                                                    </form> \
                                            </div> \
                                    </div>\
                            </li>'),

    initialize: function(){

    },

    render : function(){
            this.$el.append( this.template(this.model.toJSON()) ).addClass('tax-item').attr('id', 'tax_' + this.model.get('id'));
            return this;
    },

    openForm : function(event){
            var view = this;
            var id = $(event.currentTarget).attr('rel');

            if ( this.model.get('id') == id ){
                    $html = this.sub_template({id : id});
                    if (view.$el.find('ul').length == 0)
                            view.$el.append('<ul>');
                    view.$el.children('ul').append($html);
                    view.$el.children('ul').find('.new-tax').focus();
            }
    },

    keyupSubTax: function(event){
            event.preventDefault();
            if (keyup.which == 13)
                    this.addSubTax(event);
            return false;
    },

    addSubTax : function(event){
            event.stopPropagation();
            event.preventDefault();
            var view = this;
            var formContainer = view.$el.children('ul').children('li.form-sub-tax');
            var form = formContainer.find('form'),
                    loadingView = new CE.Views.LoadingEffect();

            if (form.find('input[name=name]').val() == '') return false;
            /**
             * use factory to create tax model
            */
            var model        =        CE.TaxFactory.getTaxModel (form.attr('data-tax'), {
                    parent        : form.find('input[name=parent]').val(),
                    name        : form.find('input[name=name]').val()
            });

            model.save(model.toJSON(), {
                    beforeSend : function(){
                            loadingView.render().$el.appendTo( formContainer.find('.controls') );
                    },
                    success: function(model, resp){
                            if (resp.success){
                                    loadingView.finish();
                                    /**
                                     * use factory to create object tax item
                                    */
                                    var subView = CE.TaxFactory.getTaxItem(form.attr('data-tax'),{model: model});
                                    /**
                                     * render tax item view
                                    */
                                    $(subView.render().el).insertBefore(view.$el.children('ul').find('li:last'));
                                    formContainer.remove();
                            }
            }});
    },
    /**
     * update tax name
    */
    updateName: function(event){
            var current = $(event.currentTarget),
                    id                = current.attr('rel'),
                    val                = current.val(),
                    loadingView = new CE.Views.LoadingEffect(),
                    view = this;

            if ( id == this.model.get('id') ){
                    this.model.set('name', val);
                    this.model.save(this.model.toJSON(), {
                            beforeSend : function(){
                                    loadingView.render().$el.appendTo( view.$el.children('.container').find('.controls') );
                            },
                            success: function(model, resp){
                                    loadingView.finish();
                            }
                    });
            }
    },

    displayReplaceList: function(event){
            event.stopPropagation();
            var $html                 = $($('#'+this.confirm_html).html()),
                container          = this.$el.children('.container'),
                view                 = this;
            var tax_list        = this.$el.parents('ul.list-tax').find('li');
            $html.find('select').html ('');
            _.each(tax_list, function (element, index) {
                    $html.find('select').append('<option value="' + $(element).find('input').attr('rel')+ '" >'+ $(element).find('input').val() +'</option>');
            });
            if (this.$el.find('ul > li').length > 0){
                    if(this.$el.parents('ul.list-tax').attr('data-tax') == et_globals.ce_ad_cat)
                        alert(et_setting.del_parent_cat_msg);
                    else
                        alert(et_setting.del_parent_location_msg);
                    return false;
            }

            // hide the container
            container.fadeOut('normal', function(){
                    $html.insertAfter(container).hide().fadeIn('normal', function(){
                            $html.find('button.accept-btn').bind('click', function(event){
                                    var def = $html.find('select').val();
                                    view.deleteTax(def);
                            });
                            $html.find('a.cancel-del').bind('click', function(event){
                                    $html.fadeOut('normal', function(){
                                            container.fadeIn();
                                    });
                            });
                            $html.bind('keyup', function(event){
                                    if (event.which == 27)
                                            $html.fadeOut('normal', function(){
                                                    container.fadeIn();
                                            });
                            });
                    });
                    // apply styling
                    $html.find('option[value=' + view.model.get('id') + ']').remove();
                    view.styleSelect();
            });
    },
    // perform delete action
    deleteTax : function(def){
            var view = this,
            blockUi = new CE.Views.BlockUi(),
            loadingView = new CE.Views.LoadingEffect();
            this.model.remove({
                    data : {
                            default_cat : def
                    },
                    beforeSend : function(){
                            blockUi.block(view.$el.find('.moved-tax'));
                    },
                    success : function(data){
                            blockUi.unblock();
                            //loadingView.finish();
                            if ( data.success )
                                    view.$el.fadeOut('normal', function(){ $(this).remove(); });
                    }
            });
    },

    cancelAddition : function(event){
            if (event.keyCode == 27) {
                    this.closeForm();
            }
    },

    closeForm : function(event){
            this.$el.children('ul').children('li.form-sub-tax').remove();
            if (this.$el.children('ul').find('li').length == 0)
                    this.$el.children('ul').remove();
    },

    styleSelect : function(){
        this.$(".select-style select").each(function(){
	        //var title = $(this).attr('title') || $(this).html;
	        var title = $(this).find('option:selected').html();
	        var arrow = "";
	        if ($(".select-style select").attr('arrow') !== undefined) 
	                arrow = " " + $(".select-style select").attr('arrow');

	        if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text() + arrow ;
	        $(this)
	                .css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
	                .after('<span class="select">' + title + arrow + '</span>')
	                .change(function(){
	                        val = $('option:selected',this).text() + arrow;
	                        $(this).next().text(val);
	                })
        });
    }
});

CE.Views.BackendTax = Backbone.View.extend({
    events: {
            'submit form.new_tax'               : 'addTax',
            'click form.new_tax .button'        : 'addTax',
            'click .input-form .bar-flag div'   : 'triggerChangeColor'
    },
    
    initialize: function(){
            this.initTax ();
            this.initView ();
    },        

    initView : function () {
            // this function should be override
            return false;
    },

    initTax : function () {
            // this function should be override by children classs
            return false;
    },

    sortTax: function(event, ui, action){
        var id = $(ui.item).attr('id').replace(/\D/g, ''),
                parent_id = $(ui.item).parents('li').length > 0 ? $(ui.item).parents('li').attr('id').replace(/\D/g, '') : '0' ,
                order = this.$el.find('.tax-sortable').nestedSortable('toArray', {startDepthCount: 0}) ,
                view = this;
        var block        =        new CE.Views.BlockUi();
        var params = {
                url : et_globals.ajaxURL,
                type : 'post',
                data : {
                        action : action,
                        content : {
                            order:JSON.stringify(order),
                            id : id,
                            parent : parent_id,
                            json    : true
                        }
                },
                beforeSend: function(){
                        block.block( view.$el.find('.list-tax') ) ;
                },
                success : function(resp){
                        block.unblock();
                }
        };

        $.ajax(params);                        
    },

    addTax: function (event){
        event.preventDefault();
        var form         = this.$el.find('form.new_tax'),
                view         = this,
                loading = new CE.Views.LoadingEffect(),
                container        =        view.$el.find('.list-tax');
        // prevent user add category too many times
        if (form.hasClass('disabled') || form.find('input[type=text]').val() == ''){
                return false;
        }
        
        // var model = new CE.Models.Tax({
        //         name : form.find('input[type=text]').val()
        // });
        
        var model =        CE.TaxFactory.getTaxModel(form.attr('data-tax'), {
                color : form.find('div.cursor').attr('data') ? form.find('div.cursor').attr('data') : 0,
                name : form.find('input[type=text]').val()
        });

        model.save( model.toJSON(), {
                beforeSend : function(){
                        form.addClass('disabled');
                        loading.render().$el.appendTo( form.find('.controls') );
                },
                success : function( model, resp){
                        form.removeClass('disabled');
                        loading.finish();
                        //adding to list
                        var view =  CE.TaxFactory.getTaxItem(form.attr('data-tax'),{model: model});                                
                        $(view.render().el).hide().appendTo( container ).fadeIn();
                        form.find('input[type=text]').val('');
                        
                        pubsub.trigger('ce:addTaxSucess');
                }
        } );
    },

    submitForm : function(event){
            var form = $(event.target).parents('form');
            form.submit();
    },

    triggerChangeColor : function (event) {
            var target                =        jQuery(event.currentTarget),
                    color                 =          jQuery(event.currentTarget).attr("class"),
                    color                 =          color.replace(" active",""),
                    appView                =        this;
            var code                 = target.attr('data');
            var action                 = target.parents('.input-form').attr('data-action');

            target.parent().parent().find(".cursor").removeAttr('class').addClass("cursor").addClass(color).attr('data', code);
            target.parent().parent().find("input").removeAttr('class').addClass("bg-grey-input").addClass(color);
            
            target.parent().remove();

            // send color data via ajax
            if ( $('.current-job-type').length > 0 ){
                    var term_id         = $('.current-job-type').attr('data');
                    appView.changeJobTypeColor(term_id, code, action );
            }
    },

    changeJobTypeColor : function(term_id, code, action ){
            if(action == 'undefined') return;
            var params         = ajaxParams,
                    view        =        this;
            
            var block        =        new CE.Views.BlockUi();
            params.data = {
                    action  : action,
                    content : {
                            term_id : term_id,
                            color : code
                    }
            };

            params.beforeSend = function(){ block.block( view.$el.find('.list-tax') ) ; }
            params.success = function(resp){
                    block.unblock();
            }

            $.ajax(params);
    }
});
/*
/*CE File uploader
*/
CE.Views.File_Uploader	= Backbone.View.extend({
    //options            : [],
    initialize        : function(options){
        _.bindAll( this , 'onFileUploaded', 'onFileAdded' , 'onFilesBeforeSend' , 'onUploadComplete');
        this.options           = options;
        this.uploaderID        = ( this.options.uploaderID ) ? this.options.uploaderID : 'et_uploader';

        this.config        = {
                runtimes			: 'gears,html5,flash,silverlight,browserplus,html4',
                multiple_queues     : true,
                multipart           : true,
                urlstream_upload    : true,
                multi_selection     : false,
                upload_later        : false,
                container           : this.uploaderID + '_container',
                browse_button       : this.uploaderID + '_browse_button',
                thumbnail           : this.uploaderID + '_thumbnail',
                thumbsize           : 'thumbnail',
                file_data_name      : this.uploaderID,
                max_file_size       : '1mb',
                //chunk_size                         : '1mb',
                // this filters is an array so if we declare it when init Uploader View, this filters will be replaced instead of extend
                filters                                : [
                    { title : 'Image Files', extensions : 'jpg,jpeg,gif,png' }
                ],
                multipart_params        : {
                    fileID                : this.uploaderID
                }
        };

        jQuery.extend( true, this.config, et_globals.plupload_config, this.options );

        this.controller        = new plupload.Uploader( this.config );
        this.controller.init();

        this.controller.bind( 'FileUploaded', this.onFileUploaded );
        this.controller.bind( 'FilesAdded', this.onFileAdded );
        this.controller.bind( 'BeforeUpload', this.onFilesBeforeSend );
        this.bind( 'UploadSuccessfully', this.onUploadComplete );

        if( typeof this.controller.settings.onProgress === 'function' ){
                this.controller.bind( 'UploadProgress', this.controller.settings.onProgress );
        }
        if( typeof this.controller.settings.onError === 'function' ){
                this.controller.bind( 'Error', this.controller.settings.onError );
        } else {

            this.controller.bind( 'Error', this.errorLog );
        }
        if( typeof this.controller.settings.cbRemoved === 'function' ){
                this.controller.bind( 'FilesRemoved', this.controller.settings.cbRemoved );
        }

    },

    errorLog : function (up , err) {
        var o = this;
        var message, details = "";
            message = '<strong>' + err.message + '</strong>';

            switch (err.code) {
                case plupload.FILE_EXTENSION_ERROR:
                    details = et_globals.plupload_config.msg.FILE_EXTENSION_ERROR.replace("%s",o.settings.filters.mime_types[0].extensions);
                    break;

                case plupload.FILE_SIZE_ERROR:
                    details = et_globals.plupload_config.msg.FILE_SIZE_ERROR.replace( "%s", o.settings.max_file_size );
                    break;

                case plupload.FILE_DUPLICATE_ERROR:
                    details = et_globals.plupload_config.msg.FILE_DUPLICATE_ERROR;
                    break;

                case self.FILE_COUNT_ERROR:
                    details = et_globals.plupload_config.msg.FILE_COUNT_ERROR;
                    break;

                case plupload.IMAGE_FORMAT_ERROR :
                   details = et_globals.plupload_config.msg.IMAGE_FORMAT_ERROR;
                    break;  

                case plupload.IMAGE_MEMORY_ERROR :
                    details = et_globals.plupload_config.msg.IMAGE_MEMORY_ERROR;
                    break;


                case plupload.HTTP_ERROR:
                    details = et_globals.plupload_config.msg.HTTP_ERROR;
                    break;
            }
            alert(details);

    },

	onFileAdded        : function(up, files){
        if( typeof this.controller.settings.cbAdded === 'function' ){
            this.controller.settings.cbAdded(up,files);
        }
        if(!this.controller.settings.upload_later){
            up.refresh();
            up.start();
        }
    },
    sprintf: function(str) {
            var args = [].slice.call(arguments, 1);

            return str.replace(/%[a-z]/g, function() {
                var value = args.shift();
                return Basic.typeOf(value) !== 'undefined' ? value : '';
            });
    },

    onFileUploaded        : function(up, file, res){

        res        = $.parseJSON(res.response);
        if( typeof this.controller.settings.cbUploaded === 'function' ){
                this.controller.settings.cbUploaded(up,file,res);
        }
        if (res.success){

                this.updateThumbnail(res.data);
                this.trigger('UploadSuccessfully', res);
        }
        
    },

    updateThumbnail        : function(res){
        var that                = this,
            $thumb_div        = this.$('#' + this.controller.settings['thumbnail']),
            $existing_imgs, thumbsize;
        
        if ($thumb_div.length>0){

            $existing_imgs        = $thumb_div.find('img'),
            thumbsize        = this.controller.settings['thumbsize'];
            if ($existing_imgs.length > 0){
                    
                    $existing_imgs.fadeOut(100, function(){                                                
                            $existing_imgs.remove();
                            if( _.isArray(res[thumbsize]) ){                                                                                        
                                    that.insertThumb( res[thumbsize][0], $thumb_div );
                            }
                    });
            }
            else if( _.isArray(res[thumbsize]) ){                                        
                    this.insertThumb( res[thumbsize][0], $thumb_div );
            }
        }
    },

    insertThumb        : function(src,target){
        jQuery('<img>').attr({
                        'id'        : this.uploaderID + '_thumb',
                        'src'        : src
                })
                // .hide()
                .appendTo(target)
                .fadeIn(300);
    },

    updateConfig        : function(options){
        if ('updateThumbnail' in options && 'data' in options ){
            this.updateThumbnail(options.data);
        }
        $.extend( true, this.controller.settings, options );
        this.controller.refresh();
    },

    onFilesBeforeSend : function(){
        if('beforeSend' in this.options && typeof this.options.beforeSend === 'function'){
                this.options.beforeSend(this.$el);
        }                        
    },
    onUploadComplete : function(res){
        if('success' in this.options && typeof this.options.success === 'function'){
                this.options.success(res);                                
        }                        
    }

});


$.fn.styleSelect = function () {
    this.each(function(){
        //var title = $(this).attr('title') || $(this).html;
        var title = $(this).find('option:selected').html();
        var arrow = "";
        if ($(".select-style select").attr('arrow') !== undefined) 
                arrow = " " + $(".select-style select").attr('arrow');

        if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text() + arrow ;
        $(this)
                .css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
                .after('<span class="select">' + title + arrow + '</span>')
                .change(function(){
                        val = $('option:selected',this).text() + arrow;
                        $(this).next().text(val);
                })
    });
}

})(jQuery);