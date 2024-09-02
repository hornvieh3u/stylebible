(function ($) {
    "use strict";
    
    var getElementSettings = function( $element ) {
		var elementSettings = {},
			modelCID 		= $element.data( 'model-cid' );

		if ( isEditMode && modelCID ) {
			var settings 		= elementorFrontend.config.elements.data[ modelCID ],
				settingsKeys 	= elementorFrontend.config.elements.keys[ settings.attributes.widgetType || settings.attributes.elType ];

			jQuery.each( settings.getActiveControls(), function( controlKey ) {
				if ( -1 !== settingsKeys.indexOf( controlKey ) ) {
					elementSettings[ controlKey ] = settings.attributes[ controlKey ];
				}
			} );
		} else {
			elementSettings = $element.data('settings') || {};
		}

		return elementSettings;
	};

    var isEditMode		= false;

	var CustomCursorHandler = function ($scope, $) {
		var elementSettings      = getElementSettings( $scope ),
			custom_cursor_enable = elementSettings.pp_custom_cursor_enable,
			columnId             = $scope.data('id'),
			cursorType           = elementSettings.pp_custom_cursor_type,
			cursorIcon           = elementSettings.pp_custom_cursor_icon,
			cursorText           = elementSettings.pp_custom_cursor_text,
			cursorTarget         = elementSettings.pp_custom_cursor_target,
			leftOffset           = ( '' !== elementSettings.pp_custom_cursor_left_offset && undefined !== elementSettings.pp_custom_cursor_left_offset ) ? parseInt( elementSettings.pp_custom_cursor_left_offset.size ) : 0,
			topOffset            = ( '' !== elementSettings.pp_custom_cursor_top_offset && undefined !== elementSettings.pp_custom_cursor_top_offset ) ? parseInt( elementSettings.pp_custom_cursor_top_offset.size ) : 0;

		if ( 'yes' === custom_cursor_enable ) {

			leftOffset = ( isNaN(leftOffset) ) ? 0 : leftOffset;
			topOffset  = ( isNaN(topOffset) ) ? 0 : topOffset;
			
			var selector  = ".elementor-element-" + columnId,
				$selector = $(".elementor-element-" + columnId);

			if ( 'selector' === cursorTarget ) {
				selector = elementSettings.pp_custom_cursor_css_selector,
				$selector = $scope.find(selector);
			}

			if ( 'image' === cursorType ) {

				$("#style-" + columnId).remove();

				if ( cursorIcon.url === undefined || cursorIcon.url === '' ) {
					return;
				}

				$('head').append('<style type="text/css" id="style-' + columnId + '">' + selector + ', ' + selector + ' * { cursor: url(' + cursorIcon.url + ') ' + leftOffset + ' ' + topOffset + ', auto !important; }</style>');

			} else if ( 'follow-image' === cursorType ) {

				$("#style-" + columnId).remove();

				if ( cursorIcon.url === undefined || cursorIcon.url === '' ) {
					return;
				}

				$scope.append('<img src="' + cursorIcon.url + '" alt="Cursor Image" class="pp-cursor-pointer">');

				$selector.mouseenter(function() {
					$(".pp-custom-cursor").removeClass("pp-cursor-active");
					$scope.addClass( "pp-cursor-active" );

					$(document).mousemove(function(e){
						$('.pp-cursor-pointer',this).offset({
							left: e.pageX + leftOffset,
							top: e.pageY + topOffset
						});
					});
				}).mouseleave(function() {
					$scope.removeClass( "pp-cursor-active" );
				});

			} else if ( 'follow-text' === cursorType ) {

				$("#style-" + columnId).remove();

				$scope.append('<div class="pp-cursor-pointer pp-cursor-pointer-text">' + cursorText + '</div>');

				$selector.mouseenter(function() {
					$(".pp-custom-cursor").removeClass("pp-cursor-active");
					$scope.addClass( "pp-cursor-active" );

					var cursor = $scope.find('.pp-cursor-pointer'),
						width  = cursor.outerWidth(),
						height = cursor.outerHeight();

					$(document).mousemove(function(e){
						cursor.offset({
							left: e.pageX + leftOffset - (width/2),
							top: e.pageY + topOffset - (height/2)
						});
					});
				}).mouseleave(function() {
					$scope.removeClass( "pp-cursor-active" );
				});
			}
		} else {
			$("#style-" + columnId).remove();
		}
	};
    
    $(window).on('elementor/frontend/init', function () {
        if ( elementorFrontend.isEditMode() ) {
			isEditMode = true;
		}
        
        elementorFrontend.hooks.addAction('frontend/element_ready/global', CustomCursorHandler);
    });
    
}(jQuery));
