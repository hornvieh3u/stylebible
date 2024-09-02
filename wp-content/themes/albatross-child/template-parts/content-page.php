<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Albatross
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		$thumb_url = get_field("top_image_url");
		$page_title = get_the_title();
		if ( $thumb_url ) :
	?>
	
		<div class="page-header-wrapper <?php echo str_replace(' ', '-', strtolower($page_title)); ?>"
			<?php if ($thumb_url): ?>
				style="background-image: url('<?php echo esc_url($thumb_url); ?>')"
			<?php endif; ?>
		>
			<?php albatross_post_thumbnail(); ?>

			<header class="entry-header">
				<h1 class="entry-title">
					<?php the_field("top_image_title"); ?>
				</h1>
				<p class="entry-desc">
					<?php the_field("top_image_desc"); ?>
				</p>

				<?php if (has_excerpt()) the_excerpt(); ?>

				<?php
				if ($thumb_url):
					?>
					<a href="#page-content" class="scroll-to-content-button">
						<svg width="15" height="19" viewBox="0 0 15 19" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8.23396 17.7609C7.84344 18.1515 7.21027 18.1515 6.81975 17.7609L0.455787 11.397C0.0652625
						11.0065 0.0652624 10.3733 0.455787 9.98277C0.846311 9.59224 1.47948 9.59224 1.87 9.98277L7.52686
						15.6396L13.1837 9.98276C13.5742 9.59224 14.2074 9.59224 14.5979 9.98276C14.9884 10.3733 14.9884
						11.0065 14.5979 11.397L8.23396 17.7609ZM6.52686 17.0538L6.52685 0.0538331L8.52685 0.0538329L8.52686
						17.0538L6.52686 17.0538Z"/>
						</svg>
					</a>
				<?php
				endif;
				?>
			</header><!-- .entry-header -->
		</div>
	<?php endif; ?>
    
    <div class="entry-content" id="page-content">
		<?php
		the_content();

		$page_title = get_the_title();

		if( $page_title == 'City Guide' ) the_city_guide();
		
		if( $page_title != 'Say Hello' ) the_follow_us();

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__('Pages:', 'albatross'),
				'after' => '</div>',
			)
		);
		?>
    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
