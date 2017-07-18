<?php
/**
* Includes deprecated functions
 */

/**
 * Renaming old version option keys
 * @deprecated since 4.4.4
 * @todo remove after 01.04.2017
 */
if ( ! function_exists( 'gllr_check_old_options' ) ) {
	function gllr_check_old_options() {
		if ( $old_options = get_option( 'gllr_options' ) ) {
			$update_option = false;
			if ( isset( $old_options['gllr_custom_size_name'] ) ) {
				$old_options['custom_size_name'] = $old_options['gllr_custom_size_name'];
				unset( $old_options['gllr_custom_size_name'] );
				$update_option = true;
			}
			if ( isset( $old_options['gllr_custom_size_px'] ) ) {
				$old_options['custom_size_px'] = $old_options['gllr_custom_size_px'];
				unset( $old_options['gllr_custom_size_px'] );
				$update_option = true;
			}
			if ( true === $update_option )
				update_option( 'gllr_options', $old_options );
		}
	}
}

/**
 * @deprecated since 4.4.7
 * @todo remove after 30.06.2017
 */
if ( ! function_exists( 'gllr_old_template_options' ) ) {
	function gllr_old_template_options() {
		global $gllr_options, $gllr_plugin_info, $wpdb;
		if ( isset( $gllr_options['plugin_option_version'] ) && $gllr_plugin_info["Version"] <= '4.4.7' ) {
			/* get template attribute 'gallery-template.php' for pages */
			$template_pages = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND ( post_status = 'publish' OR post_status = 'private' ) AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
			if ( ! empty( $template_pages ) ) {
				$gllr_options['page_id_gallery_template'] = $template_pages;
			}
		}
		if ( isset( $gllr_options['rewrite_template'] ) ) {
			$themepath = get_stylesheet_directory() . '/';
			foreach ( array( 'gallery-single-template.php', 'gallery-template.php' ) as $filename ) {
				if ( file_exists( $themepath . $filename ) ) {
					if ( 0 == $gllr_options['rewrite_template'] ) {
						if ( ! is_dir( $themepath  . 'bws-templates/' ) )
						 	@mkdir( $themepath  . 'bws-templates/', 0755 );
						if ( rename( $themepath . $filename, $themepath . 'bws-templates/' . $filename ) ) {
							@unlink( $themepath  . $filename );
						}
					} else {
						@unlink( $themepath  . $filename );
					}
				}
			}
			unset( $gllr_options['rewrite_template'] );
		}
	}
}

/**
 * @deprecated since 4.4.9
 * @todo remove after 01.08.2017
 */
if ( ! function_exists( 'gllr_update_options_after_redesign' ) ) {
	function gllr_update_options_after_redesign() {
		global $gllr_options, $wpdb;

		delete_metadata( 'user', null, 'wp_gllr_media_library_mode', '', true );

		if ( ! isset( $gllr_options['lightbox_download_link'] ) ) {
			$any_meta = $wpdb->get_var( "SELECT `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = 'gllr_download_link'" );
			if ( ! empty( $any_meta ) ) {
				$gllr_options['lightbox_download_link'] = 1;
				delete_metadata( 'post', null, 'gllr_download_link', '', true );
			}
		}

		if ( isset( $gllr_options['custom_size_name'] ) ) {
			$gllr_options['custom_size_px']['album-thumb'] = $gllr_options['custom_size_px'][0];
			$gllr_options['custom_size_px']['photo-thumb'] = $gllr_options['custom_size_px'][1];
			unset( $gllr_options['custom_size_name'], $gllr_options['custom_size_px'][0], $gllr_options['custom_size_px'][1] );
		}
		if ( ! isset( $gllr_options['image_size_photo'] ) )
			$gllr_options['image_size_photo'] = 'photo-thumb';
		if ( ! isset( $gllr_options['image_size_album'] ) )
			$gllr_options['image_size_album'] = 'album-thumb';

		if ( isset( $gllr_options['return_link_page'] ) ) {
			if ( 'gallery_template_url' == $gllr_options['return_link_page'] )
				$gllr_options['return_link_url'] = '';

			unset( $gllr_options['return_link_page'] );
		}

		if ( ! isset( $gllr_options['cover_border_images'] ) && isset( $gllr_options['border_images'] ) ) {
			$gllr_options['cover_border_images'] = $gllr_options['border_images'];
			$gllr_options['cover_border_images_width'] = $gllr_options['border_images_width'];
			$gllr_options['cover_border_images_color'] = $gllr_options['border_images_color'];
		}
	}
}