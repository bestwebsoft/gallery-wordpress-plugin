<?php
/*
* Template - Gallery post
* Version: 1.2.3
*/
get_header(); ?>
	<div class="content-area">
		<div id="container" class="site-content site-main">
			<div id="content" class="hentry">
				<?php global $post, $wp_query;
				$args = array(
					'post_type'				=> 'gallery',
					'post_status'			=> 'publish',
					'name'					=> $wp_query->query_vars['name'],
					'posts_per_page'		=> 1
				);	
				$second_query = new WP_Query( $args ); 
				$gllr_options = get_option( 'gllr_options' );
				$gllr_download_link_title = addslashes( __( 'Download high resolution image', 'gallery-plugin' ) );
				if ( $second_query->have_posts() ) {
					while ( $second_query->have_posts() ) : $second_query->the_post(); ?>
						<h1 class="home_page_title entry-header"><?php the_title(); ?></h1>
						<div class="gallery_box_single entry-content">
							<?php if ( ! post_password_required() ) {
								the_content();

								$images_id = get_post_meta( $post->ID, '_gallery_images', true );

								$posts = get_posts( array(								
									"showposts"			=>	-1,
									"what_to_show"		=>	"posts",
									"post_status"		=>	"inherit",
									"post_type"			=>	"attachment",
									"orderby"			=>	$gllr_options['order_by'],
									"order"				=>	$gllr_options['order'],
									"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
									'post__in'			=> explode( ',', $images_id ),
									'meta_key'			=> '_gallery_order_' . $post->ID
								));	
								if ( count( $posts ) > 0 ) {
									$count_image_block = 0; ?>
									<div class="gallery clearfix">
										<?php foreach ( $posts as $attachment ) { 
											$key = "gllr_image_text";
											$link_key = "gllr_link_url";
											$alt_tag_key = "gllr_image_alt_tag";
											$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'photo-thumb' );
											$image_attributes_large = wp_get_attachment_image_src( $attachment->ID, 'large' );
											$image_attributes_full = wp_get_attachment_image_src( $attachment->ID, 'full' );
											if ( 1 == $gllr_options['border_images'] ) {
												$gllr_border = 'border-width: ' . $gllr_options['border_images_width'] . 'px; border-color:' . $gllr_options['border_images_color'] . ';border: ' . $gllr_options['border_images_width'] . 'px solid ' . $gllr_options['border_images_color'];
												$gllr_border_images = $gllr_options['border_images_width'] * 2;
											} else {
												$gllr_border = '';
												$gllr_border_images = 0;
											}
											if ( $count_image_block % $gllr_options['custom_image_row_count'] == 0 ) { ?>
												<div class="gllr_image_row">
											<?php } ?>
												<div class="gllr_image_block">
													<p style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0] + $gllr_border_images; ?>px;height:<?php echo $gllr_options['gllr_custom_size_px'][1][1] + $gllr_border_images; ?>px;">
														<?php if ( ( $url_for_link = get_post_meta( $attachment->ID, $link_key, true ) ) != "" ) { ?>
															<a href="<?php echo $url_for_link; ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" target="_blank">
																<img width="<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>" height="<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>" style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>px; height:<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php echo get_post_meta( $attachment->ID, $alt_tag_key, true ); ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" src="<?php echo $image_attributes[0]; ?>" />
															</a>
														<?php } else { ?>
														<a rel="gallery_fancybox<?php if ( 0 == $gllr_options['single_lightbox_for_multiple_galleries'] ) echo '_' . $post->ID; ?>" href="<?php echo $image_attributes_large[0]; ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" >
															<img width="<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>" height="<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>" style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>px; height:<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php echo get_post_meta( $attachment->ID, $alt_tag_key, true ); ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" src="<?php echo $image_attributes[0]; ?>" rel="<?php echo $image_attributes_full[0]; ?>" />
														</a>
														<?php } ?>											
													</p>
													<?php if ( 1 == $gllr_options["image_text"] ) { ?>
														<div style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0] + $gllr_border_images; ?>px;" class="gllr_single_image_text"><?php echo get_post_meta( $attachment->ID, $key, true ); ?>&nbsp;</div>
													<?php } ?>
												</div><!-- .gllr_image_block -->
											<?php if ( $count_image_block%$gllr_options['custom_image_row_count'] == $gllr_options['custom_image_row_count']-1 ) { ?>
												</div><!-- .gllr_image_row -->
											<?php } 
											$count_image_block++; 
										} 
										if ( $count_image_block > 0 && $count_image_block%$gllr_options['custom_image_row_count'] != 0 ) { ?>
											</div><!-- .gllr_image_row -->
										<?php } ?>
									</div><!-- .gallery.clearfix -->
								<?php } ?>
							<?php } else { ?>
								<p><?php echo get_the_password_form(); ?></p>
							<?php }
						endwhile;
					if ( 1 == $gllr_options['return_link'] ) {
						if ( 'gallery_template_url' == $gllr_options["return_link_page"] ) {
							global $wpdb;
							$parent = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND (post_status = 'publish' OR post_status = 'private') AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );	?>
							<div class="return_link"><a href="<?php echo ( !empty( $parent ) ? get_permalink( $parent ) : '' ); ?>"><?php echo $gllr_options['return_link_text']; ?></a></div>
						<?php } else { ?>
							<div class="return_link"><a href="<?php echo $gllr_options["return_link_url"]; ?>"><?php echo $gllr_options['return_link_text']; ?></a></div>
						<?php }
					}	
				} else { ?>
					<div class="gallery_box_single">
						<p class="not_found"><?php _e( 'Sorry, nothing found.', 'gallery-plugin' ); ?></p>
				<?php } ?>				
					</div><!-- .gallery_box_single -->
				<div class="gllr_clear"></div>			
			</div><!-- #content -->
			<?php comments_template(); ?>
		</div><!-- #container -->
	</div><!-- .content-area -->
	<?php get_sidebar(); ?>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$("a[rel=gallery_fancybox<?php if ( 0 == $gllr_options['single_lightbox_for_multiple_galleries'] ) echo '_' . $post->ID; ?>]").fancybox({
					'transitionIn'			: 'elastic',
					'transitionOut'			: 'elastic',
					'titlePosition' 		: 'inside',
					'speedIn'				:	500, 
					'speedOut'				:	300,
					'titleFormat'			: function( title, currentArray, currentIndex, currentOpts ) {
						return '<div id="fancybox-title-inside">' + ( title.length ? '<span id="bws_gallery_image_title">' + title + '</span><br />' : '' ) + '<span id="bws_gallery_image_counter"><?php _e( "Image", "gallery-plugin" ); ?> ' + ( currentIndex + 1 ) + ' / ' + currentArray.length + '</span></div><?php if( get_post_meta( $post->ID, 'gllr_download_link', true ) != '' ){?><a id="bws_gallery_download_link" href="' + $( currentOpts.orig ).attr('rel') + '" target="_blank"><?php echo $gllr_download_link_title; ?> </a><?php } ?>';
					}<?php if ( $gllr_options['start_slideshow'] == 1 ) { ?>,
					'onComplete':	function() {
						clearTimeout( jQuery.fancybox.slider );
						jQuery.fancybox.slider = setTimeout("jQuery.fancybox.next()",<?php echo $gllr_options['slideshow_interval']; ?>);
					}<?php } ?>
				});
			});
		})(jQuery);
	</script>
<?php get_footer(); ?>