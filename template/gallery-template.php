<?php
/*
Template Name: Gallery Template
* Version: 1.2
*/
?>
<?php get_header(); ?>
	<div id="primary" class="content-area">
		<div id="container" class="site-content site-main">
			<div id="content" class="hentry">
				<h1 class="home_page_title entry-header"><?php the_title(); ?></h1>
				<?php if ( ! post_password_required() ) { ?>
					<?php if ( function_exists( 'pdfprnt_show_buttons_for_custom_post_type' ) ) 
						echo pdfprnt_show_buttons_for_custom_post_type( 'post_type=gallery&orderby=post_date' ); ?>
					<div class="gallery_box entry-content">
						<ul>
							<?php global $post, $wpdb, $wp_query, $request;
								
							if ( get_query_var( 'paged' ) ) {
								$paged = get_query_var( 'paged' );
							} elseif ( get_query_var( 'page' ) ) {
								$paged = get_query_var( 'page' );
							} else {
								$paged = 1;
							}

							$permalink = get_permalink();
							$gllr_options = get_option( 'gllr_options' );
							$count = 0;
							$per_page = $showitems = get_option( 'posts_per_page' );  
							$count_all_albums = $wpdb->get_var( "SELECT COUNT(*) FROM wp_posts WHERE 1=1 AND wp_posts.post_type = 'gallery' AND (wp_posts.post_status = 'publish')" );

							if ( substr( $permalink, strlen( $permalink ) -1 ) != "/" ) {
								if ( strpos( $permalink, "?" ) !== false ) {
									$permalink = substr( $permalink, 0, strpos( $permalink, "?" ) -1 ) . "/";
								} else {
									$permalink .= "/";
								}
							}

							$args = array(
								'post_type'				=> 'gallery',
								'post_status'			=> 'publish',
								'orderby'				=> 'post_date',
								'posts_per_page'		=> $per_page,
								'paged'					=> $paged
							);
							$second_query = new WP_Query( $args );

							if ( function_exists( 'pdfprnt_show_buttons_for_custom_post_type' ) ) 
								echo pdfprnt_show_buttons_for_custom_post_type( $second_query );
							
							$request = $second_query->request;

							if ( $second_query->have_posts() ) : 
								while ( $second_query->have_posts() ) : $second_query->the_post();
									$attachments	= get_post_thumbnail_id( $post->ID );
									if ( empty ( $attachments ) ) {
										$attachments = get_children( 'post_parent='.$post->ID.'&post_type=attachment&post_mime_type=image&numberposts=1' );
										$id = key( $attachments );
										$image_attributes = wp_get_attachment_image_src( $id, 'album-thumb' );
									} else {
										$image_attributes = wp_get_attachment_image_src( $attachments, 'album-thumb' );
									}
									if ( 1 == $gllr_options['border_images'] ) {
										$gllr_border = 'border-width: ' . $gllr_options['border_images_width'].'px; border-color:'.$gllr_options['border_images_color'].'; padding:0;';
										$gllr_border_images = $gllr_options['border_images_width'] * 2;
									} else {
										$gllr_border = 'padding:0;';
										$gllr_border_images = 0;
									}
									$count++; ?>
									<li>
										<a rel="bookmark" href="<?php echo get_permalink(); ?>" title="<?php the_title(); ?>">
											<img style="width:<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>px; <?php echo $gllr_border; ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" src="<?php echo $image_attributes[0]; ?>" />
										</a>
										<div class="gallery_detail_box">
											<div><?php the_title(); ?></div>
											<div><?php echo the_excerpt_max_charlength( 100 ); ?></div>
											<a href="<?php echo $permalink; echo basename( get_permalink( $post->ID ) ); ?>"><?php echo $gllr_options["read_more_link_text"]; ?></a>
										</div><!-- .gallery_detail_box -->
										<div class="gllr_clear"></div>
									</li>
								<?php endwhile; 
							endif; 
							wp_reset_query(); 
							$request = $wp_query->request; ?>
						</ul>
					</div><!-- .gallery_box -->
					</div><!-- #content -->
					<?php $pages = intval( $count_all_albums / $per_page );
					if ( $count_all_albums % $per_page > 0 )
						$pages += 1;
					$range = 100;
					if ( ! $pages ) {
						$pages = 1;
					}
					if ( 1 != $pages ) { ?>
						<div class='gllr_clear'></div>
						<nav class="paging-navigation" role="navigation">
							<div class="pagination navigation loop-pagination nav-links">
								<?php for ( $i = 1; $i <= $pages; $i++ ) {
									if ( 1 != $pages && ( !( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
										echo ( $paged == $i ) ? "<span class='page-numbers current'>". $i ."</span>":"<a class='page-numbers inactive' href='". get_pagenum_link($i) ."'>". $i ."</a>";
									}
								} ?>
								<div class='gllr_clear'></div>
							</div><!-- .pagination -->		
						</nav><!-- .paging-navigation -->
					<?php }
				} else { ?>
					<div class="gallery_box entry-content">
						<p><?php echo get_the_password_form(); ?></p>
					</div><!-- .gallery_box -->
					</div><!-- #content -->
				<?php } ?>			
			<?php comments_template(); ?>
		</div><!-- #container -->
	</div><!-- .content-area -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>