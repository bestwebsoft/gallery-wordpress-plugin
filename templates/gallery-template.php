<?php
/*
Template Name: Gallery Template
* Version: 1.3.0
*/
get_header();
$current_theme_name = wp_get_theme()->get( 'Name' ); ?>
<div class="wrap gllr_wrap">
	<div id="primary" class="content-area">
		<div id="container" class="site-content site-main">
			<div id="content" class="entry entry">
                <header class="entry-header <?php echo ( 'Twenty Twenty' == $current_theme_name ) ? 'has-text-align-center' : ''; ?>">
                    <h1 class="home_page_title <?php echo ( 'Twenty Seventeen' != $current_theme_name ) ? 'entry-title' : ''; ?>">
	                    <?php if ( function_exists( 'gllr_template_title' ) )
		                    echo gllr_template_title() ?>
                    </h1>
                </header>
                <?php if ( ! post_password_required() ) { ?>
					<div class="gallery_box entry-content">
						<?php $gllr_post = get_post( get_the_ID() ); ?>
						<div class="gllr_page_content">
							<?php if ( is_page() ) {
								echo apply_filters( 'the_content', $gllr_post->post_content );
							}
							if ( function_exists( 'gllr_template_content' ) ) {
                                $content = gllr_template_content();
                                if ( 0 < $content['pages'] && 1 != $content['pages'] ) { ?>
                                    <div class='gllr_clear'></div>
                                    <div class="pagination navigation loop-pagination nav-links gllr_pagination">
                                        <div id="gallery_pagination">
                                            <?php if ( function_exists( 'gllr_template_pagination' ) )
                                                gllr_template_pagination( $content ); ?>
                                            <div class='gllr_clear'></div>
                                        </div>
                                    </div><!-- .pagination -->
                                    <?php $custom_query = ( object ) array( 'max_num_pages' => $content['second_query']->max_num_pages, 'case' => 'bws-gallery');
                                    if ( function_exists( 'pgntn_display_pagination' ) ) pgntn_display_pagination( 'custom', $custom_query );
                                }
						    } ?>
					    </div><!-- .gllr_page_content -->
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
	<?php
	/* Theme Twenty Nineteen hasn`t sidebar */
	if( 'Twenty Twenty' == $current_theme_name ) {
		get_template_part( 'template-parts/footer-menus-widgets' );
	} elseif ( file_exists( TEMPLATEPATH . '/sidebar.php' ) ) {
		get_sidebar();
	} ?>
</div><!-- .wrap -->
<?php get_footer(); ?>