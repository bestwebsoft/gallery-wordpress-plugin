<?php
/*
Plugin Name: Gallery by BestWebSoft
Plugin URI:  http://bestwebsoft.com/products/
Description: This plugin allows you to implement gallery page into web site.
Author: BestWebSoft
Text Domain: gallery-plugin
Domain Path: /languages
Version: 4.3.8
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
		global $submenu;
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		
		$settings = add_submenu_page( 'bws_plugins', 'Gallery', 'Gallery', 'manage_options', 'gallery-plugin.php', 'gllr_settings_page' );	

		$url = admin_url( 'admin.php?page=gallery-plugin.php' );
		$submenu['edit.php?post_type=gallery'][] = array( __( 'Settings', 'gallery-plugin' ), 'manage_options', $url );

		add_action( 'load-' . $settings, 'gllr_add_tabs' );
		add_action( 'load-post.php', 'gllr_add_tabs' );
		add_action( 'load-edit.php', 'gllr_add_tabs' );
	}
}

if ( ! function_exists( 'gllr_plugins_loaded' ) ) {
	function gllr_plugins_loaded() {
		/* Internationalization, first(!)  */
		load_plugin_textdomain( 'gallery-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'gllr_init' ) ) {
	function gllr_init() {
		global $gllr_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( ! $gllr_plugin_info ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$gllr_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $gllr_plugin_info, '3.8', '3.5' );

		/* Register post type */
		gllr_post_type_images();
	}
}

