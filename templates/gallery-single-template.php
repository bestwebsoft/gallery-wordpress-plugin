<?php
/*
* Template - Gallery post
* Version: 1.3.0
*/
get_header(); ?>
<div class="wrap gllr_wrap entry">
	<div id="primary" class="content-area">
		<div id="container" class="site-content site-main">
			<div id="content" class="hentry entry">
				<?php if ( function_exists( 'gllr_single_template_content' ) ) {
					gllr_single_template_content();
				} ?>
				<div class="gllr_clear"></div>
			</div><!-- .hentry -->
			<?php if ( comments_open() ) {
				comments_template();
			} ?>
		</div><!-- #container -->
	</div><!-- .content-area -->
	<?php
    $current_theme = wp_get_theme();
    /* Theme Twenty Nineteen hasn`t sidebar */
	$current_theme = wp_get_theme();
	if( 'Twenty Twenty' == $current_theme->get( 'Name' ) ) {
		get_template_part( 'template-parts/footer-menus-widgets' );
	} elseif ( file_exists( TEMPLATEPATH . '/sidebar.php' ) ) {
		get_sidebar();
	} ?>
</div><!-- .wrap -->
<?php get_footer(); ?>