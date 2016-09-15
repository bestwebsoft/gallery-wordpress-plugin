<?php
/*
* Template - Gallery post
* Version: 1.2.5
*/
get_header(); ?>
<div class="content-area">
	<div id="container" class="site-content site-main">
		<div id="content" class="hentry">
			<?php if ( function_exists( 'gllr_single_template_content' ) ) {
				gllr_single_template_content();
			} ?>
			<div class="gllr_clear"></div>
		</div><!-- .hentry -->
		<?php if( comments_open() ) {
			comments_template();
		} ?>
	</div><!-- #container -->
</div><!-- .content-area -->
<?php get_sidebar();
get_footer(); ?>