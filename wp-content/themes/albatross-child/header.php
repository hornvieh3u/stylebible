<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Albatross
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
    <script>
        var isLoggedIn = <?php echo isset($_SESSION['user_email']) ? 1 : 0; ?>;
        var ajaxUrl = '<?php echo esc_url(home_url('/wp-admin/admin-ajax.php')); ?>';
    </script>
</head>

<body <?php body_class('page-has-thumbnail'); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'albatross'); ?></a>

    <header id="masthead"
            class="<?php echo esc_attr(implode(' ', apply_filters('albatross_header_classes', ['site-header', 'absolute']))); ?>">
        <div class="site-header-container">
			<div class="navigation-container">
                <div class="header-dropdown">
                    <button id="header-dropdown-toggle" class="header-dropdown-toggle" aria-controls="header-dropdown"
                            aria-expanded="false"></button>
                    <div class="header-dropdown-content">
                        <div class="header-dropdown-content-wrapper">
                            <nav id="site-navigation" class="main-navigation">
								<?php
								wp_nav_menu(
									array(
										'theme_location' => 'menu-3',
										'menu_id' => 'primary-menu',
										'menu_class' => 'primary-menu',
										'container_class' => 'primary-menu-container'
									)
								);
								?>
                                <div class="signup-form display-none">
                                    <a class="back" href="javascript:CityGuide.backToNav();">BACK</a>
                                    <div class="signup-email-form">
                                        <h4>To access this section, sign up to our mailing list to gain full access</h4>
                                        <?php echo do_shortcode('[contact-form-7 id="2724" title="Email Form"]'); ?>
                                        <span class="policy">
                                            By signing up to our mailing list  you agree to our
                                            <span style="text-decoration: underline;">terms and conditiions</span>
                                            and
                                            <span style="text-decoration: underline;">privacy policy</span>
                                        </span>
                                    </div>
                                </div>
                            </nav><!-- #site-navigation -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="default-navigation">
                <div class="site-branding">					
					<?php
						the_custom_logo();
					?> 
                    <a class="custom-logo-link light" href="<?php echo esc_url(home_url('/')) ?>">
                        <img class="custom-logo"
                            src="<?php echo esc_url(wp_get_attachment_image_src(absint(get_theme_mod('albatross_light_logo')))[0]); ?>"
                            alt="<?php echo esc_html(get_bloginfo('name', 'display')); ?>">
                    </a>
                </div><!-- .site-branding -->
            </div>
			
			<div class="nav-social-link">
				<a href="https://www.instagram.com/stylebibleofficial"><button class="instagram-link"></button></a>
			</div>
            
        </div>
    </header><!-- #masthead -->
