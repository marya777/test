(function($) {
    /**
     * Post ad view
     */
    CE.Views.Post_Ad = Backbone.View.extend({
        el: 'div#post-classified',
        ad: {},
        tpl_login_success: null,
        // event handlers for user interactions
        events: {
            // general
            'click div.head-step': 'selectStep',
            'submit div#step-auth form': 'submitAuth',
            'submit form#ad_form': 'submitAd',
            // step: package
            'click button.select-plan': 'selectPlan',
            // step: payment
            'click li.payment-button button': 'selectPayment',
            'click .login a': 'requestModalLogin',
            'blur input#et_full_location': 'gecodeMap',
            'keydown  input#_regular_price': 'pressTabToLocation',
            'keydown  input#et_price': 'pressTabToLocation',
        },
        gecodeMap: function(event) {
            var address = $(event.currentTarget).val();
            //gmaps = new GMaps
            if (typeof(GMaps) !== 'undefined') GMaps.geocode({
                address: address,
                callback: function(results, status) {
                    if (status == 'OK') {
                        var latlng = results[0].geometry.location;
                        $('#et_location_lat').val(latlng.lat());
                        $('#et_location_lng').val(latlng.lng());
                    }
                }
            });
        },
        initialize: function() {
            this.currentStep = '';
            var view = this,
                ad_data = JSON.parse($('#ad_data').html());
            if ($('#ad_data').length > 0) {
                this.ad = new CE.Models.Ad(ad_data);
                this.ad.set('renew', 1);
                this.ad.set('id', this.ad.get('ID'));
            } else {
                this.ad = new CE.Models.Ad();
                this.ad.set('et_carousels', []);
            }
            this.ad.on('change:et_payment_package', function() {
                if (!this.isNew()) {
                    this.save();
                    //this.updateProcess();
                }
                view.updateProcess();
            });
            this.uploading = false;
            /**
             * array of completed step
             */
            this.availableStep = ['step-plan'];
            this.finishStep = [];
            //JobEngine.app.currentUser	=	new JobEngine.Models.JobSeeker();
            if (CE.app.currentUser.get('id') === 'undefined') {
                CE.app.auth = new CE.Models.Seller();
                this.availableStep = ['step-plan', 'step-auth'];
            } else CE.app.auth = new CE.Models.Seller(CE.app.currentUser.attributes);
            CE.app.auth.on('change:id', this.updateProcess, this);
            var $user_avatar = $('#user_avatar_container'),
                blockUi = new CE.Views.BlockUi(),
                view = this;
            if ($user_avatar.length > 0) {
                this.avatar_uploader = new CE.Views.File_Uploader({
                    el: $user_avatar,
                    uploaderID: 'user_avatar',
                    thumbsize: 'thumbnail',
                    multipart_params: {
                        _ajax_nonce: $user_avatar.find('.et_ajaxnonce').attr('id'),
                        action: 'et-avatar-upload',
                        imgType: 'user_avatar'
                    },
                    cbUploaded: function(up, file, res) {
                        if (res.success) {
                            $user_avatar.find('input#et_avatar').val(res.attach_id);
                        } else {
                            pubsub.trigger('ce:notification', {
                                msg: res.msg,
                                notice_type: 'error'
                            });
                            //console.log (res);
                        }
                    },
                    beforeSend: function(element) {
                        blockUi.block($user_avatar.find('#user_avatar_browse_button'));
                    },
                    success: function() {
                        blockUi.unblock();
                    }
                });
            }
            new CE.Views.AdCarousel({
                el: $('#gallery_container'),
                model: this.ad
            });
            new CE.Views.AdCategoryList({
                model: this.ad
            });
            view.$el.find('input,textarea,select').each(function() {
                var name = $(this).attr('name');
                if (typeof name !== 'undefined' && typeof view.ad.get(name) !== 'undefined') {
                    if (name != 'category') $(this).val(view.ad.get(name));
                }
            });
            // setup chosen for view
            this.$('#ad_location').chosen({
                width: '460px'
            });
            //this.setupViewForCarousel();
            /**
             * form validate
             */
            this.adFormValidate();
            // handle authentication
            pubsub.on('et:response:auth', this.handleAuth, this);
            if ($('.mark-step').length > 0) {
                this.initAdPlan();
            }
            $("#reCaptcha .btn-reload").click(function() {
                Recaptcha.reload();
                return false;
            });
        },
        adFormValidate: function() {
            $("#step-auth form").validate({
                rules: {
                    user_login: 'required',
                    password: "required",
                    repeat_password: {
                        equalTo: "#password"
                    },
                    user_email: {
                        required: true,
                        remote: et_globals.ajaxURL + '?action=et_email_check_used'
                    }
                }
            });
            /**
             * form validate
             */
            var ad_require_fields = et_globals.ad_require_fields,
                required_price = $.inArray(et_globals.regular_price, ad_require_fields) == -1 ? false : true,
                required_full_local = $.inArray('et_full_location', ad_require_fields) == -1 ? false : true;
            if($('#_regular_price').length > 0 ) {
	            $("#step-ad form").validate({
	                ignore: "",
	                rules: {
	                    post_title: "required",
	                    et_full_location: {
	                        required: required_full_local
	                    },
	                    ad_category: "required",
	                    post_content: "required",
	                    _regular_price: {
	                        required: required_price,
	                        isMoney: required_price
	                    }
	                },
	                errorPlacement: function(label, element) {
	                    // position error label after generated textarea
	                    if (element.is("textarea")) {
	                        label.insertAfter(element.next());
	                    } else {
	                        label.insertAfter(element)
	                    }
	                }
	            });
	        }else {
	        	$("#step-ad form").validate({
	                ignore: "",
	                rules: {
	                    post_title: "required",
	                    et_full_location: {
	                        required: required_full_local
	                    },
	                    ad_category: "required",
	                    post_content: "required",
	                    et_price: {
	                        required: required_price,
	                        isMoney: required_price
	                    }
	                },
	                errorPlacement: function(label, element) {
	                    // position error label after generated textarea
	                    if (element.is("textarea")) {
	                        label.insertAfter(element.next());
	                    } else {
	                        label.insertAfter(element)
	                    }
	                }
	            });
	        }
        },
        selectStep: function(event) {
            var $target = $(event.currentTarget);
            var step = $target.parents('.post-ad-step');
            var available = this.availableStep;
            if (typeof this.ad.get('et_payment_package') === 'undefined') return;
            for (var i = this.finishStep.length - 1; i >= 0; i--) {
                $('#' + this.finishStep[i]).find('.head-step').addClass('finished');
            }
            if (step.find('.content-step').hasClass('current')) {
                // console.log(step.attr('id'));
                this.currentStep = step.attr('id');
                if (this.currentStep == 'step-ad') {
                    if ($('#step-ad form').valid() && !this.ad.get('uploadingCarousel') && this.ad.get(et_globals.ce_ad_cat).length > 0) {
                        this.showNextStep();
                    } else {
                        pubsub.trigger('ce:notification', {
                            notice_type: 'error',
                            'msg': et_globals.require_fields
                        });
                    }
                    return;
                }
                this.showNextStep();
                return;
            }
            for (var i = available.length - 1; i >= 0; i--) {
                $('.head-step').removeClass('active');
                var $content = $target.parents('#' + available[i]).find('.content-step');
                if ($content.length > 0) {
                    $target.parents('#' + available[i]).find('.head-step').addClass('active').removeClass('finished');
                    // $('#'+available[i]).find('.head-step').addClass('finished');
                    $('.content-step').removeClass('current').hide();
                    $content.slideToggle(200);
                    $content.addClass('current');
                    $('.current').parents('.post-ad-step').find('.head-step').removeClass('finished');
                    break;
                }
            };
        },
        initAdPlan: function() {
            var $target = $('button.mark-step'),
                $container = $target.closest('li'),
                amount = $target.attr('data-price'),
                $step = $container.closest('div.step');
            $('ul.post-step1 li').removeClass('selected');
            $container.addClass('selected');
            this.currentStep = 'step-plan';
            // set the job package of job model & free status
            if (parseFloat(amount) === 0) {
                this.ad.set({
                    is_free: 1
                });
            } else {
                this.ad.set({
                    is_free: 0
                });
            }
            if (typeof(CE.app.auth.get('payment_plans')) != 'undefined' && typeof(CE.app.auth.get('payment_plans')[planID]) != 'undefined' && CE.app.auth.get('payment_plans')[planID] > 0) {
                this.ad.set({
                    is_use_package: 1
                });
            } else {
                this.ad.set({
                    is_use_package: 0
                });
            }
            $step.find('div.step-plan-label').html($target.attr('data-label'));
            this.ad.set('et_payment_package', $target.attr('data-package'));
            this.finishStep.push('step-plan');
            this.showNextStep();
        },
        /**
         * select payment plan
         */
        selectPlan: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget),
                $container = $target.closest('li'),
                amount = $target.attr('data-price'),
                $step = $container.closest('div.step');
            // set the job package of job model & free status
            if (parseFloat(amount) === 0) {
                this.ad.set({
                    is_free: 1
                });
                if (parseInt(et_globals.limit_free_plan) > 0) {
                    var used = CE.app.auth.get('free_plan_used');
                    if (parseInt(used) >= parseInt(et_globals.limit_free_plan)) {
                        pubsub.trigger('ce:notification', {
                            msg: et_globals.limit_free_msg,
                            notice_type: 'error'
                        });
                        return false;
                    }
                }
            } else {
                this.ad.set({
                    is_free: 0
                });
            }
            $('ul.post-step1 li').removeClass('selected');
            $target.parents('li').addClass('selected');
            this.currentStep = 'step-plan';
            $step.find('div.step-plan-label').html($target.attr('data-label'));
            if (typeof(CE.app.auth.get('payment_plans')) != 'undefined' && typeof(CE.app.auth.get('payment_plans')[planID]) != 'undefined' && CE.app.auth.get('payment_plans')[planID] > 0) {
                this.ad.set({
                    is_use_package: 1
                });
            } else {
                this.ad.set({
                    is_use_package: 0
                });
            }
            this.ad.set('et_payment_package', $target.attr('data-package'));
            this.finishStep.push('step-plan');
            this.showNextStep();
        },
        submitAuth: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget);
            var view = this;
            var loading = new CE.Views.LoadingButton({
                el: $target.find('button.submit-auth')
            });
            if ($target.valid()) {
                this.currentStep = 'step-auth';
                $target.find("input[type=text],input[type=hidden],textarea,select").each(function(index, value) {
                    var name = $(this).attr('name');
                    CE.app.auth.set(name, $(this).val());
                });
                var saveData = [];
                var temp = new Array();
                $target.find('input[type=checkbox]:checked').each(function() {
                    var name = $(this).attr('name');
                    if (jQuery.inArray(name, temp) == -1) {
                        temp.push(name);
                        saveData.push(name);
                    }
                });
                for (var i = 0; i < temp.length; i++) {
                    var key = temp[i];
                    temp[key] = new Array()
                    view.$el.find('input[name=' + key + ']:checked').each(function() {
                        var name = $(this).attr('name');
                        temp[key].push($(this).val());
                    });
                    CE.app.auth.set(key, temp[key]);
                }
                // for radio
                $target.find('input[type=radio]:checked').each(function() {
                    CE.app.auth.set($(this).attr('name'), $(this).val());
                    saveData.push($(this).attr('name'));
                });
                CE.app.auth.set('user_pass', $target.find('input#password').val());
                CE.app.auth.set('et_phone', $target.find('input#phone_number').val());
                CE.app.auth.set('user_email', $target.find('input#user_email').val());
                CE.app.auth.set('display_name', $target.find('input#full_name').val());
                CE.app.auth.save('', '', {
                    saveData: saveData,
                    beforeSend: function() {
                        loading.loading();
                    },
                    success: function(model, res) {
                        loading.finish();
                        if (res.success) {
                            // pubsub.trigger('et:response:auth', res );
                            pubsub.trigger('ce:notification', {
                                notice_type: 'success',
                                msg: res.msg
                            });
                            view.finishStep.push('step-auth');
                            view.showNextStep();
                        }
                    }
                });
            }
        },
        submitAd: function(event) {
            event.preventDefault();
            var $form = $(event.currentTarget);
            var view = this;
            var loading = new CE.Views.LoadingButton({
                el: $form.find('button.btn')
            });
            if (this.ad.get(et_globals.ce_ad_cat).length == 0) {
                $('#category').parents('.controls').addClass('error');
            }
            if ($('#step-ad form').valid() && !this.ad.get('uploadingCarousel') && this.ad.get(et_globals.ce_ad_cat).length > 0) {
                $form.find('input[type=text],input[type=hidden],textarea,select').each(function() {
                    view.ad.set($(this).attr('name'), $(this).val());
                    // danng add
                });
                //danng add
                var temp = new Array();
                $form.find('input[type=checkbox]:checked').each(function() {
                    var name = $(this).attr('name');
                    if (jQuery.inArray(name, temp) == -1) temp.push(name);
                });
                for (var i = 0; i < temp.length; i++) {
                    var key = temp[i];
                    temp[key] = new Array()
                    view.$el.find('input[name=' + key + ']:checked').each(function() {
                        var name = $(this).attr('name');
                        temp[key].push($(this).val());
                    });
                    this.ad.set(key, temp[key]);
                }
                // for radio
                $form.find('input[type=radio]:checked').each(function() {
                    view.ad.set($(this).attr('name'), $(this).val());
                });
                // danng add.
                this.ad.set('post_author', CE.app.auth.get('id'));
                this.ad.save('', '', {
                    beforeSend: function() {
                        loading.loading();
                    },
                    success: function(model, res) {
                        loading.finish();
                        if (res.success) {
                            view.currentStep = 'step-ad';
                            if (typeof res.redirect_url !== 'undefined') {
                                window.location.href = res.redirect_url;
                                return;
                            } else {
                                view.showNextStep();
                                view.finishStep.push('step-ad');
                            }
                        } else {
                            if (typeof Recaptcha.reload === 'function') {
                                Recaptcha.reload();
                            }
                        }
                    }
                });
            } else {
                pubsub.trigger('ce:notification', {
                    notice_type: 'error',
                    'msg': et_globals.require_fields
                });
            }
        },
        selectPayment: function(event) {
            event.preventDefault();
            var $target = $(event.currentTarget).closest("li"),
                paymentType = $target.attr('data-type'),
                $button = $(event.currentTarget);
            var loading = new CE.Views.LoadingButton({
                el: $button
            });
            $.ajax({
                url: et_globals.ajaxURL,
                type: 'post',
                contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
                data: {
                    action: 'et_payment_process',
                    ID: this.ad.id,
                    author: this.ad.get('post_author'),
                    packageID: this.ad.get('et_payment_package'),
                    paymentType: paymentType,
                    coupon_code: $('#coupon_code').val()
                },
                beforeSend: function() {
                    loading.loading();
                },
                success: function(response) {
                    loading.finish();
                    if (response.data.ACK) {
                        $('#checkout_form').attr('action', response.data.url);
                        if (typeof response.data.extend !== "undefined") {
                            $('#checkout_form .payment_info').html('').append(response.data.extend.extend_fields);
                        }
                        $('#payment_submit').click();
                    } else {
                        pubsub.trigger('ce:notification', {
                            msg: response.errors[0],
                            notice_type: 'error'
                        });
                    }
                }
            });
        },
        requestModalLogin: function(event) {
            event.preventDefault();
            pubsub.trigger('et:request:auth');
        },
        // handler user login
        handleAuth: function(resp, status, jqXHR) {
            if (resp.status) {
                this.markStepCompleted($('#step-auth'));
                if (($.inArray('step-auth', this.availableStep) < 0)) this.availableStep.push('step-auth');
                if (($.inArray('step-ad', this.availableStep) < 0)) this.availableStep.push('step-ad');
                $('#step-auth').find('.post-step2').remove();
                $('#step-ad').find('.content-step').addClass('current').slideToggle().end().find('.head-step').addClass('active');
                this.ad.set('post_author', CE.app.currentUser.get('id'));
            }
            if (resp.success) {
                this.ad.set('post_author', CE.app.currentUser.get('id'));
                this.avatar_uploader.config.multipart_params._ajax_nonce = resp.data.logoajaxnonce;
            }
        },
        showNextStep: function() {
            if (this.currentStep == 'step-plan') {
                this.markStepCompleted($('#step-plan'));
                if (!$('#step-auth').hasClass('complete') && $('#step-auth').length > 0) {
                    $('#step-auth .content-step').addClass('current').slideToggle();
                    $('#step-auth').find('.head-step').addClass('active');
                    if (($.inArray('step-auth', this.availableStep) < 0)) this.availableStep.push('step-auth');
                } else {
                    $('#step-ad .content-step').addClass('current').slideToggle();
                    $('#step-ad').find('.head-step').addClass('active').removeClass('finished');
                    if (($.inArray('step-ad', this.availableStep) < 0)) this.availableStep.push('step-ad');
                }
            }
            if (this.currentStep == 'step-auth') {
                this.markStepCompleted($('#step-auth'));
                $('#step-ad .content-step').addClass('current').slideToggle();
                $('#step-ad').find('.head-step').addClass('active');
                if (($.inArray('step-ad', this.availableStep) < 0)) this.availableStep.push('step-ad');
            }
            if (this.currentStep == 'step-ad') {
                this.markStepCompleted($('#step-ad'));
                $('#step-payment .content-step').addClass('current').slideToggle();
                $('#step-payment').find('.head-step').addClass('active');
                if (($.inArray('step-payment', this.availableStep) < 0)) this.availableStep.push('step-payment');
            }
        },
        setUploading: function() {
            this.uploading = true;
        },
        setUploadFinish: function() {
            this.uploading = false;
        },
        markStepCompleted: function(step) {
            step.find('.content-step').hide().removeClass('current');
            step.find('.head-step').removeClass('active').addClass('finished');
            step.addClass('complete');
            return this;
        },
        markStepIncompleted: function(step) {
            if (step.hasClass('completed')) {
                step.removeClass('completed').find('.toggle-title').removeClass('toggle-complete');
            }
            return this;
        },
        updateProcess: function() {
            var isFree = this.ad.get('is_free'),
                ad_package = this.ad.get('et_payment_package'),
                package_data = CE.app.auth.get('package_data');
            if (typeof(package_data) != 'undefined' && typeof(package_data[ad_package]) != 'undefined' && package_data[ad_package]['qty'] > 0) var is_use_package = 1;
            else var is_use_package = 0;
            if (isFree === 1 || is_use_package === 1) {
                this.removePaymentStep();
            } else {
                this.showPaymentStep();
            }
        },
        removePaymentStep: function() {
            this.$('#step-payment').hide();
            this.$('#step-ad .btn-primary').html($('#step-ad .btn-primary').attr('data-submit'));
        },
        showPaymentStep: function() {
            this.$('#step-payment').show();
            this.$('#step-ad .btn-primary').html($('#step-ad .btn-primary').attr('data-continue'));
        },
        pressTabToLocation: function(event) {
            if (event.which == 9) {
                $("a.chosen-single").trigger("mousedown");
                setTimeout(function() {
                    $(".chosen-search input").focus();
                }, 100);
            }
        },
    });
})(jQuery);