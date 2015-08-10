<?php
/*
Plugin Name: Gallery by BestWebSoft
Plugin URI:  http://bestwebsoft.com/products/
Description: This plugin allows you to implement gallery page into web site.
Author: BestWebSoft
Version: 4.3.4
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $gllr_filenames, $gllr_filepath, $gllr_themepath;
$gllr_filepath = WP_PLUGIN_DIR . '/gallery-plugin/template/';
$gllr_themepath = get_stylesheet_directory() . '/';

$gllr_filenames[]	=	'gallery-single-template.php';
$gllr_filenames[]	=	'gallery-template.php';

if ( ! function_exists( 'add_gllr_admin_menu' ) ) {
	function add_gllr_admin_menu() {
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', 'Gallery', 'Gallery', 'manage_options', 'gallery-plugin.php', 'gllr_settings_page' );
	}
}

if ( ! function_exists ( 'gllr_init' ) ) {
	function gllr_init() {
		global $gllr_plugin_info;
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );

		if ( ! $gllr_plugin_info ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$gllr_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version  */
		bws_wp_version_check( plugin_basename( __FILE__ ), $gllr_plugin_info, '3.2' );

		/* Register post type */
		gllr_post_type_images();
	}
}

if ( ! function_exists ( 'gllr_admin_init' ) ) {
	function gllr_admin_init() {
		global $bws_plugin_info, $gllr_plugin_info;
		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '79', 'version' => $gllr_plugin_info["Version"] );
		}
		/* Call register settings function */
		gllr_settings();
		/* add error if templates were not found in the theme directory */
		gllr_admin_error();
	}
}

/* Register settings function */
if ( ! function_exists( 'gllr_settings' ) ) {
	function gllr_settings() {
		global $gllr_options, $gllr_plugin_info, $gllr_option_defaults;

		$gllr_option_defaults	=	array(
			'plugin_option_version' 					=> $gllr_plugin_info["Version"],
			'gllr_custom_size_name'						=>	array( 'album-thumb', 'photo-thumb' ),
			'gllr_custom_size_px'						=>	array( array( 120, 80 ), array( 160, 120 ) ),
			'border_images'								=>	1,
			'border_images_width'						=>	10,
			'border_images_color'						=>	'#F1F1F1',
			'custom_image_row_count'					=>	3,
			'start_slideshow'							=>	0,
			'slideshow_interval'						=>	2000,
			'single_lightbox_for_multiple_galleries'	=>	0,
			'order_by'									=>	'menu_order',
			'order'										=>	'ASC',
			'read_more_link_text'						=>	__( 'See images &raquo;', 'gallery' ),
			'image_text'								=>	0,
			'return_link'								=>	0,
			'return_link_text'							=>	'Return to all albums',
			'return_link_page'							=>	'gallery_template_url',
			'return_link_url'							=>	'',
			'return_link_shortcode'						=>	0,
			'rewrite_template'							=>	1,
			'display_demo_notice'						=>	1,
		);

		/* Install the option defaults */
		if ( ! get_option( 'gllr_options' ) )
			add_option( 'gllr_options', $gllr_option_defaults );

		/* Get options from the database */
		$gllr_options = get_option( 'gllr_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $gllr_options['plugin_option_version'] ) || $gllr_options['plugin_option_version'] != $gllr_plugin_info["Version"] ) {
			$gllr_option_defaults['display_demo_notice'] = 0;
			$gllr_options = array_merge( $gllr_option_defaults, $gllr_options );
			$gllr_options['plugin_option_version'] = $gllr_plugin_info["Version"];
			update_option( 'gllr_options', $gllr_options );
			/* update templates when updating plugin */
			gllr_plugin_install();
		}

		if ( function_exists( 'add_image_size' ) ) { 
			add_image_size( 'album-thumb', $gllr_options['gllr_custom_size_px'][0][0], $gllr_options['gllr_custom_size_px'][0][1], true );
			add_image_size( 'photo-thumb', $gllr_options['gllr_custom_size_px'][1][0], $gllr_options['gllr_custom_size_px'][1][1], true );
		}
	}
}

/**
 * Function for activation
 */
if ( ! function_exists( 'gllr_plugin_activate' ) ) {
	function gllr_plugin_activate() {
		gllr_plugin_install();
		/* first register CPT */
		gllr_post_type_images();
		/* then flush rules on activation to add custom post */
		flush_rewrite_rules();
	}
}

/**
 * Function to copy or update templates
 */
if ( ! function_exists( 'gllr_plugin_install' ) ) {
	function gllr_plugin_install() {
		global $gllr_filenames, $gllr_filepath, $gllr_themepath, $gllr_options;
		foreach ( $gllr_filenames as $filename ) {
			if ( ! file_exists( $gllr_themepath . $filename ) ) {
				$handle		=	@fopen( $gllr_filepath . $filename, "r" );
				$contents	=	@fread( $handle, filesize( $gllr_filepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename, 'w' ) ) )
					return false;
				@fwrite( $handle, $contents );
				@fclose( $handle );
				@chmod( $gllr_themepath . $filename, octdec( 755 ) );
			} elseif ( ! isset( $gllr_options['rewrite_template'] ) || 1 == $gllr_options['rewrite_template'] ) {
				$handle		=	@fopen( $gllr_themepath . $filename, "r" );
				$contents	=	@fread( $handle, filesize( $gllr_themepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename . '.bak', 'w' ) ) )
					return false;
				@fwrite( $handle, $contents );
				@fclose( $handle );
				
				$handle		=	@fopen( $gllr_filepath . $filename, "r" );
				$contents	=	@fread( $handle, filesize( $gllr_filepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename, 'w' ) ) )
					return false;
				@fwrite( $handle, $contents );
				@fclose( $handle );
				@chmod( $gllr_themepath . $filename, octdec( 755 ) );
			}
		}
	}
}

if ( ! function_exists ( 'gllr_after_switch_theme' ) ) {
	function gllr_after_switch_theme() {
		global $gllr_filenames, $gllr_themepath;
		$file_exists_flag = true;
		foreach ( $gllr_filenames as $filename ) {
			if ( ! file_exists( $gllr_themepath . $filename ) )
				$file_exists_flag = false;
		}
		if ( ! $file_exists_flag )
			gllr_plugin_install();
	}
}

if ( ! function_exists( 'gllr_admin_error' ) ) {
	function gllr_admin_error() {
		global $gllr_filenames, $gllr_filepath, $gllr_themepath;

		$post		=	isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : "" ;
		$post_type	=	isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : get_post_type( $post );

		$file_exists_flag = true;
		if ( 'gallery' == $post_type || ( isset( $_REQUEST['page'] ) && 'gallery-plugin.php' == $_REQUEST['page'] ) ) {
			foreach ( $gllr_filenames as $filename ) {
				if ( ! file_exists( $gllr_themepath . $filename ) )
					$file_exists_flag = false;
			}
		}
		if ( ! $file_exists_flag )
			echo '<div class="error"><p><strong>' . __( 'The following files "gallery-template.php" and "gallery-single-template.php" were not found in the directory of your theme. Please copy them from the directory `/wp-content/plugins/gallery-plugin/template/` to the directory of your theme for the correct work of the Gallery plugin', 'gallery' ) . '</strong></p></div>';
	}
}

/* Create post type for Gallery */
if ( ! function_exists( 'gllr_post_type_images' ) ) {
	function gllr_post_type_images() {
		register_post_type( 'gallery', array(
			'labels' => array(
				'name'				=>	__( 'Galleries', 'gallery' ),
				'singular_name'		=>	__( 'Gallery', 'gallery' ),
				'add_new' 			=>	__( 'Add a Gallery', 'gallery' ),
				'add_new_item' 		=>	__( 'Add New Gallery', 'gallery' ),
				'edit_item' 		=>	__( 'Edit Gallery', 'gallery' ),
				'new_item' 			=>	__( 'New Gallery', 'gallery' ),
				'view_item' 		=>	__( 'View Gallery', 'gallery' ),
				'search_items' 		=>	__( 'Find a Gallery', 'gallery' ),
				'not_found' 		=>	__( 'No Gallery found', 'gallery' ),
				'parent_item_colon'	=>	'',
				'menu_name' 		=>	__( 'Galleries', 'gallery' )
			),
			'public' 				=>	true,
			'publicly_queryable'	=>	true,
			'exclude_from_search'	=>	true,
			'query_var'				=>	true,
			'rewrite' 				=>	true,
			'capability_type' 		=>	'post',
			'has_archive' 			=>	false,
			'hierarchical' 			=>	true,
			'supports' 				=>	array( 'title', 'editor', 'thumbnail', 'author', 'page-attributes', 'comments' ),
			'register_meta_box_cb'	=>	'init_metaboxes_gallery',
			'taxonomy'				=>	array( 'gallery_categories' )
		) );
	}
}

if ( ! function_exists( 'gllr_addImageAncestorToMenu' ) ) {
	function gllr_addImageAncestorToMenu( $classes ) {
		if ( is_singular( 'gallery' ) ) {
			global $wpdb, $post;
			
			if ( empty( $post->ancestors ) ) {
				$parent_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND post_status = 'publish' AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
				while ( $parent_id ) {
					$page = get_page( $parent_id );
					if ( 0 < $page->post_parent )
						$parent_id  = $page->post_parent;
					else 
						break;
				}
				wp_reset_query();
				if ( empty( $parent_id ) ) 
					return $classes;
				$post_ancestors = array( $parent_id );
			} else {
				$post_ancestors = $post->ancestors;
			}
			
			$menuItems = $wpdb->get_col( "SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = '_menu_item_object_id' AND meta_value IN (" . implode( ',', $post_ancestors ) . ")" );
			
			if ( is_array( $menuItems ) ) {
				foreach ( $menuItems as $menuItem ) {
					if ( in_array( 'menu-item-' . $menuItem, $classes ) ) {
						$classes[] = 'current-page-ancestor';
					}
				}
			}
		}
		return $classes;
	}
}

if ( ! function_exists( 'init_metaboxes_gallery' ) ) {
	function init_metaboxes_gallery() {
		add_meta_box( 'Upload-File', __( 'Upload File', 'gallery' ), 'gllr_post_custom_box', 'gallery', 'normal', 'high' ); 
		add_meta_box( 'Gallery-Shortcode', __( 'Gallery Shortcode', 'gallery' ), 'gllr_post_shortcode_box', 'gallery', 'side', 'high' );
		if ( ! ( is_plugin_active( 'gallery-categories/gallery-categories.php' ) || is_plugin_active( 'gallery-categories-pro/gallery-categories-pro.php' ) ) ) {
			add_meta_box( 'Gallery-Categories', __( 'Gallery Categories', 'gallery' ), 'gllr_gallery_categories', 'gallery', 'side', 'core' );
		}
	}
}

if ( ! function_exists( 'gllr_add_button_for_reattacher' ) ) {
	function gllr_add_button_for_reattacher() { 
		global $wp_version, $gllr_plugin_info; ?>
		<div id='gllr-rttchr-gallery-media-buttons' class='hide-if-no-js'>
			<p><span class='rttchr-button-title'> <?php _e( 'Choose a media file that will be attached', 'gallery' ); if ( 3.3 > $wp_version ) echo ' (' . sprintf( __( 'You need to install "%s" plugin to use this functionality', 'gallery'), '<a href="http://bestwebsoft.com/products/re-attacher/?k=f8c93192ba527e10974f5e901b5adb52&pn=79&v=' . $gllr_plugin_info["Version"] . '&wp_v=' . $wp_version . '">Re attacher</a>' ) . ')'; ?>: </span></p>
			<a class='button' id='gllr-rttchr-attach-media-item'><?php _e( 'Attach media item to this gallery', 'gallery' ); ?></a>
		</div>
	<?php }
}

