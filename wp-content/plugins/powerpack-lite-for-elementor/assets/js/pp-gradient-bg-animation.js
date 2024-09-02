( function ( $ ) {
	"use strict";

	var AnimatedGradientBg = function ( $scope, $ ) {

		if ( ! $scope.hasClass( 'pp-animated-gradient-bg-yes' ) ) {
			return;
		}

		var color          = $scope.data( 'color' ),
			angle          = $scope.data( 'angle' ),
			gradientColor  = 'linear-gradient( ' + angle + ',' + color + ' )';
		
		$scope.css( 'background-image', gradientColor );

		if ( elementorFrontend.isEditMode() ) {
			var bg_overlay       = $scope.find( '.elementor-element-overlay ~ .elementor-background-overlay' ),
				animated_bg_wrap = $scope.find( '.pp-animated-gradient-bg' );

			if ( bg_overlay.next( '.pp-animated-gradient-bg' ).length == 0 ) {
				bg_overlay.after( animated_bg_wrap );
			}
		}

	};

	$( window ).on( 'elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/global', AnimatedGradientBg );
	} );
	
}( jQuery ) );
