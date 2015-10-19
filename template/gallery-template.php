<?php
/*
Template Name: Gallery Template
* Version: 1.2.5
*/
?>
<?php get_header(); ?>
	<div id="primary" class="content-area">
		<div id="container" class="site-content site-main">
			<div id="content" class="hentry">
				<h1 class="home_page_title entry-header">
					<?php if ( isset( $wp_query->query_vars["gallery_categories"] ) ) {
						$term = get_term_by( 'slug', $wp_query->query_vars["gallery_categories"], 'gallery_categories' );
						echo __( 'Gallery Category', 'gallery-plugin' ) . ':&nbsp;' . ( $term->name );
					} else {
						the_title();
					} ?>
				</h1>
				<?php if ( ! post_password_required() ) { ?>
					<div class="gallery_box entry-content">
						<?php if ( function_exists( 'pdfprnt_show_buttons_for_custom_post_type' ) ) 
							echo pdfprnt_show_buttons_for_custom_post_type();
						elseif ( function_exists( 'pdfprntpr_show_buttons_for_custom_post_type' ) ) 
							echo pdfprntpr_show_buttons_for_custom_post_type(); ?>
						<ul>
							<?php global $post, $wpdb, $wp_query, $request;
								
							if ( get_query_var( 'paged' ) ) {
								$paged = get_query_var( 'paged' );
							} elseif ( get_query_var( 'page' ) ) {
								$paged = get_query_var( 'page' );
							} else {
								$paged = 1;
							}

							$permalink    = get_permalink();
							$gllr_options = get_option( 'gllr_options' );
							$count        = 0;
							$per_page = $showitems = get_option( 'posts_per_page' );  

							if ( substr( $permalink, strlen( $permalink ) -1 ) != "/" ) {
								if ( strpos( $permalink, "?" ) !== false ) {
									$permalink = substr( $permalink, 0, strpos( $permalink, "?" ) -1 ) . "/";
								} else {
									$permalink .= "/";
								}
							}
							$args = array(
								'post_type'			=> 'gallery',
								'post_status'		=> 'publish',
								'orderby'			=> $gllr_options['album_order_by'],
								'order'				=> $gllr_options['album_order'],
								'posts_per_page'	=> $per_page,
								'paged'				=> $paged
							);
							if ( isset( $wp_query->query_vars["gallery_categories"] ) && ( ! empty( $wp_query->query_vars["gallery_categories"] ) ) ) {
								$args['tax_query'] = array(
									array(
										'taxonomy'  => 'gallery_categories',
										'field'     => 'slug',
										'terms'     => $wp_query->query_vars["gallery_categories"]
									)
								);
							}
							$second_query = new WP_Query( $args );
							
							$request = $second_query->request;

							if ( $second_query->have_posts() ) : 
								while ( $second_query->have_posts() ) : $second_query->the_post();
									$attachments	= get_post_thumbnail_id( $post->ID );
									if ( empty ( $attachments ) ) {
										$images_id = get_post_meta( $post->ID, '_gallery_images', true );
										$attachments = get_posts( array(								
											'showposts'			=>	1,
											'what_to_show'		=>	'posts',
											'post_status'		=>	'inherit',
											'post_type'			=>	'attachment',
											'orderby'			=>	$gllr_options['order_by'],
											'order'				=>	$gllr_options['order'],
											'post_mime_type'	=>	'image/jpeg,image/gif,image/jpg,image/png',
											'post__in'			=> explode( ',', $images_id ),
											'meta_key'			=> '_gallery_order_' . $post->ID
										));
										if ( ! empty( $attachments[0] ) ) {
											$first_attachment = $attachments[0];
											$image_attributes = wp_get_attachment_image_src( $first_attachment->ID, "album-thumb" );
										} else
											$image_attributes = array( '' );
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
											<img width="<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>" height="<?php echo $gllr_options['gllr_custom_size_px'][0][1]; ?>" style="width:<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>px; height:<?php echo $gllr_options['gllr_custom_size_px'][0][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" src="<?php echo $image_attributes[0]; ?>" />
										</a>
										<div class="gallery_detail_box">
											<div><?php the_title(); ?></div>
											<div><?php if ( function_exists( 'gllr_the_excerpt_max_charlength' ) ) echo gllr_the_excerpt_max_charlength( 100 ); ?></div>
											<a href="<?php echo get_permalink(); ?>"><?php echo $gllr_options["read_more_link_text"]; ?></a>
										</div><!-- .gallery_detail_box -->
										<div class="gllr_clear"></div>
									</li>
								<?php endwhile; 
							endif;  ?>
						</ul>
					</div><!-- .gallery_box -->
					</div><!-- #content -->
					<?php $count_all_albums = $second_query->found_posts;
					wp_reset_query(); 
					$request = $wp_query->request;
					$pages = intval( $count_all_albums / $per_page );
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