/* Create custom meta box for gallery post type */
if ( ! function_exists( 'gllr_post_custom_box' ) ) {
	function gllr_post_custom_box( $obj = '', $box = '' ) {
		global $post, $wp_version, $gllr_plugin_info;
		$key				=	"gllr_image_text";
		$error				=	"";
		$uploader			=	true;
		$link_key			=	"gllr_link_url";
		$alt_tag_key		=	"gllr_image_alt_tag";
		$gllr_options		=	get_option( 'gllr_options' );
		$gllr_download_link	=	get_post_meta( $post->ID, 'gllr_download_link', true );

		$post_types = get_post_types( array( '_builtin' => false ) );
		if ( ! is_writable ( plugin_dir_path( __FILE__ ) ."upload/files/" ) ) {
			$error		=	__( "The gallery temp directory (gallery-plugin/upload/files) is not available for record on your webserver. Please use the standard WP functionality to upload images (media library)", 'gallery' );
			$uploader	=	false;
		} 
		/* Add link for Re-attached plugin  */
		if ( current_user_can( 'edit_posts' ) ) {
			if ( function_exists( 'rttchr_add_button_in_gallery' ) )
				rttchr_add_button_in_gallery(); 
			elseif ( function_exists( 'rttchrpr_add_button_in_gallery' ) )
				rttchrpr_add_button_in_gallery();
			else
				gllr_add_button_for_reattacher();
		} ?>
		<div style="padding-top:10px;"><label for="uploadscreen"><?php _e( 'Choose an image for upload:', 'gallery' ); ?></label>
			<input name="MAX_FILE_SIZE" value="1048576" type="hidden" />
			<div id="file-uploader-demo1" style="padding-top:10px;">
				<?php echo $error; ?>
				<noscript>
					<p><?php _e( 'Please enable JavaScript to use the file uploader.', 'gallery' ); ?></p>
				</noscript>
			</div>
			<ul id="files" ></ul>
			<div id="hidden"></div>
			<div class="gllr_clear"></div>
		</div>
		<div class="gllr_order_message hidden">
			<label><input type="checkbox" name="gllr_download_link" value="1" <?php if ( '' != $gllr_download_link ) echo "checked='checked'"; ?> /> <?php _e( 'Allow the download link for all images in this gallery', 'gallery' ); ?></label><br /><br />
			<?php _e( 'Please use the drag and drop function to change an order of the images displaying and do not forget to save the post.', 'gallery'); ?>
			<br />
			<?php _e( 'Please make a choice', 'gallery'); echo ' `'; _e( 'Sort images by', 'gallery' ); echo '` -> `'; _e( 'sort images', 'gallery' ); echo '` '; _e( 'on the plugin settings page (', 'gallery' ); ?> <a href="<?php echo admin_url( 'admin.php?page=gallery-plugin.php', 'http' ); ?>" target="_blank"><?php echo admin_url( 'admin.php?page=gallery-plugin.php', 'http' ); ?></a>)
		</div>
		<script type="text/javascript">
			<?php if ( true === $uploader ) { ?>
				jQuery(document).ready( function() {
					var uploader = new qq.FileUploader({
							element: document.getElementById('file-uploader-demo1'),
							action: '../wp-admin/admin-ajax.php?action=upload_gallery_image&gllr_ajax_nonce_field=' + '<?php echo wp_create_nonce( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' ); ?>',
							debug: false,
							onComplete: function( id, fileName, result ) {
								if ( result.error ) {
									/**/
								} else {
									var size = result;
									jQuery.ajax({
										/* sanitize file name */
										url: '../wp-admin/admin-ajax.php?action=gllr_sanitize_file_name',
										type: "POST",
										data: "gllr_ajax_nonce_field=<?php echo wp_create_nonce( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' ); ?>&gllr_name=" + fileName,
										success: function( fileName ) {
											jQuery('<li></li>').appendTo('#files').html('<img src="<?php echo plugins_url( "upload/files/" , __FILE__ ); ?>' + fileName + '" alt="" /><div style="width:200px">' + fileName + '<br />' + size.width + 'x' + size.height + '</div>').addClass('success');
											jQuery('<input type="hidden" name="undefined[]" id="undefined" value="' + fileName + '" />').appendTo('#hidden');
										}
									});
								}
							}
					});
					jQuery('#images_albumdiv').remove();
				});
			<?php } ?>
			function img_delete( id ) {
				jQuery( '#' + id ).hide();
				jQuery( '#delete_images' ).append( '<input type="hidden" name="delete_images[]" value="' + id + '" />' );
			}
		</script>
		<?php $posts = get_posts( array(
			"showposts"			=>	-1,
			"what_to_show"		=>	"posts",
			"post_status"		=>	"inherit",
			"post_type"			=>	"attachment",
			"orderby"			=>	$gllr_options['order_by'],
			"order"				=>	$gllr_options['order'],
			"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
			"post_parent"		=>	$post->ID )); ?>
		<ul class="gallery clearfix">
			<?php /* common values */
			$thumbnail_size_h = get_option( 'thumbnail_size_h' );
			$thumbnail_size_w = get_option( 'thumbnail_size_w' );
			foreach ( $posts as $page ):
				$image_text = get_post_meta( $page->ID, $key, FALSE );
				echo '<li id="' . $page->ID . '" class="gllr_image_block"><div class="gllr_drag">';
					$image_attributes = wp_get_attachment_image_src( $page->ID, 'thumbnail' );
					echo '<div class="gllr_border_image"><img src="' . $image_attributes[0] . '" alt="' . $page->post_title . '" title="' . $page->post_title . '" height="' . $thumbnail_size_h . '" width="' . $thumbnail_size_w . '" /></div>';
					echo '<br />' . __( "Title", "gallery" ) . '<br /><input type="text" name="gllr_image_text[' . $page->ID . ']" value="' . get_post_meta( $page->ID, $key, TRUE ) . '" class="gllr_image_text" />';
					echo '<input type="text" name="gllr_order_text[' . $page->ID . ']" value="' . $page->menu_order . '" class="gllr_order_text ' . ( $page->menu_order == 0 ? "hidden" : '' ) . '" />';
					echo '<br />' . __( "Alt tag", "gallery" ) . '<br /><input type="text" name="gllr_image_alt_tag[' . $page->ID . ']" value="' . get_post_meta( $page->ID, $alt_tag_key, TRUE ) . '" class="gllr_image_alt_tag" />';
					echo '<br />' . __( "URL", "gallery" ) . '<br /><input type="text" name="gllr_link_url[' . $page->ID . ']" value="' . get_post_meta( $page->ID, $link_key, TRUE ) . '" class="gllr_link_text" /><br /><span class="small_text">' . __( "(by click on image opens a link in a new window)", "gallery" ) . '</span>';
					echo '<a class="bws_plugin_pro_version" href="http://bestwebsoft.com/products/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=' . $gllr_plugin_info["Version"] . '&wp_v=' . $wp_version . '" target="_blank" title="' . __( 'This setting is available in Pro version', 'gallery' ) . '">' .
						'<div>' . __( "Open the URL", "gallery" ) . '<br/><input disabled type="radio" value="_self" > ' . __( "Current window", "gallery" ) . '<br/><input disabled type="radio" value="_blank" > ' . __( "New window", "gallery" ) . '<br/>' .
						__( "Lightbox button URL", "gallery" ) . '<br><input class="gllr_link_text" disabled type="text" value="" name="gllrprfssnl_lightbox_button_url"><br/>' . 
						__( "Description", "gallery" ) . '<br><input class="gllr_link_text" disabled type="text" value="" name="gllrprfssnl_description"></div></a>';
					echo '<div class="delete"><a href="javascript:void(0);" onclick="img_delete(' . $page->ID . ');">' . __( "Delete", "gallery" ) . '</a><div/>';
					/**
					* Add link for Re-attached plugin 
					*/
					if ( function_exists( 'rttchr_add_button_unattach_gallery' ) )
						rttchr_add_button_unattach_gallery( $page->ID );
					elseif ( function_exists( 'rttchrpr_add_button_unattach_gallery' ) )
						rttchrpr_add_button_unattach_gallery( $page->ID );
				echo '</div></li>';
			endforeach; ?>
		</ul>
		<div class="gllr_clear"></div>
		<div id="delete_images"></div>
	<?php }
}

/* Create shortcode meta box for gallery post type */
if ( ! function_exists( 'gllr_post_shortcode_box' ) ) {
	function gllr_post_shortcode_box( $obj = '', $box = '' ) {
		global $post; ?>
		<p><?php _e( 'You can add a Single Gallery to the page or post by inserting this shortcode into the content', 'gallery' ); ?>:</p>
		<p><span class="gllr_code">[print_gllr id=<?php echo $post->ID; ?>]</span></p>
		<p><?php _e( 'If you want to display a short description containing a screenshot and the link to the Single Gallery Page', 'gallery' ); ?>:</p>
		<p><span class="gllr_code">[print_gllr id=<?php echo $post->ID; ?> display=short]</span></p>
	<?php }
}

/* Metabox-ad for plugin Gallery categories */
if ( ! function_exists( 'gllr_gallery_categories' ) ) {
	function gllr_gallery_categories() { ?>
		<div class="bws_pro_version_bloc">
			<div class="bws_pro_version_table_bloc">
				<div class="bws_table_bg" style="top: 0px;"></div>
				<div id="gallery_categoriesdiv" class="postbox gllr_ad_block" style="min-width: auto; margin-bottom: 0;">
					<div class="handlediv" title="Click to toggle"><br></div>
					<div class="inside">
						<div id="taxonomy-gallery_categories" class="categorydiv">
							<ul id="gallery_categories-tabs" class="category-tabs">
								<li class="tabs"><?php _e( 'Gallery Categories', 'gallery' ); ?></li>
								<li class="hide-if-no-js" style="color:#0074A2;"><?php _e( 'Most Used', 'gallery' ); ?></li>
							</ul>
							<div id="gallery_categories-all" class="tabs-panel">
								<ul id="gallery_categorieschecklist" data-wp-lists="list:gallery_categories" class="categorychecklist form-no-clear">
									<li id="gallery_categories-2" class="popular-category">
										<label class="selectit"><input value="2" type="checkbox" disabled="disabled" name="tax_input[gallery_categories][]" id="in-gallery_categories-2" checked="checked"><?php _e( 'Default', 'gallery' ); ?></label>
									</li>
								</ul>
							</div>
							<div id="gallery_categories-adder" class="wp-hidden-children">
								<h4><a id="gallery_categories-add-toggle" href="#" class="hide-if-no-js">+ <?php _e( 'Add New Gallery Category', 'gallery' ); ?></a></h4>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="gllr_show_gallery_categories_notice"><?php _e( 'Install plugin', 'gallery'); ?> <a href="http://bestwebsoft.com/products/gallery-categories/">Gallery Categories</a></div>
	<?php }
}

