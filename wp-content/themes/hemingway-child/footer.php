	<div class="footer section large-padding bg-dark">
		
		<div class="footer-inner section-inner">
		
			<?php if ( is_active_sidebar( 'footer-a' ) ) : ?>
			
				<div class="column column-1 left">
				
					<div class="widgets">
			
						<?php dynamic_sidebar( 'footer-a' ); ?>
											
					</div>
					
				</div>
				
			<?php endif; ?> <!-- /footer-a -->
				
			<?php if ( is_active_sidebar( 'footer-b' ) ) : ?>
			
				<div class="column column-2 left">
				
					<div class="widgets">
			
						<?php dynamic_sidebar( 'footer-b' ); ?>
											
					</div> <!-- /widgets -->
					
				</div>
				
			<?php endif; ?> <!-- /footer-b -->
								
			<?php if ( is_active_sidebar( 'footer-c' ) ) : ?>
			
				<div class="column column-3 left">
			
					<div class="widgets">
			
						<?php dynamic_sidebar( 'footer-c' ); ?>
											
					</div> <!-- /widgets -->
					
				</div>
				
			<?php endif; ?> <!-- /footer-c -->
			
			<div class="clear"></div>
		
		</div> <!-- /footer-inner -->
	
	</div> <!-- /footer -->
			
	<div class="credits section bg-dark no-padding">
	
		<div class="credits-inner section-inner">
	
			<p class="credits-left">
			
				&copy; <?php echo date("Y") ?> <a href="<?php echo home_url(); ?>" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a>
			
			</p>
			
			<p class="credits-right">
				
				<span> <!-- <?php printf( __( 'Theme by <a href="%s">Anders Noren</a>', 'hemingway'), 'http://www.andersnoren.se' ); ?></span> &mdash; <a title="<?php _e('To the top', 'hemingway'); ?>" class="tothetop"><?php _e('Up', 'hemingway' ); ?> &uarr;</a>-->
			</p>
			<div class="clear"></div>
		
		</div> <!-- /credits-inner -->
		
	</div> <!-- /credits -->

</div> <!-- /big-wrapper -->

<?php wp_footer(); ?>

	<!-- BEE: hemingway-child override to scroll to the top function -->
<a class="back-to-top" href="#"><i class="fa fa-arrow-circle-up"></i></a>
<script>
jQuery(document).ready(function() {
   var offset = 250;
   var duration = 300;
   jQuery(window).scroll(function() {
      if (jQuery(this).scrollTop() > offset) {
         jQuery('.back-to-top').fadeIn(duration);
      } else {
         jQuery('.back-to-top').fadeOut(duration);
      }
   });
   jQuery('.back-to-top').click(function(event) {
      event.preventDefault();
      jQuery('html, body').animate({scrollTop: 0}, duration);
      return false;
   })
});
</script>

</body>
</html>