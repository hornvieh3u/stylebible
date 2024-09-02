var CityGuide = function() {
    var filters = {
        city: 0,
        category: 0,
        sort: 0,
		hiddengem: 0
    }

    var pagination = {
        limit: 6,
        current: 1
    }

    var ajaxRequest = function() {
		jQuery.blockUI({
			css: { 
				border: 'none', 
				padding: '15px', 
				backgroundColor: 'transparent',
				'-webkit-border-radius': '10px', 
				'-moz-border-radius': '10px', 
				color: '#fff',
				width: '140px',
				left: 'calc(50% - 70px)',
				padding: 0
        	},
			message: '<span class="loader"></span>'
		}); 
        jQuery.ajax({
            method: 'POST',
            url: ajaxUrl,
            data: {
                action:     'the_list',
                filters,
                pagination
            },
            success: function(res) {
                jQuery(".item-list").html(res);
				jQuery.unblockUI();
            }, 
            error: function(err) {
                console.log(err);
				jQuery.unblockUI();
                alert('Error is occuered!');
            }
        });
    }

    return {
		init: function() {
			if( !isLoggedIn ) {
				jQuery("#access_guide").attr("href", "javascript:CityGuide.openMenu();");
			}
			
			document.addEventListener( 'wpcf7mailsent', function( event ) {
				if ( '2974' == event.detail.contactFormId ) {
					ajaxRequest();
				}
			}, false );
			
			jQuery("#hidden-gem-check").change(function(){
				if( this.checked ) {
					filters.hiddengem = 1;
				} else {
					filters.hiddengem = 0;
				}
				CityGuide.goPage( 1 );
			});
		},
		openMenu: function() {
			jQuery(".scroll-to-top-button").click();
			jQuery("#header-dropdown-toggle").click();
			CityGuide.signUpForm();
		},
        setFilter: function( key, id, value ) {
            if( key == 'city' ) {
                jQuery("h4.city-name").text( value );
            }
            jQuery('.filter.' + key + ' li').removeClass('active');
			if( filters[key] === id ) {
				if( key == 'city' ) {
					jQuery("h4.city-name").text( 'All' );
				}
				filters[key] = 0;
			} else {
            	jQuery('.filter.' + key + ' li.' + key + id).addClass('active');
				filters[key] = id;
			}
            CityGuide.goPage( 1 );
        },
        goPage: function( pageNum ) {
            pagination['current'] = pageNum;
            ajaxRequest( filters );
        },
        backToNav: function() {
            jQuery(".site-header .primary-menu-container").removeClass("display-none");
            jQuery(".site-header .signup-form").addClass("display-none");
        },
        signUpForm: function() {
            jQuery(".site-header .primary-menu-container").addClass("display-none");
            jQuery(".site-header .signup-form").removeClass("display-none");
        },
		vote: function(establishmentId) {
			jQuery.ajax({
				method: 'POST',
				url: ajaxUrl,
				data: {
					action:     'the_can_leave_review',
					establishmentId
				},
				dataType: "json",
				success: function(res) {
					if( res.review_id === null ) {
						jQuery("#popmake-2963 .popmake-content form").removeClass("display-none");
						jQuery("#popmake-2963 .popmake-content .already-exist-review").addClass("display-none");
						
						jQuery('#popmake-2963 input[name="establelishment_id"]').val( establishmentId );
						/** form init */
						jQuery('#popmake-2963 form').removeClass('sent').removeClass('invalid').addClass('init');
						jQuery('#popmake-2963 form').attr('data-status', 'init');
					} else {
						jQuery("#popmake-2963 .popmake-content form").addClass("display-none");
						jQuery("#popmake-2963 .popmake-content .already-exist-review").removeClass("display-none");
					}
					
					PUM.open(2963);
				}, 
				error: function(err) {
					console.log(err);
					alert('Error is occuered!');
				}
			});
		},
		showMap: function(address) {
			jQuery("#popmake-2983 .pum-content").html('<span class="loader"></span>');
			jQuery.ajax({
				method: 'POST',
				url: ajaxUrl,
				data: {
					action: 'display_google_map',
					address
				},
				success: function(res) {
					jQuery("#popmake-2983 .pum-content").html(res);
					jQuery.unblockUI();
				}, 
				error: function(err) {
					console.log(err);
					jQuery.unblockUI();
					alert('Error is occuered!');
				}
			});
			PUM.open(2983);
		}
    }
}();

jQuery(document).ready(function(){
	CityGuide.init();
});