if ( ! function_exists ( 'gllr_save_postdata' ) ) {
	function gllr_save_postdata( $post_id, $post ) {
		global $post, $wpdb;
		$key			=	"gllr_image_text";
		$link_key		=	"gllr_link_url";
		$alt_tag_key	=	"gllr_image_alt_tag";

		if ( isset( $_REQUEST['undefined'] ) && ! empty( $_REQUEST['undefined'] ) ) {
			$array_file_name	=	$_REQUEST['undefined'];
			$uploadFile			=	array();
			$newthumb			=	array();
			$time				=	current_time( 'mysql' );
			$uploadDir			=	wp_upload_dir( $time );

			while ( list( $key, $val ) = each( $array_file_name ) ) {
				$imagename		=	sanitize_file_name( $val );
				$uploadFile[]	=	$uploadDir["path"] . "/" . $imagename;
			}
			reset( $array_file_name );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			while ( list( $key, $val ) = each( $array_file_name ) ) {
				$file_name = sanitize_file_name( $val );
				if ( file_exists( $uploadFile[ $key ] ) ){
					$uploadFile[ $key ] = $uploadDir["path"] . "/" . pathinfo( $uploadFile[ $key ], PATHINFO_FILENAME ).uniqid().".".pathinfo( $uploadFile[$key], PATHINFO_EXTENSION );
				}

				if ( copy ( plugin_dir_path( __FILE__ ) . "upload/files/" . $file_name, $uploadFile[ $key ] ) ) {
					unlink( plugin_dir_path( __FILE__ ) . "upload/files/" . $file_name );
					$overrides	=	array( 'test_form' => false );
					$file		=	$uploadFile[$key];
					$filename	=	basename( $file );
					
					$wp_filetype	=	wp_check_filetype( $filename, null );
					$attachment		=	array(
						'post_mime_type'	=>	$wp_filetype['type'],
						'post_title'		=>	$filename,
						'post_content'		=>	'',
						'post_status'		=>	'inherit'
					);
					$attach_id		=	wp_insert_attachment( $attachment, $file );
					$attach_data	=	wp_generate_attachment_metadata( $attach_id, $file );
					wp_update_attachment_metadata( $attach_id, $attach_data );
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE ID = %d", $post->ID, $attach_id ) );
				}
			}
		}
		if ( isset( $_REQUEST['delete_images'] ) ) {
			foreach ( $_REQUEST['delete_images'] as $delete_id ) {
				delete_post_meta( $delete_id, $key );
				wp_delete_attachment( $delete_id );
				if ( isset( $_REQUEST['gllr_order_text'][ $delete_id ] ) )
					unset( $_REQUEST['gllr_order_text'][ $delete_id ] );
			}
		}
		if ( isset( $_REQUEST['gllr_image_text'] ) ) {
			$posts = get_posts( array(
				"showposts"			=>	-1,
				"what_to_show"		=>	"posts",
				"post_status"		=>	"inherit",
				"post_type"			=>	"attachment",
				"orderby"			=>	"menu_order",
				"order"				=>	"ASC",
				"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
				"post_parent"		=>	$post->ID ) );
			foreach ( $posts as $page ) {
				if ( isset( $_REQUEST['gllr_image_text'][ $page->ID ] ) ) {
					$value = htmlspecialchars( trim( $_REQUEST['gllr_image_text'][ $page->ID ] ) );
					if ( get_post_meta( $page->ID, $key, FALSE ) ) {
						/* Custom field has a value and this custom field exists in database */
						update_post_meta( $page->ID, $key, $value );
					} elseif ( $value ) {
						/* Custom field has a value, but this custom field does not exist in database */
						add_post_meta( $page->ID, $key, $value );
					}
				}
			}
		}
		if ( isset( $_REQUEST['gllr_order_text'] ) ) {
			foreach ( $_REQUEST['gllr_order_text'] as $key => $val ) {
				wp_update_post( array( 'ID' => $key, 'menu_order' => $val ) );
			}
		}
		if ( isset( $_REQUEST['gllr_link_url'] ) ) {
			$posts = get_posts( array(
				"showposts"			=>	-1,
				"what_to_show"		=>	"posts",
				"post_status"		=>	"inherit",
				"post_type"			=>	"attachment",
				"orderby"			=>	"menu_order",
				"order"				=>	"ASC",
				"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
				"post_parent"		=>	$post->ID ) );
			foreach ( $posts as $page ) {
				if ( isset( $_REQUEST['gllr_link_url'][ $page->ID ] ) ) {
					$value = esc_url( trim( $_REQUEST['gllr_link_url'][ $page->ID ] ) );
					if ( get_post_meta( $page->ID, $link_key, FALSE ) ) {
						/* Custom field has a value and this custom field exists in database */
						update_post_meta( $page->ID, $link_key, $value );
					} elseif ( $value ) {
						/* Custom field has a value, but this custom field does not exist in database */
						add_post_meta( $page->ID, $link_key, $value );
					}
				}
			}
		}
		if ( isset( $_REQUEST['gllr_image_alt_tag'] ) ) {
			$posts = get_posts( array(
				"showposts"			=>	-1,
				"what_to_show"		=>	"posts",
				"post_status"		=>	"inherit",
				"post_type"			=>	"attachment",
				"orderby"			=>	"menu_order",
				"order"				=>	"ASC",
				"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
				"post_parent"		=>	$post->ID ));
			foreach ( $posts as $page ) {
				if ( isset( $_REQUEST['gllr_image_alt_tag'][ $page->ID ] ) ) {
					$value = htmlspecialchars( trim( $_REQUEST['gllr_image_alt_tag'][ $page->ID ] ) );
					if ( get_post_meta( $page->ID, $alt_tag_key, FALSE ) ) {
						/* Custom field has a value and this custom field exists in database */
						update_post_meta( $page->ID, $alt_tag_key, $value );
					} elseif ( $value ) {
						/* Custom field has a value, but this custom field does not exist in database */
						add_post_meta( $page->ID, $alt_tag_key, $value );
					}
				}
			}
		}
		if ( isset( $_REQUEST['gllr_download_link'] ) ) {
			if ( get_post_meta( $post_id, 'gllr_download_link', FALSE ) ) {
				/* Custom field has a value and this custom field exists in database */
				update_post_meta( $post_id, 'gllr_download_link', 1 );
			} else {
				/* Custom field has a value, but this custom field does not exist in database */
				add_post_meta( $post_id, 'gllr_download_link', 1 );
			}
		} else {
			delete_post_meta( $post_id, 'gllr_download_link' );
		}
		/* flush rules if the page with gallery-template is saved */
		if ( isset( $post->post_type ) && $post->post_type == 'page' ) {
			$post_meta_value = $wpdb->get_var( "SELECT $wpdb->postmeta.meta_value FROM $wpdb->postmeta, $wpdb->posts WHERE meta_key = '_wp_page_template' AND ( post_status = 'publish' OR post_status = 'private' ) AND $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->posts.ID = $post_id" );
			if ( 'gallery-template.php' == $post_meta_value ) {
				/* update rewrite_rules */
				flush_rewrite_rules();
			}
		}
	}
}

/**
 * Add custom permalinks for pages with 'gallery' template attribute
 */
