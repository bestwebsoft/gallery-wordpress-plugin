<?php
/*
Plugin Name: Gallery Plugin
Plugin URI:  http://bestwebsoft.com/plugin/
Description: This plugin allows you to implement gallery page into web site.
Author: BestWebSoft
Version: 4.0.2
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2011  BestWebSoft  ( http://support.bestwebsoft.com )

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

if ( ! function_exists( 'gllr_plugin_install' ) ) {
	function gllr_plugin_install() {
		$filename_1 = WP_PLUGIN_DIR .'/gallery-plugin/template/gallery-template.php';
		$filename_2 = WP_PLUGIN_DIR .'/gallery-plugin/template/gallery-single-template.php';

		$filename_theme_1 = get_stylesheet_directory() .'/gallery-template.php';
		$filename_theme_2 = get_stylesheet_directory() .'/gallery-single-template.php';

		if ( ! file_exists( $filename_theme_1 ) ) {
			$handle = @fopen( $filename_1, "r" );
			$contents = @fread( $handle, filesize( $filename_1 ) );
			@fclose( $handle );
			if ( ! ( $handle = @fopen( $filename_theme_1, 'w' ) ) )
				return false;
			@fwrite( $handle, $contents );
			@fclose( $handle );
			chmod( $filename_theme_1, octdec( 755 ) );
		} else {
			$handle = @fopen( $filename_theme_1, "r" );
			$contents = @fread( $handle, filesize( $filename_theme_1 ) );
			@fclose( $handle );
			if ( ! ( $handle = @fopen( $filename_theme_1.'.bak', 'w' ) ) )
				return false;
			@fwrite( $handle, $contents );
			@fclose( $handle );
			
			$handle = @fopen( $filename_1, "r" );
			$contents = @fread( $handle, filesize( $filename_1 ) );
			@fclose( $handle );
			if ( ! ( $handle = @fopen( $filename_theme_1, 'w' ) ) )
				return false;
			@fwrite( $handle, $contents );
			@fclose( $handle );
			chmod( $filename_theme_1, octdec( 755 ) );
		}
		if ( ! file_exists( $filename_theme_2 ) ) {
			$handle = @fopen( $filename_2, "r" );
			$contents = @fread( $handle, filesize( $filename_2 ) );
			@fclose( $handle );
			if ( ! ( $handle = @fopen( $filename_theme_2, 'w' ) ) )
				return false;
			@fwrite( $handle, $contents );
			@fclose( $handle );
			chmod( $filename_theme_2, octdec( 755 ) );
		} else {
			$handle = @fopen( $filename_theme_2, "r" );
			$contents = @fread( $handle, filesize( $filename_theme_2 ) );
			@fclose( $handle );
			if ( ! ( $handle = @fopen( $filename_theme_2.'.bak', 'w' ) ) )
				return false;
			@fwrite( $handle, $contents );
			@fclose( $handle );
			
			$handle = @fopen( $filename_2, "r" );
			$contents = @fread( $handle, filesize( $filename_2 ) );
			@fclose( $handle );
			if ( ! ( $handle = @fopen( $filename_theme_2, 'w' ) ) )
				return false;
			@fwrite( $handle, $contents );
			@fclose( $handle );
			chmod( $filename_theme_2, octdec( 755 ) );
		}
	}
}

if ( ! function_exists( 'gllr_admin_error' ) ) {
	function gllr_admin_error() {
		$post = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : "" ;
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : "" ;
		if ( ( 'gallery' == get_post_type( $post ) || 'gallery' == $post_type ) && ( ! file_exists( get_stylesheet_directory() .'/gallery-template.php' ) || ! file_exists( get_stylesheet_directory() .'/gallery-single-template.php' ) ) ) {
				gllr_plugin_install();
		}
		if ( ( 'gallery' == get_post_type( $post ) || 'gallery' == $post_type ) && ( ! file_exists( get_stylesheet_directory() .'/gallery-template.php' ) || ! file_exists( get_stylesheet_directory() .'/gallery-single-template.php' ) ) ) {
			echo '<div class="error"><p><strong>'.__( 'The following files "gallery-template.php" and "gallery-single-template.php" were not found in the directory of your theme. Please copy them from the directory `/wp-content/plugins/gallery-plugin/template/` to the directory of your theme for the correct work of the Gallery plugin', 'gallery' ).'</strong></p></div>';
		}
	}
}

if ( ! function_exists( 'gllr_plugin_uninstall' ) ) {
	function gllr_plugin_uninstall() {
		if ( file_exists( get_stylesheet_directory() .'/gallery-template.php' ) && ! unlink( get_stylesheet_directory() .'/gallery-template.php' ) ) {
			add_action( 'admin_notices', create_function( '', ' return "Error delete template file";' ) );
		}
		if ( file_exists( get_stylesheet_directory() .'/gallery-single-template.php' ) && ! unlink( get_stylesheet_directory() .'/gallery-single-template.php' ) ) {
			add_action( 'admin_notices', create_function( '', ' return "Error delete template file";' ) );
		}
		if( get_option( 'gllr_options' ) ) {
			delete_option( 'gllr_options' );
		}
	}
}

// Create post type for Gallery
if ( ! function_exists( 'post_type_images' ) ) {
	function post_type_images() {
		register_post_type( 'gallery', array(
			'labels' => array(
				'name' => __( 'Galleries', 'gallery' ),
				'singular_name' => __( 'Gallery', 'gallery' ),
				'add_new' => __( 'Add a Gallery', 'gallery' ),
				'add_new_item' => __( 'Add New Gallery', 'gallery' ),
				'edit_item' => __( 'Edit Gallery', 'gallery' ),
				'new_item' => __( 'New Gallery', 'gallery' ),
				'view_item' => __( 'View Gallery', 'gallery' ),
				'search_items' => __( 'Find a Gallery', 'gallery' ),
				'not_found' =>	__( 'No Gallery found', 'gallery' ),
				'parent_item_colon' => '',
				'menu_name' => __( 'Galleries', 'gallery' )
			),
			'public' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'author', 'page-attributes', 'comments' ),
			'register_meta_box_cb' => 'init_metaboxes_gallery'
		));
	}
}

if ( ! function_exists( 'addImageAncestorToMenu' ) ) {
	function addImageAncestorToMenu( $classes ) {
		if ( is_singular( 'gallery' ) ) {
			global $wpdb, $post;
			
			if ( empty( $post->ancestors ) ) {
				$parent_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND post_status = 'publish' AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
				while ( $parent_id ) {
					$page = get_page( $parent_id );
					if( $page->post_parent > 0 )
						$parent_id  = $page->post_parent;
					else 
						break;
				}
				wp_reset_query();
				if( empty( $parent_id ) ) 
					return $classes;
				$post_ancestors = array( $parent_id );
			} else {
				$post_ancestors = $post->ancestors;
			}			
			
			$menuQuery = "SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = '_menu_item_object_id' AND meta_value IN (" . implode( ',', $post_ancestors ) . ")";
			$menuItems = $wpdb->get_col( $menuQuery );
			
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
	}
}

// Create custom meta box for portfolio post type
if ( ! function_exists( 'gllr_post_custom_box' ) ) {
	function gllr_post_custom_box( $obj = '', $box = '' ) {
		global $post;
		$gllr_options = get_option( 'gllr_options' );
		$key = "gllr_image_text";
		$gllr_download_link = get_post_meta( $post->ID, 'gllr_download_link', true );
		$link_key = "gllr_link_url";
		$alt_tag_key = "gllr_image_alt_tag";
		$error = "";
		$uploader = true;
		
		$post_types = get_post_types( array( '_builtin' => false ) );
		if ( ! is_writable ( plugin_dir_path( __FILE__ ) ."upload/files/" ) ) {
			$error = __( "The gallery temp directory (gallery-plugin/upload/files) is not available for record on your webserver. Please use the standard WP functionality to upload images (media library)", 'gallery' );
			$uploader = false;
		}
		?>
		<div style="padding-top:10px;"><label for="uploadscreen"><?php echo __( 'Choose an image for upload:', 'gallery' ); ?></label>
			<input name="MAX_FILE_SIZE" value="1048576" type="hidden" />
			<div id="file-uploader-demo1" style="padding-top:10px;">	
				<?php echo $error; ?>
				<noscript>			
					<p><?php echo __( 'Please enable JavaScript to use the file uploader.', 'gallery' ); ?></p>
				</noscript>         
			</div>
			<ul id="files" ></ul>
			<div id="hidden"></div>
			<div style="clear:both;"></div></div>
			<div class="gllr_order_message hidden">
				<input type="checkbox" name="gllr_download_link" value="1" <?php if( $gllr_download_link != '' ) echo "checked='checked'"; ?> style="position:relative; top:-2px " /> <?php _e( 'Allow the download link for all images in this gallery', 'gallery' ); ?><br /><br />
				<?php _e( 'Please use the drag and drop function to change an order of the images displaying and do not forget to save the post.', 'gallery'); ?>
				<br />
				<?php _e( 'Please make a choice', 'gallery'); echo ' `'; _e( 'Sort images by', 'gallery' ); echo '` -> `'; _e( 'sort images', 'gallery' ); echo '` '; _e( 'on the plugin settings page (', 'gallery' ); ?> <a href="<?php echo admin_url( 'admin.php?page=gallery-plugin.php', 'http' ); ?>" target="_blank"><?php echo admin_url( 'admin.php?page=gallery-plugin.php', 'http' ); ?></a>)
			</div>
		<script type="text/javascript">
		<?php if ( $uploader === true ) { ?>
		jQuery(document).ready( function(){
				var uploader = new qq.FileUploader({
						element: document.getElementById('file-uploader-demo1'),
						action: '../wp-admin/admin-ajax.php?action=upload_gallery_image',
						debug: false,
						onComplete: function( id, fileName, result ) {
							if ( result.error ) {
								//
							} else {
								jQuery('<li></li>').appendTo('#files').html('<img src="<?php echo plugins_url( "upload/files/" , __FILE__ ); ?>'+fileName+'" alt="" /><div style="width:200px">'+fileName+'<br />' +result.width+'x'+result.height+'</div>').addClass('success');
								jQuery('<input type="hidden" name="undefined[]" id="undefined" value="'+fileName+'" />').appendTo('#hidden');
							}
						}
				});           
				jQuery('#images_albumdiv').remove();

		});
		<?php } ?>
		function img_delete(id) {
			jQuery('#'+id).hide();
			jQuery('#delete_images').append('<input type="hidden" name="delete_images[]" value="'+id+'" />');
		}
		</script>
		<?php
		$plugin_info = get_plugin_data( __FILE__ );
		$posts = get_posts( array(
			"showposts"			=> -1,
			"what_to_show"	=> "posts",
			"post_status"		=> "inherit",
			"post_type"			=> "attachment",
			"orderby"				=> $gllr_options['order_by'],
			"order"					=> $gllr_options['order'],
			"post_mime_type"=> "image/jpeg,image/gif,image/jpg,image/png",
			"post_parent"		=> $post->ID )); ?>
		<ul class="gallery clearfix">
		<?php foreach ( $posts as $page ):
			$image_text = get_post_meta( $page->ID, $key, FALSE );
			echo '<li id="'.$page->ID.'" class="gllr_image_block"><div class="gllr_drag">';
				$image_attributes = wp_get_attachment_image_src( $page->ID, 'thumbnail' );
				echo '<div class="gllr_border_image"><img src="'.$image_attributes[0].'" alt="'.$page->post_title.'" title="'.$page->post_title.'" height="'.get_option( 'thumbnail_size_h' ).'" width="'.get_option( 'thumbnail_size_w' ).'" /></div>';
				echo '<br />'.__( "Title", "gallery" ).'<br /><input type="text" name="gllr_image_text['.$page->ID.']" value="'.get_post_meta( $page->ID, $key, TRUE ).'" class="gllr_image_text" />';
				echo '<input type="text" name="gllr_order_text['.$page->ID.']" value="'.$page->menu_order.'" class="gllr_order_text '.( $page->menu_order == 0 ? "hidden" : '' ).'" />';
				echo '<br />'.__( "Alt tag", "gallery" ).'<br /><input type="text" name="gllr_image_alt_tag['.$page->ID.']" value="'.get_post_meta( $page->ID, $alt_tag_key, TRUE ).'" class="gllr_image_alt_tag" />';
				echo '<br />'.__( "URL", "gallery" ).'<br /><input type="text" name="gllr_link_url['.$page->ID.']" value="'.get_post_meta( $page->ID, $link_key, TRUE ).'" class="gllr_link_text" /><br /><span class="small_text">'.__( "(by click on image opens a link in a new window)", "gallery" ).'</span>';
				echo '<a class="gllr_pro_version" href="http://bestwebsoft.com/plugin/gallery-pro/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v='.$plugin_info["Version"].'" target="_blank" title="'. __( 'This setting is available in Pro version', 'gallery' ).'"><br />'.
					'<div class="gllr_pro_version">'.__( "Open the link", "gallery" ).'<br/><input disabled type="radio" value="_self" > '.__( "Current window", "gallery" ).'<br/><input disabled type="radio" value="_blank" > '.__( "New window", "gallery" ).'</div></a>';
				echo '<div class="delete"><a href="javascript:void(0);" onclick="img_delete('.$page->ID.');">'.__( "Delete", "gallery" ).'</a><div/>';
			echo '</div></li>';
    endforeach; ?>
		</ul><div style="clear:both;"></div>
		<div id="delete_images"></div>	 
	<?php
	}
}

// Create shortcode meta box for portfolio post type
if ( ! function_exists( 'gllr_post_shortcode_box' ) ) {
	function gllr_post_shortcode_box( $obj = '', $box = '' ) {
		global $post; ?>
		<p><?php _e( 'You can add a Single Gallery to the page or post by inserting this shortcode into the content', 'gallery' ); ?>:</p>
		<p><code>[print_gllr id=<?php echo $post->ID; ?>]</code></p>
		<p><?php _e( 'If you want to display a short description containing a screenshot and the link to the Single Gallery Page', 'gallery' ); ?>:</p>
		<p><code>[print_gllr id=<?php echo $post->ID; ?> display=short]</code></p>
	<?php }
}

if ( ! function_exists ( 'gllr_save_postdata' ) ) {
	function gllr_save_postdata( $post_id, $post ) {
		global $post, $wpdb;
		$key = "gllr_image_text";
		$link_key = "gllr_link_url";
		$alt_tag_key = "gllr_image_alt_tag";

		if ( isset( $_REQUEST['undefined'] ) && ! empty( $_REQUEST['undefined'] ) ) {
			$array_file_name = $_REQUEST['undefined'];
			$uploadFile = array();
			$newthumb = array();
			$time = current_time( 'mysql' );

			$uploadDir =  wp_upload_dir( $time );

			while( list( $key, $val ) = each( $array_file_name ) ) {
				$imagename = sanitize_file_name( $val );
				$uploadFile[] = $uploadDir["path"] ."/" . $imagename;
			}
			reset( $array_file_name );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			while( list( $key, $val ) = each( $array_file_name ) ) {
				$file_name = sanitize_file_name( $val );
				if( file_exists( $uploadFile[$key] ) ){
					$uploadFile[$key] = $uploadDir["path"] ."/" . pathinfo( $uploadFile[$key], PATHINFO_FILENAME ).uniqid().".".pathinfo( $uploadFile[$key], PATHINFO_EXTENSION );
				}

				if ( copy ( plugin_dir_path( __FILE__ ) ."upload/files/".$file_name, $uploadFile[$key] ) ) {
					unlink( plugin_dir_path( __FILE__ ) ."upload/files/".$file_name );
					$overrides = array( 'test_form' => false );
				
					$file = $uploadFile[$key];
					$filename = basename( $file );
					
					$wp_filetype	= wp_check_filetype( $filename, null );
					$attachment		= array(
						 'post_mime_type' => $wp_filetype['type'],
						 'post_title' => $filename,
						 'post_content' => '',
						 'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $file );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
					wp_update_attachment_metadata( $attach_id, $attach_data );			
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE ID = %d", $post->ID, $attach_id ) );
				}
			}
		}
		if ( isset( $_REQUEST['delete_images'] ) ) {
			foreach( $_REQUEST['delete_images'] as $delete_id ) {
				delete_post_meta( $delete_id, $key );
				wp_delete_attachment( $delete_id );
				if( isset( $_REQUEST['gllr_order_text'][ $delete_id ] ) )
					unset( $_REQUEST['gllr_order_text'][ $delete_id ] );
			}
		}
		if ( isset( $_REQUEST['gllr_image_text'] ) ) {
			$posts = get_posts( array(
				"showposts"			=> -1,
				"what_to_show"		=> "posts",
				"post_status"		=> "inherit",
				"post_type"			=> "attachment",
				"orderby"			=> "menu_order",
				"order"				=> "ASC",
				"post_mime_type"	=> "image/jpeg,image/gif,image/jpg,image/png",
				"post_parent"		=> $post->ID ));
			foreach ( $posts as $page ) {
				if( isset( $_REQUEST['gllr_image_text'][$page->ID] ) ) {
					$value = $_REQUEST['gllr_image_text'][$page->ID];
					if ( get_post_meta( $page->ID, $key, FALSE ) ) {
						// Custom field has a value and this custom field exists in database
						update_post_meta( $page->ID, $key, $value );
					} elseif( $value ) {
						// Custom field has a value, but this custom field does not exist in database
						add_post_meta( $page->ID, $key, $value );
					}
				}
			}
		}
		if ( isset( $_REQUEST['gllr_order_text'] ) ) {
			foreach( $_REQUEST['gllr_order_text'] as $key=>$val ){
				wp_update_post( array( 'ID'=>$key, 'menu_order'=>$val ) );
			}
		}
		if ( isset( $_REQUEST['gllr_link_url'] ) ) {
			$posts = get_posts( array(
				"showposts"			=> -1,
				"what_to_show"		=> "posts",
				"post_status"		=> "inherit",
				"post_type"			=> "attachment",
				"orderby"			=> "menu_order",
				"order"				=> "ASC",
				"post_mime_type"	=> "image/jpeg,image/gif,image/jpg,image/png",
				"post_parent"		=> $post->ID ));
			foreach ( $posts as $page ) {
				if( isset( $_REQUEST['gllr_link_url'][$page->ID] ) ) {
					$value = $_REQUEST['gllr_link_url'][$page->ID];
					if ( get_post_meta( $page->ID, $link_key, FALSE ) ) {
						// Custom field has a value and this custom field exists in database
						update_post_meta( $page->ID, $link_key, $value );
					} elseif( $value ) {
						// Custom field has a value, but this custom field does not exist in database
						add_post_meta( $page->ID, $link_key, $value );
					}
				}
			}
		}
		if ( isset( $_REQUEST['gllr_image_alt_tag'] ) ) {
			$posts = get_posts( array(
				"showposts"			=> -1,
				"what_to_show"		=> "posts",
				"post_status"		=> "inherit",
				"post_type"			=> "attachment",
				"orderby"			=> "menu_order",
				"order"				=> "ASC",
				"post_mime_type"	=> "image/jpeg,image/gif,image/jpg,image/png",
				"post_parent"		=> $post->ID ));
			foreach ( $posts as $page ) {
				if( isset( $_REQUEST['gllr_image_alt_tag'][$page->ID] ) ) {
					$value = $_REQUEST['gllr_image_alt_tag'][$page->ID];
					if ( get_post_meta( $page->ID, $alt_tag_key, FALSE ) ) {
						// Custom field has a value and this custom field exists in database
						update_post_meta( $page->ID, $alt_tag_key, $value );
					} elseif( $value ) {
						// Custom field has a value, but this custom field does not exist in database
						add_post_meta( $page->ID, $alt_tag_key, $value );
					}
				}
			}
		}
		if ( isset( $_REQUEST['gllr_download_link'] ) ) {
			if ( get_post_meta( $post_id, 'gllr_download_link', FALSE ) ) {
				// Custom field has a value and this custom field exists in database
				update_post_meta( $post_id, 'gllr_download_link', 1 );
			} else {
				// Custom field has a value, but this custom field does not exist in database
				add_post_meta( $post_id, 'gllr_download_link', 1 );
			}
		} else {
			delete_post_meta( $post_id, 'gllr_download_link' );
		}
	}
}

if ( ! function_exists ( 'gllr_plugin_init' ) ) {
	function gllr_plugin_init() {
		// Internationalization, first(!)
		load_plugin_textdomain( 'gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
		load_plugin_textdomain( 'bestwebsoft', false, dirname( plugin_basename( __FILE__ ) ) . '/bws_menu/languages/' ); 
	}
}

if ( ! function_exists( 'gllr_custom_permalinks' ) ) {
    function gllr_custom_permalinks( $rules ) {
        $newrules = array();
        if ( ! isset( $rules['(.+)/gallery/([^/]+)/?$'] ) || ! isset( $rules['/gallery/([^/]+)/?$'] ) ) {
            global $wpdb;
            $parent = $wpdb->get_var( "SELECT $wpdb->posts.post_name FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND (post_status = 'publish' OR post_status = 'private') AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );   
            if ( ! empty( $parent ) ) {
                $newrules['(.+)/'.$parent.'/([^/]+)/?$']= 'index.php?post_type=gallery&name=$matches[2]&posts_per_page=-1';
                $newrules[''.$parent.'/([^/]+)/?$']= 'index.php?post_type=gallery&name=$matches[1]&posts_per_page=-1';
                $newrules[''.$parent.'/page/([^/]+)/?$']= 'index.php?pagename='.$parent.'&paged=$matches[1]';
                $newrules[''.$parent.'/page/([^/]+)?$']= 'index.php?pagename='.$parent.'&paged=$matches[1]';
            } else {
                $newrules['(.+)/gallery/([^/]+)/?$']= 'index.php?post_type=gallery&name=$matches[2]&posts_per_page=-1';
                $newrules['gallery/([^/]+)/?$']= 'index.php?post_type=gallery&name=$matches[1]&posts_per_page=-1';
                $newrules['gallery/page/([^/]+)/?$']= 'index.php?pagename=gallery&paged=$matches[1]';
                $newrules['gallery/page/([^/]+)?$']= 'index.php?pagename=gallery&paged=$matches[1]';
            }
        }
        if ( false === $rules )
        	return $newrules;

        return $newrules + $rules;
    }
}

// flush_rules() if our rules are not yet included
if ( ! function_exists( 'gllr_flush_rules' ) ) {
	function gllr_flush_rules() {
		$rules = get_option( 'rewrite_rules' );
		if ( ! isset( $rules['(.+)/gallery/([^/]+)/?$'] ) || ! isset( $rules['/gallery/([^/]+)/?$'] ) ) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
	}
}

if ( ! function_exists( 'gllr_template_redirect' ) ) {
	function gllr_template_redirect() { 
		global $wp_query, $post, $posts;
		if ( 'gallery' == get_post_type() && "" == $wp_query->query_vars["s"] ) {
			include( STYLESHEETPATH . '/gallery-single-template.php' );
			exit(); 
		}
	}
}

// Change the columns for the edit CPT screen
if ( ! function_exists( 'gllr_change_columns' ) ) {
	function gllr_change_columns( $cols ) {
		$cols = array(
			'cb'			=> '<input type="checkbox" />',
			'title'			=> __( 'Title', 'gallery' ),
			'autor'			=> __( 'Author', 'gallery' ),
			'gallery'		=> __( 'Photo', 'gallery' ),
			'status'		=> __( 'Publishing', 'gallery' ),
			'dates'			=> __( 'Date', 'gallery' )
		);
		return $cols;
	}
}

if ( ! function_exists( 'gllr_custom_columns' ) ) {
	function gllr_custom_columns( $column, $post_id ) {
		global $wpdb;
		$post = get_post( $post_id );	
		$row = $wpdb->get_results( "SELECT *
				FROM $wpdb->posts
				WHERE $wpdb->posts.post_parent = $post_id
				AND $wpdb->posts.post_type = 'attachment'
				AND (
				$wpdb->posts.post_status = 'inherit'
				)
				ORDER BY $wpdb->posts.post_title ASC" );
		switch ( $column ) {
		 //case "category":
			case "autor":
				$author_id=$post->post_author;
				echo '<a href="edit.php?post_type=post&amp;author='.$author_id.'">'.get_the_author_meta( 'user_nicename' , $author_id ).'</a>';
				break;
			case "gallery":
				echo count( $row );
				break;
			case "status":
				if( $post->post_status == 'publish' )
					echo '<a href="javascript:void(0)">Yes</a>';
				else
					echo '<a href="javascript:void(0)">No</a>';
				break;
			case "dates":
				echo strtolower( __( date( "F", strtotime( $post->post_date ) ), 'kerksite' ) )." ".date( "j Y", strtotime( $post->post_date ) );				
				break;
		}
	}
}

if ( ! function_exists( 'get_ID_by_slug' ) ) {
	function get_ID_by_slug( $page_slug ) {
		$page = get_page_by_path( $page_slug );
		if ( $page ) {
			return $page->ID;
		} else {
			return null;
		}
	}
}

if ( ! function_exists( 'the_excerpt_max_charlength' ) ) {
	function the_excerpt_max_charlength( $charlength ) {
		$excerpt = get_the_excerpt();
		$charlength ++;
		if ( strlen( $excerpt ) > $charlength ) {
			$subex = substr( $excerpt, 0, $charlength-5 );
			$exwords = explode( " ", $subex );
			$excut = - ( strlen ( $exwords [ count( $exwords ) - 1 ] ) );
			if( $excut < 0 ) {
				echo substr( $subex, 0, $excut );
			} 
			else {
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
		$post_type = get_query_var( 'post_type' );
		$parent_id = 0;
		if( $post_type == "gallery" ) {
			$parent_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND post_status = 'publish' AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );
			while ( $parent_id ) {
				$page = get_page( $parent_id );
				if( $page->post_parent > 0 )
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

require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );

if ( ! function_exists( 'add_gllr_admin_menu' ) ) {
	function add_gllr_admin_menu() {
		add_menu_page( 'BWS Plugins', 'BWS Plugins', 'manage_options', 'bws_plugins', 'bws_add_menu_render', plugins_url( "images/px.png", __FILE__ ), 1001); 
		add_submenu_page( 'bws_plugins', __( 'Gallery', 'gallery' ), __( 'Gallery', 'gallery' ), 'manage_options', "gallery-plugin.php", 'gllr_settings_page' );

		//call register settings function
		add_action( 'admin_init', 'register_gllr_settings' );
	}
}

// register settings function
if ( ! function_exists( 'register_gllr_settings' ) ) {
	function register_gllr_settings() {
		global $wpmu, $gllr_options;
		//global $wp_filesystem;
		//WP_Filesystem();
		//var_dump($wp_filesystem);

		$gllr_option_defaults = array(
			'gllr_custom_size_name'			=> array( 'album-thumb', 'photo-thumb' ),
			'gllr_custom_size_px'			=> array( array( 120, 80 ), array( 160, 120 ) ),
			'border_images'					=> 1,
			'border_images_width'			=> 10,
			'border_images_color'			=> '#F1F1F1',
			'custom_image_row_count'		=> 3,
			'start_slideshow'				=> 0,
			'slideshow_interval'			=> 2000,
			'order_by'						=> 'menu_order',
			'order'							=> 'ASC',
			'read_more_link_text'			=> __( 'See images &raquo;', 'gallery' ),
			'image_text'					=> 1,
			'return_link'					=> 0,
			'return_link_text'				=> 'Return to all albums',
			'return_link_page'				=> 'gallery_template_url',
			'return_link_url'				=> '',
			'return_link_shortcode'			=> 0
		);

		// install the option defaults
		if ( 1 == $wpmu ) {
			if ( ! get_site_option( 'gllr_options' ) ) {
				add_site_option( 'gllr_options', $gllr_option_defaults, '', 'yes' );
			}
		} else {
			if ( ! get_option( 'gllr_options' ) )
				add_option( 'gllr_options', $gllr_option_defaults, '', 'yes' );
		}

		// get options from the database
		if ( 1 == $wpmu )
			$gllr_options = get_site_option( 'gllr_options' ); // get options from the database
		else
			$gllr_options = get_option( 'gllr_options' );// get options from the database

		// array merge incase this version has added new options
		$gllr_options = array_merge( $gllr_option_defaults, $gllr_options );

		update_option( 'gllr_options', $gllr_options );

		if ( function_exists( 'add_image_size' ) ) { 
			add_image_size( 'album-thumb', $gllr_options['gllr_custom_size_px'][0][0], $gllr_options['gllr_custom_size_px'][0][1], true );
			add_image_size( 'photo-thumb', $gllr_options['gllr_custom_size_px'][1][0], $gllr_options['gllr_custom_size_px'][1][1], true );
		}
	}
}

if( ! function_exists( 'gllr_settings_page' ) ) {
	function gllr_settings_page() {
		global $gllr_options, $wp_version;
		$error = "";
		$plugin_info = get_plugin_data( __FILE__ );
		
		// Save data for settings page
		if( isset( $_REQUEST['gllr_form_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'gllr_nonce_name' ) ) {
			$gllr_request_options = array();
			$gllr_request_options["gllr_custom_size_name"] = $gllr_options["gllr_custom_size_name"];

			$gllr_request_options["gllr_custom_size_px"] = array( 
				array( intval( trim( $_REQUEST['gllr_custom_image_size_w_album'] ) ), intval( trim( $_REQUEST['gllr_custom_image_size_h_album'] ) ) ), 
				array( intval( trim( $_REQUEST['gllr_custom_image_size_w_photo'] ) ), intval( trim( $_REQUEST['gllr_custom_image_size_h_photo'] ) ) ) 
			);

			$gllr_request_options["border_images"] = ( isset( $_REQUEST['gllr_border_images'] ) ) ? 1 : 0;

			$gllr_request_options["border_images_width"] = intval( trim( $_REQUEST['gllr_border_images_width'] ) );
			$gllr_request_options["border_images_color"] = trim( $_REQUEST['gllr_border_images_color'] );

			$gllr_request_options["custom_image_row_count"] =  intval( trim( $_REQUEST['gllr_custom_image_row_count'] ) );
			if ( $gllr_request_options["custom_image_row_count"] == "" || $gllr_request_options["custom_image_row_count"] < 1 )
				$gllr_request_options["custom_image_row_count"] = 1;

			$gllr_request_options["start_slideshow"] = ( isset( $_REQUEST['gllr_start_slideshow'] ) ) ? 1 : 0;

			$gllr_request_options["slideshow_interval"] = $_REQUEST['gllr_slideshow_interval'];
			$gllr_request_options["order_by"] = $_REQUEST['gllr_order_by'];
			$gllr_request_options["order"] = $_REQUEST['gllr_order'];
			
			$gllr_request_options["image_text"] = ( isset( $_REQUEST['gllr_image_text'] ) ) ? 1 : 0;
			$gllr_request_options["return_link"] = ( isset( $_REQUEST['gllr_return_link'] ) ) ? 1 : 0;

			$gllr_request_options["return_link_page"] = $_REQUEST['gllr_return_link_page'];
			$gllr_request_options["return_link_url"] = strpos( $_REQUEST['gllr_return_link_url'], "http:" ) !== false ? trim( $_REQUEST['gllr_return_link_url'] ) : 'http://'.trim( $_REQUEST['gllr_return_link_url'] );

			$gllr_request_options["return_link_shortcode"] = ( isset( $_REQUEST['gllr_return_link_shortcode'] ) ) ? 1 : 0;

			$gllr_request_options["return_link_text"] = $_REQUEST['gllr_return_link_text'];
			$gllr_request_options["read_more_link_text"] = $_REQUEST['gllr_read_more_link_text'];			

			// array merge incase this version has added new options
			$gllr_options = array_merge( $gllr_options, $gllr_request_options );

			// Check select one point in the blocks Arithmetic actions and Difficulty on settings page
			update_option( 'gllr_options', $gllr_options, '', 'yes' );
			$message = __( "Settings are saved", 'gallery' );
		}

		if ( ! file_exists( get_stylesheet_directory() .'/gallery-template.php' ) || ! file_exists( get_stylesheet_directory() .'/gallery-single-template.php' ) ) {
			gllr_plugin_install();
		}
		if ( ! file_exists( get_stylesheet_directory() .'/gallery-template.php' ) || ! file_exists( get_stylesheet_directory() .'/gallery-single-template.php' ) ) {
			$error .= __( 'The following files "gallery-template.php" and "gallery-single-template.php" were not found in the directory of your theme. Please copy them from the directory `/wp-content/plugins/gallery-plugin/template/` to the directory of your theme for the correct work of the Gallery plugin', 'gallery' );
		}
		// Display form on the setting page
	?>
	<div class="wrap">
		<div class="icon32 icon32-bws" id="icon-options-general"></div>
		<h2><?php _e( 'Gallery Settings', 'gallery' ); ?></h2>
		<div class="updated fade" <?php if( ! isset( $_REQUEST['gllr_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
		<div class="error" <?php if( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
		<p><?php _e( "If you would like to add a Single Gallery to your page or post, just copy and paste this shortcode into your post or page:", 'gallery' ); ?> [print_gllr id=Your_gallery_post_id]</p>
		<form method="post" action="admin.php?page=gallery-plugin.php" id="gllr_form_image_size">
			<table class="form-table">
				<tr valign="top" class="gllr_width_labels">
					<th scope="row"><?php _e( 'Image size for the album cover', 'gallery' ); ?> </th>
					<td>
						<label for="custom_image_size_name"><?php _e( 'Image size', 'gallery' ); ?></label> <?php echo $gllr_options["gllr_custom_size_name"][0]; ?><br />
						<label for="custom_image_size_w"><?php _e( 'Width (in px)', 'gallery' ); ?></label> <input type="text" name="gllr_custom_image_size_w_album" value="<?php echo $gllr_options["gllr_custom_size_px"][0][0]; ?>" /><br />
						<label for="custom_image_size_h"><?php _e( 'Height (in px)', 'gallery' ); ?></label> <input type="text" name="gllr_custom_image_size_h_album" value="<?php echo $gllr_options["gllr_custom_size_px"][0][1]; ?>" />
					</td>
				</tr>
				<tr valign="top" class="gllr_width_labels">
					<th scope="row"><?php _e( 'Gallery image size', 'gallery' ); ?> </th>
					<td>
						<label for="custom_image_size_name"><?php _e( 'Image size', 'gallery' ); ?></label> <?php echo $gllr_options["gllr_custom_size_name"][1]; ?><br />
						<label for="custom_image_size_w"><?php _e( 'Width (in px)', 'gallery' ); ?></label> <input type="text" name="gllr_custom_image_size_w_photo" value="<?php echo $gllr_options["gllr_custom_size_px"][1][0]; ?>" /><br />
						<label for="custom_image_size_h"><?php _e( 'Height (in px)', 'gallery' ); ?></label> <input type="text" name="gllr_custom_image_size_h_photo" value="<?php echo $gllr_options["gllr_custom_size_px"][1][1]; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2"><span style="color: #888888;font-size: 10px;"><?php _e( 'WordPress will create a new thumbnail with the specified dimensions when you upload a new photo.', 'gallery' ); ?></span></td>
				</tr>
			</table>
			<table class="form-table gllr_pro_version" style="float: left;">
				<tr valign="top" class="gllr_width_labels">
					<th scope="row"><?php _e( 'Gallery image size in the lightbox', 'gallery' ); ?> </th>
					<td>
						<label for="custom_image_size_name"><?php _e( 'Image size', 'gallery' ); ?></label> full-photo<br />
						<label for="custom_image_size_w"><?php _e( 'Max width (in px)', 'gallery' ); ?></label> <input disabled class="gllrprfssnl_size_photo_full" type="text" name="gllrprfssnl_custom_image_size_w_full" value="1024"/><br />
						<label for="custom_image_size_h"><?php _e( 'Max height (in px)', 'gallery' ); ?></label> <input disabled class="gllrprfssnl_size_photo_full" type="text" name="gllrprfssnl_custom_image_size_h_full" value="1024"/><br />
						<input disabled type="checkbox" name="gllrprfssnl_size_photo_full" value="1" /> <?php _e( 'Display a full size image in the lightbox', 'gallery' ); ?>
					</td>
				</tr>
				<tr valign="top" class="gllr_pro_version gllr_width_labels">
					<th scope="row"><?php _e( 'Crop position', 'gallery' ); ?></th>
					<td>
						<label><?php _e( 'Horizontal', 'gallery' ); ?></label> 
						<select disabled>
							<option value="left"><?php _e( 'left', 'gallery' ); ?></option>
							<option value="center"><?php _e( 'center', 'gallery' ); ?></option>
							<option value="right"><?php _e( 'right', 'gallery' ); ?></option>
						</select>
						<br />
						<label><?php _e( 'Vertical', 'gallery' ); ?></label> 
						<select disabled>							
							<option value="top"><?php _e( 'top', 'gallery' ); ?></option>
							<option value="center"><?php _e( 'center', 'gallery' ); ?></option>
							<option value="bottom"><?php _e( 'bottom', 'gallery' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Lightbox background', 'gallery' ); ?> </th>	
					<td>					
						<input disabled class="button button-small gllrprfssnl_lightbox_default" type="button" value="<?php _e( 'Default', 'gallery' ); ?>"> <br />
						<input disabled type="text" size="8" value="0.7" name="gllrprfssnl_background_lightbox_opacity" /> <?php _e( 'Background transparency (from 0 to 1)', 'gallery' ); ?><br />
						<?php if( $wp_version >= 3.5 ) { ?>
							<input disabled id="gllrprfssnl_background_lightbox_color" type="minicolors" name="gllrprfssnl_background_lightbox_color" value="#777777" id="gllrprfssnl_background_lightbox_color" /> <?php _e( 'Select a background color', 'gallery' ); ?>
						<?php } else { ?>
							<input disabled id="gllrprfssnl_background_lightbox_color" type="text" name="gllrprfssnl_background_lightbox_color" value="#777777" id="gllrprfssnl_background_lightbox_color" /><span id="gllrprfssnl_background_lightbox_color_small" style="background-color:#777777"></span> <?php _e( 'Background color', 'gallery' ); ?>
							<div id="colorPickerDiv_backgraund" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
						<?php } ?>
					</td>
				</tr>		
			</table>					
			<div class="gllr_pro_version_tooltip">				
				<?php _e( 'This functionality is available in the Pro version of the plugin. For more details, please follow the link', 'gallery' ); ?> 
				<a href="http://bestwebsoft.com/plugin/gallery-pro/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $plugin_info["Version"]; ?>" target="_blank" title="Gallery Pro Plugin">
					Gallery Pro Plugin
				</a>	
			</div>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Images with border', 'gallery' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_border_images" value="1" <?php if( $gllr_options["border_images"] == 1 ) echo 'checked="checked"'; ?> /> <br />
						<input type="text" value="<?php echo $gllr_options["border_images_width"]; ?>" name="gllr_border_images_width" /> <?php _e( 'Border width in px, just numbers', 'gallery' ); ?><br />
						<?php if( $wp_version >= 3.5 ) { ?>
							<input type="minicolors" name="gllr_border_images_color" value="<?php echo $gllr_options["border_images_color"]; ?>" id="gllr_border_images_color" /> <?php _e( 'Select a border color', 'gallery' ); ?>
						<?php } else { ?>
							<input type="text" name="gllr_border_images_color" value="<?php echo $gllr_options["border_images_color"]; ?>" id="gllr_border_images_color" /><span id="gllr_border_images_color_small" style="background-color:<?php echo $gllr_options["border_images_color"]; ?>"></span> <?php _e( 'Select a border color', 'gallery' ); ?>
							<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
						<?php } ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Number of images in the row', 'gallery' ); ?> </th>
					<td>
						<input type="text" name="gllr_custom_image_row_count" value="<?php echo $gllr_options["custom_image_row_count"]; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Start slideshow', 'gallery' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_start_slideshow" value="1" <?php if( $gllr_options["start_slideshow"] == 1 ) echo 'checked="checked"'; ?> />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Slideshow interval', 'gallery' ); ?> </th>
					<td>
						<input type="text" name="gllr_slideshow_interval" value="<?php echo $gllr_options["slideshow_interval"]; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Sort images by', 'gallery' ); ?> </th>
					<td>
						<input type="radio" name="gllr_order_by" value="ID" <?php if( $gllr_options["order_by"] == 'ID' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order_by"><?php _e( 'Attachment ID', 'gallery' ); ?></label><br />
						<input type="radio" name="gllr_order_by" value="title" <?php if( $gllr_options["order_by"] == 'title' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order_by"><?php _e( 'Image Name', 'gallery' ); ?></label><br />
						<input type="radio" name="gllr_order_by" value="date" <?php if( $gllr_options["order_by"] == 'date' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order_by"><?php _e( 'Date', 'gallery' ); ?></label><br />
						<input type="radio" name="gllr_order_by" value="menu_order" <?php if( $gllr_options["order_by"] == 'menu_order' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order_by"><?php _e( 'Sorting order (the input field for sorting order in the Insert / Upload Media Gallery dialog)', 'gallery' ); ?></label><br />
						<input type="radio" name="gllr_order_by" value="rand" <?php if( $gllr_options["order_by"] == 'rand' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order_by"><?php _e( 'Random', 'gallery' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Sort images', 'gallery' ); ?> </th>
					<td>
						<input type="radio" name="gllr_order" value="ASC" <?php if( $gllr_options["order"] == 'ASC' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order"><?php _e( 'ASC (ascending order from lowest to highest values - 1, 2, 3; a, b, c)', 'gallery' ); ?></label><br />
						<input type="radio" name="gllr_order" value="DESC" <?php if( $gllr_options["order"] == 'DESC' ) echo 'checked="checked"'; ?> /> <label class="label_radio" for="order"><?php _e( 'DESC (descending order from highest to lowest values - 3, 2, 1; c, b, a)', 'gallery' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Display text above the image', 'gallery' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_image_text" value="1" <?php if( $gllr_options["image_text"] == 1 ) echo 'checked="checked"'; ?> /> <?php _e( 'Turn off the checkbox, if you want to display text just in a lightbox', 'gallery' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Display the Back link', 'gallery' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_return_link" value="1" <?php if( $gllr_options["return_link"] == 1 ) echo 'checked="checked"'; ?> />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Display the Back link in the shortcode', 'gallery' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_return_link_shortcode" value="1" <?php if( $gllr_options["return_link_shortcode"] == 1 ) echo 'checked="checked"'; ?> />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'The Back link text', 'gallery' ); ?> </th>
					<td>
						<input type="text" name="gllr_return_link_text" value="<?php echo $gllr_options["return_link_text"]; ?>" style="width:200px;" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'The Back link URL', 'gallery' ); ?> </th>
					<td>
						<input type="radio" value="gallery_template_url" name="gllr_return_link_page" <?php if( $gllr_options["return_link_page"] == 'gallery_template_url' ) echo 'checked="checked"'; ?> /><?php _e( 'Gallery page (Page with Gallery Template)', 'gallery'); ?><br>
						<input type="radio" value="custom_url" name="gllr_return_link_page" id="gllr_return_link_url" <?php if( $gllr_options["return_link_page"] == 'custom_url' ) echo 'checked="checked"'; ?> /> <input type="text" onfocus="document.getElementById('gllr_return_link_url').checked = true;" value="<?php echo $gllr_options["return_link_url"]; ?>" name="gllr_return_link_url">
						<?php _e( '(Full URL to custom page)' , 'gallery'); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'The Read More link text', 'gallery' ); ?> </th>
					<td>
						<input type="text" name="gllr_read_more_link_text" value="<?php echo $gllr_options["read_more_link_text"]; ?>" style="width:200px;" />
					</td>
				</tr>
			</table> 
			<table class="form-table gllr_pro_version" style="float: left;">
				<tr valign="top" class="gllr_width_labels">
					<th scope="row"><?php _e( 'Display Like buttons in the lightbox', 'gallery' ); ?> </th>
					<td>
						<input disabled type="checkbox" name="gllrprfssnl_like_button_fb" value="1" /> <?php _e( 'FaceBook', 'gallery' ); ?><br />
						<input disabled type="checkbox" name="gllrprfssnl_like_button_twit" value="1" /> <?php _e( 'Twitter', 'gallery' ); ?><br />
						<input disabled type="checkbox" name="gllrprfssnl_like_button_pint" value="1" /> <?php _e( 'Pinterest', 'gallery' ); ?><br />
						<input disabled type="checkbox" name="gllrprfssnl_like_button_g_plusone" value="1" /> <?php _e( 'Google +1', 'gallery' ); ?>		
					</td>
				</tr>
				<tr valign="top" class="gllr_width_labels">
					<th scope="row"><?php _e( 'Slug for gallery item', 'gallery' ); ?></th>
					<td>
						<input type="text" name="gllrprfssnl_slug" value="gallery" disabled /> <span style="color: #888888;font-size: 10px;"><?php _e( 'for any structure of permalinks except the default structure', 'gallery' ); ?></span>
					</td>	
				</tr>					
			</table>					
			<div class="gllr_pro_version_tooltip">				
				<?php _e( 'This functionality is available in the Pro version of the plugin. For more details, please follow the link', 'gallery' ); ?> 
				<a href="http://bestwebsoft.com/plugin/gallery-pro/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $plugin_info["Version"]; ?>" target="_blank" title="Gallery Pro Plugin">
					Gallery Pro Plugin
				</a>	
			</div> 
			<div style="clear:both;"></div>
			<input type="hidden" name="gllr_form_submit" value="submit" />
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
			</p>
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'gllr_nonce_name' ); ?>
		</form>
	</div>
	<?php } 
}

if ( ! function_exists( 'gllr_register_plugin_links' ) ) {
	function gllr_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=gallery-plugin.php">' . __( 'Settings', 'gallery' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/extend/plugins/gallery-plugin/faq/" target="_blank">' . __( 'FAQ', 'gallery' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'gallery' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'gllr_plugin_action_links' ) ) {
	function gllr_plugin_action_links( $links, $file ) {
			//Static so we don't call plugin_basename on every plugin row.
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );

		if ( $file == $this_plugin ){
				 $settings_link = '<a href="admin.php?page=gallery-plugin.php">' . __( 'Settings', 'gallery' ) . '</a>';
				 array_unshift( $links, $settings_link );
			}
		return $links;
	} // end function gllr_plugin_action_links
}

if ( ! function_exists ( 'gllr_add_admin_script' ) ) {
	function gllr_add_admin_script() { 
		global $wp_version;

		if ( $wp_version >= 3.5 && isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'gallery-plugin.php' ) { ?>
			<link rel="stylesheet" media="screen" type="text/css" href="<?php echo plugins_url( 'minicolors/jquery.miniColors.css', __FILE__ ); ?>" />
			<script type="text/javascript" src="<?php echo plugins_url( 'minicolors/jquery.miniColors.js', __FILE__ ); ?>"></script>
		<?php } ?>
		<script type="text/javascript">
		(function($) {
			$(document).ready(function(){
				$('.gllr_image_block img').css('cursor', 'all-scroll' );
				$('.gllr_order_message').removeClass('hidden');
				var d=false;
				$( '#Upload-File .gallery' ).sortable({
					stop: function(event, ui) { 
						$('.gllr_order_text').removeClass('hidden');
						var g=$('#Upload-File .gallery').sortable('toArray');
						var f=g.length;
						$.each(		g,
							function( k,l ){
									var j=d?(f-k):(1+k);
									$('.gllr_order_text[name^="gllr_order_text['+l+']"]').val(j);
							}
						)
					}
				});
				<?php if( $wp_version < 3.5 && $_REQUEST['page'] == 'gallery-plugin.php' ) { ?>
				var gllr_farbtastic = $.farbtastic('#colorPickerDiv', function(color) {
					gllr_farbtastic.setColor(color);
					$('#gllr_border_images_color').val(color);
					$('#gllr_border_images_color_small').css('background-color', color);
				});
				$('#gllr_border_images_color').click(function(){
					$('#colorPickerDiv').show();				
				});
				$('#gllr_border_images_color_small').click(function(){
					$('#colorPickerDiv').show();				
				});
				$(document).mousedown(function(){
					$('#colorPickerDiv').each(function(){
						var display = $(this).css('display');
						if ( display == 'block' )
							jQuery(this).fadeOut(2);
					});
				});
				<?php } ?>
			});
		})(jQuery);
		</script>
	<?php }
}

if ( ! function_exists ( 'gllr_admin_head' ) ) {
	function gllr_admin_head() {
		global $wp_version;
		wp_enqueue_style( 'gllrStylesheet', plugins_url( 'css/stylesheet.css', __FILE__ ) );
		wp_enqueue_style( 'gllrFileuploaderCss', plugins_url( 'upload/fileuploader.css', __FILE__ ) );
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'jquery' );
		if ( $wp_version < 3.5 ) {
			wp_enqueue_script( 'farbtastic' );
		} 
		wp_enqueue_script( 'jquery-ui-sortable' );	 
		wp_enqueue_script( 'gllrFileuploaderJs', plugins_url( 'upload/fileuploader.js', __FILE__ ), array( 'jquery' ) );

		if ( isset( $_GET['page'] ) && $_GET['page'] == "bws_plugins" )
			wp_enqueue_script( 'bws_menu_script', plugins_url( 'js/bws_menu.js' , __FILE__ ) );
	}
}

if ( ! function_exists ( 'gllr_wp_head' ) ) {
	function gllr_wp_head() {
		wp_enqueue_style( 'gllrStylesheet', plugins_url( 'css/stylesheet.css', __FILE__ ) );
		wp_enqueue_style( 'gllrFancyboxStylesheet', plugins_url( 'fancybox/jquery.fancybox-1.3.4.css', __FILE__ ) );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'gllrFancyboxMousewheelJs', plugins_url( 'fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__ ), array( 'jquery' ) ); 
		wp_enqueue_script( 'gllrFancyboxJs', plugins_url( 'fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__ ), array( 'jquery' ) ); 
	}
}

if ( ! function_exists ( 'gllr_shortcode' ) ) {
	function gllr_shortcode( $attr ) {
		extract( shortcode_atts( array(
				'id'	=> '',
				'display' => 'full'
			), $attr ) 
		);
		$args = array(
			'post_type'			=> 'gallery',
			'post_status'		=> 'publish',
			'p'					=> $id,
			'posts_per_page'	=> 1
		);	
		ob_start();
		$second_query = new WP_Query( $args ); 
		$gllr_options = get_option( 'gllr_options' );
		if ( $display == 'short' ) { ?>
				<div class="gallery_box">
				<ul>
				<?php 
					global $post, $wpdb, $wp_query;
					if ( $second_query->have_posts() ) : $second_query->the_post();
						$attachments = get_post_thumbnail_id( $post->ID );
							if( empty ( $attachments ) ) {
								$attachments = get_children( 'post_parent='.$post->ID.'&post_type=attachment&post_mime_type=image&numberposts=1' );
								$id = key( $attachments );
								$image_attributes = wp_get_attachment_image_src( $id, 'album-thumb' );
							}
							else {
								$image_attributes = wp_get_attachment_image_src( $attachments, 'album-thumb' );
							}
							?>
							<li>
								<img style="width:<?php echo $gllr_options['gllr_custom_size_px'][0][0]; ?>px;" alt="<?php echo $post->post_title; ?>" title="<?php echo $post->post_title; ?>" src="<?php echo $image_attributes[0]; ?>" />
								<div class="gallery_detail_box">
									<div><?php echo $post->post_title; ?></div>
									<div><?php echo the_excerpt_max_charlength( 100 ); ?></div>
									<a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $gllr_options["read_more_link_text"]; ?></a>
								</div>
								<div class="clear"></div>
							</li>
				<?php endif; ?>
				</ul></div>
		<?php } else { 
		if ( $second_query->have_posts() ) : 
			while ( $second_query->have_posts() ) : 
				global $post;
				$second_query->the_post(); ?>
				<div class="gallery_box_single">
					<?php the_content(); 
					$posts = get_posts( array(
						"showposts"			=> -1,
						"what_to_show"	=> "posts",
						"post_status"		=> "inherit",
						"post_type"			=> "attachment",
						"orderby"				=> $gllr_options['order_by'],
						"order"					=> $gllr_options['order'],
						"post_mime_type"=> "image/jpeg,image/gif,image/jpg,image/png",
						"post_parent"		=> $post->ID
					));
					if( count( $posts ) > 0 ) {
						$count_image_block = 0; ?>
						<div class="gallery clearfix">
							<?php foreach( $posts as $attachment ) { 
								$key = "gllr_image_text";
								$link_key = "gllr_link_url";
								$alt_tag_key = "gllr_image_alt_tag";
								$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'photo-thumb' );
								$image_attributes_large = wp_get_attachment_image_src( $attachment->ID, 'large' );
								$image_attributes_full = wp_get_attachment_image_src( $attachment->ID, 'full' );
								if ( 1 == $gllr_options['border_images'] ) {
									$gllr_border = 'border-width: '.$gllr_options['border_images_width'].'px; border-color:'.$gllr_options['border_images_color'].'';
									$gllr_border_images = $gllr_options['border_images_width'] * 2;
								} else {
									$gllr_border = '';
									$gllr_border_images = 0;
								}
								if ( $count_image_block % $gllr_options['custom_image_row_count'] == 0 ) { ?>
								<div class="gllr_image_row">
								<?php } ?>
									<div class="gllr_image_block">
										<p style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]+$gllr_border_images; ?>px;height:<?php echo $gllr_options['gllr_custom_size_px'][1][1]+$gllr_border_images; ?>px;">
											<?php if( ( $url_for_link = get_post_meta( $attachment->ID, $link_key, true ) ) != "" ) { ?>
													<a href="<?php echo $url_for_link; ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" target="_blank">
														<img style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>px;height:<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php echo get_post_meta( $attachment->ID, $alt_tag_key, true ); ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" src="<?php echo $image_attributes[0]; ?>" />
													</a>
												<?php } else { ?>
											<a rel="gallery_fancybox" href="<?php echo $image_attributes_large[0]; ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>">
												<img style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]; ?>px;height:<?php echo $gllr_options['gllr_custom_size_px'][1][1]; ?>px; <?php echo $gllr_border; ?>" alt="<?php echo get_post_meta( $attachment->ID, $alt_tag_key, true ); ?>" title="<?php echo get_post_meta( $attachment->ID, $key, true ); ?>" src="<?php echo $image_attributes[0]; ?>" rel="<?php echo $image_attributes_full[0]; ?>" />
											</a>
												<?php } ?>
										</p>
										<div style="width:<?php echo $gllr_options['gllr_custom_size_px'][1][0]+$gllr_border_images; ?>px; <?php if( 0 == $gllr_options["image_text"] ) echo "visibility:hidden;"; ?>" class="gllr_single_image_text"><?php echo get_post_meta( $attachment->ID, $key, true ); ?>&nbsp;</div>
									</div>
								<?php if( $count_image_block%$gllr_options['custom_image_row_count'] == $gllr_options['custom_image_row_count']-1 ) { ?>
								</div>
								<?php } 
								$count_image_block++; 
							} 
							if( $count_image_block > 0 && $count_image_block%$gllr_options['custom_image_row_count'] != 0 ) { ?>
								</div>
							<?php } ?>
							</div>
						<?php } ?>
					</div>
					<div class="clear"></div>
			<?php endwhile; 
		else: ?>
			<div class="gallery_box_single">
				<p class="not_found"><?php _e( 'Sorry, nothing found.', 'gallery' ); ?></p>
			</div>
		<?php endif; ?>
		<?php if( 1 == $gllr_options['return_link_shortcode'] ) {
			if( 'gallery_template_url' == $gllr_options["return_link_page"] ){
				global $wpdb;
				$parent = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = 'gallery-template.php' AND (post_status = 'publish' OR post_status = 'private') AND $wpdb->posts.ID = $wpdb->postmeta.post_id" ); ?>
				<div class="return_link"><a href="<?php echo ( !empty( $parent ) ? get_permalink( $parent ) : '' ); ?>"><?php echo $gllr_options['return_link_text']; ?></a></div>
			<?php } else { ?>
				<div class="return_link"><a href="<?php echo $gllr_options["return_link_url"]; ?>"><?php echo $gllr_options['return_link_text']; ?></a></div>
			<?php }
		} ?>
		<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$("a[rel=gallery_fancybox]").fancybox({
					'transitionIn'		: 'elastic',
					'transitionOut'		: 'elastic',
					'titlePosition' 	: 'inside',
					'speedIn'					:	500, 
					'speedOut'				:	300,
					'titleFormat'			: function(title, currentArray, currentIndex, currentOpts) {
						return '<span id="fancybox-title-inside">' + (title.length ? title + '<br />' : '') + 'Image ' + (currentIndex + 1) + ' / ' + currentArray.length + '</span><?php if( get_post_meta( $post->ID, 'gllr_download_link', true ) != '' ){?><br /><a href="'+$(currentOpts.orig).attr('rel')+'" target="_blank"><?php echo __('Download high resolution image', 'gallery'); ?> </a><?php } ?>';
					}<?php if( $gllr_options['start_slideshow'] == 1 ) { ?>,
					'onComplete':	function() {
						clearTimeout(jQuery.fancybox.slider);
						jQuery.fancybox.slider=setTimeout("jQuery.fancybox.next()",<?php echo empty( $gllr_options['slideshow_interval'] )? 2000 : $gllr_options['slideshow_interval'] ; ?>);
					}<?php } ?>
				});
			});
		})(jQuery);
		</script>
	<?php }
		$gllr_output = ob_get_clean();
		wp_reset_query();
		return $gllr_output;
	}
}

if ( ! function_exists( 'upload_gallery_image' ) ) {
	function upload_gallery_image() {
			class qqUploadedFileXhr {
				/**
				 * Save the file to the specified path
				 * @return boolean TRUE on success
				 */
				function save( $path ) {
					$input = fopen( "php://input", "r" );
					$temp = tmpfile();
					$realSize = stream_copy_to_stream( $input, $temp );
					fclose( $input );
				 
					if ( $realSize != $this->getSize() ){            
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
			function save($path) {
				if( ! move_uploaded_file( $_FILES['qqfile']['tmp_name'], $path ) ){
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

			function __construct( array $allowedExtensions = array(), $sizeLimit = 10485760 ){        
				$allowedExtensions = array_map( "strtolower", $allowedExtensions );
				    
				$this->allowedExtensions = $allowedExtensions;        
				$this->sizeLimit = $sizeLimit;
				
				//$this->checkServerSettings();       

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
				$last = strtolower( $str[strlen( $str )-1] );
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
				$filename = str_replace( ".".$ext, "", $pathinfo['basename'] );
				//$filename = md5(uniqid());

				if( $this->allowedExtensions && ! in_array( strtolower( $ext ), $this->allowedExtensions ) ){
				    $these = implode( ', ', $this->allowedExtensions );
				    return "{error:'File has an invalid extension, it should be one of $these .'}";
				}
				
				if( ! $replaceOldFile ){
				    /// don't overwrite previous files that were uploaded
				    while ( file_exists( $uploadDirectory . $filename . '.' . $ext ) ) {
				        $filename .= rand( 10, 99 );
				    }
				}

				if ( $this->file->save( $uploadDirectory . $filename . '.' . $ext ) ){						 
						list( $width, $height, $type, $attr ) = getimagesize( $uploadDirectory . $filename . '.' . $ext );
				    return "{success:true,width:".$width.",height:".$height."}";
				} else {
				    return "{error:'Could not save uploaded file. The upload was cancelled, or server error encountered'}";
				}
					
			}
		}

		// list of valid extensions, ex. array("jpeg", "xml", "bmp")
		$allowedExtensions = array( "jpeg", "jpg", "gif", "png" );
		// max file size in bytes
		$sizeLimit = 10 * 1024 * 1024;

		$uploader = new qqFileUploader( $allowedExtensions, $sizeLimit );
		$result = $uploader->handleUpload( plugin_dir_path( __FILE__ ) . 'upload/files/' );

		// to pass data through iframe you will need to encode all html tags
		echo $result;
		die(); // this is required to return a proper result
	}
}

if ( ! function_exists( 'gllr_add_for_ios' ) ) {
	function gllr_add_for_ios() { ?>
		<!-- Start ios -->
		<script type="text/javascript">
			(function($){
				$(document).ready(function(){
					$('#fancybox-overlay').css({
						'width' : $(document).width()
					});	
				});	
			})(jQuery);
		</script>
		<!-- End ios -->
	<?php
	}
}

if ( ! function_exists ( 'gllr_plugin_banner' ) ) {
	function gllr_plugin_banner() {
		global $hook_suffix;	
		$plugin_info = get_plugin_data( __FILE__ );		
		if ( $hook_suffix == 'plugins.php' ) {              
	       	echo '<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
	       		<script type="text/javascript" src="' . plugins_url( 'js/c_o_o_k_i_e.js', __FILE__ ).'"></script>
				<script type="text/javascript">		
					(function($){
						$(document).ready(function(){		
							var hide_message = $.cookie("gllr_hide_banner_on_plugin_page");
							if ( hide_message == "true") {
								$(".gllr_message").css("display", "none");
							};
							$(".gllr_close_icon").click(function() {
								$(".gllr_message").css("display", "none");
								$.cookie( "gllr_hide_banner_on_plugin_page", "true", { expires: 32 } );
							});	
						});
					})(jQuery);				
				</script>					                      
				<div class="gllr_message">
					<a class="button gllr_button" target="_blank" href="http://bestwebsoft.com/plugin/gallery-pro/?k=01a04166048e9416955ce1cbe9d5ca16&pn=79&v=' . $plugin_info["Version"] . '">Learn More</a>				
					<div class="gllr_text">
						It’s time to upgrade your <strong>Gallery plugin</strong> to <strong>PRO</strong> version!<br />
						<span>Extend standard plugin functionality with new great options.</span>
					</div> 					
					<img class="gllr_close_icon" title="" src="' . plugins_url( 'images/close_banner.png', __FILE__ ) . '" alt=""/>
					<img class="gllr_icon" title="" src="' . plugins_url( 'images/banner.png', __FILE__ ) . '" alt=""/>	
				</div>  
			</div>';      
		}
	}
}

register_activation_hook( __FILE__, 'gllr_plugin_install' ); // activate plugin
register_uninstall_hook( __FILE__, 'gllr_plugin_uninstall' ); // deactivate plugin

// adds "Settings" link to the plugin action page
add_filter( 'plugin_action_links', 'gllr_plugin_action_links', 10, 2 );
//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'gllr_register_plugin_links', 10, 2 );

add_action( 'admin_menu', 'add_gllr_admin_menu' );
add_action( 'init', 'gllr_plugin_init' );

add_action( 'init', 'post_type_images' ); // register post type

add_filter( 'rewrite_rules_array', 'gllr_custom_permalinks' ); // add custom permalink for gallery
add_action( 'wp_loaded', 'gllr_flush_rules' );

add_action( 'admin_init', 'gllr_admin_error' );

add_action( 'template_redirect', 'gllr_template_redirect' ); // add themplate for single gallery page

add_action( 'save_post', 'gllr_save_postdata', 1, 2 ); // save custom data from admin 

add_filter( 'nav_menu_css_class', 'addImageAncestorToMenu' );
add_filter( 'page_css_class', 'gllr_page_css_class', 10, 2 );

add_filter( 'manage_gallery_posts_columns', 'gllr_change_columns' );
add_action( 'manage_gallery_posts_custom_column', 'gllr_custom_columns', 10, 2 );

add_action( 'admin_head', 'gllr_add_admin_script' );
add_action( 'admin_enqueue_scripts', 'gllr_admin_head' );
add_action( 'wp_enqueue_scripts', 'gllr_wp_head' );

add_shortcode( 'print_gllr', 'gllr_shortcode' );

add_action( 'wp_ajax_upload_gallery_image', 'upload_gallery_image' );

add_action( 'wp_head', 'gllr_add_for_ios' );

add_action('admin_notices', 'gllr_plugin_banner');
?>