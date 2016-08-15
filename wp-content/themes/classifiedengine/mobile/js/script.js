
(function($){
	$(document).ready(function($) {
		$.tabInit();
		$.modalInit();

		$('#respond').find('a,form').attr('data-ajax', 'false');

		/**	
		 * categories, locations list page tap to show sub 
		*/
		$('.border-bottom').on('tap' , function () {
			$('.catelory-child').hide();

			var $li	=	$(this).parent('.cat-item');
			if($li.hasClass('active')) {
				$li.removeClass('active');
				return false;
			}

			$li.find('.catelory-child').show().end().addClass('active');
		});
		

	
		// open setting
		$(".search-btn").on('tap' , function() {
			if(!$(this).hasClass('tapped')) {
				$(this).addClass('tapped');
				$(".menu-filter").show();
			}else {
				$(this).removeClass('tapped');
				$(".menu-filter").hide();
			}
				
		});

		// // close setting
		// $(".menu-filter .icon-header").on('tap', function() {
			
		// });
		
		$(".filter-search-btn").on('tap', function() {
			$(".menu-filter").hide();
		});

		if($('.carousel').length > 0 )
			$('.carousel').carousel();
		

		// $(".headroom").headroom();
		var lastScrollTop = 0;
    
	    $(window).scroll(function(){
	        
	        var st = $(this).scrollTop();
	        
	        return ;
	        if (st > lastScrollTop){
	            console.log('down');
	            // scrolling down
	            if($('.headroom').data('size') === 'big')
	            {
	                $('.headroom').data('size','small');
	                $('.headroom').stop().animate({
	                    top:'-100px'
	                },600);
	            }
	        }
	        else
	        {
	            // scrolling up
	            if($('.headroom').data('size') === 'small')
	            {
	                            console.log('up');
	                $('.headroom').data('size','big');
	                $('.headroom').stop().animate({
	                    top:'0'
	                },600);
	            }  
	        }
	        lastScrollTop = st;
	    });
		// $('#myTab a').click(function (e) {
		// 	e.preventDefault();
		// 	jQuery(this).tab('show');
		// })
		//slide 
		
		// tab choose
		// $(".tabs .ui-tabs").on('tap',function(){
		// 	var index = $(this).index();
		// 	$(".tabs .ui-tabs").removeClass("tab-active");
		// 	$(this).addClass("tab-active");

		// 	$(".content-tabs .tab-cont").fadeOut(50);
		// 	$(".content-tabs .tab-cont").eq(index).fadeIn(100);
		// });

		// active categories
		$(".list-categories .ui-list").on('tap', function(){
			var t = $(this);
		
			if ( t.hasClass("ui-list-main") ) {				
				if ( t.hasClass("ui-list-active") ){
					t.removeClass('ui-list-active');
				}
				else {
					$(".list-categories .ui-list").removeClass("ui-list-active");
					t.addClass("ui-list-active");
				}

			} else {				
				$(".list-categories .ui-list-main").removeClass("ui-list-active");
				// check child
				if ( t.hasClass("ui-list-active") ){
					t.removeClass('ui-list-active');
				}
				else{
					$(".list-categories .ui-list").removeClass("ui-list-active");
					t.addClass("ui-list-active");
				}
			}
		});

		$(".contact-type .ui-list").on('tap', function(){
			var t = $(this);
			// check child
			if ( t.hasClass("ui-list-active") ){
				t.removeClass("ui-list-active");
			}
			else{
				$(".contact-type .ui-list").removeClass("ui-list-active");
				t.addClass("ui-list-active");
			}
		});

	});

	$.tabInit = function(){
		$('.content-tabs').each(function(){
			var container	= $(this),
				tabs		= container.find('.tabs'),
				contents	= container.find('.tabcontent-wrapper');

			tabs.find('li a').on('tap', function(){
				var wrapper		= $(this).parent(),
					tabItems	= tabs.find('li'),
					content		= $( $(this).attr('href') );
				console.log( content );
				// refresh tab's status
				tabItems.removeClass('activated');
				wrapper.addClass('activated');

				// toggle tab content
				contents.find('.tabcontent').hide();
				content.show();
			});
		});
	};

	$.modalInit = function(){
		$('.modal-open').on('tap', function(){
			var current = $(this);
			var target = current.attr('href');
			console.log(target);
			$(target).modal({overlayClose : true});
			return false;
		});
	};

})(jQuery);