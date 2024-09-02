<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Albatross
 */

get_header();
?>

	<main id="primary" class="site-main full-width-page">
        
		<?php get_template_part( 'template-parts/content', 'page' ); ?>

	</main><!-- #main -->

<?php
get_footer();