if ( ! function_exists ( 'gllr_admin_init' ) ) {
	function gllr_admin_init() {
		global $bws_plugin_info, $gllr_plugin_info, $bws_shortcode_list;
		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '79', 'version' => $gllr_plugin_info["Version"] );
		}
		/* Call register settings function */
		gllr_settings();
		/* add gallery to global $bws_shortcode_list  */
		$bws_shortcode_list['gllr'] = array( 'name' => 'Gallery', 'js_function' => 'gllr_shortcode_init' );
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
			'order_by'									=>	'meta_value_num',
			'order'										=>	'ASC',
			'album_order_by'							=>	'date',
			'album_order'								=>	'DESC',
			'read_more_link_text'						=>	__( 'See images &raquo;', 'gallery-plugin' ),
			'image_text'								=>	0,
			'return_link'								=>	0,
			'return_link_text'							=>	__( 'Return to all albums', 'gallery-plugin' ),
			'return_link_page'							=>	'gallery_template_url',
			'return_link_url'							=>	'',
			'return_link_shortcode'						=>	0,
			'rewrite_template'							=>	1,
			'display_demo_notice'						=>	1,
			'display_settings_notice'					=>	1,
			'first_install'								=>	strtotime( "now" ),
			'template_update'							=>  ''
		);

		/* Install the option defaults */
		if ( ! get_option( 'gllr_options' ) )
			add_option( 'gllr_options', $gllr_option_defaults );

		/* Get options from the database */
		$gllr_options = get_option( 'gllr_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $gllr_options['plugin_option_version'] ) || $gllr_options['plugin_option_version'] != $gllr_plugin_info["Version"] ) {
			if ( isset( $gllr_options['plugin_option_version'] ) && $gllr_options['plugin_option_version'] < '4.3.6' ) {
				if ( 'menu_order' == $gllr_options['order_by'] )
					$gllr_options['order_by'] = 'meta_value_num';
				gllr_plugin_upgrade();

				$gllr_options['template_update'] = 0;
			}

			$gllr_option_defaults['display_demo_notice'] = 0;
			$gllr_option_defaults['display_settings_notice'] = 0;
			$gllr_options = array_merge( $gllr_option_defaults, $gllr_options );
			$gllr_options['plugin_option_version'] = $gllr_plugin_info["Version"];
			/* show pro features */
			$gllr_options['hide_premium_options'] = array();

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
			} elseif ( 'gallery-single-template.php' == $filename && file_exists( $gllr_themepath . $filename ) && isset( $gllr_options['template_update'] ) && $gllr_options['template_update'] == 0 ) {
				/* replace get_posts query for new functionality */ 
				$handle		=	@fopen( $gllr_themepath . $filename, "r" );
				$contents	=	@fread( $handle, filesize( $gllr_themepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename . '.bak', 'w' ) ) )
					return false;
				@fwrite( $handle, $contents );
				@fclose( $handle );
				
				$handle		=	@fopen( $gllr_filepath . $filename, "r" );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename, 'w' ) ) )
					return false;

				$contents = str_replace( '$posts = get_posts( array(',  '$images_id = get_post_meta( $post->ID, "_gallery_images", true );' . "\n" . '$posts = get_posts( array(', $contents );
				$contents = str_replace( '"post_parent"		=> $post->ID',  '"post__in"			=> explode( \',\', $images_id ),' . "\n" . '"meta_key"			=> "_gallery_order_" . $post->ID', $contents );

				@fwrite( $handle, $contents );
				@fclose( $handle );
				@chmod( $gllr_themepath . $filename, octdec( 755 ) );

				$template_update_complete = true;
			} elseif ( 'gallery-template.php' == $filename && file_exists( $gllr_themepath . $filename ) && isset( $gllr_options['template_update'] ) && $gllr_options['template_update'] == 0 ) {
				/* replace get_posts query for new functionality */ 
				$handle		=	@fopen( $gllr_themepath . $filename, "r" );
				$contents	=	@fread( $handle, filesize( $gllr_themepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename . '.bak', 'w' ) ) )
					return false;
				@fwrite( $handle, $contents );
				@fclose( $handle );
				
				$handle		=	@fopen( $gllr_filepath . $filename, "r" );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $gllr_themepath . $filename, 'w' ) ) )
					return false;

				$contents = str_replace( '$image_attributes = wp_get_attachment_image_src( $id, ' . "'album-thumb' );",
								'$images_id = get_post_meta( $post->ID, "_gallery_images", true );
								$attachments = get_posts( array(								
									"showposts"			=>	1,
									"what_to_show"		=>	"posts",
									"post_status"		=>	"inherit",
									"post_type"			=>	"attachment",
									"orderby"			=>	$gllr_options["order_by"],
									"order"				=>	$gllr_options["order"],
									"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
									"post__in"			=> explode( ",", $images_id ),
									"meta_key"			=> "_gallery_order_" . $post->ID
								));
								if ( ! empty( $attachments[0] ) ) {
									$first_attachment = $attachments[0];
									$image_attributes = wp_get_attachment_image_src( $first_attachment->ID, "album-thumb" );
								} else
									$image_attributes = array( "" );',
							$contents );

				@fwrite( $handle, $contents );
				@fclose( $handle );
				@chmod( $gllr_themepath . $filename, octdec( 755 ) );
				
				$template_update_complete = true;
			}
		}
		if ( isset( $template_update_complete ) ) {
			$gllr_options['template_update'] = 1;
			update_option( 'gllr_options', $gllr_options );
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

/**
 * Function for update all gallery images to new version ( Stable tag: 4.3.6 )
 */
if ( ! function_exists( 'gllr_plugin_upgrade' ) ) {
	function gllr_plugin_upgrade() {
		global $wpdb;

		$all_gallery_attachments = $wpdb->get_results( "SELECT p1.ID, p1.post_parent, p1.menu_order
			FROM {$wpdb->posts} p1, {$wpdb->posts} p2
			WHERE p1.post_parent = p2.ID 
			AND p1.post_mime_type LIKE 'image%'
			AND p1.post_type = 'attachment'
			AND p1.post_status = 'inherit'
			AND p2.post_type = 'gallery'",
			ARRAY_A
		);
		if ( ! empty( $all_gallery_attachments ) ) {
			$attachments_array = array();
			foreach ( $all_gallery_attachments as $key => $value ) {
				$post = $value['post_parent'];
				$attachment = $value['ID'];
				$order = $value['menu_order'];
				if ( ! isset( $attachments_array[ $post ] ) || ( isset( $attachments_array[ $post ] ) && ! in_array( $attachment, $attachments_array[ $post ] ) ) ) {
					$attachments_array[ $post ][] = $attachment;
					update_post_meta( $attachment, '_gallery_order_' . $post, $order );
				}
			}
			foreach ( $attachments_array as $key => $value ) {
				update_post_meta( $key, '_gallery_images', implode( ',', $value ) );
			}
			/* set gallery category for demo data */
			if ( function_exists( 'gllrctgrs_add_default_term_all_gallery' ) )
				gllrctgrs_add_default_term_all_gallery();
		}		
	}
}

if ( ! function_exists( 'gllr_admin_error' ) ) {
	function gllr_admin_error() {
		global $gllr_filenames, $gllr_filepath, $gllr_themepath;

		$post		=	isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : "" ;
		$post_type	=	isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : get_post_type( $post );

		if ( 'gallery' == $post_type || ( isset( $_REQUEST['page'] ) && 'gallery-plugin.php' == $_REQUEST['page'] ) ) {
			$file_exists_flag = true;
			foreach ( $gllr_filenames as $filename ) {
				if ( ! file_exists( $gllr_themepath . $filename ) )
					$file_exists_flag = false;
			}
			if ( ! $file_exists_flag ) { ?>
				<div class="error"><p><strong><?php printf( __( "The following files '%s' and '%s' were not found in the directory of your theme. Please copy them from the directory `%s` to the directory of your theme for the correct work of the Gallery plugin", 'gallery-plugin' ), 'gallery-template.php','gallery-single-template.php', '/wp-content/plugins/gallery-plugin/template/' ); ?></strong></p></div>
			<?php }
		}		
	}
}

/* Create post type for Gallery */
if ( ! function_exists( 'gllr_post_type_images' ) ) {
	function gllr_post_type_images() {
		register_post_type( 'gallery', array(
			'labels' => array(
				'name'				=>	__( 'Galleries', 'gallery-plugin' ),
				'singular_name'		=>	__( 'Gallery', 'gallery-plugin' ),
				'add_new_item' 		=>	__( 'Add New Gallery', 'gallery-plugin' ),
				'edit_item' 		=>	__( 'Edit Gallery', 'gallery-plugin' ),
				'new_item' 			=>	__( 'New Gallery', 'gallery-plugin' ),
				'view_item' 		=>	__( 'View Gallery', 'gallery-plugin' ),
				'search_items' 		=>	__( 'Search Galleries', 'gallery-plugin' ),
				'not_found' 		=>	__( 'No Gallery found', 'gallery-plugin' ),
				'parent_item_colon'	=>	'',
				'menu_name' 		=>	__( 'Galleries', 'gallery-plugin' )
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
				$parent_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE `meta_key` = '_wp_page_template' AND `meta_value` = 'gallery-template.php' AND `post_status` = 'publish' AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
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
			
			$menuItems = $wpdb->get_col( "SELECT DISTINCT `post_id` FROM $wpdb->postmeta WHERE `meta_key` = '_menu_item_object_id' AND `meta_value` IN (" . implode( ',', $post_ancestors ) . ")" );
			
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
		add_meta_box( 'Gallery-Shortcode', __( 'Gallery Shortcode', 'gallery-plugin' ), 'gllr_post_shortcode_box', 'gallery', 'side', 'high' );
		if ( ! ( is_plugin_active( 'gallery-categories/gallery-categories.php' ) || is_plugin_active( 'gallery-categories-pro/gallery-categories-pro.php' ) ) ) {
			add_meta_box( 'Gallery-Categories', __( 'Gallery Categories', 'gallery-plugin' ), 'gllr_gallery_categories', 'gallery', 'side', 'core' );
		}
	}
}

/* Create shortcode meta box for gallery post type */
if ( ! function_exists( 'gllr_post_shortcode_box' ) ) {
	function gllr_post_shortcode_box( $obj = '', $box = '' ) {
		global $post, $wp_version; ?>
		<div><?php printf( 
			__( "If you would like to add a Gallery to your page or post, please use %s button", 'gallery-plugin' ), 
			'<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ); ?> 
			<div class="bws_help_box bws_help_box_right<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
				<div class="bws_hidden_help_text" style="min-width: 180px;">
					<?php printf( 
						__( "You can add the Gallery to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode below", 'gallery-plugin' ), 
						'<code><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt="" /></code>'
					); ?>
				</div>
			</div>
		</div>
		<p><?php _e( 'Add this shortcode to a page, post or widget to display a single gallery', 'gallery-plugin' ); ?>:</p>
		<p><span class="bws_code">[print_gllr id=<?php echo $post->ID; ?>]</span></p>
		<div>
			<?php _e( 'Use this shortcode to display an album image with the description and the link to a single gallery page', 'gallery-plugin' ); ?>:
			<div class="bws_help_box bws_help_box_right<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
				<div class="bws_hidden_help_text"><img style="border: 1px solid #ccc;" src="<?php echo plugins_url( 'gallery-plugin/images/gallery_short_view.png' ); ?>" title="<?php _e( 'Short display', 'gallery-plugin' ); ?>" alt=""/></div>
			</div>
		</div>
		<p><span class="bws_code">[print_gllr id=<?php echo $post->ID; ?> display=short]</span></p>
	<?php }
}

/* Metabox-ad for plugin Gallery categories */
if ( ! function_exists( 'gllr_gallery_categories' ) ) {
	function gllr_gallery_categories() { ?>
		<div id="gallery_categoriesdiv" class="postbox gllr_ad_block" style="min-width: auto; margin-bottom: 0;">
			<div class="handlediv" title="Click to toggle"><br></div>
			<div class="inside">
				<div id="taxonomy-gallery_categories" class="categorydiv">
					<ul id="gallery_categories-tabs" class="category-tabs">
						<li class="tabs"><?php _e( 'Gallery Categories', 'gallery-plugin' ); ?></li>
						<li class="hide-if-no-js" style="color:#0074A2;"><?php _e( 'Most Used', 'gallery-plugin' ); ?></li>
					</ul>
					<div id="gallery_categories-all" class="tabs-panel">
						<ul id="gallery_categorieschecklist" data-wp-lists="list:gallery_categories" class="categorychecklist form-no-clear">
							<li id="gallery_categories-2" class="popular-category">
								<label class="selectit"><input value="2" type="checkbox" disabled="disabled" name="tax_input[gallery_categories][]" id="in-gallery_categories-2" checked="checked"><?php _e( 'Default', 'gallery-plugin' ); ?></label>
							</li>
						</ul>
					</div>
					<div id="gallery_categories-adder" class="wp-hidden-children">
						<h4><a id="gallery_categories-add-toggle" href="#" class="hide-if-no-js">+ <?php _e( 'Add New Gallery Category', 'gallery-plugin' ); ?></a></h4>
					</div>
				</div>
			</div>
		</div>
		<div id="gllr_show_gallery_categories_notice"><?php _e( 'Install plugin', 'gallery-plugin'); ?> <a href="http://bestwebsoft.com/products/gallery-categories/">Gallery Categories</a></div>
	<?php }
}

if ( ! function_exists ( 'gllr_save_postdata' ) ) {
	function gllr_save_postdata( $post_id, $post ) {
		global $post, $wpdb;
		$key			=	"gllr_image_text";
		$link_key		=	"gllr_link_url";
		$alt_tag_key	=	"gllr_image_alt_tag";

		if ( isset( $post ) && isset( $_POST['_gallery_order_' . $post->ID ] ) ) {
			$i = 1;
			foreach ( $_POST['_gallery_order_' . $post->ID ] as $post_order_id => $order_id ) {
				update_post_meta( $post_order_id, '_gallery_order_' . $post->ID, $i );
				$i++;
			}	
			update_post_meta( $post->ID, '_gallery_images', implode( ',', array_keys( $_POST['_gallery_order_' . $post->ID ] ) ) );
		}
		/*
		if ( isset( $_REQUEST['delete_images'] ) ) {
			foreach ( $_REQUEST['delete_images'] as $delete_id ) {
				delete_post_meta( $delete_id, $key );
				wp_delete_attachment( $delete_id );
			}
		} */
		if ( ( isset( $_POST['action-top'] ) && $_POST['action-top'] == 'delete' ) ||
			( isset( $_POST['action-bottom'] ) && $_POST['action-bottom'] == 'delete' ) ) {
			$gallery_images = get_post_meta( $post_id, '_gallery_images', true );
			$gallery_images_array = explode( ',', $gallery_images );
			$gallery_images_array = array_flip( $gallery_images_array );
			foreach ( $_POST['media'] as $delete_id ) {
				delete_post_meta( $delete_id, '_gallery_order_' . $post->ID );
				unset( $gallery_images_array[ $delete_id ] );
			}
			$gallery_images_array = array_flip( $gallery_images_array );
			$gallery_images = implode( ',', $gallery_images_array );
			update_post_meta( $post->ID, '_gallery_images', $gallery_images );
		}
		if ( isset( $_REQUEST['gllr_image_text'] ) ) {
			foreach ( $_REQUEST['gllr_image_text'] as $gllr_image_text_key => $gllr_image_text ) {
				$value = htmlspecialchars( trim( $gllr_image_text ) );
				if ( get_post_meta( $gllr_image_text_key, $key, false ) ) {
					/* Custom field has a value and this custom field exists in database */
					update_post_meta( $gllr_image_text_key, $key, $value );
				} elseif ( $value ) {
					/* Custom field has a value, but this custom field does not exist in database */
					add_post_meta( $gllr_image_text_key, $key, $value );
				}
			}
		}
		if ( isset( $_REQUEST['gllr_link_url'] ) ) {
			foreach ( $_REQUEST['gllr_link_url'] as $gllr_link_url_key => $gllr_link_url ) {
				$value = esc_url( trim( $gllr_link_url ) );
				if ( get_post_meta( $gllr_link_url_key, $link_key, FALSE ) ) {
					/* Custom field has a value and this custom field exists in database */
					update_post_meta( $gllr_link_url_key, $link_key, $value );
				} elseif ( $value ) {
					/* Custom field has a value, but this custom field does not exist in database */
					add_post_meta( $gllr_link_url_key, $link_key, $value );
				}
			}
		}
		if ( isset( $_REQUEST['gllr_image_alt_tag'] ) ) {
			foreach ( $_REQUEST['gllr_image_alt_tag'] as $gllr_image_alt_tag_key => $gllr_image_alt_tag ) {
				$value = htmlspecialchars( trim( $gllr_image_alt_tag ) );
				if ( get_post_meta( $gllr_image_alt_tag_key, $alt_tag_key, FALSE ) ) {
					/* Custom field has a value and this custom field exists in database */
					update_post_meta( $gllr_image_alt_tag_key, $alt_tag_key, $value );
				} elseif ( $value ) {
					/* Custom field has a value, but this custom field does not exist in database */
					add_post_meta( $gllr_image_alt_tag_key, $alt_tag_key, $value );
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
			'cb'			=>	'<input type="checkbox" />',
			'title'			=>	__( 'Title', 'gallery-plugin' ),
			'author'		=>	__( 'Author', 'gallery-plugin' ),
			'shortcode'		=>	__( 'Shortcode', 'gallery-plugin' ),
			'photos'		=>	__( 'Photos', 'gallery-plugin' ),
			'date'			=>	__( 'Date', 'gallery-plugin' )
		);
		return $cols;
	}
}

if ( ! function_exists( 'gllr_custom_columns' ) ) {
	function gllr_custom_columns( $column, $post_id ) {
		global $wpdb;
		$post	=	get_post( $post_id );		
		switch ( $column ) {
			case "shortcode":
				echo '[print_gllr id=' . $post->ID . ']<br/>[print_gllr id=' . $post->ID . ' display=short]';
				break;
			case "photos":
				$images_id = get_post_meta( $post->ID, '_gallery_images', true );
				if ( empty( $images_id  ) )
					echo 0;
				else
					echo $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE ID IN( " . $images_id . " )" );
				break;
		}
	}
}

if ( ! function_exists( 'gllr_manage_pre_get_posts' ) ) {
	function gllr_manage_pre_get_posts( $query ) {
		if ( is_admin() && $query->is_main_query() && $query->get( 'post_type' ) == 'gallery' && ! isset( $_GET['order'] ) && ( $orderby = $query->get( 'orderby' ) ) ) {
			global $gllr_options;
			$query->set( 'orderby', $gllr_options['album_order_by'] );
			$query->set( 'order', $gllr_options['album_order'] );
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

			if ( isset( $_POST['bws_hide_premium_options'] ) ) {
				$hide_result = bws_hide_premium_options( $gllr_request_options );
				$gllr_request_options = $hide_result['options'];
			}

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

			$gllr_request_options["order_by"]			=	$_REQUEST['gllr_order_by'];
			$gllr_request_options["order"]				=	$_REQUEST['gllr_order'];
			$gllr_request_options["album_order_by"]		=	$_REQUEST['gllr_album_order_by'];
			$gllr_request_options["album_order"]		=	$_REQUEST['gllr_album_order'];
			
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
			$message = __( "Settings are saved", 'gallery-plugin' );
		}

		$bws_hide_premium_options_check = bws_hide_premium_options_check( $gllr_options );

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'gllr_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		} /* Display form on the setting page */ 

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$gllr_options = $gllr_option_defaults;
			update_option( 'gllr_options', $gllr_options );
			$message = __( 'All plugin settings were restored.', 'gallery-plugin' );
		}

		$result = apply_filters( 'bws_handle_demo_data', 'gllr_plugin_upgrade', 'gllr_settings' );
		if ( ( ! empty( $result ) ) && is_array( $result ) ) { 
			$error   = $result['error'];
			$message = $result['done'];
			if ( ! empty( $result['done'] ) )
				$gllr_options = $result['options'];
		} ?>
		<div class="wrap">
			<h2><?php _e( 'Gallery Settings', 'gallery-plugin' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>"  href="admin.php?page=gallery-plugin.php"><?php _e( 'Settings', 'gallery-plugin' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=gallery-plugin.php&amp;action=go_pro"><?php _e( 'Go PRO', 'gallery-plugin' ); ?></a>
			</h2>
			<div id="gllr_settings_message" class="updated fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo 'style="display:none"'; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php if ( ! isset( $_GET['action'] ) ) { 
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} elseif ( isset( $_POST['bws_handle_demo'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_demo_confirm();
				} else { ?>
					<noscript><div class="error"><p><?php _e( 'Please enable JavaScript to use the option to renew images.', 'gallery-plugin' ); ?></p></div></noscript> 
					<?php bws_show_settings_notice();
					if ( ! empty( $hide_result['message'] ) ) { ?>
						<div class="updated fade"><p><strong><?php echo $hide_result['message']; ?></strong></p></div>
					<?php } ?>
					<br/>
					<div><?php printf( 
						__( "If you would like to add a Gallery to your page or post, please use %s button", 'gallery-plugin' ), 
						'<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ); ?> 
						<div class="bws_help_box bws_help_box_right<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
							<div class="bws_hidden_help_text" style="min-width: 180px;">
								<?php printf( 
									__( "You can add the Gallery to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s, where * stands for gallery ID", 'gallery-plugin' ), 
									'<code><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt="" /></code>',
									'<span class="bws_code">[print_gllr id=*]</span>'
								); ?>
							</div>
						</div>
					</div>
					<table class="form-table hide-if-no-js">
						<tr valign="top">
							<th scope="row"><?php _e( 'Update images for gallery', 'gallery-plugin' ); ?> </th>
							<td style="position:relative">
								<input type="button" value="<?php _e( 'Update images', 'gallery-plugin' ); ?>" id="gllr_ajax_update_images" name="ajax_update_images" class="button" /> <div id="gllr_img_loader"><img src="<?php echo plugins_url( 'images/ajax-loader.gif', __FILE__ ); ?>" alt="loader" /></div>
							</td>
						</tr>
					</table>
					<br/>
					<form class="bws_form" method="post" action="admin.php?page=gallery-plugin.php">
						<table class="gllr_settings_table form-table">
							<tr valign="top" class="gllr_width_labels">
								<th scope="row"><?php _e( 'Image size for the album cover', 'gallery-plugin' ); ?> </th>
								<td>
									<label><?php _e( 'Image size', 'gallery-plugin' ); ?> <?php echo $gllr_options["gllr_custom_size_name"][0]; ?></label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_w_album" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][0][0]; ?>" /> 
										<?php _e( 'Width (in px)', 'gallery-plugin' ); ?>
									</label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_h_album" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][0][1]; ?>" /> 
										<?php _e( 'Height (in px)', 'gallery-plugin' ); ?>
									</label>
								</td>
							</tr>
							<tr valign="top" class="gllr_width_labels">
								<th scope="row"><?php _e( 'Image size for thumbnails', 'gallery-plugin' ); ?></th>
								<td>
									<label><?php _e( 'Image size', 'gallery-plugin' ); ?> <?php echo $gllr_options["gllr_custom_size_name"][1]; ?></label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_w_photo" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][1][0]; ?>" /> 
										<?php _e( 'Width (in px)', 'gallery-plugin' ); ?>
									</label><br />
									<label>
										<input type="number" name="gllr_custom_image_size_h_photo" min="1" max="10000" value="<?php echo $gllr_options["gllr_custom_size_px"][1][1]; ?>" /> 
										<?php _e( 'Height (in px)', 'gallery-plugin' ); ?>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2"><span class="bws_info"><?php _e( 'WordPress will create a new thumbnail with the specified dimensions when you upload a new photo.', 'gallery-plugin' ); ?></span></td>
							</tr>
						</table>
						<?php if ( ! $bws_hide_premium_options_check ) { ?>
							<div class="bws_pro_version_bloc">
								<div class="bws_pro_version_table_bloc">	
									<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
									<div class="bws_table_bg"></div>
									<table class="gllr_settings_table form-table bws_pro_version">
										<tr valign="top" class="gllr_width_labels">
											<th scope="row"><?php _e( 'Image size in the lightbox', 'gallery-plugin' ); ?> </th>
											<td>
												<label><?php _e( 'Image size', 'gallery-plugin' ); ?> full-photo</label><br />
												<label><input disabled class="gllrprfssnl_size_photo_full" type="number" name="gllrprfssnl_custom_image_size_w_full" value="1024" /> <?php _e( 'Max width (in px)', 'gallery-plugin' ); ?></label><br />
												<label><input disabled class="gllrprfssnl_size_photo_full" type="number" name="gllrprfssnl_custom_image_size_h_full" value="1024" /> <?php _e( 'Max height (in px)', 'gallery-plugin' ); ?></label><br />
												<input disabled type="checkbox" name="gllrprfssnl_size_photo_full" value="1" /> <?php _e( 'Display a full size image in the lightbox', 'gallery-plugin' ); ?>
											</td>
										</tr>
										<tr valign="top" class="gllr_width_labels">
											<th scope="row"><?php _e( 'Crop position', 'gallery-plugin' ); ?></th>
											<td>
												<label>
													<select disabled>
														<option value="center"><?php _e( 'center', 'gallery-plugin' ); ?></option>
													</select> 
													<?php _e( 'Horizontal', 'gallery-plugin' ); ?>
												</label><br />
												<label>
													<select disabled>
														<option value="center"><?php _e( 'center', 'gallery-plugin' ); ?></option>
													</select>
													<?php _e( 'Vertical', 'gallery-plugin' ); ?>
												</label>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?php _e( 'Lightbox background', 'gallery-plugin' ); ?> </th>
											<td>
												<input disabled class="button button-small gllrprfssnl_lightbox_default" type="button" value="<?php _e( 'Default', 'gallery-plugin' ); ?>"> <br />
												<input disabled type="text" size="8" value="0.7" name="gllrprfssnl_background_lightbox_opacity" /> <?php _e( 'Background transparency (from 0 to 1)', 'gallery-plugin' ); ?><br />
												<label><input disabled id="gllrprfssnl_background_lightbox_color" type="minicolors" name="gllrprfssnl_background_lightbox_color" value="#777777" id="gllrprfssnl_background_lightbox_color" /> <?php _e( 'Select a background color', 'gallery-plugin' ); ?></label>
											</td>
										</tr>	
										<tr valign="top">
											<th scope="row" colspan="2">
												* <?php _e( 'If you upgrade to Pro version all your settings and galleries will be saved.', 'gallery-plugin' ); ?>
											</th>
										</tr>
									</table>
								</div>
								<div class="bws_pro_version_tooltip">
									<div class="bws_info">
										<?php _e( 'Unlock premium options by upgrading to Pro version', 'gallery-plugin' ); ?> 
									</div>
									<div class="bws_pro_links">
										<span class="bws_trial_info">
											<a href="http://bestwebsoft.com/products/gallery/trial/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Start Your Trial', 'gallery-plugin' ); ?></a>
											 <?php _e( 'or', 'gallery-plugin' ); ?>
										</span>
										<a class="bws_button" href="http://bestwebsoft.com/products/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Learn More', 'gallery-plugin' ); ?></a> 
									</div>
									<div class="gllr_clear"></div>
								</div>
							</div>
						<?php } ?>
						<table class="gllr_settings_table form-table">
							<tr valign="top">
								<th scope="row"><?php _e( 'Images with border', 'gallery-plugin' ); ?></th>
								<td>
									<input type="checkbox" name="gllr_border_images" value="1" <?php if ( 1 == $gllr_options["border_images"] ) echo 'checked="checked"'; ?> /><br />
									<input type="number" min="0" max="10000" value="<?php echo $gllr_options["border_images_width"]; ?>" name="gllr_border_images_width" /> <?php _e( 'Border width in px, just numbers', 'gallery-plugin' ); ?><br />
									<label><input type="minicolors" name="gllr_border_images_color" maxlength="7" value="<?php echo $gllr_options["border_images_color"]; ?>" id="gllr_border_images_color" /> <?php _e( 'Select a border color', 'gallery-plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Number of images in the row', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="number" name="gllr_custom_image_row_count" min="1" max="10000" value="<?php echo $gllr_options["custom_image_row_count"]; ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Start slideshow', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="checkbox" name="gllr_start_slideshow" value="1" <?php if ( 1 == $gllr_options["start_slideshow"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Slideshow interval', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="number" name="gllr_slideshow_interval" min="1" max="1000000" value="<?php echo $gllr_options["slideshow_interval"]; ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Use single lightbox for multiple galleries on one page', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="checkbox" name="gllr_single_lightbox_for_multiple_galleries" value="1" <?php if ( 1 == $gllr_options["single_lightbox_for_multiple_galleries"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Sort images by', 'gallery-plugin' ); ?></th>
								<td>
									<label><input type="radio" name="gllr_order_by" value="ID" <?php if ( 'ID' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Attachment ID', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_order_by" value="title" <?php if ( 'title' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Image Name', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_order_by" value="date" <?php if ( 'date' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_order_by" value="meta_value_num" <?php if ( 'meta_value_num' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Sorting order in the Gallery', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_order_by" value="rand" <?php if ( 'rand' == $gllr_options["order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Random', 'gallery-plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Sort images', 'gallery-plugin' ); ?></th>
								<td>
									<label><input type="radio" name="gllr_order" value="ASC" <?php if ( 'ASC' == $gllr_options["order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC (ascending order from lowest to highest values - 1, 2, 3; a, b, c)', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_order" value="DESC" <?php if ( 'DESC' == $gllr_options["order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC (descending order from highest to lowest values - 3, 2, 1; c, b, a)', 'gallery-plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Sort galleries by', 'gallery-plugin' ); ?></th>
								<td>
									<label><input type="radio" name="gllr_album_order_by" value="ID" <?php if ( 'ID' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Gallery ID', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="title" <?php if ( 'title' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Title', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="date" <?php if ( 'date' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="modified" <?php if ( 'modified' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Last modified date', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="comment_count" <?php if ( 'comment_count' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Comment count', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="menu_order" <?php if ( 'menu_order' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Sorting order (the input field for sorting order)', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="author" <?php if ( 'author' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Author', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order_by" value="rand" <?php if ( 'rand' == $gllr_options["album_order_by"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Random', 'gallery-plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Sort galleries', 'gallery-plugin' ); ?></th>
								<td>
									<label><input type="radio" name="gllr_album_order" value="ASC" <?php if ( 'ASC' == $gllr_options["album_order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC (ascending order from lowest to highest values - 1, 2, 3; a, b, c)', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" name="gllr_album_order" value="DESC" <?php if ( 'DESC' == $gllr_options["album_order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC (descending order from highest to lowest values - 3, 2, 1; c, b, a)', 'gallery-plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Display text under the image', 'gallery-plugin' ); ?></th>
								<td>
									<label><input type="checkbox" name="gllr_image_text" value="1" <?php if ( 1 == $gllr_options["image_text"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Turn off the checkbox, if you want to display text just in a lightbox', 'gallery-plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Display the Back link', 'gallery-plugin' ); ?></th>
								<td>
									<input type="checkbox" name="gllr_return_link" value="1" <?php if ( 1 == $gllr_options["return_link"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Display the Back link in the shortcode', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="checkbox" name="gllr_return_link_shortcode" value="1" <?php if ( 1 == $gllr_options["return_link_shortcode"] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'The Back link text', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="text" name="gllr_return_link_text" maxlength="250" value="<?php echo $gllr_options["return_link_text"]; ?>" style="width:200px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'The Back link URL', 'gallery-plugin' ); ?></th>
								<td>
									<label><input type="radio" value="gallery_template_url" name="gllr_return_link_page" <?php if ( 'gallery_template_url' == $gllr_options["return_link_page"] ) echo 'checked="checked"'; ?> /><?php _e( 'Gallery page (Page with Gallery Template)', 'gallery-plugin' ); ?></label><br />
									<label><input type="radio" maxlength="250" value="custom_url" name="gllr_return_link_page" id="gllr_return_link_url" <?php if ( 'custom_url' == $gllr_options["return_link_page"] ) echo 'checked="checked"'; ?> /> <input type="text" onfocus="document.getElementById('gllr_return_link_url').checked = true;" value="<?php echo $gllr_options["return_link_url"]; ?>" name="gllr_return_link_url" />
									<?php _e( '(Full URL to custom page)' , 'gallery-plugin'); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'The Read More link text', 'gallery-plugin' ); ?></th>
								<td>
									<input type="text" name="gllr_read_more_link_text" maxlength="250" value="<?php echo $gllr_options["read_more_link_text"]; ?>" style="width:200px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Add gallery to the search', 'gallery-plugin' ); ?></th>
								<td>
									<?php if ( array_key_exists( 'custom-search-plugin/custom-search-plugin.php', $all_plugins ) || array_key_exists( 'custom-search-pro/custom-search-pro.php', $all_plugins ) ) {
										if ( is_plugin_active( 'custom-search-plugin/custom-search-plugin.php' ) || is_plugin_active( 'custom-search-pro/custom-search-pro.php' ) ) { ?>
											<input type="checkbox" name="gllr_add_to_search" value="1" <?php if ( in_array( 'gallery', $cstmsrch_options['post_types'] ) ) echo 'checked="checked"'; ?> />
											<span class="bws_info"> (<?php _e( 'Using', 'gallery-plugin' ); ?> Custom Search <?php _e( 'powered by', 'gallery-plugin' ); ?> <a href="http://bestwebsoft.com/products/">bestwebsoft.com</a>)</span>
										<?php } else { ?>
											<input disabled="disabled" type="checkbox" name="gllr_add_to_search" value="1" <?php if ( in_array( 'gallery', $cstmsrch_options['post_types'] ) ) echo 'checked="checked"'; ?> /> 
											<span class="bws_info">(<?php _e( 'Using', 'gallery-plugin' ); ?> Custom Search <?php _e( 'powered by', 'gallery-plugin' ); ?> <a href="http://bestwebsoft.com/products/">bestwebsoft.com</a>) <a href="<?php echo bloginfo("url"); ?>/wp-admin/plugins.php"><?php _e( 'Activate', 'gallery-plugin' ); ?> Custom Search</a></span>
										<?php }
									} else { ?>
										<input disabled="disabled" type="checkbox" name="gllr_add_to_search" value="1" />  
										<span class="bws_info">(<?php _e( 'Using', 'gallery-plugin' ); ?> Custom Search <?php _e( 'powered by', 'gallery-plugin' ); ?> <a href="http://bestwebsoft.com/products/">bestwebsoft.com</a>) <a href="http://bestwebsoft.com/products/custom-search/"><?php _e( 'Download', 'gallery-plugin' ); ?> Custom Search</a></span>
									<?php } ?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Rewrite templates after update', 'gallery-plugin' ); ?></th>
								<td>
									<input type="checkbox" name="gllr_rewrite_template" value="1" <?php if ( 1 ==  $gllr_options['rewrite_template'] ) echo 'checked="checked"'; ?> /> <span class="bws_info"><?php printf( __( "Turn off the checkbox, if You edited the file '%s' or '%s' file in your theme folder and You don't want to rewrite them", 'gallery-plugin' ), 'gallery-template.php', 'gallery-single-template.php' ); ?></span>
								</td>
							</tr>
						</table>
						<?php if ( ! $bws_hide_premium_options_check ) { ?>
							<div class="bws_pro_version_bloc">
								<div class="bws_pro_version_table_bloc">
									<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
									<div class="bws_table_bg"></div>
									<table class="gllr_settings_table form-table bws_pro_version">
										<tr valign="top" class="gllr_width_labels">
											<th scope="row"><?php _e( 'Use pagination for images', 'gallery-plugin' ); ?></th>
											<td>
												<input disabled type="checkbox" name="gllrprfssnl_images_pagination" value="1" /><br />
												<label><input disabled type="number" name="gllrprfssnl_images_per_page" value="" /> <?php _e( 'per page', 'gallery-plugin' ); ?></label>
											</td>
										</tr>
										<tr valign="top" class="gllr_width_labels">
											<th scope="row"><?php _e( 'The lightbox helper', 'gallery-plugin' ); ?></th>
											<td>
												<label><input disabled type="radio" name="gllrprfssnl_fancybox_helper" value="none" /> <?php _e( 'Do not use', 'gallery-plugin' ); ?></label><br />
												<label><input disabled type="radio" name="gllrprfssnl_fancybox_helper" value="button" /> <?php _e( 'Button helper', 'gallery-plugin' ); ?></label><br />
												<label><input disabled type="radio" name="gllrprfssnl_fancybox_helper" value="thumbnail" /> <?php _e( 'Thumbnail helper', 'gallery-plugin' ); ?></label>
											</td>
										</tr>
										<tr valign="top" class="gllr_width_labels">
											<th scope="row"><?php _e( 'Display Like buttons in the lightbox', 'gallery-plugin' ); ?></th>
											<td>
												<label><input disabled type="checkbox" name="gllrprfssnl_like_button_fb" value="1" /> <?php _e( 'FaceBook', 'gallery-plugin' ); ?></label><br />
												<label><input disabled type="checkbox" name="gllrprfssnl_like_button_twit" value="1" /> <?php _e( 'Twitter', 'gallery-plugin' ); ?></label><br />
												<label><input disabled type="checkbox" name="gllrprfssnl_like_button_pint" value="1" /> <?php _e( 'Pinterest', 'gallery-plugin' ); ?></label><br />
												<label><input disabled type="checkbox" name="gllrprfssnl_like_button_g_plusone" value="1" /> <?php _e( 'Google +1', 'gallery-plugin' ); ?></label>
											</td>
										</tr>
										<tr valign="top" class="gllr_width_labels">
											<th scope="row"><?php _e( 'Slug for gallery item', 'gallery-plugin' ); ?></th>
											<td>
												<input type="text" name="gllrprfssnl_slug" value="gallery" disabled /> <span class="bws_info"><?php _e( 'for any structure of permalinks except the default structure', 'gallery-plugin' ); ?></span>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?php _e( 'Title for lightbox button', 'gallery-plugin' ); ?></th>
											<td>
												<input type="text" name="gllrprfssnl_lightbox_button_text" disabled value="" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?php _e( 'Display all images in the lightbox instead of going into a single gallery', 'gallery-plugin' ); ?> </th>
											<td>
												<input type="checkbox" name="gllrpr_hide_single_gallery" value="1" disabled />
												<span class="bws_info">(<?php printf( __( 'When using the gallery template or a shortcode with `%s` parameter', 'gallery-plugin' ), 'display=short' ); ?>)</span>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row" colspan="2">
												* <?php _e( 'If you upgrade to Pro version all your settings and galleries will be saved.', 'gallery-plugin' ); ?>
											</th>
										</tr>
									</table>
								</div>
								<div class="bws_pro_version_tooltip">
									<div class="bws_info">
										<?php _e( 'Unlock premium options by upgrading to Pro version', 'gallery-plugin' ); ?> 
									</div>
									<div class="bws_pro_links">
										<span class="bws_trial_info">
											<a href="http://bestwebsoft.com/products/gallery/trial/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Start Your Trial', 'gallery-plugin' ); ?></a>
											 <?php _e( 'or', 'gallery-plugin' ); ?>
										</span>
										<a class="bws_button" href="http://bestwebsoft.com/products/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Gallery Pro Plugin"><?php _e( 'Learn More', 'gallery-plugin' ); ?></a>
									</div>
									<div class="gllr_clear"></div>
								</div>
							</div>
						<?php } ?>						
						<p class="submit">
							<input id="bws-submit-button" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'gallery-plugin' ) ?>" /> 
							<input type="hidden" name="gllr_form_submit" value="submit" />
							<?php wp_nonce_field( $plugin_basename, 'gllr_nonce_name' ); ?>
						</p>						
					</form>
					<?php do_action( 'bws_show_demo_button');
					bws_form_restore_default_settings( $plugin_basename );
				}
			} elseif ( 'go_pro' == $_GET['action'] ) {
				bws_go_pro_tab_show( $bws_hide_premium_options_check, $gllr_plugin_info, $plugin_basename, 'gallery-plugin.php', 'gallery-plugin-pro.php', 'gallery-plugin-pro/gallery-plugin-pro.php', 'gallery', '63a36f6bf5de0726ad6a43a165f38fe5', '79', isset( $go_pro_result['pro_plugin_is_activated'] ), '7' );
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
				$links[]	=	'<a href="admin.php?page=gallery-plugin.php">' . __( 'Settings', 'gallery-plugin' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/gallery-plugin/faq/" target="_blank">' . __( 'FAQ', 'gallery-plugin' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'gallery-plugin' ) . '</a>';
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
				$settings_link = '<a href="admin.php?page=gallery-plugin.php">' . __( 'Settings', 'gallery-plugin' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists ( 'gllr_admin_head' ) ) {
	function gllr_admin_head() {
		global $wp_version, $gllr_plugin_info, $post_type, $pagenow;

		wp_enqueue_style( 'gllr_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		if ( isset( $_GET['page'] ) && "gallery-plugin.php" == $_GET['page'] ) {
			wp_enqueue_script( 'gllr_minicolors_js', plugins_url( 'minicolors/jquery.miniColors.js', __FILE__ ) );
			wp_enqueue_style( 'gllr_minicolors_css', plugins_url( 'minicolors/jquery.miniColors.css', __FILE__ ) );

			wp_enqueue_script( 'gllr_script', plugins_url( 'js/script.js', __FILE__ ) );
			wp_localize_script( 'gllr_script', 'gllr_vars',
				array(
					'gllr_nonce'			=> wp_create_nonce( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' ),
					'update_img_message'	=> __( 'Updating images...', 'gallery-plugin' ),
					'not_found_img_info' 	=> __( 'No image found.', 'gallery-plugin' ),
					'img_success' 			=> __( 'All images are updated.', 'gallery-plugin' ),
					'img_error'				=> __( 'Error.', 'gallery-plugin' )
				) 
			);
		} else if ( 
			( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && get_post_type( get_the_ID() ) == 'gallery' ) || 
			( isset( $pagenow ) && $pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'gallery' ) ) {
			wp_enqueue_script( 'gllr_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_localize_script( 'gllr_script', 'gllr_vars',
				array(
					'gllr_nonce'				=> wp_create_nonce( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' ),
					'gllr_add_nonce'			=> wp_create_nonce( plugin_basename( __FILE__ ), 'gllr_ajax_add_nonce' ),
					'warnBulkDelete'			=> __( "You are about to delete these items from this gallery.\n 'Cancel' to stop, 'OK' to delete.", 'gallery-plugin' ),
					'confirm_update_gallery'	=> __( "Switching to another mode, all unsaved data will be lost. Save data before switching?", 'gallery-plugin' ),
					'wp_media_title'			=> __( 'Insert Media', 'gallery-plugin' ),
					'wp_media_button'			=> __( 'Insert', 'gallery-plugin' ),
				) 
			);
		}

		if ( isset( $post_type ) && 'gallery' == $post_type ) {
			if ( ! function_exists( 'bws_add_tooltip_in_admin' ) )
				require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );

			if ( ! function_exists( 'get_plugins' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$all_plugins = get_plugins();
			$learn_more = str_replace( ' ', '&nbsp', __( 'Learn more', 'gallery-plugin' ) );
			/* tooltip for gallery categories */
			if ( isset( $all_plugins['gallery-categories/gallery-categories.php'] ) || isset( $all_plugins['gallery-categories-pro/gallery-categories-pro.php'] ) ) {
				/* if gallery categories is installed */
				$link = "plugins.php";
				$text = __( 'Activate', 'gallery-plugin' );
			} else {
				if ( function_exists( 'is_multisite' ) )
					$link = ( ! is_multisite() ) ? admin_url( '/' ) : network_admin_url( '/' );
				else
					$link = admin_url( '/' );
				$link = $link . 'plugin-install.php?tab=search&type=term&s=Gallery+Categories+BestWebSoft&plugin-search-input=Search+Plugins';
				$text = __( 'Install now', 'gallery-plugin' );
			}
			$tooltip_args = array(
				'tooltip_id'	=> 'gllr_install_gallery_categories_tooltip',
				'css_selector' 	=> '.gllr_ad_block #gallery_categories-add-toggle',
				'actions' 		=> array(
					'click' 	=> true,
					'onload' 	=> true,
				), 
				'content' 		=> '<h3>' . __( 'Add multiple gallery categories', 'gallery-plugin' ) . '</h3><p>' . __( "Install Gallery Categories plugin to add unlimited number of categories.", 'gallery-plugin' ) . ' <a href="http://bestwebsoft.com/products/gallery-categories/?k=bb17b69bfb50827f3e2a9b3a75978760&pn=79&v=' . $gllr_plugin_info["Version"] . '&wp_v=' . $wp_version . '" target="_blank">' . $learn_more . '</a></p>',
				'buttons'		=> array(
					array(
						'type' => 'link',
						'link' => $link,
						'text' => $text
					),
					'close' => array(
						'type' => 'dismiss',
						'text' => __( 'Close', 'gallery-plugin' ),
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
					'gallery_categories'=>	$term->slug,
					'orderby'			=> $gllr_options['album_order_by'],
					'order'				=> $gllr_options['album_order']
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
									$images_id = get_post_meta( $post->ID, '_gallery_images', true );
									$attachments = get_posts( array(								
										"showposts"			=>	1,
										"what_to_show"		=>	"posts",
										"post_status"		=>	"inherit",
										"post_type"			=>	"attachment",
										"orderby"			=>	$gllr_options['order_by'],
										"order"				=>	$gllr_options['order'],
										"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
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
						if ( $second_query->have_posts() ) : 
							$second_query->the_post();
							$attachments = get_post_thumbnail_id( $post->ID );
							
							if ( empty ( $attachments ) ) {						
								$images_id = get_post_meta( $post->ID, '_gallery_images', true );
								$attachments = get_posts( array(								
									"showposts"			=>	1,
									"what_to_show"		=>	"posts",
									"post_status"		=>	"inherit",
									"post_type"			=>	"attachment",
									"orderby"			=>	$gllr_options['order_by'],
									"order"				=>	$gllr_options['order'],
									"post_mime_type"	=>	"image/jpeg,image/gif,image/jpg,image/png",
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
						<p class="not_found"><?php _e( 'Sorry, nothing found.', 'gallery-plugin' ); ?></p>
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
									return '<div id="fancybox-title-inside">' + ( title.length ? '<span id="bws_gallery_image_title">' + title + '</span><br />' : '' ) + '<span id="bws_gallery_image_counter"><?php _e( "Image", "gallery-plugin" ); ?> ' + ( currentIndex + 1 ) + ' / ' + currentArray.length + '</span></div><?php if( get_post_meta( $post->ID, 'gllr_download_link', true ) != '' ){?><a id="bws_gallery_download_link" href="' + $( currentOpts.orig ).attr( 'rel' ) + '" target="_blank"><?php echo addslashes( __( "Download high resolution image", "gallery-plugin" ) ); ?> </a><?php } ?>';
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

if ( ! function_exists ( 'gllr_update_image' ) ) {
	function gllr_update_image() {
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
					'height'	=>	$info[1]
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
			return new WP_Error( 'invalid_image', __( 'Image size not defined', 'gallery-plugin' ), $file );

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
			return new WP_Error( 'invalid_image', __( 'We can update only PNG, JPEG, GIF, WPMP or XBM filetype. For other, please, manually reload image.', 'gallery-plugin' ), $file );

		if ( ! is_resource( $image ) )
			return new WP_Error( 'error_loading_image', $image, $file );

		/*$size = @getimagesize( $file );*/
		list( $orig_w, $orig_h, $orig_type ) = $size;

		$dims = gllr_image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);

		if ( ! $dims )
			return new WP_Error( 'error_getting_dimensions', __( 'Image size changes not defined', 'gallery-plugin' ) );
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
				return new WP_Error( 'resize_path_invalid', __( 'Invalid path', 'gallery-plugin' ) );
		} elseif ( IMAGETYPE_PNG == $orig_type ) {
			if ( !imagepng( $newimage, $destfilename ) )
				return new WP_Error( 'resize_path_invalid', __( 'Invalid path', 'gallery-plugin' ) );
		} else {
			/* All other formats are converted to jpg */
			$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
			if ( !imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) ) )
				return new WP_Error( 'resize_path_invalid', __( 'Invalid path', 'gallery-plugin' ) );
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

if ( ! function_exists ( 'gllr_admin_notices' ) ) {
	function gllr_admin_notices() {
		global $hook_suffix, $gllr_options;
		/* add error if templates were not found in the theme directory */
		gllr_admin_error();

		if ( 'plugins.php' == $hook_suffix ) {
			global $gllr_plugin_info;

			if ( isset( $gllr_options['first_install'] ) && strtotime( '-1 week' ) > $gllr_options['first_install'] )
				bws_plugin_banner( $gllr_plugin_info, 'gllr', 'gallery', '01a04166048e9416955ce1cbe9d5ca16', '79', '//ps.w.org/gallery-plugin/assets/icon-128x128.png' );

			bws_plugin_banner_to_settings( $gllr_plugin_info, 'gllr_options', 'gallery-plugin', 'admin.php?page=gallery-plugin.php', 'post-new.php?post_type=gallery', 'Gallery' );
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
		global $gllr_filenames, $gllr_themepath, $wpdb;

		foreach ( $gllr_filenames as $filename ) {
			if ( file_exists( $gllr_themepath . $filename ) && ! unlink( $gllr_themepath . $filename ) ) {
				add_action( 'admin_notices', create_function( '', ' return "Error delete template file";' ) );
			}
		}

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'gllr_options' );
				delete_option( 'gllr_demo_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'gllr_options' );
			delete_option( 'gllr_demo_options' );
		}		
	}
}

/* Create custom meta box for gallery post type */
if ( ! function_exists( 'gllr_media_custom_box' ) ) {
	function gllr_media_custom_box( $obj = '', $box = '' ) {
		global $post, $gllr_plugin_info, $mode, $original_post;
		$original_post = $post; ?>
		<div style="padding-top:10px;">
			<div class="error hide-if-js">
				<p><?php _e( 'Add images requires JavaScript.', 'gallery-plugin' ); ?></p>
			</div>
			<div class="wp-media-buttons">
				<a href="#" id="gllr-media-insert" class="button add_media hide-if-no-js"><span class="dashicons dashicons-admin-media wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'gallery-plugin' ); ?></a>	
			</div>
			<div class="clear"></div>
			<?php $wp_gallery_media_table = new Gllr_Media_Table();
				$wp_gallery_media_table->prepare_items();
				$wp_gallery_media_table->views();
				if ( $mode == 'list' ) {
					$wp_gallery_media_table->display();
				} else { ?>
					<div class="error hide-if-js">
						<p><?php _e( 'The grid view for the Gallery images requires JavaScript.', 'gallery-plugin' ); ?> <a href="<?php echo esc_url( add_query_arg( 'mode', 'list', $_SERVER['REQUEST_URI'] ) ) ?>"><?php _e( 'Switch to the list view', 'gallery-plugin' ); ?></a></p>
					</div>
					<ul tabindex="-1" class="attachments ui-sortable ui-sortable-disabled hide-if-no-js" id="__attachments-view-39">
						<?php $wp_gallery_media_table->display_grid_rows(); ?>
					</ul>
				<?php } ?>
			 <div class="clear"></div>
			 <div id="hidden"></div>
		</div>
	<?php }
}

global $wp_version;

if ( $wp_version > '3.3' ) {

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	if ( ! class_exists( 'WP_Media_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );
	}

	class Gllr_Media_Table extends WP_Media_List_Table {
		public function __construct( $args = array() ) {

			$this->modes = array(
				'list' => __( 'List View', 'gallery-plugin' ),
				'grid' => __( 'Grid View', 'gallery-plugin' )
			);

			parent::__construct( array(
				'plural' => 'media',
				'screen' => isset( $args['screen'] ) ? $args['screen'] : '',
			) );
		}

		function prepare_items() {
			global $wpdb, $wp_query, $mode, $original_post, $wp_version;

			$columns = $this->get_columns();
			$hidden  = array( 'order' );
			$sortable = array();
			$current_page = $this->get_pagenum();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$images_id = get_post_meta( $original_post->ID, '_gallery_images', true );
			if ( empty( $images_id  ) )
				$total_items = 0;
			else
				$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE ID IN( " . $images_id . " )" );
	 		
	 		$per_page = -1;

			$mode = get_user_option( 'gllr_media_library_mode', get_current_user_id() ) ? get_user_option( 'gllr_media_library_mode', get_current_user_id() ) : 'list';
			$modes = array( 'grid', 'list' );

			if ( isset( $_GET['mode'] ) && in_array( $_GET['mode'], $modes ) ) {
				$mode = $_GET['mode'];
				update_user_option( get_current_user_id(), 'gllr_media_library_mode', $mode );
			}

			$this->set_pagination_args( array(
				'total_items' 	=> $total_items,
				'total_pages' 	=> 1,
				'per_page' 		=> $per_page
			) );

			if ( $wp_version < '4.2' )
				$this->is_trash = isset( $_REQUEST['attachment-filter'] ) && 'trash' == $_REQUEST['attachment-filter'];
		}

		function extra_tablenav( $which ) {
			if ( 'bar' !== $which ) {
				return;
			} ?>
			<div class="actions">
				<?php if ( ! is_singular() ) {
					if ( ! $this->is_trash ) {
						$this->months_dropdown( 'attachment' );
					}

					/** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
					do_action( 'restrict_manage_posts' );
					submit_button( __( 'Filter', 'gallery-plugin' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
				}

				if ( $this->is_trash && current_user_can( 'edit_others_posts' ) ) {
					submit_button( __( 'Empty Trash', 'gallery-plugin' ), 'apply', 'delete_all', false );
				} ?>
			</div>
		<?php }

		function has_items() {
			global $wpdb, $post, $original_post;

			$images_id = get_post_meta( $original_post->ID, '_gallery_images', true );
			if ( empty( $images_id  ) )
				$total_items = 0;
			else
				$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE ID IN( " . $images_id . " )" );

			if ( $total_items > 0 )
				return true;
			else
				return false;
		}

		function no_items() {
			_e( 'No images found', 'gallery-plugin' );
		}

		function get_views() {
			return false;
		}

		function display_tablenav( $which ) { ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php $this->extra_tablenav( $which );
				$this->pagination( $which ); ?>
				<br class="clear" />
			</div>
		<?php }

		/**
		 * Display the bulk actions dropdown.
		 *
		 * @since 3.1.0
		 * @access protected
		 *
		 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
		 *                      This is designated as optional for backwards-compatibility.
		 */
		function bulk_actions( $which = '' ) {
			if ( is_null( $this->_actions ) ) {
				$no_new_actions = $this->_actions = $this->get_bulk_actions();
				/**
				 * Filter the list table Bulk Actions drop-down.
				 *
				 * The dynamic portion of the hook name, `$this->screen->id`, refers
				 * to the ID of the current screen, usually a string.
				 *
				 * This filter can currently only be used to remove bulk actions.
				 *
				 * @since 3.5.0
				 *
				 * @param array $actions An array of the available bulk actions.
				 */
				$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
				$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
				$two = '';
			} else {
				$two = '2';
			}

			if ( empty( $this->_actions ) )
				return;

			echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action', 'gallery-plugin' ) . "</label>";
			echo "<select name='action-" . esc_attr( $which ) . "' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
			echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'gallery-plugin' ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

				echo "\t<option value='$name'$class>$title</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply', 'gallery-plugin' ), 'action', '', false, array( 'id' => "doaction$two" ) );
			echo "\n";
		}

		function get_bulk_actions() {
			$actions = array();

			$actions['delete'] = __( 'Delete from Gallery', 'gallery-plugin' );

			return $actions;
		}
		
		public function views() {
			global $mode, $original_post, $wp_version;
			
			$gllr_download_link	=	get_post_meta( $original_post->ID, 'gllr_download_link', true );
			if ( $wp_version < '4.0' ) {
				$views = $this->get_views(); ?>
				<div class="postbox" style="margin-top: 10px;">
					<div class="inside">
						<label><input type="checkbox" name="gllr_download_link" value="1" <?php if ( '' != $gllr_download_link ) echo "checked='checked'"; ?> /> <?php _e( 'Display link to the original file under each image in the lightbox', 'gallery-plugin' ); ?></label>
					</div>
				</div>
			<?php } else { 
				$views = $this->get_views(); ?>
				<div class="wp-filter">
					<div class="filter-items">
						<?php $this->view_switcher( $mode ); ?>
						<?php if ( $mode == 'grid' ) { ?>
							<a href="#" class="button media-button button-large gllr-media-bulk-select-button hide-if-no-js"><?php _e( 'Bulk Select', 'gallery-plugin' ); ?></a>
						<?php } ?>
						<a href="#" class="button media-button button-large gllr-media-bulk-cansel-select-button hide-if-no-js"><?php _e( 'Cancel Selection', 'gallery-plugin' ); ?></a>
						<a href="#" class="button media-button button-primary button-large gllr-media-bulk-delete-selected-button hide-if-no-js" disabled="disabled"><?php _e( 'Delete Selected', 'gallery-plugin' ); ?></a>
						<span class="gllr-media-spinner"></span>
						<label><input type="checkbox" name="gllr_download_link" value="1" <?php if ( '' != $gllr_download_link ) echo "checked='checked'"; ?> /> <?php _e( 'Display link to the original file under each image in the lightbox', 'gallery-plugin' ); ?></label>
					</div>
				</div>
			<?php }
		}

		function get_columns() {
			global $wp_version;
			$tooltip_class = ( $wp_version >= '3.9' ) ? ' dashicons dashicons-editor-help' : '';

			$lists_columns = array(
				'cb'            		=> '<input type="checkbox" />',
				'title'					=> __( 'File', 'gallery-plugin' ),
				'dimensions'			=> __( 'Dimensions', 'gallery-plugin' ),
				'gllr_image_text'		=> __( 'Title', 'gallery-plugin' ) . '<div class="bws_help_box' . $tooltip_class . '"><div class="bws_hidden_help_text"><img src="' . plugins_url( 'gallery-plugin/images/image-title-example.png' ) . '" title="" alt=""/></div></div>',
				'gllr_image_alt_tag'	=> __( 'Alt tag', 'gallery-plugin' ) . ' <div class="bws_help_box' . $tooltip_class . '"><div class="bws_hidden_help_text" style="min-width: 130px;">' . __( 'The alt attribute specifies an alternate text for an image, if the image cannot be displayed.', 'gallery-plugin' ) . '</div></div>',
				'gllr_link_url'			=> __( 'Custom URL', 'gallery-plugin' ) . ' <div class="bws_help_box' . $tooltip_class . '"><div class="bws_hidden_help_text" style="min-width: 130px;">' . __( "By clicking on the thumbnail you'll go to the link (if the field is filled) or the image will be opened in the lightbox (if the field isn't filled)", 'gallery-plugin' ) . '</div></div>',
				'order' 				=> ''
			);
			return $lists_columns;
		}
		
		/*function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="lists[]" value="%s" />', $item['id']
			);
		}*/

		function display_rows( $lists = array(), $level = 0 ) {
			global $post, $wp_query, $mode, $original_post, $gllr_options;

			add_filter( 'the_title','esc_html' );

			$images_id = get_post_meta( $original_post->ID, '_gallery_images', true );
			
			$old_post = $post;

			query_posts( array(
				'post__in'			=> explode( ',', $images_id ), 
				'post_type'			=> 'attachment', 
				'posts_per_page'	=> -1, 
				'post_status'		=> 'inherit', 
				'meta_key'			=> '_gallery_order_' . $original_post->ID, 
				'orderby'			=> $gllr_options['order_by'],
				'order'				=> $gllr_options['order']
			) );

			while ( have_posts() ) {
				the_post();
				$this->single_row( $mode );
			}
			wp_reset_postdata();
			wp_reset_query();
			$post = $old_post;
		}

		function display_grid_rows() {
			global $post, $mode, $original_post, $gllr_plugin_info, $gllr_options;
			$old_post = $post;
			add_filter( 'the_title','esc_html' );

			$images_id = get_post_meta( $original_post->ID, '_gallery_images', true );
			query_posts( array(
				'post__in'			=> explode( ',', $images_id ), 
				'post_type'			=> 'attachment', 
				'posts_per_page'	=> -1, 
				'post_status'		=> 'inherit', 
				'meta_key'			=> '_gallery_order_' . $post->ID, 
				'orderby'			=> $gllr_options['order_by'],
				'order'				=> $gllr_options['order']
			) );
			while ( have_posts() ) {
				the_post();
				$this->single_row( $mode );
			}
			wp_reset_postdata();
			wp_reset_query();
			$post = $old_post;
		}

		function single_row( $mode ) {
			global $post, $original_post, $gllr_plugin_info, $wp_version;
			$attachment_metadata = wp_get_attachment_metadata( $post->ID );
			if ( $mode == 'grid' ) {			
				$image_attributes = wp_get_attachment_image_src( $post->ID, 'medium' ); ?>
				<li tabindex="0" id="post-<?php echo $post->ID; ?>" class="gllr-media-attachment">
					<div class="gllr-media-attachment-preview">
						<div class="gllr-media-thumbnail">
							<div class="centered">
								<img src="<?php echo $image_attributes[0]; ?>" class="thumbnail" draggable="false" />
								<input type="hidden" name="_gallery_order_<?php echo $original_post->ID; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, '_gallery_order_'.$original_post->ID, true ); ?>" />
							</div>
						</div>
					</div>
					<a class="gllr-media-check" tabindex="-1" title="<?php _e( 'Deselect', 'gallery-plugin' ); ?>" href="#">
						<div class="media-modal-icon"></div>
					</a>
					<div class="gllr-media-attachment-details">
						<div class="gllr-media-attachment-info">						
							<div class="gllr-media-details">
								<div class="gllr-media-filename"><strong><?php _e( 'File name', 'gallery-plugin' ); ?>:</strong> <?php the_title(); ?></div>
								<div class="gllr-media-filetype"><strong><?php _e( 'File type', 'gallery-plugin' ); ?>:</strong> <?php echo get_post_mime_type( $post->ID ); ?></div>
								<div class="gllr-media-dimensions"><strong><?php _e( 'Dimensions', 'gallery-plugin' ); ?>:</strong> <?php echo $attachment_metadata['width']; ?> &times; <?php echo $attachment_metadata['height']; ?></div>
							</div>
							<div class="gllr-media-actions">
								<a href="<?php echo get_edit_post_link( $post->ID ); ?>#TB_inline?width=800&height=450&inlineId=gllr-media-attachment-details-box-<?php echo $post->ID; ?>" class="thickbox" title="<?php _e( 'Edit Attachment Info', 'gallery-plugin' ); ?>"><?php _e( 'Edit Attachment', 'gallery-plugin' ); ?></a>
							</div>
						</div>					
					</div>
					<div id="gllr-media-attachment-details-box-<?php echo $post->ID; ?>" class="gllr-media-attachment-details-box">
						<?php $key			= "gllr_image_text";
						$link_key			= "gllr_link_url";
						$alt_tag_key		= "gllr_image_alt_tag"; 
						$image_attributes = wp_get_attachment_image_src( $post->ID, 'large' ); ?>
						<div class="gllr-pro-version-block">
							<a class="button bws_plugin_pro_version" href="http://bestwebsoft.com/products/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="<?php _e( 'Go Pro', 'gallery-plugin' ); ?>"><?php _e( 'Pro version', 'gallery-plugin' ); ?>								
							</a>
							<div class="gllr-pro-settings">
								<?php _e( 'This setting is available in Pro version', 'gallery-plugin' ); ?>
								<img src="<?php echo plugins_url( 'images/pro-settings.jpg', __FILE__ ); ?>" alt="pro-settings" />
							</div>
						</div>
						<div class="gllr-media-attachment-details-box-left">
							<div class="gllr_border_image">
								<img src="<?php echo $image_attributes[0]; ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" height="auto" width="<?php echo $image_attributes[1]; ?>" />
							</div>
						</div>
						<div class="gllr-media-attachment-details-box-right">
							<div>
								<?php _e( "Title", 'gallery-plugin' ); ?>
								<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>"><div class="bws_hidden_help_text"><img src="<?php echo plugins_url( 'gallery-plugin/images/image-title-example.png' ); ?>" title="" alt=""/></div></div>
								<br />
								<input type="text" name="gllr_image_text[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $key, true ); ?>" />
							</div>
							<div>
								<?php _e( "Alt tag", 'gallery-plugin' ); ?>
								<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
									<div class="bws_hidden_help_text" style="min-width: 130px;"><?php _e( 'The alt attribute specifies an alternate text for an image, if the image cannot be displayed.', 'gallery-plugin' ); ?></div>
								</div>
								<br />
								<input type="text" name="gllr_image_alt_tag[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $alt_tag_key, true ); ?>" />
							</div>
							<div>
								<?php _e( "Custom URL", 'gallery-plugin' ); ?>
								<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
									<div class="bws_hidden_help_text" style="min-width: 130px;"><?php _e( "By clicking on the thumbnail you'll go to the link (if the field is filled) or the image will be opened in the lightbox (if the field isn't filled)", 'gallery-plugin' ); ?></div>
								</div>
								<br />
								<input type="text" name="gllr_link_url[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $link_key, true ); ?>" /><br />
							</div>
							<div class="gllr-media-attachment-actions">
								<a href="post.php?post=<?php echo $post->ID; ?>&amp;action=edit"><?php _e( 'Edit more details', 'gallery-plugin' ); ?></a> 
								<span class="gllr-separator">|</span> 
								<a href="#" class="gllr-media-delete-attachment"><?php _e( 'Delete from Gallery', 'gallery-plugin' ); ?></a>
								<input type="hidden" class="gllr_attachment_id" name="_gllr_attachment_id" value="<?php echo $post->ID; ?>" />
								<input type="hidden" class="gllr_post_id" name="_gllr_post_id" value="<?php echo $original_post->ID; ?>" />
							</div>
						</div>
						<div class="gllr_clear"></div>
					</div>
				</li>
			<?php } else {
				$user_can_edit = current_user_can( 'edit_post', $post->ID );
				$post_owner = ( get_current_user_id() == $post->post_author ) ? 'self' : 'other';
				$att_title = _draft_or_post_title(); ?>
				<tr id="post-<?php echo $post->ID; ?>" class="<?php if ( $wp_version < '4.3' ) echo 'gllr_add_responsive_column ';  echo trim( ' author-' . $post_owner . ' status-' . $post->post_status ); ?>">
					<?php list ( $columns, $hidden ) = $this->get_column_info();
					foreach ( $columns as $column_name => $column_display_name ) {
						
						$classes = "$column_name column-$column_name";
						if ( in_array( $column_name, $hidden ) )
							$classes .= ' hidden';

						if ( 'title' == $column_name )
							$classes .= ' column-primary has-row-actions';

						$attributes = "class='$classes'";					
						switch ( $column_name ) {
							case 'order': ?>
								<th <?php echo $attributes; ?>>
									<input type="hidden" name="_gallery_order_<?php echo $original_post->ID; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, '_gallery_order_'.$original_post->ID, true ); ?>" />
								</th>
								<?php break;
							case 'cb': ?>
								<th scope="row" class="check-column">
									<?php if ( $user_can_edit ) { ?>
										<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php echo sprintf( __( 'Select %s', 'gallery-plugin' ), $att_title );?></label>
										<input type="checkbox" name="media[]" id="cb-select-<?php the_ID(); ?>" value="<?php the_ID(); ?>" />
									<?php } ?>
								</th>
								<?php break;
							case 'title': ?>
								<td <?php echo $attributes; ?>><strong>
									<?php $thumb = wp_get_attachment_image( $post->ID, array( 80, 60 ), true );
									if ( $this->is_trash || ! $user_can_edit ) {
										if ( $thumb )
											echo '<span class="media-icon image-icon">' . $thumb . '</span>';
										echo '<span aria-hidden="true">' . $att_title . '</span>';
									} else { ?>
										<a href="<?php echo get_edit_post_link( $post->ID ); ?>" title="<?php echo esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'gallery-plugin' ), $att_title ) ); ?>">
											<?php if ( $thumb ) echo '<span class="media-icon image-icon">' . $thumb . '</span>'; ?>
											<?php echo '<span aria-hidden="true">' . $att_title . '</span>'; ?>
										</a>
									<?php }
									_media_states( $post ); ?></strong>
									<p class="filename"><?php echo wp_basename( $post->guid ); ?></p>
									<?php echo $this->row_actions( $this->_get_row_actions( $post, $att_title ) ); ?>
									<a href="#" class="gllr_info_show hidden"><?php _e( 'Edit Attachment Info', 'gallery-plugin' ); ?></a>
								</td>
								<?php break;
							case 'dimensions': ?>
								<td <?php echo $attributes; ?> data-colname="<?php _e( 'Dimensions', 'gallery-plugin' ); ?>">
									<?php echo $attachment_metadata['width']; ?> &times; <?php echo $attachment_metadata['height']; ?>
								</td>
								<?php break;
							case 'gllr_image_text': ?>
								<td <?php echo $attributes; ?> data-colname="<?php _e( 'Title', 'gallery-plugin' ); ?>">
									<input type="text" name="<?php echo $column_name; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $column_name, true ); ?>" />
								</td>
								<?php break;
							case 'gllr_image_alt_tag': ?>
								<td <?php echo $attributes; ?> data-colname="<?php _e( 'Alt tag', 'gallery-plugin' ); ?>">
									<input type="text" name="<?php echo $column_name; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $column_name, true ); ?>" />
								</td>
								<?php break;
							case 'gllr_link_url': ?>
								<td <?php echo $attributes; ?> data-colname="<?php _e( 'Custom URL', 'gallery-plugin' ); ?>">
									<input type="text" name="<?php echo $column_name; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $column_name, true ); ?>" />
								</td>
								<?php break;
						}
					} ?>
				</tr>
			<?php }
		}
		/**
		 * @param WP_Post $post
		 * @param string  $att_title
		 */
		function _get_row_actions( $post, $att_title ) {
			$actions = array();

			if ( $this->detached ) {
				if ( current_user_can( 'edit_post', $post->ID ) )
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'Edit', 'gallery-plugin' ) . '</a>';
				if ( current_user_can( 'delete_post', $post->ID ) )
					if ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
						$actions['trash'] = "<a class='submitdelete' href='" . wp_nonce_url( "post.php?action=trash&amp;post=$post->ID", 'trash-post_' . $post->ID ) . "'>" . __( 'Trash', 'gallery-plugin' ) . "</a>";
					} else {
						$delete_ays = !MEDIA_TRASH ? " onclick='return showNotice.warn();'" : '';
						$actions['delete'] = "<a class='submitdelete'$delete_ays href='" . wp_nonce_url( "post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID ) . "'>" . __( 'Delete Permanently', 'gallery-plugin' ) . "</a>";
					}
				$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'gallery-plugin' ), $att_title ) ) . '" rel="permalink">' . __( 'View', 'gallery-plugin' ) . '</a>';
				if ( current_user_can( 'edit_post', $post->ID ) )
					$actions['attach'] = '<a href="#the-list" onclick="findPosts.open( \'media[]\',\''.$post->ID.'\' );return false;" class="hide-if-no-js">' . __( 'Attach', 'gallery-plugin' ) . '</a>';
			} else {
				if ( current_user_can( 'edit_post', $post->ID ) && !$this->is_trash )
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'Edit', 'gallery-plugin' ) . '</a>';
				if ( current_user_can( 'delete_post', $post->ID ) ) {
					if ( $this->is_trash )
						$actions['untrash'] = "<a class='submitdelete' href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$post->ID", 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore', 'gallery-plugin' ) . "</a>";
					elseif ( EMPTY_TRASH_DAYS && MEDIA_TRASH )
						$actions['trash'] = "<a class='submitdelete' href='" . wp_nonce_url( "post.php?action=trash&amp;post=$post->ID", 'trash-post_' . $post->ID ) . "'>" . __( 'Trash', 'gallery-plugin' ) . "</a>";
					if ( $this->is_trash || !EMPTY_TRASH_DAYS || !MEDIA_TRASH ) {
						$delete_ays = ( !$this->is_trash && !MEDIA_TRASH ) ? " onclick='return showNotice.warn();'" : '';
						$actions['delete'] = "<a class='submitdelete'$delete_ays href='" . wp_nonce_url( "post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID ) . "'>" . __( 'Delete Permanently', 'gallery-plugin' ) . "</a>";
					}
				}
				if ( !$this->is_trash ) {
					$title =_draft_or_post_title( $post->post_parent );
					$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'gallery-plugin' ), $title ) ) . '" rel="permalink">' . __( 'View', 'gallery-plugin' ) . '</a>';
				}
			}
			return $actions;
		}
	}
}

if ( ! function_exists( 'gllr_delete_image' ) ) {
	function gllr_delete_image() {
		global $wpdb;
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' );

		$action				=	isset( $_POST['action'] ) ? $_POST['action'] : "";
		$delete_id_array	=	isset( $_POST['delete_id_array'] ) ? $_POST['delete_id_array'] : "";
		$post_id			= $_POST['post_id'];

		if ( $action == 'gllr_delete_image' && ! empty( $delete_id_array ) && ! empty( $post_id ) ){
			if ( is_array( $delete_id_array ) )
				$delete_id = explode( ',', trim( $delete_id_array, ',' ) );
			else
				$delete_id[] = $delete_id_array;

			$gallery_images = get_post_meta( $post_id, '_gallery_images', true );

			$gallery_images_array = explode( ',', $gallery_images );
			$gallery_images_array = array_flip( $gallery_images_array );		

			foreach ( $delete_id as $delete_id ) {
				delete_post_meta( $delete_id, '_gallery_order_' . $post_id );
				unset( $gallery_images_array[ $delete_id ] );
			}

			$gallery_images_array = array_flip( $gallery_images_array );
			$gallery_images = implode( ',', $gallery_images_array );
			/* Custom field has a value and this custom field exists in database */
			update_post_meta( $post_id, '_gallery_images', $gallery_images );
			echo 'updated';
		} else {
			echo 'error';
		}
		die();
	}
}

if ( ! function_exists( 'gllr_add_from_media' ) ) {
	function gllr_add_from_media() {
		global $wpdb, $original_post, $post;
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_add_nonce' );

		$action				= isset( $_POST['action'] ) ? $_POST['action'] : "";
		$add_id				= isset( $_POST['add_id'] ) ? $_POST['add_id'] : "";
		$original_post		= $_POST['post_id'];
		$mode				= $_POST['mode'];

		if ( ! empty( $add_id ) && ! empty( $original_post ) ) {
			$post = get_post( $add_id );
			if ( ! empty( $post ) ) {
				if ( preg_match( '!^image/!', $post->post_mime_type ) ) {
					setup_postdata( $post );
					$original_post	= get_post( $original_post );
					$GLOBALS['hook_suffix'] = 'gallery';

					$wp_gallery_media_table = new Gllr_Media_Table();
					$wp_gallery_media_table->prepare_items();
					$wp_gallery_media_table->single_row( $mode );
				}
			}
			
		}
		die();
	}
}

if ( ! function_exists( 'gllr_change_view_mode' ) ) {
	function gllr_change_view_mode() {
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' );
		$mode = $_POST['mode'];
		if ( ! empty( $mode ) ) {
			update_user_option( get_current_user_id(), 'gllr_media_library_mode', $mode );
		}
		die();
	}
}

/**
*	Add place for notice in media upoader for gallery	
*
*	See wp_print_media_templates() in "wp-includes/media-template.php"
*/
if ( ! function_exists( 'gllr_print_media_notice' ) ) {
	function gllr_print_media_notice() {
		global $post;
		if ( isset( $post ) ) {
			if ( $post->post_type == 'gallery' ) {	
				$image_info = '<# gllr_notice_wiev( data.id ); #><div id="gllr_media_notice" class="upload-errors"></div>'; ?>
				<script type="text/javascript">
					( function ($) {
						$( '#tmpl-attachment-details' ).html(
							$( '#tmpl-attachment-details' ).html().replace( '<div class="attachment-info"', '<?php echo $image_info; ?>$&' )
						);
					} )(jQuery);
				</script>
			<?php }
		}
	}
}

/**
*	Add notises in media upoader for portfolio	and gallery
*/
if ( ! function_exists( 'gllr_media_check_ajax_action' ) ) {
	function gllr_media_check_ajax_action() {
		check_ajax_referer( plugin_basename( __FILE__ ), 'gllr_ajax_nonce_field' );
		$thumbnail_id = ( isset( $_POST['thumbnail_id'] ) ) ? $_POST['thumbnail_id'] : false;
		$notice_attach = "";	
		if ( $thumbnail_id ) {
			/*get information about the selected item */ 
			$atachment_detail = get_post( $thumbnail_id );
			if ( ! empty( $atachment_detail ) ) {
				if ( ! preg_match( '!^image/!', $atachment_detail->post_mime_type ) ) {
					$notice_attach = "<div class='upload-error'><strong>" . __( 'Warning', 'gallery-plugin' ) . ": </strong>" . __( 'You can add only images to the gallery', 'gallery-plugin' ) . "</div>";
					wp_send_json_success( $notice_attach );		
				}
			}
			
		}
		wp_die( 0 );
	}
}

/* add shortcode content  */
if ( ! function_exists( 'gllr_shortcode_button_content' ) ) {
	function gllr_shortcode_button_content( $content ) {
		global $wp_version, $post; ?>
		<div id="gllr" style="display:none;">
			<fieldset>
				<label>					
					<?php $old_post = $post; 
					$query = new WP_Query( 'post_type=gallery&post_status=publish&posts_per_page=-1&order=DESC&orderby=date' );
					if ( $query->have_posts() ) { 
						if ( is_plugin_active( 'gallery-categories/gallery-categories.php' ) || is_plugin_active( 'gallery-categories-pro/gallery-categories-pro.php' ) ) { 
							$cat_args = array(
								'orderby'		=> 'date',
								'order'         => 'DESC',
								'show_count'	=> 1,
								'hierarchical'	=> 1,
								'taxonomy'		=> 'gallery_categories',
								'name'			=> 'gllr_gallery_categories',
								'id'			=> 'gllr_gallery_categories'
							);
							wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) ); ?>
							<span class="title"><?php _e( 'Gallery Categories', 'gallery-plugin' ); ?></span>
							</label><br/>
							<p><?php _e( 'or', 'gallery-plugin' ); ?></p>
							<label>
						<?php } ?>
						<select name="gllr_list" id="gllr_shortcode_list" style="max-width: 350px;">
						<?php while ( $query->have_posts() ) {
							$query->the_post();
							if ( ! isset( $gllr_first ) ) $gllr_first = get_the_ID(); 
							$title = get_the_title( $post->ID );
							if ( empty( $title ) )
								$title = '(' . __( 'no title', 'gallery-plugin-pro' ) . ')'; ?>
							<option value="<?php the_ID(); ?>"><h2><?php echo $title; ?> (<?php echo get_the_date( 'Y-m-d' ); ?>)</h2></option>
						<?php }
						wp_reset_postdata();
						$post = $old_post; ?>
						</select>
						<span class="title"><?php _e( 'Gallery', 'gallery-plugin' ); ?></span>
					<?php } else { ?>
						<span class="title"><?php _e( 'Sorry, no gallery found.', 'gallery-plugin' ); ?></span>						
					<?php } ?>
				</label><br/>
				<label>
					<input type="checkbox" value="1" name="gllr_display_short" id="gllr_display_short" /> 
					<span class="checkbox-title">	
						<?php _e( 'Display an album image with the description and the link to a single gallery page', 'gallery-plugin' ); ?>
					</span>				
				</label>
			</fieldset>
			<?php if ( ! empty( $gllr_first ) ) { ?>
				<input class="bws_default_shortcode" type="hidden" name="default" value="[print_gllr id=<?php echo $gllr_first; ?>]" />
			<?php } ?>
			<script type="text/javascript">
				function gllr_shortcode_init() {
					(function($) {	
						<?php if ( $wp_version < '3.9' ) { ?>	
							var current_object = '#TB_ajaxContent';
						<?php } else { ?>
							var current_object = '.mce-reset';
						<?php } ?>			

						$( current_object + ' #gllr_shortcode_list, ' + current_object + ' #gllr_display_short' ).on( 'change', function() {
							var gllr_list = $( current_object + ' #gllr_shortcode_list option:selected' ).val();
							if ( $( current_object + ' #gllr_display_short' ).is( ':checked' ) )
								var shortcode = '[print_gllr id=' + gllr_list + ' display=short]';
							else
								var shortcode = '[print_gllr id=' + gllr_list + ']';

							$( current_object + ' #bws_shortcode_display' ).text( shortcode );
						});
						$( current_object + ' #gllr_gallery_categories' ).on( 'click', function() {
							var gllr_list = $( current_object + ' #gllr_gallery_categories option:selected' ).val();
							var shortcode = '[print_gllr cat_id=' + gllr_list + ']';
							$( current_object + ' #bws_shortcode_display' ).text( shortcode );
						});	         
					})(jQuery);
				}
			</script>
			<div class="clear"></div>
		</div>
	<?php }
}

/* add help tab  */
if ( ! function_exists( 'gllr_add_tabs' ) ) {
	function gllr_add_tabs() {
		$screen = get_current_screen();
		if ( ( ! empty( $screen->post_type ) && 'gallery' == $screen->post_type ) ||
			( isset( $_GET['page'] ) && $_GET['page'] == 'gallery-plugin.php' ) ) {
			$args = array(
				'id' 			=> 'gllr',
				'section' 		=> '200538899'
			);
			bws_help_tab( $screen, $args );
		}
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

add_action( 'plugins_loaded', 'gllr_plugins_loaded' );

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
add_action( 'pre_get_posts', 'gllr_manage_pre_get_posts', 1 );

add_action( 'admin_enqueue_scripts', 'gllr_admin_head' );
add_action( 'wp_enqueue_scripts', 'gllr_wp_head' );
add_action( 'wp_head', 'gllr_add_wp_head' );

/* add theme name as class to body tag */
add_filter( 'body_class', 'gllr_theme_body_classes' );

add_shortcode( 'print_gllr', 'gllr_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

add_action( 'wp_ajax_gllr_update_image', 'gllr_update_image' );
add_action( 'wp_ajax_gllr_sanitize_file_name', 'gllr_sanitize_file_name' );
add_filter( 'sanitize_file_name', 'gllr_filter_sanitize_file_name' );

add_action( 'admin_notices', 'gllr_admin_notices' );

/*	Add place for notice in media upoader for portfolio	*/
add_action( 'print_media_templates', 'gllr_print_media_notice', 11 );
/*	Add notises in media upoader for gallery	*/
add_action( 'wp_ajax_gllr_media_check', 'gllr_media_check_ajax_action' ); 

/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'gllr_shortcode_button_content' );

global $pagenow;
if ( ( isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'gallery' ) || ( isset( $pagenow ) && $pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'gallery' ) )
	add_action( 'edit_form_after_title', 'gllr_media_custom_box' );

add_action( 'wp_ajax_gllr_delete_image', 'gllr_delete_image' );
add_action( 'wp_ajax_gllr_add_from_media', 'gllr_add_from_media' );
add_action( 'wp_ajax_gllr_change_view_mode', 'gllr_change_view_mode' );

/* Delete plugin */
register_uninstall_hook( __FILE__, 'gllr_plugin_uninstall' );