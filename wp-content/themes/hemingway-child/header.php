<!DOCTYPE html>

<html <?php language_attributes(); ?>>

	<head>
		
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" >
				
		<?php if ( is_singular() ) wp_enqueue_script( "comment-reply" ); ?>
		 
		<?php wp_head(); ?>

		<!-- BEE: hemingway-child override to scroll to the top function -->
<link rel="stylesheet" id="font-awesome-css" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" type="text/css" media="screen">
<style>
.back-to-top {
   background: none;
   margin: 0;
   position: fixed;
   bottom: 0;
   right: 0;
   width: 70px;
   height: 70px;
   z-index: 100;
   display: none;
   text-decoration: none;
   color: #c4c4c4;
   background-color: transparent;
}
.back-to-top i {
  font-size: 60px;
}
</style>
	
	</head>
	
	<body <?php body_class(); ?>>
	
		<div class="big-wrapper">
	
			<div class="header-cover section bg-dark-light no-padding">
		
				<div class="header section" style="background-image: url(<?php if (get_header_image() != '') : ?><?php header_image(); ?><?php else : ?><?php echo get_template_directory_uri() . '/images/header.jpg'; ?><?php endif; ?>);">
							
					<div class="header-inner section-inner">
					
						<?php if ( get_theme_mod( 'hemingway_logo' ) ) : ?>
						
							<div class='blog-logo'>
							
						        <a href='<?php echo esc_url( home_url( '/' ) ); ?>' title='<?php echo esc_attr( get_bloginfo( 'title' ) ); ?> &mdash; <?php echo esc_attr( get_bloginfo( 'description' ) ); ?>' rel='home'>
						        	<img src='<?php echo esc_url( get_theme_mod( 'hemingway_logo' ) ); ?>' alt='<?php echo esc_attr( get_bloginfo( 'title' ) ); ?>'>
						        </a>
						        
						    </div> <!-- /blog-logo -->
					
						<?php elseif ( get_bloginfo( 'description' ) || get_bloginfo( 'title' ) ) : ?>
					
							<div class="blog-info">
							
								<h2 class="blog-title">
									<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'title' ) ); ?> &mdash; <?php echo esc_attr( get_bloginfo( 'description' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'title' ) ); ?></a>
								</h2>
								
								<?php if ( get_bloginfo( 'description' ) ) { ?>
								
									<h3 class="blog-description"><?php echo esc_attr( get_bloginfo( 'description' ) ); ?></h3>
									
								<?php } ?>
							
							</div> <!-- /blog-info -->
							
						<?php endif; ?>
									
					</div> <!-- /header-inner -->
								
				</div> <!-- /header -->
			
			</div> <!-- /bg-dark -->
			
			<div class="navigation section no-padding bg-dark">
			
				<div class="navigation-inner section-inner">
				
					<div class="toggle-container hidden">
			
						<div class="nav-toggle toggle">
								
							<div class="bar"></div>
							<div class="bar"></div>
							<div class="bar"></div>
							
							<div class="clear"></div>
						
						</div>
						
						<div class="search-toggle toggle">
								
							<div class="metal"></div>
							<div class="glass"></div>
							<div class="handle"></div>
						
						</div>
						
						<div class="clear"></div>
					
					</div> <!-- /toggle-container -->
					
					<div class="blog-search hidden">
					
						<?php get_search_form(); ?>
					
					</div>
				
					<ul class="blog-menu">
					
						<?php if ( has_nav_menu( 'primary' ) ) {
																			
							wp_nav_menu( array( 
							
								'container' => '', 
								'items_wrap' => '%3$s',
								'theme_location' => 'primary', 
								'walker' => new hemingway_nav_walker
															
							) ); } else {
						
							wp_list_pages( array(
							
								'container' => '',
								'title_li' => ''
							
							));
							
						} ?>
						
						<div class="clear"></div>
												
					 </ul>
					 
					 <ul class="mobile-menu">
					
						<?php if ( has_nav_menu( 'primary' ) ) {
																			
							wp_nav_menu( array( 
							
								'container' => '', 
								'items_wrap' => '%3$s',
								'theme_location' => 'primary', 
								'walker' => new hemingway_nav_walker
															
							) ); } else {
						
							wp_list_pages( array(
							
								'container' => '',
								'title_li' => ''
							
							));
							
						} ?>
						
					 </ul>
				 
				</div> <!-- /navigation-inner -->
				
			</div> <!-- /navigation -->