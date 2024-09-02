( function( $ ) {

	var loadStatus = true;
	var count = 1;
	var loader = '';
	var total = 0;
	var isEditMode = false;
	
	function equalHeight( $scope ) {
		var activeSlide = $scope.find( '.swiper-slide-visible' ),
			maxHeight   = -1;

		activeSlide.each( function() {
            var $this      = $( this ),
                post       = $this.find( '.pp-post' ),
                postHeight = post.outerHeight();

            if ( maxHeight < postHeight ) {
                maxHeight = postHeight;
            }
        });

		activeSlide.each( function() {
            var selector = $( this ).find( '.pp-post' );

            selector.animate({ height: maxHeight }, { duration: 200, easing: 'linear' });
        });
	}

	var ppSwiperSliderAfterinit = function ( $scope, carousel, carouselWrap, elementSettings, mySwiper ) {
		equalHeight( $scope );

		mySwiper.on('slideChange', function () {
			equalHeight( $scope );
		});

		if ( true === elementSettings.autoplay.pauseOnHover ) {
			carousel.on( 'mouseover', function() {
				mySwiper.autoplay.stop();
			});

			carousel.on( 'mouseout', function() {
				mySwiper.autoplay.start();
			});
		}

		if ( isEditMode ) {
			carouselWrap.resize( function() {
				mySwiper.update();
			});
		}

		var $triggers = [
			'ppe-tabs-switched',
			'ppe-toggle-switched',
			'ppe-accordion-switched',
			'ppe-popup-opened',
		];

		$triggers.forEach(function(trigger) {
			if ( 'undefined' !== typeof trigger ) {
				$(document).on(trigger, function(e, wrap) {
					if ( wrap.find( '.pp-swiper-slider' ).length > 0 ) {
						setTimeout(function() {
							mySwiper.update();
						}, 100);
					}
				});
			}
		});
    };
	
	var PostsHandler = function( $scope, $ ) {
		
		var container = $scope.find( '.pp-posts-container' ),
			selector = $scope.find( '.pp-posts-grid' ),
			layout = $scope.find( '.pp-posts' ).data( 'layout' ),
			loader = $scope.find( '.pp-posts-loader' );

		if ( 'masonry' == layout ) {

			$scope.imagesLoaded( function(e) {

				selector.isotope({
					layoutMode: layout,
					itemSelector: '.pp-grid-item-wrap',
				});

			});
		}
		
		if ( 'carousel' == layout ) {
			var carouselWrap  = $scope.find( '.swiper-container-wrap' ).eq( 0 ),
				carousel      = $scope.find( '.pp-posts-carousel' ).eq( 0 ),
				sliderOptions = JSON.parse( carousel.attr('data-slider-settings') );

			/* $($carousel).on('setPosition', function () {
				equalHeight($scope);
			}); */

			if ( carousel.length > 0 ) {
				if ( 'undefined' === typeof Swiper ) {
					var asyncSwiper = elementorFrontend.utils.swiper;
		
					new asyncSwiper( carousel, sliderOptions ).then( function( newSwiperInstance ) {
						var mySwiper = newSwiperInstance;
						ppSwiperSliderAfterinit( $scope, carousel, carouselWrap, sliderOptions, mySwiper );
					} );
				} else {
					var mySwiper = new Swiper(carousel, sliderOptions);
					ppSwiperSliderAfterinit( $scope, carousel, carouselWrap, sliderOptions, mySwiper );
				}
			}
		}
	}

	$( 'body' ).delegate( '.pp-posts-pagination-ajax .page-numbers', 'click', function( e ) {

		$scope = $( this ).closest( '.elementor-widget-pp-posts' );
		
		if ( 'main' == $scope.find( '.pp-posts-grid' ).data( 'query-type' ) ) {
			return;
		}

		e.preventDefault();

		$scope.find( '.pp-posts-grid .pp-post' ).last().after( '<div class="pp-post-loader"><div class="pp-loader"></div><div class="pp-loader-overlay"></div></div>' );

		var page_number = 1;
		var curr = parseInt( $scope.find( '.pp-posts-pagination .page-numbers.current' ).html() );

		if ( $( this ).hasClass( 'next' ) ) {
			page_number = curr + 1;
		} else if ( $( this ).hasClass( 'prev' ) ) {
			page_number = curr - 1;
		} else {
			page_number = $( this ).html();
		}

		$scope.find( '.pp-posts-grid .pp-post' ).last().after( '<div class="pp-post-loader"><div class="pp-loader"></div><div class="pp-loader-overlay"></div></div>' );

		var $args = {
			'page_id':		$scope.find( '.pp-posts-grid' ).data('page'),
			'widget_id':	$scope.data( 'id' ),
			'skin':			$scope.find( '.pp-posts-grid' ).data( 'skin' ),
			'page_number':	page_number
		};

		$('html, body').animate({
			scrollTop: ( ( $scope.find( '.pp-posts-container' ).offset().top ) - 30 )
		}, 'slow');

		_callAjax( $scope, $args );

	} );

	var _callAjax = function( $scope, $obj, $append, $count ) {

		var loader = $scope.find( '.pp-posts-loader' );
		
		$.ajax({
			url: pp_posts_script.ajax_url,
			data: {
				action:			'pp_get_post',
				page_id:		$obj.page_id,
				widget_id:		$obj.widget_id,
				skin:			$obj.skin,
				page_number:	$obj.page_number,
				nonce:			pp_posts_script.posts_nonce,
			},
			dataType: 'json',
			type: 'POST',
			success: function( data ) {

				var sel = $scope.find( '.pp-posts-grid' );

				if ( true == $append ) {

					var html_str = data.data.html;

					sel.append( html_str );
				} else {
					sel.html( data.data.html );
				}

				$scope.find( '.pp-posts-pagination-wrap' ).html( data.data.pagination );

				var layout = $scope.find( '.pp-posts-grid' ).data( 'layout' ),
					selector = $scope.find( '.pp-posts-grid' );

				if ( 'masonry' == layout ) {

					$scope.imagesLoaded( function() {
						selector.isotope( 'destroy' );
						selector.isotope({
							layoutMode: layout,
							itemSelector: '.pp-grid-item-wrap',
						});
					});
				}

				//	Complete the process 'loadStatus'
				loadStatus = true;
				if ( true == $append ) {
					loader.hide();
				}
				
				$count = $count + 1;

				$scope.trigger('posts.rendered');
			}
		});
	}

	$( window ).on( 'elementor/frontend/init', function () {
        if ( elementorFrontend.isEditMode() ) {
			isEditMode = true;
		}

		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.classic', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.card', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.checkerboard', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.creative', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.event', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.news', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.portfolio', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.overlap', PostsHandler );
		
		elementorFrontend.hooks.addAction( 'frontend/element_ready/pp-posts.template', PostsHandler );

	});

} )( jQuery );