if ( ! function_exists( 'gllr_custom_permalinks' ) ) {
	function gllr_custom_permalinks( $rules ) {
		global $wpdb;
		$newrules = array();
		$parents = $wpdb->get_col( "SELECT $wpdb->posts.post_name FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND ( post_status = 'publish' OR post_status = 'private' ) AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
		if ( ! empty( $parents ) ) {
			/* loop through all pages with gallery-template */
			foreach ( $parents as $parent ) {
				if ( ! isset( $rules['(.+)/' . $parent . '/([^/]+)/?$'] ) || ! isset( $rules[ $parent . '/([^/]+)/?$'] ) ) {
					$newrules['(.+)/' . $parent . '/([^/]+)/?$'] 	= 'index.php?post_type=gallery&name=$matches[2]&posts_per_page=-1';
					$newrules[ $parent . '/([^/]+)/?$'] 			= 'index.php?post_type=gallery&name=$matches[1]&posts_per_page=-1';
					$newrules[ $parent . '/page/([^/]+)/?$'] 		= 'index.php?pagename=' . $parent . '&paged=$matches[1]';
					$newrules[ $parent . '/page/([^/]+)?$'] 		= 'index.php?pagename=' . $parent . '&paged=$matches[1]';
				}
			}
		}
		if ( false === $rules )
			return $newrules;

		return $newrules + $rules;
	}
}

if ( ! function_exists( 'gllr_template_redirect' ) ) {
	function gllr_template_redirect() { 
		global $wp_query, $post, $posts, $gllr_filenames, $gllr_themepath;
		$post_type = get_post_type();
		$file_exists_flag = false;
		if ( 'gallery' == $post_type && "" == $wp_query->query_vars["s"] && ( ! isset( $wp_query->query_vars["gallery_categories"] ) ) ) {
			foreach ( $gllr_filenames as $filename ) {
				if ( file_exists( $gllr_themepath . $filename ) ) {
					$file_exists_flag = true;
					$template = '/gallery-single-template.php';
				}
			}
		} elseif ( 'gallery' == $post_type && isset( $wp_query->query_vars["gallery_categories"] ) ) {
			foreach ( $gllr_filenames as $filename ) {
				if ( file_exists( $gllr_themepath . $filename ) ) {
					$file_exists_flag = true;
					$template = '/gallery-template.php';
				}
			}
		}
		if ( $file_exists_flag ) {
			include( STYLESHEETPATH . $template );
			exit();
		}
	}
}

/* Change the columns for the edit CPT screen */
if ( ! function_exists( 'gllr_change_columns' ) ) {
	function gllr_change_columns( $cols ) {
		$cols = array(
			'cb'		=>	'<input type="checkbox" />',
			'title'		=>	__( 'Title', 'gallery' ),
			'autor'		=>	__( 'Author', 'gallery' ),
			'gallery'	=>	__( 'Photo', 'gallery' ),
			'status'	=>	__( 'Publishing', 'gallery' ),
			'dates'		=>	__( 'Date', 'gallery' )
		);
		return $cols;
	}
}

if ( ! function_exists( 'gllr_custom_columns' ) ) {
	function gllr_custom_columns( $column, $post_id ) {
		global $wpdb;
		$post	=	get_post( $post_id );
		$row	=	$wpdb->get_results( "SELECT *
				FROM $wpdb->posts
				WHERE $wpdb->posts.post_parent = $post_id
				AND $wpdb->posts.post_type = 'attachment'
				AND (
				$wpdb->posts.post_status = 'inherit'
				)
				ORDER BY $wpdb->posts.post_title ASC" );
		switch ( $column ) {
			case "autor":
				$author_id = $post->post_author;
				echo '<a href="edit.php?post_type=post&amp;author=' . $author_id . '">' . get_the_author_meta( 'user_nicename' , $author_id ) . '</a>';
				break;
			case "gallery":
				echo count( $row );
				break;
			case "status":
				if ( 'publish' == $post->post_status )
					echo '<a href="javascript:void(0)">Yes</a>';
				else
					echo '<a href="javascript:void(0)">No</a>';
				break;
			case "dates":
				echo strtolower( __( date( "F", strtotime( $post->post_date ) ), 'kerksite' ) ) . " " . date( "j Y", strtotime( $post->post_date ) );				
				break;
		}
	}
}

if ( ! function_exists( 'gllr_the_excerpt_max_charlength' ) ) {
	function gllr_the_excerpt_max_charlength( $charlength ) {
		$excerpt = get_the_excerpt();
		$charlength ++;
		if ( strlen( $excerpt ) > $charlength ) {
			$subex		=	substr( $excerpt, 0, $charlength-5 );
			$exwords	=	explode( " ", $subex );
			$excut		=	- ( strlen ( $exwords [ count( $exwords ) - 1 ] ) );
			if ( 0 > $excut ) {
				echo substr( $subex, 0, $excut );
			} else {
				echo $subex;
			}
			echo "...";
		} else {
			echo $excerpt;
		}
	}
}

if ( ! function_exists( 'gllr_page_css_class' ) ) {
	function gllr_page_css_class( $classes, $item ) {
		global $wpdb;
		$post_type	=	get_query_var( 'post_type' );
		$parent_id	=	0;
		if ( "gallery" == $post_type ) {
			$parent_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE `meta_key` = '_wp_page_template' AND `meta_value` = 'gallery-template.php' AND `post_status` = 'publish' AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
			while ( $parent_id ) {
				$page = get_page( $parent_id );
				if ( 0 < $page->post_parent )
					$parent_id  = $page->post_parent;
				else 
					break;
			}
			wp_reset_query();
		}
		if ( $item->ID == $parent_id ) {
			array_push( $classes, 'current_page_item' );
		}
		return $classes;
	}
}

if ( ! function_exists( 'gllr_settings_page' ) ) {
	function gllr_settings_page() {
		global $gllr_options, $wp_version, $gllr_plugin_info, $gllr_option_defaults;
		$error = $message = "";
		$plugin_basename = plugin_basename( __FILE__ );
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();
		$cstmsrch_options = get_option( 'cstmsrch_options' );
		if ( $cstmsrch_options ) {
			$option_name = 'cstmsrch_options';
		} else {
			$cstmsrch_options = get_option( 'cstmsrchpr_options' );
			if ( $cstmsrch_options ) {
				$option_name = 'cstmsrchpr_options';
			} else {
				$cstmsrch_options = get_option( 'bws_custom_search' );
				if ( $cstmsrch_options ) {
					$option_name = 'bws_custom_search';
				} else {
					$cstmsrch_options = $option_name ='';
				}
			}
		}
		/* Save data for settings page */
		if ( isset( $_REQUEST['gllr_form_submit'] ) && check_admin_referer( $plugin_basename, 'gllr_nonce_name' ) ) {
			$gllr_request_options = array();
			$gllr_request_options["gllr_custom_size_name"] = $gllr_options["gllr_custom_size_name"];

			$gllr_request_options["gllr_custom_size_px"] = array( 
				array( intval( trim( $_REQUEST['gllr_custom_image_size_w_album'] ) ), intval( trim( $_REQUEST['gllr_custom_image_size_h_album'] ) ) ), 
				array( intval( trim( $_REQUEST['gllr_custom_image_size_w_photo'] ) ), intval( trim( $_REQUEST['gllr_custom_image_size_h_photo'] ) ) ) 
			);

			$gllr_request_options["border_images"]			=	( isset( $_REQUEST['gllr_border_images'] ) ) ? 1 : 0;
			$gllr_request_options["border_images_width"]	=	intval( trim( $_REQUEST['gllr_border_images_width'] ) );
			$gllr_request_options["border_images_color"]	=	trim( $_REQUEST['gllr_border_images_color'] );
			$gllr_request_options["custom_image_row_count"]	=	intval( trim( $_REQUEST['gllr_custom_image_row_count'] ) );

			if ( "" == $gllr_request_options["custom_image_row_count"] || 1 > $gllr_request_options["custom_image_row_count"] )
				$gllr_request_options["custom_image_row_count"] = 1;

			$gllr_request_options["start_slideshow"]						=	( isset( $_REQUEST['gllr_start_slideshow'] ) ) ? 1 : 0;
			$gllr_request_options["slideshow_interval"]						=	( ! isset( $_REQUEST['gllr_slideshow_interval'] ) ) || empty( $_REQUEST['gllr_slideshow_interval'] ) ? 2000 : intval( $_REQUEST['gllr_slideshow_interval'] );
			$gllr_request_options["single_lightbox_for_multiple_galleries"]	=	( isset( $_REQUEST['gllr_single_lightbox_for_multiple_galleries'] ) ) ? 1 : 0;

			$gllr_request_options["order_by"]	=	$_REQUEST['gllr_order_by'];
			$gllr_request_options["order"]		=	$_REQUEST['gllr_order'];
			$gllr_request_options["image_text"] =	( isset( $_REQUEST['gllr_image_text'] ) ) ? 1 : 0;

			$gllr_request_options["return_link"]			= ( isset( $_REQUEST['gllr_return_link'] ) ) ? 1 : 0;
			$gllr_request_options["return_link_page"]		= $_REQUEST['gllr_return_link_page'];
			$gllr_request_options["return_link_url"]		= esc_url( $_REQUEST['gllr_return_link_url'] );
			$gllr_request_options["return_link_shortcode"]	= ( isset( $_REQUEST['gllr_return_link_shortcode'] ) ) ? 1 : 0;
			$gllr_request_options["return_link_text"]		= stripslashes( esc_html( $_REQUEST['gllr_return_link_text'] ) );
			$gllr_request_options["read_more_link_text"]	= stripslashes( esc_html( $_REQUEST['gllr_read_more_link_text'] ) );

			$gllr_request_options["rewrite_template"] = isset( $_REQUEST['gllr_rewrite_template'] ) ? 1 : 0;

			if ( $cstmsrch_options && ! empty( $cstmsrch_options ) ) {
				if ( isset( $_REQUEST['gllr_add_to_search'] ) ) { 
					if ( ! in_array( 'gallery', $cstmsrch_options['post_types'] ) ) {
						array_push( $cstmsrch_options['post_types'], 'gallery' );
					}
				} else {
					if ( in_array( 'gallery', $cstmsrch_options['post_types'] ) ) {
						unset( $cstmsrch_options['post_types'][ array_search( 'gallery', $cstmsrch_options['post_types'] ) ] );
					}
				}
				update_option( $option_name, $cstmsrch_options );
			}

			/* Array merge incase this version has added new options */
			$gllr_options = array_merge( $gllr_options, $gllr_request_options );

			/* Check select one point in the blocks Arithmetic actions and Difficulty on settings page */
			update_option( 'gllr_options', $gllr_options );
			$message = __( "Settings are saved", 'gallery' );
		}

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
		} /* Display form on the setting page */ 

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$gllr_options = $gllr_option_defaults;
			update_option( 'gllr_options', $gllr_options );
			$message = __( 'All plugin settings were restored.', 'gallery' );
		}

		$result = apply_filters( 'bws_handle_demo_data', 'gllr_settings' );
		if ( ( ! empty( $result ) ) && is_array( $result ) ) { 
			$error   = $result['error'];
			$message = $result['done'];
			if ( ! empty( $result['done'] ) )
				$gllr_options = $result['options'];
		}
		?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Gallery Settings', 'gallery' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>"  href="admin.php?page=gallery-plugin.php"><?php _e( 'Settings', 'gallery' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/gallery/faq/" target="_blank"><?php _e( 'FAQ', 'gallery' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=gallery-plugin.php&amp;action=go_pro"><?php _e( 'Go PRO', 'gallery' ); ?></a>
			</h2>
			<div id="gllr_settings_message" class="updated fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo 'style="display:none"'; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php if ( ! isset( $_GET['action'] ) ) { 
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} elseif ( isset( $_POST['bws_handle_demo'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_demo_confirm();
				} else { ?>
					<div id="gllr_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'gallery' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'gallery' ); ?></p></div>
					<p><?php _e( "If you would like to add a Single Gallery to your page or post, just copy and paste this shortcode into your post or page:", 'gallery' ); ?> [print_gllr id=Your_gallery_post_id]</p>
					<noscript>
						<div class="error"><p><?php _e( 'Please enable JavaScript to use the option to renew images.', 'gallery' ); ?></p></div>
					</noscript> 
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Update images for gallery', 'gallery' ); ?> </th>
							<td style="position:relative">
								<input type="button" value="<?php _e( 'Update images', 'gallery' ); ?>" id="ajax_update_images" name="ajax_update_images" class="button" onclick="javascript:gllr_update_images();"> <div id="gllr_img_loader"><img src="<?php echo plugins_url( 'images/ajax-loader.gif', __FILE__ ); ?>" alt="loader" /></div>
							</td>
						</tr>
					</table>
					<br/>
					<form id="gllr_settings_form" method="post" action="admin.php?page=gallery-plugin.php">
						<table class="form-table">
							<tr valign="top" class="gllr_width_labels">
								<th scope="row"><?php _e( 'Image size for the album cover', 'gallery' ); ?> </th>
								<td>
									<label><?php _e( 'Image size', 'gallery' ); ?> <?php echo $gllr_options["gllr_custom_size_name"][0]; ?></label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_w_album" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][0][0]; ?>" /> 
										<?php _e( 'Width (in px)', 'gallery' ); ?>
									</label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_h_album" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][0][1]; ?>" /> 
										<?php _e( 'Height (in px)', 'gallery' ); ?>
									</label>
								</td>
							</tr>
							<tr valign="top" class="gllr_width_labels">
								<th scope="row"><?php _e( 'Image size for thumbnails', 'gallery' ); ?></th>
								<td>
									<label><?php _e( 'Image size', 'gallery' ); ?> <?php echo $gllr_options["gllr_custom_size_name"][1]; ?></label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_w_photo" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][1][0]; ?>" /> 
										<?php _e( 'Width (in px)', 'gallery' ); ?>
									</label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_h_photo" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][1][1]; ?>" /> 
										<?php _e( 'Height (in px)', 'gallery' ); ?>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2"><span class="gllr_span"><?php _e( 'WordPress will create a new thumbnail with the specified dimensions when you upload a new photo.', 'gallery' ); ?></span></td>
							</tr>
						</table>
						<div class="bws_pro_version_bloc">
							<div class="bws_pro_version_table_bloc">
								<div class="bws_table_bg"></div>
								<table class="form-table bws_pro_version">
									<tr valign="top" class="gllr_width_labels">
										<th scope="row"><?php _e( 'Image size in the lightbox', 'gallery' ); ?> </th>
										<td>
											<label><?php _e( 'Image size', 'gallery' ); ?> full-photo</label><br />
											<label><input disabled class="gllrprfssnl_size_photo_full" type="number" name="gllrprfssnl_custom_image_size_w_full" value="1024" /> <?php _e( 'Max width (in px)', 'gallery' ); ?></label><br />
											<label><input disabled class="gllrprfssnl_size_photo_full" type="number" name="gllrprfssnl_custom_image_size_h_full" value="1024" /> <?php _e( 'Max height (in px)', 'gallery' ); ?></label><br />
											<input disabled type="checkbox" name="gllrprfssnl_size_photo_full" value="1" /> <?php _e( 'Display a full size image in the lightbox', 'gallery' ); ?>
										</td>
									</tr>
									<tr valign="top" class="gllr_width_labels">
										<th scope="row"><?php _e( 'Crop position', 'gallery' ); ?></th>
										<td>
											<label>
												<select disabled>
													<option value="center"><?php _e( 'center', 'gallery' ); ?></option>
												</select> 
												<?php _e( 'Horizontal', 'gallery' ); ?>
											</label><br />
											<label>
												<select disabled>
													<option value="center"><?php _e( 'center', 'gallery' ); ?></option>
												</select>
												<?php _e( 'Vertical', 'gallery' ); ?>
											</label>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php _e( 'Lightbox background', 'gallery' ); ?> </th>
										<td>
											<input disabled class="button button-small gllrprfssnl_lightbox_default" type="button" value="<?php _e( 'Default', 'gallery' ); ?>"> <br />
											<input disabled type="text" size="8" value="0.7" name="gllrprfssnl_background_lightbox_opacity" /> <?php _e( 'Background transparency (from 0 to 1)', 'gallery' ); ?><br />
											<?php if ( 3.5 <= $wp_version ) { ?>
												<label><input disabled id="gllrprfssnl_background_lightbox_color" type="minicolors" name="gllrprfssnl_background_lightbox_color" value="#777777" id="gllrprfssnl_background_lightbox_color" /> <?php _e( 'Select a background color', 'gallery' ); ?></label>
											<?php } else { ?>
												<label><input disabled id="gllrprfssnl_background_lightbox_color" type="text" name="gllrprfssnl_background_lightbox_color" value="#777777" id="gllrprfssnl_background_lightbox_color" /><span id="gllrprfssnl_background_lightbox_color_small" style="background-color:#777777"></span> <?php _e( 'Background color', 'gallery' ); ?></label>
												<div id="colorPickerDiv_backgraund" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
											<?php } ?>
										</td>
									</tr>	
									<tr valign="top">
										<th scope="row" colspan="2">
											* <?php _e( 'If you upgrade to Pro version all your settings and galleries will be saved.', 'gallery' ); ?>
										</th>
									</tr>
								</table>
							</div>
							<div class="bws_pro_version_tooltip">
								<div class="bws_info">
									<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'gallery' ); ?> 
									<a href="http://bestwebsoft.com/products/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Learn More', 'gallery' ); ?></a>
								</div>
								<div class="bws_pro_links">
									<span class="bws_trial_info">
										<a href="http://bestwebsoft.com/products/gallery/trial/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Start Your Trial', 'gallery' ); ?></a>
										 <?php _e( 'or', 'gallery' ); ?>
									</span> 
									<a class="bws_button" href="http://bestwebsoft.com/products/gallery/buy/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin">
										<?php _e( 'Go', 'gallery' ); ?> <strong>PRO</strong>
									</a>
								</div>
								<div class="gllr_clear"></div>
							</div>
						</div>
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php _e( 'Images with border', 'gallery' ); ?></th>
								<td>
									<input type="checkbox" name="gllr_border_images" value="1" <?php if ( 1 == $gllr_options["border_images"] ) echo 'checked="checked"'; ?> /><br />
									<input type="number" min="0" max="10000" value="<?php echo $gllr_options["border_images_width"]; ?>" name="gllr_border_images_width" /> <?php _e( 'Border width in px, just numbers', 'gallery' ); ?><br />
									<?php if ( 3.5 <= $wp_version ) { ?>
										<label><input type="minicolors" name="gllr_border_images_color" maxlength="7" value="<?php echo $gllr_options["border_images_color"]; ?>" id="gllr_border_images_color" /> <?php _e( 'Select a border color', 'gallery' ); ?></label>
									<?php } else { ?>
										<label><input type="text" name="gllr_border_images_color" maxlength="7" value="<?php echo $gllr_options["border_images_color"]; ?>" id="gllr_border_images_color" /><span id="gllr_border_images_color_small" style="background-color:<?php echo $gllr_options["border_images_color"]; ?>"></span> <?php _e( 'Select a border color', 'gallery' ); ?></label>
										<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
									<?php } ?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Number of images in the row', 'gallery' ); ?> </th>
								<td>
									<input type="number" name="gllr_custom_image_row_count" min="1" max="10000" value="<?php echo $gllr_options["custom_image_row_count"]; ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Start slideshow', 'gallery' ); ?> </th>
								<td>
									<input type="checkbox" name="gllr_start_slideshow" value="1" <?php if ( 1 == $gllr_options["start_slideshow"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Slideshow interval', 'gallery' ); ?> </th>
								<td>
									<input type="number" name="gllr_slideshow_interval" min="1" max="1000000" value="<?php echo $gllr_options["slideshow_interval"]; ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Use single lightbox for multiple galleries on one page', 'gallery' ); ?> </th>
								<td>
									<input type="checkbox" name="gllr_single_lightbox_for_multiple_galleries" value="1" <?php if ( 1 == $gllr_options["single_lightbox_for_multiple_galleries"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Sort images by', 'gallery' ); ?></th>
								<td>
									<label class="label_radio"><input type="radio" name="gllr_order_by" value="ID" <?php if ( 'ID' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Attachment ID', 'gallery' ); ?></label><br />
									<label class="label_radio"><input type="radio" name="gllr_order_by" value="title" <?php if ( 'title' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Image Name', 'gallery' ); ?></label><br />
									<label class="label_radio"><input type="radio" name="gllr_order_by" value="date" <?php if ( 'date' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'gallery' ); ?></label><br />
									<label class="label_radio"><input type="radio" name="gllr_order_by" value="menu_order" <?php if ( 'menu_order' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Sorting order (the input field for sorting order in the Insert / Upload Media Gallery dialog)', 'gallery' ); ?></label><br />
									<label class="label_radio"><input type="radio" name="gllr_order_by" value="rand" <?php if ( 'rand' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Random', 'gallery' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Sort images', 'gallery' ); ?></th>
								<td>
									<label class="label_radio"><input type="radio" name="gllr_order" value="ASC" <?php if ( 'ASC' == $gllr_options["order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC (ascending order from lowest to highest values - 1, 2, 3; a, b, c)', 'gallery' ); ?></label><br />
									<label class="label_radio"><input type="radio" name="gllr_order" value="DESC" <?php if ( 'DESC' == $gllr_options["order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC (descending order from highest to lowest values - 3, 2, 1; c, b, a)', 'gallery' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Display text under the image', 'gallery' ); ?></th>
								<td>
									<label><input type="checkbox" name="gllr_image_text" value="1" <?php if ( 1 == $gllr_options["image_text"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Turn off the checkbox, if you want to display text just in a lightbox', 'gallery' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Display the Back link', 'gallery' ); ?></th>
								<td>
									<input type="checkbox" name="gllr_return_link" value="1" <?php if ( 1 == $gllr_options["return_link"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Display the Back link in the shortcode', 'gallery' ); ?> </th>
								<td>
									<input type="checkbox" name="gllr_return_link_shortcode" value="1" <?php if ( 1 == $gllr_options["return_link_shortcode"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'The Back link text', 'gallery' ); ?> </th>
								<td>
									<input type="text" name="gllr_return_link_text" maxlength="250" value="<?php echo $gllr_options["return_link_text"]; ?>" style="width:200px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'The Back link URL', 'gallery' ); ?></th>
								<td>
									<label><input type="radio" value="gallery_template_url" name="gllr_return_link_page" <?php if ( 'gallery_template_url' == $gllr_options["return_link_page"] ) echo 'checked="checked"'; ?> /><?php _e( 'Gallery page (Page with Gallery Template)', 'gallery'); ?></label><br />
									<label><input type="radio" maxlength="250" value="custom_url" name="gllr_return_link_page" id="gllr_return_link_url" <?php if ( 'custom_url' == $gllr_options["return_link_page"] ) echo 'checked="checked"'; ?> /> <input type="text" onfocus="document.getElementById('gllr_return_link_url').checked = true;" value="<?php echo $gllr_options["return_link_url"]; ?>" name="gllr_return_link_url" />
									<?php _e( '(Full URL to custom page)' , 'gallery'); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'The Read More link text', 'gallery' ); ?></th>
								<td>
									<input type="text" name="gllr_read_more_link_text" maxlength="250" value="<?php echo $gllr_options["read_more_link_text"]; ?>" style="width:200px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Add gallery to the search', 'gallery' ); ?></th>
								<td>
									<?php if ( array_key_exists( 'custom-search-plugin/custom-search-plugin.php', $all_plugins ) || array_key_exists( 'custom-search-pro/custom-search-pro.php', $all_plugins ) ) {
										if ( is_plugin_active( 'custom-search-plugin/custom-search-plugin.php' ) || is_plugin_active( 'custom-search-pro/custom-search-pro.php' ) ) { ?>
											<input type="checkbox" name="gllr_add_to_search" value="1" <?php if ( in_array( 'gallery', $cstmsrch_options['post_types'] ) ) echo 'checked="checked"'; ?> />
											<span class="gllr_span"> (<?php _e( 'Using', 'gallery' ); ?> Custom Search <?php _e( 'powered by', 'gallery' ); ?> <a href="http://bestwebsoft.com/products/">bestwebsoft.com</a>)</span>
										<?php } else { ?>
											<input disabled="disabled" type="checkbox" name="gllr_add_to_search" value="1" <?php if ( in_array( 'gallery', $cstmsrch_options['post_types'] ) ) echo 'checked="checked"'; ?> /> 
											<span class="gllr_span">(<?php _e( 'Using Custom Search powered by', 'gallery' ); ?> <a href="http://bestwebsoft.com/products/">bestwebsoft.com</a>) <a href="<?php echo bloginfo("url"); ?>/wp-admin/plugins.php"><?php _e( 'Activate Custom Search', 'gallery' ); ?></a></span>
										<?php }
									} else { ?>
										<input disabled="disabled" type="checkbox" name="gllr_add_to_search" value="1" />  
										<span class="gllr_span">(<?php _e( 'Using Custom Search powered by', 'gallery' ); ?> <a href="http://bestwebsoft.com/products/">bestwebsoft.com</a>) <a href="http://bestwebsoft.com/products/custom-search/"><?php _e( 'Download Custom Search', 'gallery' ); ?></a></span>
									<?php } ?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Rewrite templates after update', 'gallery' ); ?></th>
								<td>
									<input type="checkbox" name="gllr_rewrite_template" value="1" <?php if ( 1 ==  $gllr_options['rewrite_template'] ) echo 'checked="checked"'; ?> /> <span class="gllr_span"><?php _e( "Turn off the checkbox, if You edited the file 'gallery-template.php' or 'gallery-single-template.php' file in your theme folder and You don't want to rewrite them", 'gallery' ); ?></span>
								</td>
							</tr>
						</table>
						<div class="bws_pro_version_bloc">
							<div class="bws_pro_version_table_bloc">
								<div class="bws_table_bg"></div>
								<table class="form-table bws_pro_version">
									<tr valign="top" class="gllr_width_labels">
										<th scope="row"><?php _e( 'Use pagination for images', 'gallery' ); ?></th>
										<td>
											<input disabled type="checkbox" name="gllrprfssnl_images_pagination" value="1" /><br />
											<label><input disabled type="number" name="gllrprfssnl_images_per_page" value="" /> <?php _e( 'per page', 'gallery' ); ?></label>
										</td>
									</tr>
									<tr valign="top" class="gllr_width_labels">
										<th scope="row"><?php _e( 'The lightbox helper', 'gallery' ); ?></th>
										<td>
											<label><input disabled type="radio" name="gllrprfssnl_fancybox_helper" value="none" /> <?php _e( 'Do not use', 'gallery' ); ?></label><br />
											<label><input disabled type="radio" name="gllrprfssnl_fancybox_helper" value="button" /> <?php _e( 'Button helper', 'gallery' ); ?></label><br />
											<label><input disabled type="radio" name="gllrprfssnl_fancybox_helper" value="thumbnail" /> <?php _e( 'Thumbnail helper', 'gallery' ); ?></label>
										</td>
									</tr>
									<tr valign="top" class="gllr_width_labels">
										<th scope="row"><?php _e( 'Display Like buttons in the lightbox', 'gallery' ); ?></th>
										<td>
											<label><input disabled type="checkbox" name="gllrprfssnl_like_button_fb" value="1" /> <?php _e( 'FaceBook', 'gallery' ); ?></label><br />
											<label><input disabled type="checkbox" name="gllrprfssnl_like_button_twit" value="1" /> <?php _e( 'Twitter', 'gallery' ); ?></label><br />
											<label><input disabled type="checkbox" name="gllrprfssnl_like_button_pint" value="1" /> <?php _e( 'Pinterest', 'gallery' ); ?></label><br />
											<label><input disabled type="checkbox" name="gllrprfssnl_like_button_g_plusone" value="1" /> <?php _e( 'Google +1', 'gallery' ); ?></label>
										</td>
									</tr>
									<tr valign="top" class="gllr_width_labels">
										<th scope="row"><?php _e( 'Slug for gallery item', 'gallery' ); ?></th>
										<td>
											<input type="text" name="gllrprfssnl_slug" value="gallery" disabled /> <span class="gllr_span"><?php _e( 'for any structure of permalinks except the default structure', 'gallery' ); ?></span>
										</td
									</tr>
									<tr valign="top">
										<th scope="row"><?php _e( 'Title for lightbox button', 'gallery' ); ?></th>
										<td>
											<input type="text" name="gllrprfssnl_lightbox_button_text" disabled value="" />
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php _e( 'Display all images in the lightbox instead of going into a single gallery', 'gallery' ); ?> </th>
										<td>
											<input type="checkbox" name="gllrpr_hide_single_gallery" value="1" disabled />
											<span class="gllr_span">(<?php _e( 'When using the gallery template or a shortcode with `display=short` parameter', 'gallery' ); ?>)</span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" colspan="2">
											* <?php _e( 'If you upgrade to Pro version all your settings and galleries will be saved.', 'gallery' ); ?>
										</th>
									</tr>
								</table>
							</div>
							<div class="bws_pro_version_tooltip">
								<div class="bws_info">
									<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'gallery' ); ?> 
									<a href="http://bestwebsoft.com/products/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Learn More', 'gallery' ); ?></a>
								</div>
								<div class="bws_pro_links">
									<span class="bws_trial_info">
										<a href="http://bestwebsoft.com/products/gallery/trial/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Start Your Trial', 'gallery' ); ?></a>
										 <?php _e( 'or', 'gallery' ); ?>
									</span>
									<a class="bws_button" href="http://bestwebsoft.com/products/gallery/buy/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin">
										<?php _e( 'Go', 'gallery' ); ?> <strong>PRO</strong>
									</a>
								</div>
								<div class="gllr_clear"></div>
							</div>
						</div>
						<input type="hidden" name="gllr_form_submit" value="submit" />
						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" /> 
						</p>
						<?php wp_nonce_field( $plugin_basename, 'gllr_nonce_name' ); ?>
					</form>
					<?php do_action( 'bws_show_demo_button');
					bws_form_restore_default_settings( $plugin_basename );
				}
			} elseif ( 'go_pro' == $_GET['action'] ) {
				bws_go_pro_tab( $gllr_plugin_info, $plugin_basename, 'gallery-plugin.php', 'gallery-plugin-pro.php', 'gallery-plugin-pro/gallery-plugin-pro.php', 'gallery', '63a36f6bf5de0726ad6a43a165f38fe5', '79', isset( $go_pro_result['pro_plugin_is_activated'] ), '7' );
			} 
			bws_plugin_reviews_block( $gllr_plugin_info['Name'], 'gallery-plugin' ); ?>
		</div>
	<?php }
}

/**
 * Remove shortcode from the content of the same gallery
 */
if ( ! function_exists ( 'gllr_content_save_pre' ) ) {
	function gllr_content_save_pre( $content ) {
		global $post;

		if ( isset( $post ) && "gallery" == $post->post_type && ! wp_is_post_revision( $post->ID ) && ! empty( $_POST ) ) {
			/* remove shortcode */
			$content = preg_replace( '/\[print_gllr id=' . $post->ID . '( display=short){0,1}\]/', '', $content );
		}
		return $content;
	}
}

if ( ! function_exists( 'gllr_register_plugin_links' ) ) {
	function gllr_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=gallery-plugin.php">' . __( 'Settings', 'gallery' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/gallery-plugin/faq/" target="_blank">' . __( 'FAQ', 'gallery' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'gallery' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'gllr_plugin_action_links' ) ) {
	function gllr_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename( __FILE__ );

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=gallery-plugin.php">' . __( 'Settings', 'gallery' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists ( 'gllr_add_admin_script' ) ) {
	function gllr_add_admin_script() { 
		global $wp_version, $post_type; ?>
		<script type="text/javascript">
			(function($) {
				$(document).ready( function() {
					$( '.gllr_image_block img' ).css( 'cursor', 'all-scroll' );
					$( '.gllr_order_message' ).removeClass( 'hidden' );
					var d = false;
					if ( $.fn.sortable ) {
						$( '#Upload-File .gallery' ).sortable( {
							stop: function( event, ui ) { 
								$( '.gllr_order_text' ).removeClass( 'hidden' );
								var g = $( '#Upload-File .gallery' ).sortable( 'toArray' );
								var f = g.length;
								$.each(	g,
									function( k,l ) {
										var j = d?(f-k):(1+k);
										$( '.gllr_order_text[name^="gllr_order_text[' + l + ']"]' ).val( j );
									}
								)
							}
						});
						$( '#Upload-File .gallery input' ).bind( 'click.sortable mousedown.sortable',function( ev ) {
							ev.target.focus();
						});
					}
					<?php if ( 3.5 > $wp_version && isset( $_REQUEST['page'] ) && 'gallery-plugin.php' == $_REQUEST['page'] ) { ?>
						var gllr_farbtastic = $.farbtastic( '#colorPickerDiv', function( color ) {
							gllr_farbtastic.setColor( color );
							$( '#gllr_border_images_color' ).val( color );
							$( '#gllr_border_images_color_small' ).css( 'background-color', color );
						});
						$( '#gllr_border_images_color' ).click( function() {
							$( '#colorPickerDiv' ).show();
						});
						$( '#gllr_border_images_color_small' ).click( function() {
							$( '#colorPickerDiv' ).show();
						});
						$(document).mousedown( function() {
							$( '#colorPickerDiv' ).each( function() {
								var display = $( this ).css( 'display' );
								if ( display == 'block' )
									jQuery( this ).fadeOut(2);
							});
						});
					<?php } 
					if ( ! ( 3.3 > $wp_version ) && isset( $post_type ) && 'gallery' == $post_type ) { ?>
						$( '#gllr_show_gallery_categories_notice' ).hide();
					<?php } ?>
				});
			})(jQuery);
		</script>
	<?php }
}

if ( ! function_exists ( 'gllr_admin_head' ) ) {
	function gllr_admin_head() {
		global $wp_version, $gllr_plugin_info, $post_type;

		if ( 3.8 > $wp_version )
			wp_enqueue_style( 'gllr_stylesheet', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );
		else
			wp_enqueue_style( 'gllr_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );

		wp_enqueue_style( 'gllr_FileuploaderCss', plugins_url( 'upload/fileuploader.css', __FILE__ ) );
		
		wp_enqueue_script( 'jquery' );
		if ( 3.5 > $wp_version ) {
			wp_enqueue_style( 'farbtastic' );
			wp_enqueue_script( 'farbtastic' );
		} 
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'gllr_FileuploaderJs', plugins_url( 'upload/fileuploader.js', __FILE__ ), array( 'jquery' ) );

		if ( isset( $_GET['page'] ) && "gallery-plugin.php" == $_GET['page'] ) {
			if ( $wp_version >= 3.5 ) {
				wp_enqueue_script( 'gllr_minicolors_js', plugins_url( 'minicolors/jquery.miniColors.js', __FILE__ ) );
				wp_enqueue_style( 'gllr_minicolors_css', plugins_url( 'minicolors/jquery.miniColors.css', __FILE__ ) );
			}

			wp_enqueue_script( 'gllr_script', plugins_url( 'js/script.js', __FILE__ ) );
			wp_localize_script( 'gllr_script', 'gllr_vars',
				array(
					'gllr_nonce'			=> wp_create_nonce( plugin_basename( __FILE__ ), 'gllrprfssnl_ajax_nonce_field' ),
					'update_img_message'	=> __( 'Updating images...', 'gallery' ),
					'not_found_img_info' 	=> __( 'No image found.', 'gallery' ),
					'img_success' 			=> __( 'All images are updated.', 'gallery' ),
					'img_error'				=> __( 'Error.', 'gallery' )
				) );
		}
		if ( ! ( 3.3 > $wp_version ) && isset( $post_type ) && 'gallery' == $post_type ) {
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );

			if ( ! function_exists( 'get_plugins' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$all_plugins = get_plugins();
			$learn_more = str_replace( ' ', '&nbsp', __( 'Learn more', 'gallery' ) );
			/* tooltip for gallery categories */
			if ( isset( $all_plugins['gallery-categories/gallery-categories.php'] ) || isset( $all_plugins['gallery-categories-pro/gallery-categories-pro.php'] ) ) {
				/* if gallery categories is installed */
				$link = "plugins.php";
				$text = __( 'Activate', 'gallery' );
			} else {
				if ( function_exists( 'is_multisite' ) )
					$link = ( ! is_multisite() ) ? admin_url( '/' ) : network_admin_url( '/' );
				else
					$link = admin_url( '/' );
				$link = $link . 'plugin-install.php?tab=search&type=term&s=Gallery+Categories+BestWebSoft&plugin-search-input=Search+Plugins';
				$text = __( 'Install now', 'gallery' );
			}
			$tooltip_args = array(
				'tooltip_id'	=> 'gllr_install_gallery_categories_tooltip',
				'css_selector' 	=> '.gllr_ad_block #gallery_categories-add-toggle',
				'actions' 		=> array(
					'click' 	=> true,
					'onload' 	=> true,
				), 
				'content' 		=> '<h3>' . __( 'Add multiple gallery categories', 'gallery' ) . '</h3><p>' . __( "Install Gallery Categories plugin to add unlimited number of categories.", 'gallery' ) . ' <a href="http://bestwebsoft.com/products/gallery-categories/?k=bb17b69bfb50827f3e2a9b3a75978760&pn=79&v=' . $gllr_plugin_info["Version"] . '&wp_v=' . $wp_version . '" target="_blank">' . $learn_more . '</a></p>',
				'buttons'		=> array(
					array(
						'type' => 'link',
						'link' => $link,
						'text' => $text
					),
					'close' => array(
						'type' => 'dismiss',
						'text' => __( 'Close', 'gallery' ),
					),
				),
				'position' => array( 
					'edge' 		=> 'right',
				),
			);
			if ( 4.0 > $wp_version && 3.8 < $wp_version) {
				$tooltip_args['position']['edge'] = 'top';
			}
			bws_add_tooltip_in_admin( $tooltip_args );
			/* tooltip for re-attacher*/
			if ( isset( $all_plugins['re-attacher/re-attacher.php'] ) || isset( $all_plugins['re-attacher-pro/re-attacher-pro.php'] ) ) {
				/* if re-attacher is installed */
				$link = "plugins.php";
				$text = __( 'Activate', 'gallery' );
			} else {
				if ( function_exists( 'is_multisite' ) )
					$link = ( ! is_multisite() ) ? admin_url( '/' ) : network_admin_url( '/' );
				else
					$link = admin_url( '/' );
				$link = $link . 'plugin-install.php?tab=search&type=term&s=Re-attacher+BestWebSoft&plugin-search-input=Search+Plugins';
				$text = __( 'Install now', 'gallery' );
			}

			$tooltip_args = array(
				'tooltip_id'	=> 'gllr_install_re_attacher_tooltip',
				'css_selector' 	=> '#gllr-rttchr-attach-media-item',
				'actions' 		=> array(
					'click' 	=> true,
					'onload' 	=> true,
				), 
				'content' 		=> '<h3>' . __( 'Already attached?', 'gallery' ) . '</h3><p>' . __( "If you'd like to attach the files, which are already uploaded, please use Re-attacher plugin.", 'gallery' ) . ' <a href="http://bestwebsoft.com/products/re-attacher/?k=f8c93192ba527e10974f5e901b5adb52&pn=79&v=' . $gllr_plugin_info["Version"] . '&wp_v=' . $wp_version . '" target="_blank">' . $learn_more . '</a></p>',
				'buttons'		=> array(
					array(
						'type' => 'link',
						'link' => $link,
						'text' => $text
					),
					'close' => array(
						'type' => 'dismiss',
						'text' => __( 'Close', 'gallery' ),
					),
				),
				'position' => array( 
					'edge' 		=> 'left',
				),
			);
			/* click tootip */
			$tooltip_args['actions']['onload'] = false;
			bws_add_tooltip_in_admin( $tooltip_args );
			/* onload tooltip */
			$tooltip_args['actions']['onload'] = true;
			$tooltip_args['actions']['click'] = false;
			if ( 3.5 > $wp_version ) {
				$tooltip_args['position']['pos-top'] = 12;
			} elseif ( 3.9 > $wp_version ) {
				$tooltip_args['position']['pos-top'] = 33;
			} elseif ( 4.0 > $wp_version ) {
				$tooltip_args['position']['pos-top'] = 60;
			} else {
				$tooltip_args['position']['pos-top'] = 30;
			}
			bws_add_tooltip_in_admin( $tooltip_args );
		}
	}
}

if ( ! function_exists ( 'gllr_wp_head' ) ) {
	function gllr_wp_head() {
		global $gllr_options;
		if ( empty( $gllr_options ) )
			$gllr_options = get_option( 'gllr_options' );

		wp_enqueue_style( 'gllr_stylesheet', plugins_url( 'css/frontend_style.css', __FILE__ ) );

		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();

		if ( ! is_plugin_active( 'portfolio-pro/portfolio-pro.php' ) || ( isset( $all_plugins["portfolio-pro/portfolio-pro.php"]["Version"] ) && "1.0.0" >= $all_plugins["portfolio-pro/portfolio-pro.php"]["Version"] ) ) { 
			wp_enqueue_style( 'gllr_fancybox_stylesheet', plugins_url( 'fancybox/jquery.fancybox-1.3.4.css', __FILE__ ) );
			wp_enqueue_script( 'gllr_fancybox_mousewheel_js', plugins_url( 'fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__ ), array( 'jquery' ) ); 
			wp_enqueue_script( 'gllr_fancybox_js', plugins_url( 'fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__ ), array( 'jquery' ) );
		}
		if ( 1 == $gllr_options["image_text"] )
			wp_enqueue_script( 'gllr_js', plugins_url( 'js/frontend_script.js', __FILE__ ), array( 'jquery' ) );

	}
}

if ( ! function_exists( 'gllr_add_wp_head' ) ) {
	function gllr_add_wp_head() {
		global $gllr_options;
		if ( empty( $gllr_options ) )
			$gllr_options = get_option( 'gllr_options' );

		if ( 1 == $gllr_options["image_text"] ) { ?>
			<style type="text/css">
				.gllr_image_row {
					clear: both;
				}
			</style>
		<?php } ?>
		<!-- Start ios -->
		<script type="text/javascript">
			(function($){
				$(document).ready( function() {
					$( '#fancybox-overlay' ).css({
						'width' : $(document).width()
					});
				});
			})(jQuery);
		</script>
		<!-- End ios -->
	<?php }
}

if ( ! function_exists ( 'gllr_shortcode' ) ) {
	function gllr_shortcode( $attr ) {
		global $gllr_options;
		$gllr_download_link_title = addslashes( __( 'Download high resolution image', 'gallery' ) );
		extract( shortcode_atts( array(
				'id'		=>	'',
				'display'	=>	'full',
				'cat_id'	=>	''
			), $attr ) 
		);
		ob_start();
		if ( empty( $gllr_options ) )
			$gllr_options = get_option( 'gllr_options' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! empty( $cat_id ) && ( is_plugin_active( 'gallery-categories/gallery-categories.php' ) || is_plugin_active( 'gallery-categories-pro/gallery-categories-pro.php' ) ) ) {
			global $first_query;
			$term = get_term( $cat_id, 'gallery_categories' );
			if ( !empty ( $term ) ) {
				$args = array(
					'post_type'			=>	'gallery',
					'post_status'		=>	'publish',
					'posts_per_page'	=>	-1,
					'gallery_categories'=>	$term->slug
				);
				$first_query = new WP_Query( $args ); ?>
				<div class="gallery_box">
					<ul>
						<?php global $post, $wpdb, $wp_query;
						if ( $first_query->have_posts() ) {
							while ( $first_query->have_posts() ) {
								$first_query->the_post();
								$attachments = get_post_thumbnail_id( $post->ID );
								if ( empty ( $attachments ) ) {
									$attachments = get_children( 'post_parent=' . $post->ID . '&post_type=attachment&post_mime_type=image&numberposts=1' );
									$id = key( $attachments );
									$image_attributes = wp_get_attachment_image_src( $id, 'album-thumb' ); 
								} else {
									$image_attributes = wp_get_attachment_image_src( $attachments, 'album-thumb' );
								} ?>
								<li>
									<a rel="bookmark" href="<?php echo get_permalink(); ?>" title="<?php the_title(); ?>">
										<img width="<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>" height="<?php echo $gllr_options['gllr_custom_size_px'][0][1]; ?>" style="width:<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>px; height:<?php echo $gllr_options['gllr_custom_size_px'][0][1]; ?>px;" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" src="<?php echo $image_attributes[0]; ?>" />
									</a>
									<div class="gallery_detail_box">
										<div><?php the_title(); ?></div>
										<div><?php echo gllr_the_excerpt_max_charlength( 100 ); ?></div>
										<a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $gllr_options["read_more_link_text"]; ?></a>
									</div><!-- .gallery_detail_box -->
									<div class="gllr_clear"></div>
								</li>
							<?php }
						} ?>
					</ul>
				</div><!-- .gallery_box -->
				<?php wp_reset_query();
			}
		} else {
			$args = array(
				'post_type'			=>	'gallery',
				'post_status'		=>	'publish',
				'p'					=>	$id,
				'posts_per_page'	=>	1
			);
			$second_query = new WP_Query( $args );
			if ( $display == 'short' ) { ?>
				<div class="gallery_box">
					<ul>
						<?php global $post, $wpdb, $wp_query;
						if ( $second_query->have_posts() ) : $second_query->the_post();
							$attachments = get_post_thumbnail_id( $post->ID );
							if ( empty ( $attachments ) ) {
								$attachments = get_children( 'post_parent=' . $post->ID . '&post_type=attachment&post_mime_type=image&numberposts=1' );
								$id = key( $attachments );
								$image_attributes = wp_get_attachment_image_src( $id, 'album-thumb' );
							} else {
								$image_attributes = wp_get_attachment_image_src( $attachments, 'album-thumb' );
							} 
							if ( 1 == $gllr_options['border_images'] ) {
								$gllr_border = 'border-width: ' . $gllr_options['border_images_width'] . 'px; border-color:' . $gllr_options['border_images_color'] . ';border: ' . $gllr_options['border_images_width'] . 'px solid ' . $gllr_options['border_images_color'];
							} else {
								$gllr_border = '';
							} ?>
							<li>
								<a rel="bookmark" href="<?php echo get_permalink(); ?>" title="<?php the_title(); ?>">
									<img width="<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>" height="<?php echo $gllr_options['gllr_custom_size_px'][0][1]; ?>" style="width:<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>px; height:<?php echo $gllr_options['gllr_custom_size_px'][0][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" src="<?php echo $image_attributes[0]; ?>" />
								</a>
								<div class="gallery_detail_box">
									<div><?php the_title(); ?></div>
									<div><?php echo gllr_the_excerpt_max_charlength( 100 ); ?></div>
									<a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $gllr_options["read_more_link_text"]; ?></a>
								</div><!-- .gallery_detail_box -->
								<div class="gllr_clear"></div>
							</li>
						<?php endif; ?>
					</ul>
				</div><!-- .gallery_box -->
			<?php } else { 
				if ( $second_query->have_posts() ) : 
					while ( $second_query->have_posts() ) : 
						global $post;
						$second_query->the_post(); ?>
						<div class="gallery_box_single">
							<?php echo do_shortcode( get_the_content() );
							$posts = get_posts( array(
								"showposts"			=>	-1,
								"what_to_show"		=>	"posts",
								"post_status"		=>	"inherit",
								"post_type"			=>	"attachment",
								"orderby"			=>	$gllr_options['order_by'],
								"order"				=>	$gllr_options['order'],
								"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
								"post_parent"		=>	$post->ID
							));
							if ( 0 < count( $posts ) ) {
								$count_image_block = 0; ?>
								<div class="gallery clearfix">
									<?php foreach ( $posts as $attachment ) { 
										$key			=	"gllr_image_text";
										$link_key		=	"gllr_link_url";
										$alt_tag_key	=	"gllr_image_alt_tag";
										$image_attributes		= 	wp_get_attachment_image_src( $attachment->ID, 'photo-thumb' );
										$image_attributes_large	=	wp_get_attachment_image_src( $attachment->ID, 'large' );
										$image_attributes_full	=	wp_get_attachment_image_src( $attachment->ID, 'full' );
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
														<a rel="gallery_fancybox<?php if ( 0 == $gllr_options['single_lightbox_for_multiple_galleries'] ) echo '_' . $post->ID; ?>" href="<?php echo $image_attributes_large[0]; ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>">
															<img style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>px;height:<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php echo get_post_meta( $attachment->ID, $alt_tag_key, true ); ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" src="<?php echo $image_attributes[0]; ?>" rel="<?php echo $image_attributes_full[0]; ?>" />
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
									if ( 0 < $count_image_block && $count_image_block%$gllr_options['custom_image_row_count'] != 0 ) { ?>
										</div><!-- .gllr_image_row -->
									<?php } ?>
								</div><!-- .gallery.clearfix -->
							<?php } ?>
						</div><!-- .gallery_box_single -->
						<div class="gllr_clear"></div>
					<?php endwhile; 
				else: ?>
					<div class="gallery_box_single">
						<p class="not_found"><?php _e( 'Sorry, nothing found.', 'gallery' ); ?></p>
					</div><!-- .gallery_box_single -->
				<?php endif;
				if ( 1 == $gllr_options['return_link_shortcode'] ) {
					if ( 'gallery_template_url' == $gllr_options["return_link_page"] ) {
						global $wpdb;
						$parent = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND (post_status = 'publish' OR post_status = 'private') AND $wpdb->posts.ID = $wpdb->postmeta.post_id" ); ?>
						<div class="return_link"><a href="<?php echo ( ! empty( $parent ) ? get_permalink( $parent ) : '' ); ?>"><?php echo $gllr_options['return_link_text']; ?></a></div>
					<?php } else { ?>
						<div class="return_link"><a href="<?php echo $gllr_options["return_link_url"]; ?>"><?php echo $gllr_options['return_link_text']; ?></a></div>
					<?php }
				} ?>
				<script type="text/javascript">
					(function($) {
						$(document).ready( function() {
							$( "a[rel=gallery_fancybox<?php if ( 0 == $gllr_options['single_lightbox_for_multiple_galleries'] ) echo '_' . $post->ID; ?>]" ).fancybox( {
								'transitionIn'		:	'elastic',
								'transitionOut'		:	'elastic',
								'titlePosition' 	:	'inside',
								'speedIn'			:	500, 
								'speedOut'			:	300,
								'titleFormat'		:	function( title, currentArray, currentIndex, currentOpts ) {
									return '<div id="fancybox-title-inside">' + ( title.length ? '<span id="bws_gallery_image_title">' + title + '</span><br />' : '' ) + '<span id="bws_gallery_image_counter"><?php _e( "Image", "gallery"); ?> ' + ( currentIndex + 1 ) + ' / ' + currentArray.length + '</span></div><?php if( get_post_meta( $post->ID, 'gllr_download_link', true ) != '' ){?><a id="bws_gallery_download_link" href="' + $( currentOpts.orig ).attr( 'rel' ) + '" target="_blank"><?php echo $gllr_download_link_title; ?> </a><?php } ?>';
								}<?php if ( 1 == $gllr_options['start_slideshow'] ) { ?>,
								'onComplete':	function() {
									clearTimeout( jQuery.fancybox.slider );
									jQuery.fancybox.slider = setTimeout( "jQuery.fancybox.next()",<?php echo $gllr_options['slideshow_interval']; ?> );
								}<?php } ?>
							});
						});
					})(jQuery);
				</script>
			<?php }
			wp_reset_query();
		}
		$gllr_output = ob_get_clean();
		return $gllr_output;
	}
}

if ( ! function_exists( 'upload_gallery_image' ) ) {
	function upload_gallery_image() {
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' );

		class qqUploadedFileXhr {
			/**
			 * Save the file to the specified path
			 * @return boolean TRUE on success
			 */
			function save( $path ) {
				$input		=	fopen( "php://input", "r" );
				$temp		=	tmpfile();
				$realSize	=	stream_copy_to_stream( $input, $temp );
				fclose( $input );

				if ( $realSize != $this->getSize() ) {
					return false;
				}

				$target = fopen( $path, "w" );
				fseek( $temp, 0, SEEK_SET );
				stream_copy_to_stream( $temp, $target );
				fclose( $target );
		
				return true;
			}
			function getName() {
				return sanitize_file_name( $_GET['qqfile'] );
			}
			function getSize() {
				if ( isset( $_SERVER["CONTENT_LENGTH"] ) ){
					return (int)$_SERVER["CONTENT_LENGTH"];
				} else {
					throw new Exception( 'Getting content length is not supported.' );
				}
			}
		}

		/**
		 * Handle file uploads via regular form post (uses the $_FILES array)
		 */
		class qqUploadedFileForm {  
			/**
			 * Save the file to the specified path
			 * @return boolean TRUE on success
			 */
			function save( $path ) {
				if ( ! move_uploaded_file( $_FILES['qqfile']['tmp_name'], $path ) ) {
					return false;
				}
				return true;
			}
			function getName() {
				return sanitize_file_name( $_FILES['qqfile']['name'] );
			}
			function getSize() {
				return $_FILES['qqfile']['size'];
			}
		}

		class qqFileUploader {
			private $allowedExtensions = array();
			private $sizeLimit = 10485760;
			private $file;

			function __construct( array $allowedExtensions = array(), $sizeLimit = 10485760 ) {
				$allowedExtensions = array_map( "strtolower", $allowedExtensions );
					
				$this->allowedExtensions = $allowedExtensions;
				$this->sizeLimit = $sizeLimit;
				
				/*$this->checkServerSettings();*/

				if ( isset( $_GET['qqfile'] ) ) {
					$this->file = new qqUploadedFileXhr();
				} elseif ( isset( $_FILES['qqfile'] ) ) {
					$this->file = new qqUploadedFileForm();
				} else {
					$this->file = false;
				}
			}

			private function checkServerSettings() {
				$postSize = $this->toBytes( ini_get( 'post_max_size' ) );
				$uploadSize = $this->toBytes( ini_get( 'upload_max_filesize' ) );
				
				if ( $postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit ){
					$size = max( 1, $this->sizeLimit / 1024 / 1024 ) . 'M';
					die( "{error:'increase post_max_size and upload_max_filesize to $size'}" );
				}
			}
	
			private function toBytes( $str ) {
				$val = trim( $str );
				$last = strtolower( $str[ strlen( $str ) - 1 ] );
				switch( $last ) {
					case 'g': $val *= 1024;
					case 'm': $val *= 1024;
					case 'k': $val *= 1024;
				}
				return $val;
			}
	
			/**
			 * Returns array('success'=>true) or array('error'=>'error message')
			 */
			function handleUpload( $uploadDirectory, $replaceOldFile = FALSE ) {
				if ( ! is_writable( $uploadDirectory ) ){
					return "{error:'Server error. Upload directory isn't writable.'}";
				}
				
				if ( ! $this->file ){
					return "{error:'No files were uploaded.'}";
				}
				
				$size = $this->file->getSize();
				
				if ( $size == 0 ) {
					return "{error:'File is empty'}";
				}
				
				if ( $size > $this->sizeLimit ) {
					return "{error:'File is too large'}";
				}
				
				$pathinfo = pathinfo( $this->file->getName() );
				$ext = $pathinfo['extension'];
				$filename = str_replace( "." . $ext, "", $pathinfo['basename'] );
				/*$filename = md5(uniqid());*/

				if ( $this->allowedExtensions && ! in_array( strtolower( $ext ), $this->allowedExtensions ) ) {
					$these = implode( ', ', $this->allowedExtensions );
					return "{error:'File has an invalid extension, it should be one of $these .'}";
				}
				
				if ( ! $replaceOldFile ) {
					/* Don't overwrite previous files that were uploaded */
					while ( file_exists( $uploadDirectory . $filename . '.' . $ext ) ) {
						$filename .= rand( 10, 99 );
					}
				}

				if ( $this->file->save( $uploadDirectory . $filename . '.' . $ext ) ) {
						list( $width, $height, $type, $attr ) = getimagesize( $uploadDirectory . $filename . '.' . $ext );
					return "{success:true,width:" . $width . ",height:" . $height . "}";
				} else {
					return "{error:'Could not save uploaded file. The upload was cancelled, or server error encountered'}";
				}

			}
		}

		/* List of valid extensions, ex. array("jpeg", "xml", "bmp") */
		$allowedExtensions = array( "jpeg", "jpg", "gif", "png" );
		/* Max file size in bytes */
		$sizeLimit = 10 * 1024 * 1024;

		$uploader = new qqFileUploader( $allowedExtensions, $sizeLimit );
		$result = $uploader->handleUpload( plugin_dir_path( __FILE__ ) . 'upload/files/' );
		/* To pass data through iframe you will need to encode all html tags */
		echo $result;
		die(); /* This is required to return a proper result */
	}
}

if ( ! function_exists ( 'gllr_update_image' ) ) {
	function gllr_update_image(){
		global $wpdb;
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' );

		$action	=	isset( $_REQUEST['action1'] ) ? $_REQUEST['action1'] : "";
		$id		=	isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : "";
		switch ( $action ) {
			case 'get_all_attachment':
				$result_parent_id	=	$wpdb->get_results( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type = %s", 'gallery' ) , ARRAY_N );
				$array_parent_id	=	array();
				
				while ( list( $key, $val ) = each( $result_parent_id ) )
					$array_parent_id[] = $val[0];

				$string_parent_id = implode( ",", $array_parent_id );
				
				$result_attachment_id = $wpdb->get_results( "SELECT `ID` FROM " . $wpdb->posts . " WHERE `post_type` = 'attachment' AND `post_mime_type` LIKE 'image%' AND `post_parent` IN (" . $string_parent_id . ")" );
				echo json_encode( $result_attachment_id );
				break;
			case 'update_image':
				$metadata	=	wp_get_attachment_metadata( $id );
				$uploads	=	wp_upload_dir();
				$path		=	$uploads['basedir'] . "/" . $metadata['file'];
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$metadata_new = gllr_wp_generate_attachment_metadata( $id, $path, $metadata );
				wp_update_attachment_metadata( $id, array_merge( $metadata, $metadata_new ) );
				break;
			case 'update_options':
				add_option( 'gllr_images_update', '1', '', 'no' );
				break;
		}
		die();
	}
}

if ( ! function_exists( 'gllr_sanitize_file_name' ) ) {
	function gllr_sanitize_file_name() {
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' );
		if ( 
			isset( $_REQUEST['action'] ) && 'gllr_sanitize_file_name' == $_REQUEST['action'] &&
			isset( $_REQUEST['gllr_name'] ) && ( ! empty( $_REQUEST['gllr_name'] ) )
			) 
			echo sanitize_file_name( $_REQUEST['gllr_name'] );
		else 
			echo '';
		die();
	}
}

if ( ! function_exists ( 'gllr_wp_generate_attachment_metadata' ) ) {
	function gllr_wp_generate_attachment_metadata( $attachment_id, $file, $metadata ) {
		$attachment		=	get_post( $attachment_id );
		$gllr_options	=	get_option( 'gllr_options' );

		add_image_size( 'album-thumb', $gllr_options['gllr_custom_size_px'][0][0], $gllr_options['gllr_custom_size_px'][0][1], true );
		add_image_size( 'photo-thumb', $gllr_options['gllr_custom_size_px'][1][0], $gllr_options['gllr_custom_size_px'][1][1], true );

		$metadata = array();
		if ( preg_match( '!^image/!', get_post_mime_type( $attachment ) ) && file_is_displayable_image( $file ) ) {
			$imagesize	= getimagesize( $file );
			$metadata['width']	=	$imagesize[0];
			$metadata['height']	=	$imagesize[1];
			list( $uwidth, $uheight )	=	wp_constrain_dimensions( $metadata['width'], $metadata['height'], 128, 96 );
			$metadata['hwstring_small']	=	"height='$uheight' width='$uwidth'";

			/* Make the file path relative to the upload dir */
			$metadata['file'] = _wp_relative_upload_path( $file );

			/* Make thumbnails and other intermediate sizes */
			global $_wp_additional_image_sizes;
			
			$image_size = array( 'album-thumb', 'photo-thumb', 'thumbnail' );
			/*get_intermediate_image_sizes();*/
			
			foreach ( $image_size as $s ) {
				$sizes[ $s ] = array( 'width' => '', 'height' => '', 'crop' => FALSE );
				if ( isset( $_wp_additional_image_sizes[ $s ]['width'] ) )
					$sizes[ $s ]['width'] = intval( $_wp_additional_image_sizes[ $s ]['width'] ); /* For theme-added sizes */
				else
					$sizes[ $s ]['width'] = get_option( "{$s}_size_w" ); /* For default sizes set in options */
				if ( isset( $_wp_additional_image_sizes[ $s ]['height'] ) )
					$sizes[ $s ]['height'] = intval( $_wp_additional_image_sizes[ $s ]['height'] ); /* For theme-added sizes */
				else
					$sizes[ $s ]['height'] = get_option( "{$s}_size_h" ); /* For default sizes set in options */
				if ( isset( $_wp_additional_image_sizes[ $s ]['crop'] ) )
					$sizes[ $s ]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); /* For theme-added sizes */
				else
					$sizes[ $s ]['crop'] = get_option( "{$s}_crop" ); /* For default sizes set in options */
			}
			$sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes );
			foreach ( $sizes as $size => $size_data ) {
				$resized = gllr_image_make_intermediate_size( $file, $size_data['width'], $size_data['height'], $size_data['crop'] );
				if ( $resized )
					$metadata['sizes'][$size] = $resized;
			}
			/* Fetch additional metadata from exif/iptc */
			$image_meta = wp_read_image_metadata( $file );
			if ( $image_meta )
				$metadata['image_meta'] = $image_meta;
		}
		return apply_filters( 'wp_generate_attachment_metadata', $metadata, $attachment_id );
	}
}

if ( ! function_exists ( 'gllr_image_make_intermediate_size' ) ) {
	function gllr_image_make_intermediate_size( $file, $width, $height, $crop=false ) {
		if ( $width || $height ) {
			$resized_file = gllr_image_resize( $file, $width, $height, $crop );
			if ( ! is_wp_error( $resized_file ) && $resized_file && $info = getimagesize( $resized_file ) ) {
				$resized_file = apply_filters( 'image_make_intermediate_size', $resized_file );
				return array(
					'file'		=>	wp_basename( $resized_file ),
					'width'		=>	$info[0],
					'height'	=>	$info[1],
				);
			}
		}
		return false;
	}
}

if ( ! function_exists ( 'gllr_image_resize' ) ) {
	function gllr_image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {
		$size = @getimagesize( $file );
		if ( ! $size )
			return new WP_Error( 'invalid_image', __( 'Image size not defined' ), $file );

		$type = $size[2];

		if ( 3 == $type )
			$image = imagecreatefrompng( $file );
		else if ( 2 == $type )
			$image = imagecreatefromjpeg( $file );
		else if ( 1 == $type )
			$image = imagecreatefromgif( $file );
		else if ( 15 == $type )
			$image = imagecreatefromwbmp( $file );
		else if ( 16 == $type )
			$image = imagecreatefromxbm( $file );
		else
			return new WP_Error( 'invalid_image', __( 'We can update only PNG, JPEG, GIF, WPMP or XBM filetype. For other, please, manually reload image.' ), $file );

		if ( ! is_resource( $image ) )
			return new WP_Error( 'error_loading_image', $image, $file );

		/*$size = @getimagesize( $file );*/
		list( $orig_w, $orig_h, $orig_type ) = $size;

		$dims = gllr_image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);

		if ( ! $dims )
			return new WP_Error( 'error_getting_dimensions', __( 'Image size changes not defined' ) );
		list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;

		$newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );

		imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );

		/* Convert from full colors to index colors, like original PNG. */
		if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imageistruecolor' ) && !imageistruecolor( $image ) )
			imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

		/* We don't need the original in memory anymore */
		imagedestroy( $image );

		/* $suffix will be appended to the destination filename, just before the extension */
		if ( ! $suffix )
			$suffix = "{$dst_w}x{$dst_h}";

		$info	=	pathinfo($file);
		$dir	=	$info['dirname'];
		$ext	=	$info['extension'];
		$name	=	wp_basename( $file, ".$ext" );

		if ( ! is_null( $dest_path ) and $_dest_path = realpath( $dest_path ) )
			$dir = $_dest_path;
		$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

		if ( IMAGETYPE_GIF == $orig_type ) {
			if ( !imagegif( $newimage, $destfilename ) )
				return new WP_Error( 'resize_path_invalid', __( 'Invalid path' ) );
		} elseif ( IMAGETYPE_PNG == $orig_type ) {
			if ( !imagepng( $newimage, $destfilename ) )
				return new WP_Error( 'resize_path_invalid', __( 'Invalid path' ) );
		} else {
			/* All other formats are converted to jpg */
			$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
			if ( !imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) ) )
				return new WP_Error( 'resize_path_invalid', __( 'Invalid path' ) );
		}
		imagedestroy( $newimage );

		/* Set correct file permissions */
		$stat = stat( dirname( $destfilename ));
		$perms = $stat['mode'] & 0000666; /* Same permissions as parent folder, strip off the executable bits */
		@chmod( $destfilename, $perms );
		return $destfilename;
	}
}

if ( ! function_exists ( 'gllr_image_resize_dimensions' ) ) {
	function gllr_image_resize_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $crop = false ) {
		if ( 0 >= $orig_w || 0 >= $orig_h )
			return false;
		/* At least one of dest_w or dest_h must be specific */
		if ( 0 >= $dest_w && 0 >= $dest_h )
			return false;

		if ( $crop ) {
			/* Crop the largest possible portion of the original image that we can size to $dest_w x $dest_h */
			$aspect_ratio = $orig_w / $orig_h;
			$new_w = min( $dest_w, $orig_w );
			$new_h = min( $dest_h, $orig_h );

			if ( ! $new_w )
				$new_w = intval( $new_h * $aspect_ratio );

			if ( ! $new_h )
				$new_h = intval( $new_w / $aspect_ratio );

			$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

			$crop_w	=	round( $new_w / $size_ratio );
			$crop_h	=	round( $new_h / $size_ratio );
			$s_x	=	floor( ( $orig_w - $crop_w ) / 2 );
			$s_y	=	0;

		} else {
			/* Don't crop, just resize using $dest_w x $dest_h as a maximum bounding box */
			$crop_w	=	$orig_w;
			$crop_h	=	$orig_h;
			$s_x	=	$s_y	=	0;

			list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
		}
		/* If the resulting image would be the same size or larger we don't want to resize it */
		if ( $new_w >= $orig_w && $new_h >= $orig_h )
			return false;
		/* The return array matches the parameters to imagecopyresampled() */
		/* Int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h */
		return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
	}
}

if ( ! function_exists( 'gllr_filter_sanitize_file_name' ) ) {
	function gllr_filter_sanitize_file_name( $file_name ) {
		if ( isset( $_REQUEST['gllr_ajax_nonce_field'] ) ) {
			$image_ext = array( "jpeg", "jpg", "gif", "png", "JPEG", "JPG", "GIF", "PNG" );
			$info      = pathinfo( $file_name );
			if ( isset( $info['extension'] ) && in_array( $info['extension'], $image_ext ) ) {
				$new_file_name = preg_replace( '/--+/', '-', preg_replace( '/[^a-zA-Z0-9_-]/', '', $info['filename'] ) );
				if ( $new_file_name == '' || $new_file_name == '-' )
					$new_file_name = 'galery' . '-' . time();
				$file_name = trim( $new_file_name, '-' ) . '.' . $info['extension'];
			}
		}
		return $file_name;
	}
}

if ( ! function_exists ( 'gllr_theme_body_classes' ) ) {
	function gllr_theme_body_classes( $classes ) {
		if ( function_exists( 'wp_get_theme' ) ) {
			$current_theme = wp_get_theme();
			$classes[] = 'gllr_' . basename( $current_theme->get( 'ThemeURI' ) );
		}
		return $classes;
	}
}

if ( ! function_exists ( 'gllr_plugin_banner' ) ) {
	function gllr_plugin_banner() {
		global $hook_suffix, $gllr_options;
		if ( 'plugins.php' == $hook_suffix ) {
			global $gllr_plugin_info;
			bws_plugin_banner( $gllr_plugin_info, 'gllr', 'gallery', '01a04166048e9416955ce1cbe9d5ca16', '79', '//ps.w.org/gallery-plugin/assets/icon-128x128.png' );
		}
		require_once( plugin_dir_path( __FILE__ ) . 'inc/demo-data/demo-data-loader.php' );
		do_action( 'bws_display_demo_notice', $gllr_options['display_demo_notice'] );
	}
}

/**
 * Perform at uninstall
 */
if ( ! function_exists( 'gllr_plugin_uninstall' ) ) {
	function gllr_plugin_uninstall() {
		global $gllr_filenames, $gllr_themepath;

		foreach ( $gllr_filenames as $filename ) {
			if ( file_exists( $gllr_themepath . $filename ) && ! unlink( $gllr_themepath . $filename ) ) {
				add_action( 'admin_notices', create_function( '', ' return "Error delete template file";' ) );
			}
		}
		delete_option( 'gllr_options' );
		delete_option( 'gllr_demo_options' );
	}
}

/* Activate plugin */
register_activation_hook( __FILE__, 'gllr_plugin_activate' );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'gllr_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'gllr_register_plugin_links', 10, 2 );

add_action( 'admin_menu', 'add_gllr_admin_menu' );

add_action( 'init', 'gllr_init' );
add_action( 'admin_init', 'gllr_admin_init' );

add_action( 'after_switch_theme', 'gllr_after_switch_theme', 10, 2 );

add_filter( 'rewrite_rules_array', 'gllr_custom_permalinks' ); /* Add custom permalink for gallery */

/* Add themplate for single gallery page */
add_action( 'template_redirect', 'gllr_template_redirect' );
/* Save custom data from admin  */
add_action( 'save_post', 'gllr_save_postdata', 1, 2 );
add_filter( 'content_save_pre', 'gllr_content_save_pre', 10, 1 );

add_filter( 'nav_menu_css_class', 'gllr_addImageAncestorToMenu' );
add_filter( 'page_css_class', 'gllr_page_css_class', 10, 2 );

add_filter( 'manage_gallery_posts_columns', 'gllr_change_columns' );
add_action( 'manage_gallery_posts_custom_column', 'gllr_custom_columns', 10, 2 );

add_action( 'admin_head', 'gllr_add_admin_script' );
add_action( 'admin_enqueue_scripts', 'gllr_admin_head' );
add_action( 'wp_enqueue_scripts', 'gllr_wp_head' );
add_action( 'wp_head', 'gllr_add_wp_head' );

/* add theme name as class to body tag */
add_filter( 'body_class', 'gllr_theme_body_classes' );

add_shortcode( 'print_gllr', 'gllr_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

add_action( 'wp_ajax_upload_gallery_image', 'upload_gallery_image' );
add_action( 'wp_ajax_gllr_update_image', 'gllr_update_image' );
add_action( 'wp_ajax_gllr_sanitize_file_name', 'gllr_sanitize_file_name' );
add_filter( 'sanitize_file_name', 'gllr_filter_sanitize_file_name' );

add_action( 'admin_notices', 'gllr_plugin_banner' );
/* Delete plugin */
register_uninstall_hook( __FILE__, 'gllr_plugin_uninstall' );
?>