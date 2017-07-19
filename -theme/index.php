<?php
/**
 * @author Valentin Alisch <hallo@valentinalisch.de>
 * @version 2.5.8
 */
?>



<?php get_header(); ?>		
	<?php 	
	if ( have_posts() ) : while ( have_posts() ) : the_post();
		
		the_content();
		
	endwhile; else: ?>
	<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
	<?php endif; ?>
<?php get_footer(); ?>