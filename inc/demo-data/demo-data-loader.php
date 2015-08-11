<?php

/**
 * Load demo data
 * @version 1.0.1
 */

/**
 * Get plugin text domain and prefix
 * @return void
 */
if ( ! function_exists( 'bws_get_plugin_data' ) ) {
	function bws_get_plugin_data() {
		global $bws_plugin_text_domain, $bws_plugin_prefix, $bws_plugin_file, $bws_plugin_name;
		$plugin_dir_array      = explode( '/', plugin_basename( __FILE__ ) );
		$plugin_dir            = $plugin_dir_array[0];
		$bws_plugin_file_array = array_keys( get_plugins( "/" . $plugin_dir ) );
		$bws_plugin_file       = $bws_plugin_file_array[0];
		switch( $bws_plugin_file ) {
			case 'gallery-plugin.php':
				$bws_plugin_text_domain = 'gallery';
				$bws_plugin_prefix      = 'gllr_';
				break;
			case 'gallery-plugin-pro.php':
				$bws_plugin_text_domain = 'gallery_pro';
				$bws_plugin_prefix      = 'gllrprfssnl_';
				break;
			case 'portfolio.php':
				$bws_plugin_text_domain = 'portfolio';
				$bws_plugin_prefix      = 'prtfl_';
				break;
			case 'portfolio-pro.php':
				$bws_plugin_text_domain = 'portfolio-pro';
				$bws_plugin_prefix      = 'prtflpr_';
				break;
			case 'quotes-and-tips.php':
				$bws_plugin_text_domain = 'quotes_and_tips';
				$bws_plugin_prefix      = 'qtsndtps_';
				break;
			case 'realty.php':
				$bws_plugin_text_domain = 'realty';
				$bws_plugin_prefix      = 'rlt_';
				break;
			default:
				$bws_plugin_text_domain = '';
				$bws_plugin_prefix      = '';
				break;
		}
		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_path     = preg_replace( "/inc\/demo-data/", '', dirname( __FILE__ ) );
		$bws_plugin_info = get_plugin_data( $plugin_path . $bws_plugin_file );
		$bws_plugin_name = $bws_plugin_info['Name'];
		/**
		 ***********************************************************
		 ** Another way to get text domain from plugin info: **
		 ***********************************************************
		 * $bws_plugin_text_domain = $bws_plugin_info['TextDomain'];
		 ***********************************************************
		 */
	}
}

/**
 * Display "Install demo data" or "Uninstal demo data" buttons
 * @return void
 */
if ( ! function_exists ( 'bws_button' ) ) {
	function bws_button() {
		if ( ! ( is_multisite() && is_network_admin() ) ) {
			global $bws_plugin_text_domain, $bws_plugin_prefix, $bws_plugin_file;
			if ( empty( $bws_plugin_prefix ) )
				bws_get_plugin_data();
			$demo_options = bws_get_demo_option();
			if ( empty( $demo_options ) ) {
				$value        = 'install';
				$button_title = __( 'Install Demo Data', $bws_plugin_text_domain );
				$form_title   = __( 'If you install the demo-data, will be created galleries with images, demo-post with available shortcodes and page with a list of all the galleries, 
plugin settings will be overwritten, however, when you delete the demo data, they will be restored.', $bws_plugin_text_domain );
			} else {
				$value        = 'remove';
				$button_title = __( 'Remove Demo Data', $bws_plugin_text_domain );
				$form_title   = __( 'Delete demo-data and restore old plugin settings.', $bws_plugin_text_domain );
			}
			$plugin_dir_array      = explode( '/', plugin_basename( __FILE__ ) );
			$plugin_dir            = $plugin_dir_array[0]; ?>
			<form method="post" action="" id="bws_handle_demo_data">
				<p><?php echo $form_title; ?></p>
				<p>
					<button class="button" name="bws_handle_demo" value="<?php echo $value; ?>"><?php echo $button_title; ?></button>
					<?php wp_nonce_field( $plugin_dir . '/' . $bws_plugin_file, 'bws_settings_nonce_name' ); ?>
				</p>
			</form>
	<?php }
	}
}

/**
 * Display page for confirmation action to install demo data
 * @return void
 */
if ( ! function_exists ( 'bws_demo_confirm' ) ) {
	function bws_demo_confirm() { 
		global $bws_plugin_text_domain;
		if ( empty( $bws_plugin_prefix ) )
			bws_get_plugin_data();
		if ( 'install' == $_POST['bws_handle_demo'] ) { 
			$button_title = __( 'Yes, install demo data', $bws_plugin_text_domain );
			$label        = __( 'Are you sure you want to install demo data?', $bws_plugin_text_domain );
		} else {
			$button_title = __( 'Yes, remove demo data', $bws_plugin_text_domain );
			$label        = __( 'Are you sure you want to remove demo data?', $bws_plugin_text_domain );
		} ?>
		<div>
			<p><?php echo $label; ?></p>
			<form method="post" action="">
				<p>
					<button class="button" name="bws_<?php echo $_POST['bws_handle_demo']; ?>_demo_confirm" value="true"><?php echo $button_title; ?></button>
					<button class="button" name="bws_<?php echo $_POST['bws_handle_demo']; ?>_demo_deny" value="true"><?php _e( 'No, go back to the settings page', $bws_plugin_text_domain ) ?></button>
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'bws_settings_nonce_name' ); ?>
				</p>
			</form>
		</div>
	<?php }
}

