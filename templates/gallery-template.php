<?php
/*
Template Name: Gallery Template
* Version: 1.2.9
*/
get_header(); ?>
<div class="wrap gllr_wrap">
	<div id="primary" class="content-area">
		<div id="container" class="site-content site-main">
			<div id="content" class="hentry">
				<h1 class="home_page_title entry-header">
					<?php if ( function_exists( 'gllr_template_title' ) )
						echo gllr_template_title(); ?>
				</h1>
				<?php if ( ! post_password_required() ) { ?>
					<div class="gallery_box entry-content">
						<?php $gllr_post = get_post( get_the_ID() ); ?>
						<div class="gllr_page_content">
							<?php if ( is_page() ) {
								echo apply_filters( 'the_content', $gllr_post->post_content );
							} ?>
						</div>
						<?php if ( function_exists( 'gllr_template_content' ) ) {
							$content = gllr_template_content();
							if ( 1 != $content['pages'] ) { ?>
								<div class='gllr_clear'></div>
								<div class="pagination navigation loop-pagination nav-links gllr_pagination">
									<div id="gallery_pagination">
										<?php if ( function_exists( 'gllr_template_pagination' ) )
											gllr_template_pagination( $content ); ?>
										<div class='gllr_clear'></div>
									</div>
								</div><!-- .pagination -->
								<?php if ( function_exists( 'pgntn_display_pagination' ) ) pgntn_display_pagination( 'custom', $content['second_query'] ); ?>
							<?php }
						} ?>
					</div><!-- .gallery_box -->
				<?php } else { ?>
					<div class="gallery_box entry-content">
						<p><?php echo get_the_password_form(); ?></p>
					</div><!-- .gallery_box -->
				<?php } ?>
			</div><!-- .hentry -->
			<?php if ( comments_open() ) {
				comments_template();
			} ?>
		</div><!-- #container -->
	</div><!-- .content-area -->
	<?php get_sidebar(); ?>
</div><!-- .wrap -->
<?php get_footer(); ?>