if ( ! function_exists( 'bws_handle_demo_data' ) ) {
	function bws_handle_demo_data( $callback ) {
		if ( isset( $_POST['bws_install_demo_confirm'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'bws_settings_nonce_name' ) )
			return bws_install_demo_data();
		elseif ( isset( $_POST['bws_remove_demo_confirm'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'bws_settings_nonce_name' ) ) 
			return bws_remove_demo_data( $callback );
	}
}

/**
 * Load demo data
 * @return $messge   array()   message about the result of the query
 */
if ( ! function_exists ( 'bws_install_demo_data' ) ) {
	function bws_install_demo_data() {
		global $bws_plugin_text_domain, $bws_plugin_prefix, $wpdb;
		if ( empty( $bws_plugin_prefix ) )
			bws_get_plugin_data();
		/* get demo data*/
		$demo_data = array();
		$message   = array(
			'error'   => null,
			'done'    => null,
			'options' => null
		);
		$demo_options = array(
			'posts'       => null,
			'attachments' => null,
			'image_sizes' => null,
			'options'     => null
		);
		$error   = 0;
		$page_id = $gallery_post_id = $post_id = '';
		/* get demo data */
		@include_once( dirname( __FILE__ ) . '/demo-data.php' );
		$demo_data = apply_filters( 'bws_get_demo_data', '' );
		/* 
		 * load demo data 
		 */
		if ( empty( $demo_data ) ) {
			$message['error'] = __( 'Can not get demo data.', $bws_plugin_text_domain );
		} else {
			/* 
			 * check if demo options already loaded 
			 */
			if ( bws_get_demo_option() ) {
				$message['error'] = __( 'Demo options already installed.', $bws_plugin_text_domain );
				return $message;
			}

			/*
			 * load demo options 
			 */
			$plugin_options = get_option( $bws_plugin_prefix . 'options' );
			/* remember old plugin options */
			if ( ! empty( $plugin_options ) )
				$demo_options['options'] = $plugin_options;
			if ( ! empty( $demo_data['options'] ) )
				update_option( $bws_plugin_prefix . 'options', $demo_data['options'] );

			/*
			 * Add custom image sizes
			 */
			if ( isset( $demo_data['image_sizes'] ) && ( ! empty( $demo_data['image_sizes'] ) ) && function_exists( 'add_image_size' ) ) {
				foreach( $demo_data['image_sizes'] as $key => $value ) {
					$demo_options['image_sizes'][] = $key;
					add_image_size( $key, $value[0], $value[1], true );
				}
			}

			/*
			 * load demo posts 
			 */
			if ( 0 < count( $demo_data['posts'] ) ) {
				$wp_upload_dir      = wp_upload_dir();
				$attachments_folder = dirname( __FILE__ ) . '/images';
				/* insert current post */
				foreach ( $demo_data['posts'] as $post ) {
					if ( preg_match( '/{last_post_id}/', $post['post_content'] ) && ! ( empty( $post_id ) ) ) {
						$post['post_content'] = preg_replace( '/{last_post_id}/', $post_id, $post['post_content'] );
					}
					if ( preg_match( '/{template_page}/', $post['post_content'] ) ) {
						if ( empty( $page_id ) )
							$page_id = intval( $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` LIKE '_wp_page_template' AND `meta_value` LIKE 'gallery-template.php' LIMIT 1" ) );
						if ( ! empty( $page_id ) )
							$post['post_content'] = preg_replace( '/{template_page}/', '<a href="' . get_permalink( $page_id ) . '">' . get_the_title( $page_id ) . '</a>', $post['post_content'] );
					}
					$post_id = wp_insert_post( $post );
					if ( 'post' == $post['post_type'] )
						$gallery_post_id = $post_id;
					$attach_id = 0;
					if ( is_wp_error( $post_id ) || 0 == $post_id ) {
						$error ++;
					} else {
						/* remember post ID */
						$demo_options['posts'][] = $post_id;

						/*
						 * load post attachments 
						 */
						$attachments_list   = @scandir( $attachments_folder . '/' . $post['post_name'] );
						if ( 2 < count( $attachments_list ) ) {
							foreach ( $attachments_list as $attachment ) {
								$file = $attachments_folder . '/' . $post['post_name'] . '/' . $attachment;
								/* insert current attachment */
								if ( bws_is_image( $file ) ) {

									$destination   = $wp_upload_dir['path'] . '/' . $bws_plugin_prefix . 'demo_' . $attachment; /* path to new file */
									$wp_filetype   = wp_check_filetype( $file, null ); /* Mime-type */

									if ( copy( $file, $destination ) ) { /* if attachmebt copied */

										$attachment_data = array( 
											'post_mime_type' => $wp_filetype['type'],
											'post_title'     => $attachment,
											'post_content'   => '',
											'post_status'    => 'inherit'
										);

										/* insert attschment in to database */
										$attach_id = wp_insert_attachment( $attachment_data, $destination, $post_id );
										if ( 0 != $attach_id ) {
											/* remember attachment ID */
											$demo_options['attachments'][] = $attach_id;

											/* insert attachment metadata */
											$attach_data = wp_generate_attachment_metadata( $attach_id, $destination );
											wp_update_attachment_metadata( $attach_id, $attach_data );
											/* insert additional metadata */
											if ( isset( $demo_data['attachments'][ $attachment ] ) ) {
												foreach ( $demo_data['attachments'][ $attachment ] as $meta_key => $meta_value ) {
													if ( '{get_lorem_ipsum}' == $meta_value )
														$meta_value = bws_get_lorem_ipsum();
													add_post_meta( $attach_id, $meta_key, $meta_value );
												}
											}
										} else { 
											$error ++;
										}
									} else {
										$error ++;
									}
								}
							}
						}
						/* insert additional post meta */
						if ( isset( $post['post_meta'] ) && ( ! empty( $post['post_meta'] ) ) ) {
							foreach ( $post['post_meta'] as $meta_key => $meta_value ) {
								add_post_meta( $post_id, $meta_key, $meta_value );
							}
						}
						/* set template for post type "page" */
						if ( isset( $post['page_template'] ) && ( ! empty( $post['page_template'] ) ) ) {
							update_post_meta( $post_id, '_wp_page_template', $post['page_template'] );
							$page_id = $post_id;
						}
						/* last inserted image is thummbnail for post */
						if ( 0 != $attach_id )
							update_post_meta( $post_id, '_thumbnail_id', $attach_id );
					}
				}

				/*
				 * Save demo options
				 */
				add_option( $bws_plugin_prefix . 'demo_options', $demo_options );
				if ( 0 == $error ) {
					$message['done'] = __( 'Demo data successfully installed.', $bws_plugin_text_domain );
					if ( ! empty( $gallery_post_id ) ) {
						$message['done'] .= '<br />' . __( 'View post with shortcodes', $bws_plugin_text_domain ) . ':&nbsp;<a href="'.  get_permalink( $gallery_post_id ) . '" target="_blank">' . get_the_title( $gallery_post_id ) . '</a>';
					}
					if ( ! empty( $page_id ) ) {
						$message['done'] .= '<br />' . __( 'View page with examples', $bws_plugin_text_domain ) . ':&nbsp;<a href="'.  get_permalink( $page_id ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>';
					}
					$message['options'] = $demo_data['options'];
				} else {
					$message['error'] = __( 'Installation of demo data with some errors occurred.', $bws_plugin_text_domain );
				}
			} else {
				$message['error'] = __( 'Posts data is missing.', $bws_plugin_text_domain );
			}
		}
		return $message;
	}
}

/**
 * Remove demo data
 * @return $messge   array()   message about the result of the query
 */
if ( ! function_exists ( 'bws_remove_demo_data' ) ) {
	function bws_remove_demo_data( $callback ) {
		global $bws_plugin_text_domain, $bws_plugin_prefix;
		if ( empty( $bws_plugin_prefix ) )
			bws_get_plugin_data();
		$error        = 0;
		$message      = array(
			'error'   => null,
			'done'    => null,
			'options' => null
		);
		$demo_options = bws_get_demo_option();

		if ( empty( $demo_options ) ) {
			$message['error'] = __( 'Demo data have already been removed.', $bws_plugin_text_domain );
		} else {

			/*
			 * Restore plugin options
			 */
			if ( isset( $demo_options['options'] ) && ( ! empty( $demo_options['options'] ) ) ) {
				$demo_options['options']['display_demo_notice'] = 0;
				update_option( $bws_plugin_prefix . 'options', $demo_options['options'] );
				call_user_func( $callback );
			}
			$done = bws_delete_demo_option();
			if ( ! $done )
				$error ++;

			/*
			 * Remove image sizes
			 */
			if ( isset( $demo_options['image_sizes'] ) && ( ! empty( $demo_options['image_sizes'] ) ) ) {
				global $_wp_additional_image_sizes;
				foreach ( $demo_options['image_sizes'] as $name ) {
					unset( $_wp_additional_image_sizes[ $name ] );
				}
			}

			/*
			 * Delete all posts
			 */
			if ( isset( $demo_options['posts'] ) && ( ! empty( $demo_options['posts'] ) ) ) {
				foreach ( $demo_options['posts'] as $post_id ) {
					$done = wp_delete_post( $post_id, true );
					if ( ! $done )
						$error ++;
				}
			}

			/*
			 * Delete all attachments
			 */
			if ( isset( $demo_options['attachments'] ) && ( ! empty( $demo_options['attachments'] ) ) ) {
				foreach ( $demo_options['attachments'] as $post_id ) {
					$done = wp_delete_attachment( $post_id, true );
					if ( ! $done )
						$error ++;
				}
			}
			if ( empty( $error ) ) {
				$message['done']    = __( 'Demo data successfully removed.', $bws_plugin_text_domain );
				$message['options'] = get_option( $bws_plugin_prefix . 'options' );
			} else {
				$message['error'] = __( 'Removing demo data with some errors occurred.', $bws_plugin_text_domain );
			}
		}
		return $message;
	}
}

/**
 * Get demo-options
 * @return array with demo-options or false
 */
if ( ! function_exists( 'bws_get_demo_option' ) ) {
	function bws_get_demo_option() {
		global $bws_plugin_prefix;
		if ( empty( $bws_plugin_prefix ) )
			bws_get_plugin_data();
		$demo_options = get_option( $bws_plugin_prefix . 'demo_options' );
		if ( empty( $demo_options ) ) {
			switch ( $bws_plugin_prefix ) {
				case "gllr_":
					$plugin_prefix = 'gllrprfssnl_';
					break;
				case "gllrprfssnl_": 
					$plugin_prefix = 'gllr_';
					break;
				case "prtfl_":
					$plugin_prefix = 'prtflpr_';
					break;
				case "prtflpr_":
					$plugin_prefix = 'prtfl_';
					break;
				case "rlt_":
				default:
					$plugin_prefix = '';
					break;
			}
			$demo_options = get_option( $plugin_prefix . 'demo_options' );
		}
		return $demo_options;
	}
}

/**
 * Delete demo-options
 * @return boolean 
 */
if ( ! function_exists( 'bws_delete_demo_option' ) ) {
	function bws_delete_demo_option() {
		global $bws_plugin_prefix;
		if ( empty( $bws_plugin_prefix ) )
			bws_get_plugin_data();
		$done = delete_option( $bws_plugin_prefix . 'demo_options' );
		if ( ! $done ) {
			switch ( $bws_plugin_prefix ) {
				case "gllr_":
					$plugin_prefix = 'gllrprfssnl_';
					break;
				case "gllrprfssnl_": 
					$plugin_prefix = 'gllr_';
					break;
				case "prtfl_":
					$plugin_prefix = 'prtflpr_';
					break;
				case "prtflpr_":
					$plugin_prefix = 'prtfl_';
					break;
				case "rlt_":
				default:
					$plugin_prefix = '';
					break;
			}
			$done = delete_option( $plugin_prefix . 'demo_options' );
		}
		return $done;
	}
}

if ( ! function_exists( 'bws_handle_demo_notice' ) ) {
	function bws_handle_demo_notice( $show_demo_notice ) { 
		if ( 1 == $show_demo_notice ) {
			global $bws_plugin_text_domain, $bws_plugin_file, $bws_plugin_prefix, $bws_plugin_name, $hook_suffix, $wp_version;
			$plugin_dir_array = explode( '/', plugin_basename( __FILE__ ) );
			$plugin_dir = $plugin_dir_array[0];
			if ( empty( $bws_plugin_text_domain ) )
				bws_get_plugin_data(); 
			if ( isset( $_POST['bws_hide_demo_notice'] ) ) {
				$plugin_options = get_option( $bws_plugin_prefix . 'options' );
				$plugin_options['display_demo_notice'] = 0;
				update_option( $bws_plugin_prefix . 'options', $plugin_options );
				return;
			}
			if ( 
				( 'plugins.php' == $hook_suffix || ( isset( $_GET['page'] ) && $bws_plugin_file == $_GET['page'] ) ) && 
				! isset( $_POST['bws_handle_demo'] ) &&
				! isset( $_POST['bws_install_demo_confirm'] )
			) { 
				if ( 4.2 > $wp_version ) { ?>
					<style type="text/css">
						#bws_handle_notice_form {
							float: right;
							width: 20px;
							height: 20px;
							margin-bottom: 0;
						}
						.bws_hide_demo_notice {
							width: 100%;
							height: 100%;
							border: none;
							background: url("<?php echo plugins_url( $plugin_dir . '/bws_menu/images/close_banner.png' ); ?>") no-repeat center center;
							box-shadow: none;
							<?php if ( 3.8 <= $wp_version ) { ?>
								position: relative;
								top: -4px;
							<?php } ?>
						}
						.bws_hide_demo_notice:hover {
							cursor: pointer;
						}
					</style>
				<?php } 
				if ( 4.2 <= $wp_version ) { ?>
					<style type="text/css">
						#bws_handle_notice_form {
							position: absolute;
							top: 3px;
							right: 0;
						}
					</style>
				<?php } ?>
				<div class="update-nag" style="position: relative;">
					<form id="bws_handle_notice_form" action="" method="post">
						<button class="notice-dismiss bws_hide_demo_notice" title="<?php _e( 'Close notice', $bws_plugin_text_domain ); ?>"></button>
						<input type="hidden" name="bws_hide_demo_notice" value="hide" />
					</form>
					<span style="margin-right: 20px;"><a href="<?php echo admin_url( 'admin.php?page=' . $bws_plugin_file . '#bws_handle_demo_data' ); ?>"><?php _e( 'Install demo data', $bws_plugin_text_domain ); ?></a>&nbsp;<?php echo __( 'for an acquaintance with the possibilities of the', $bws_plugin_text_domain ) . '&nbsp;' . $bws_plugin_name; ?>.</span>
				</div>
		<?php }
		}
	}
}

/**
 * Check if file is image
 * @param   string   $file    path to file
 * @return  boolean
 */
if ( ! function_exists ( 'bws_is_image' ) ) {
	function bws_is_image( $file ) {
		$file_data = @getimagesize( $file );
		/* if file is broken or MIME-type is not 'GIF', 'JPEG' or 'PNG' */
		return ! ( $file_data || in_array( $file_data[2], array( 1, 2, 3 ) ) ) ? false : true;
	}
}

/**
 * Generate Lorem Ipsum 
 * @return   string
 */
if ( ! function_exists( 'bws_get_lorem_ipsum' ) ) {
	function bws_get_lorem_ipsum() {
		$lorem_ipsum = array(
			"Fusce quis varius quam, non molestie dui. ",
			"Ut eu feugiat eros. Aliquam justo mauris, volutpat eu lacinia et, bibendum non velit. ",
			"Aenean in justo et nunc facilisis varius feugiat quis elit. ",
			"Proin luctus non quam in bibendum. ",
			"Sed finibus, risus eu blandit ullamcorper, sapien urna vulputate ante, quis semper magna nibh vel orci. ",
			"Nullam eu aliquam erat. ",
			"Suspendisse massa est, feugiat nec dolor non, varius finibus massa. ",
			"Sed velit justo, semper ut ante eu, feugiat ultricies velit. ",
			"Ut sed velit ut nisl laoreet malesuada vitae non elit. ",
			"Integer eu sem justo. Nunc sit amet erat tristique, mollis neque et, iaculis purus. ",
			"Vestibulum sit amet varius sapien. Quisque maximus tempor scelerisque. ",
			"Ut eleifend, felis vel rhoncus cursus, purus ipsum consectetur ex, nec elementum mauris ipsum eget quam. ",
			"Integer sem diam, iaculis in arcu vel, pulvinar scelerisque magna. ",
			"Cras rhoncus neque aliquet, molestie justo id, finibus erat. ",
			"Proin eleifend, eros et interdum faucibus, ligula dui accumsan sem, ac tristique dolor erat vel est. ",
			"Etiam ut nulla risus. Aliquam non consequat turpis, id hendrerit magna. Suspendisse potenti. ",
			"Donec fringilla libero ac sapien porta ultricies. ",
			"Donec sapien lacus, blandit vitae fermentum vitae, accumsan ut magna. ",
			"Curabitur maximus lorem lectus, eu porta ipsum fringilla eu. ",
			"Integer vitae justo ultricies, aliquam neque in, venenatis nunc. ",
			"Pellentesque non nulla venenatis, posuere erat id, faucibus leo. ",
			"Nullam fringilla sodales arcu, nec rhoncus lorem fringilla in. ",
			"Quisque consequat lorem vel nisl pharetra iaculis. Donec aliquet interdum tristique. Sed ullamcorper urna odio. ",
			"Nam dictum dictum neque id congue. ",
			"Donec quis quam id turpis condimentum condimentum. ",
			"Morbi tincidunt, nunc nec pellentesque scelerisque, tortor eros efficitur lectus, eget molestie lacus est eu est. ",
			"Morbi non augue a tellus interdum condimentum id ac enim. ",
			"In dictum velit ultricies, dictum est ac, tempus arcu. ",
			"Duis maximus, mi nec pulvinar suscipit, arcu purus vestibulum urna, ",
			"consectetur rutrum mi sapien et massa. Donec faucibus ex vel nibh consequat, ut molestie lacus elementum. ",
			"Interdum et malesuada fames ac ante ipsum primis in faucibus. ",
			"Phasellus quam dolor, convallis vel nulla sed, pretium tristique felis. ",
			"Morbi condimentum nunc vel augue tincidunt, in porttitor metus interdum. Sed nec venenatis elit. ",
			"Donec non urna dui. Maecenas sit amet venenatis eros, sed aliquam metus. ",
			"Nulla venenatis eros ac velit pellentesque, nec semper orci faucibus. ",
			"Etiam sit amet dapibus lacus, non semper erat. ",
			"Donec dolor metus, iaculis nec lacinia a, tristique sed libero. ",
			"Phasellus a quam gravida, tincidunt metus ac, eleifend odio. ",
			"Integer facilisis mauris ut velit gravida ornare. Quisque viverra sagittis lacus, non dapibus turpis iaculis sit amet. ",
			"Vestibulum vehicula pulvinar blandit. ",
			"Praesent sit amet consectetur augue, vitae tincidunt nulla. ",
			"Curabitur metus nibh, molestie vel massa in, egestas dapibus felis. ",
			"Phasellus id erat massa. Aliquam bibendum purus ac ante imperdiet, mattis gravida dui mollis. ",
			"Fusce id purus et mauris condimentum fermentum. ",
			"Fusce tempus et purus ut fringilla. Suspendisse ornare et ligula in gravida. ",
			"Nunc id nunc mauris. Curabitur auctor sodales felis, nec dapibus urna pellentesque et. ",
			"Phasellus quam dolor, convallis vel nulla sed, pretium tristique felis. ",
			"Morbi condimentum nunc vel augue tincidunt, in porttitor metus interdum. ",
			"Sed scelerisque eget mauris et sagittis. ",
			"In eget enim nec arcu malesuada malesuada. ",
			"Nulla eu odio vel nibh elementum vestibulum vel vel magna. "
		);
		return $lorem_ipsum[ rand( 0, 50 ) ];
	}
}

/**
 * Add all hooks
 */
add_action( 'bws_show_demo_button', 'bws_button' );
add_action( 'bws_display_demo_notice', 'bws_handle_demo_notice' );
add_filter( 'bws_load_demo_data', 'bws_demo_data' );
add_filter( 'bws_handle_demo_data', 'bws_handle_demo_data' );
