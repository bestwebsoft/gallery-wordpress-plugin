<?php
/**
 * Load demo data
 * @version 1.0.6
 */

if ( ! class_exists( 'Bws_Demo_Data' ) ) {
	class Bws_Demo_Data {
		private $bws_plugin_text_domain, $bws_plugin_prefix, $bws_plugin_page, $bws_plugin_name, $bws_plugin_basename, $bws_demo_options, $bws_plugin_options, $bws_demo_folder;

		public function __construct( $args ) {
			$plugin_dir_array      	= explode( '/', $args['plugin_basename'] );
			$this->bws_plugin_basename 		= $args['plugin_basename'];
			$this->bws_plugin_prefix		= $args['plugin_prefix'];
			$this->bws_plugin_name			= $args['plugin_name'];
			$this->bws_plugin_page			= $args['plugin_page'];
			$this->bws_demo_folder			= $args['demo_folder'];
			$this->install_callback 		= isset( $args['install_callback'] ) ? $args['install_callback'] : false;
			$this->remove_callback 			= isset( $args['remove_callback'] ) ? $args['remove_callback'] : false;
			$this->bws_plugin_text_domain 	= $plugin_dir_array[0];
			$this->bws_demo_options 		= get_option( $this->bws_plugin_prefix . 'demo_options' );
			$this->bws_plugin_options		= get_option( $this->bws_plugin_prefix . 'options' );
		}

		/**
		 * Display "Install demo data" or "Uninstal demo data" buttons
		 * @return void
		 */
		function bws_show_demo_button( $form_info ) {
			if ( ! ( is_multisite() && is_network_admin() ) ) {
				if ( empty( $this->bws_demo_options ) ) {
					$value        = 'install';
					$button_title = __( 'Install Demo Data', $this->bws_plugin_text_domain );
				} else {
					$value        = 'remove';
					$button_title = __( 'Remove Demo Data', $this->bws_plugin_text_domain );
					$form_info   = __( 'Delete demo data and restore old plugin settings.', $this->bws_plugin_text_domain );
				} ?>
				<button class="button" name="bws_handle_demo" value="<?php echo $value; ?>"><?php echo $button_title; ?></button>
				<div class="bws_info"><?php echo $form_info; ?></div>
			<?php }
		}

		/**
		 * Display page for confirmation action to install demo data
		 * @return void
		 */
		function bws_demo_confirm() {
			if ( 'install' == $_POST['bws_handle_demo'] ) {
				$button_title = __( 'Yes, install demo data', $this->bws_plugin_text_domain );
				$label        = __( 'Are you sure you want to install demo data?', $this->bws_plugin_text_domain );
			} else {
				$button_title = __( 'Yes, remove demo data', $this->bws_plugin_text_domain );
				$label        = __( 'Are you sure you want to remove demo data?', $this->bws_plugin_text_domain );
			} ?>
			<div>
				<p><?php echo $label; ?></p>
				<form method="post" action="">
					<p>
						<button class="button button-primary" name="bws_<?php echo $_POST['bws_handle_demo']; ?>_demo_confirm" value="true"><?php echo $button_title; ?></button>
						<button class="button" name="bws_<?php echo $_POST['bws_handle_demo']; ?>_demo_deny" value="true"><?php _e( 'No, go back to the settings page', $this->bws_plugin_text_domain ) ?></button>
						<?php wp_nonce_field( $this->bws_plugin_basename, 'bws_nonce_name' ); ?>
					</p>
				</form>
			</div>
		<?php }

		/**
		 * @return array
		 */
		function bws_handle_demo_data() {
			if ( isset( $_POST['bws_install_demo_confirm'] ) )
				return $this->bws_install_demo_data();
			elseif ( isset( $_POST['bws_remove_demo_confirm'] ) )
				return $this->bws_remove_demo_data();
			else
				return false;
		}

		/**
		 * Load demo data
		 *
		 * @return array $message   message about the result of the query
		 */
		function bws_install_demo_data() {
			global $wpdb;
			/* get demo data*/
			$message   = array(
				'error'   => NULL,
				'done'    => NULL,
				'options' => NULL
			);
			$demo_data = array(
				'posts'							=> NULL,
				'attachments'					=> NULL,
				'distant_attachments'			=> NULL,
				'distant_attachments_metadata'	=> NULL,
				'terms'							=> NULL,
				'options'						=> NULL
			);
			$error   = 0;
			$page_id = $posttype_post_id = $post_id = '';
			/* get demo data */
			@include_once( $this->bws_demo_folder . 'demo-data.php' );
			$received_demo_data = bws_demo_data_array( $this->bws_plugin_options['post_type_name'] );

			/*
			 * load demo data
			 */
			if ( empty( $received_demo_data ) ) {
				$message['error'] = __( 'Can not get demo data.', $this->bws_plugin_text_domain );
			} else {
				$demo_data = array_merge( $demo_data, $received_demo_data );
				/*
				 * check if demo options already loaded
				 */
				if ( ! empty( $this->bws_demo_options ) ) {
					$message['error'] = __( 'Demo settings already installed.', $this->bws_plugin_text_domain );
					return $message;
				}

				/*
				 * load demo options
				 */
				if ( ! empty( $demo_data['options'] ) ) {
					/* remember old plugin options */
					if ( ! empty( $this->bws_plugin_options ) ) {
						$this->bws_demo_options['options'] = $this->bws_plugin_options;
						$demo_data['options']['display_demo_notice'] = 0;
						update_option( $this->bws_plugin_prefix . 'options', array_merge( $this->bws_plugin_options, $demo_data['options'] ) );
					}
				} else {
					/* remove demo notice */
					if ( 0 != $this->bws_plugin_options['display_demo_notice'] ) {
						$this->bws_plugin_options['display_demo_notice'] = 0;
						update_option( $this->bws_plugin_prefix . 'options', $this->bws_plugin_options );
					}
				}

				/*
				 * load demo posts
				 */
				if ( ! empty( $demo_data['posts'] ) ) {
					$wp_upload_dir      = wp_upload_dir();
					$attachments_folder = $this->bws_demo_folder . 'images';
					/*
					 * load demo terms
					 */
					if ( ! empty( $demo_data['terms'] ) ) {
						foreach ( $demo_data['terms'] as $taxonomy_name => $terms_values_array ) {
							foreach ( $terms_values_array as $term_key => $term_value_single ) {
								$term_exists = term_exists( $term_key, $taxonomy_name );
								if ( ! $term_exists ) {
									$term_id = wp_insert_term(
										$term_value_single, /* the term. */
										$taxonomy_name, /* the taxonomy. */
										array(
											'slug' 			=> $term_key
										)
									);
									if ( is_wp_error( $term_id ) ) {
										$error ++;
									} else {
										$term_IDs[ $taxonomy_name ][ $term_key ] = $term_id['term_id'];
										$term_IDs_new[ $taxonomy_name ][ $term_key ] = $term_id['term_id'];
									}
								} else {
									$term_IDs[ $taxonomy_name ][ $term_key ] = $term_exists['term_id'];
								}
							}
						}
						if ( ! empty( $term_IDs_new ) ) {
							$this->bws_demo_options['terms'] = isset( $this->bws_demo_options['terms'] ) ? array_merge( $this->bws_demo_options['terms'], $term_IDs_new ) : $term_IDs_new;
						}
					}

					/*
					 * load demo posts
					 */
					$default_category = absint( $this->bws_plugin_options['default_gallery_category'] );
					foreach ( $demo_data['posts'] as $post ) {
						if ( preg_match( '/{last_post_id}/', $post['post_content'] ) && ! empty( $post_id ) ) {
							$post['post_content'] = preg_replace( '/{last_post_id}/', $post_id, $post['post_content'] );
						}
						if ( preg_match( '/{template_page}/', $post['post_content'] ) ) {
							if ( empty( $page_id ) && ! empty( $page_template ) )
								$page_id = intval( $wpdb->get_var( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key` LIKE '_wp_page_template' AND `meta_value` LIKE '" . $page_template . "' LIMIT 1" ) );
							if ( ! empty( $page_id ) )
								$post['post_content'] = preg_replace( '/{template_page}/', '<a href="' . get_permalink( $page_id ) . '">' . get_the_title( $page_id ) . '</a>', $post['post_content'] );
						}
						/* insert current post */
						$post_id = wp_insert_post( $post );
						if ( 'post' == $post['post_type'] )
							$posttype_post_id = $post_id;

						if ( $this->bws_plugin_options['post_type_name'] == $post['post_type'] && $default_category )
							wp_set_object_terms( $post_id, $default_category, 'gallery_categories' );

						/* add taxonomy for posttype */
						if ( 'post' != $post['post_type'] && 'page' != $post['post_type'] && ! empty( $term_IDs ) ) {
							foreach ( $term_IDs as $taxonomy_name => $term_array ) {
								if ( isset( $post['terms'][ $taxonomy_name ] ) ) {
									$selected_terms = $post['terms'][ $taxonomy_name ];
								} else {
									$selected_terms = array();
									$selected_terms[] = intval( $term_array[ array_rand( $term_array ) ] );
								}

								foreach ( $selected_terms as $selected_term ) {
									if ( ! wp_set_object_terms( $post_id, $selected_term, $taxonomy_name, false ) )
										$error ++;
								}
							}
						}

						$attach_id = 0;

						if ( is_wp_error( $post_id ) || 0 == $post_id ) {
							$error ++;
						} else {
							/* remember post ID */
							$this->bws_demo_options['posts'][ $post_id ] = get_post_modified_time( 'U', false, $post_id, false );

							$featured_attach_id = '';
							/*
							 * load post attachments
							 */
							if ( ! empty( $post['attachments_folder'] ) ) {
								$attachments_list = @scandir( $attachments_folder . '/' . $post['attachments_folder'] );
								if ( 2 < count( $attachments_list ) ) {
									$k = 1;
									foreach ( $attachments_list as $attachment ) {
										$file = $attachments_folder . '/' . $post['attachments_folder'] . '/' . $attachment;
										/* insert current attachment */
										/* Check if file is image */
										$file_data = ( '.' == $attachment || '..' == $attachment ) ? false : @getimagesize( $file );
										$bws_is_image = ! ( $file_data || in_array( $file_data[2], array( 1, 2, 3 ) ) ) ? false : true;
										if ( $bws_is_image ) {

											$destination   = $wp_upload_dir['path'] . '/' . $this->bws_plugin_prefix . 'demo_' . $attachment; /* path to new file */
											$wp_filetype   = wp_check_filetype( $file, null ); /* Mime-type */

											if ( copy( $file, $destination ) ) { /* if attachment copied */

												$attachment_data = array(
													'post_mime_type' => $wp_filetype['type'],
													'post_title'     => $attachment,
													'post_content'   => '',
													'post_status'    => 'inherit'
												);

												/* insert attschment in to database */
												$attach_id = wp_insert_attachment( $attachment_data, $destination, $post_id );
												if ( 0 != $attach_id ) {
													if ( empty( $featured_attach_id ) )
														$featured_attach_id = $attach_id;
													/* remember attachment ID */
													$this->bws_demo_options['attachments'][] = $attach_id;

													/* insert attachment metadata */
													$attach_data = wp_generate_attachment_metadata( $attach_id, $destination );
													wp_update_attachment_metadata( $attach_id, $attach_data );
													/* insert additional metadata */
													if ( isset( $demo_data['attachments'][ $attachment ] ) ) {
														foreach ( $demo_data['attachments'][ $attachment ] as $meta_key => $meta_value ) {
															if ( '{get_lorem_ipsum}' == $meta_value )
																$meta_value = $this->bws_get_lorem_ipsum();
															add_post_meta( $attach_id, $meta_key, $meta_value );
														}
													}
													add_post_meta( $attach_id, '_gallery_order_' . $post_id, $k );
													$k++;
												} else {
													$error ++;
												}
											} else {
												$error ++;
											}
										}
									}
								}
							}

							/*
							 * load post attachments
							 */
							if ( ! empty( $post['distant_attachments'] ) ) {
								foreach ( $post['distant_attachments'] as $attachment_name ) {
									if ( isset( $demo_data['distant_attachments_metadata'][ $attachment_name ] ) ) {
										$data = $demo_data['distant_attachments_metadata'][ $attachment_name ];

										$attachment_data = array(
											'post_mime_type' => $data['mime_type'],
											'post_title'     => $data['title'],
											'post_content'   => '',
											'post_status'    => 'inherit'
										);

										/* insert attschment in to database */
										$attach_id = wp_insert_attachment( $attachment_data, $data['url'], $post_id );
										if ( 0 != $attach_id ) {
											if ( empty( $featured_attach_id ) )
												$featured_attach_id = $attach_id;
											/* remember attachment ID */
											$this->bws_demo_options['distant_attachments'][ $attachment_name ] = $attach_id;

											/* insert attachment metadata */
											$imagesize = @getimagesize( $data['url'] );
											$sizes = ( isset( $data['sizes'] ) ) ? $data['sizes'] : array();
											$attach_data = array(
												'width' 	=> $imagesize[0],
												'height' 	=> $imagesize[1],
												'file' 		=> $data['url'],
												'sizes' 	=> $sizes
											);

											wp_update_attachment_metadata( $attach_id, $attach_data );

											/* insert additional metadata */
											if ( isset( $demo_data['distant_attachments'][ $attachment_name ] ) ) {
												foreach ( $demo_data['distant_attachments'][ $attachment_name ] as $meta_key => $meta_value ) {
													if ( '{get_lorem_ipsum}' == $meta_value )
														$meta_value = $this->bws_get_lorem_ipsum();
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

							/* insert additional post meta */
							if ( isset( $post['post_meta'] ) && ! empty( $post['post_meta'] ) ) {
								foreach ( $post['post_meta'] as $meta_key => $meta_value ) {
									add_post_meta( $post_id, $meta_key, $meta_value );
								}
							}
							/* set template for post type "page" */
							if ( ! empty( $post['page_template'] ) ) {
								update_post_meta( $post_id, '_wp_page_template', $post['page_template'] );
								$page_id = $post_id;
								$page_template = $post['page_template'];
							}
							/* save page_id to options */
							if ( ! empty( $post['save_to_options'] ) ) {
								$page_id = $post_id;
								$this->bws_plugin_options[ $post['save_to_options'] ] = $post_id;
								update_option( $this->bws_plugin_prefix . 'options', $this->bws_plugin_options );
							}

							/* first inserted image is thummbnail for post */
							if ( ! empty( $featured_attach_id ) )
								update_post_meta( $post_id, '_thumbnail_id', $featured_attach_id );
						}
					}

					/*
					 * Save demo options
					 */
					add_option( $this->bws_plugin_prefix . 'demo_options', $this->bws_demo_options );

					if ( 0 == $error ) {
						$message['done'] = __( 'Demo data installed successfully.', $this->bws_plugin_text_domain );
						if ( ! empty( $posttype_post_id ) ) {
							$message['done'] .= '<br />' . __( 'View post with shortcodes', $this->bws_plugin_text_domain ) . ':&nbsp;<a href="'.  get_permalink( $posttype_post_id ) . '" target="_blank">' . get_the_title( $posttype_post_id ) . '</a>';
						}
						if ( ! empty( $page_id ) ) {
							$message['done'] .= '<br />' . __( 'View page with examples', $this->bws_plugin_text_domain ) . ':&nbsp;<a href="'.  get_permalink( $page_id ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>';
						}

						if ( ! empty( $demo_data['options'] ) )
							$message['options'] = $demo_data['options'];
						else
							$message['options'] = $this->bws_plugin_options;

						if ( $this->install_callback && function_exists( $this->install_callback ) )
							call_user_func( $this->install_callback );
					} else {
						$message['error'] = __( 'Installation of demo data with some errors occurred.', $this->bws_plugin_text_domain );
					}
				} else {
					$message['error'] = __( 'Posts data is missing.', $this->bws_plugin_text_domain );
				}
			}
			return $message;
		}

		/**
		 * Change url for distant attachments
		 * @return $url   string
		 */
		function bws_wp_get_attachment_url( $url, $id ) {
			if ( ! empty( $this->bws_demo_options['distant_attachments'] ) && in_array( $id, $this->bws_demo_options['distant_attachments'] ) ) {
				$url = substr( $url, strpos( $url, 'https://' ) );
			}
			return $url;
		}

		/**
		 * Replace metadata to default for images after saving ( to prevent editing image )
		 * @return $data   array()
		 */
		function bws_wp_update_attachment_metadata( $data, $id ) {
			if ( ! empty( $data ) && ! empty( $this->bws_demo_options['distant_attachments'] ) && $attachment_name = array_search( $id, $this->bws_demo_options['distant_attachments'] ) ) {
				/* get demo data */
				@include_once( $this->bws_demo_folder . 'demo-data.php' );
				$received_demo_data = bws_demo_data_array( $this->bws_plugin_options['post_type_name'] );

				if ( isset( $received_demo_data['distant_attachments_metadata'][ $attachment_name ] ) ) {

					/* insert attachment metadata */
					$imagesize = @getimagesize( $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['url'] );
					$sizes = ( isset( $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['sizes'] ) ) ? $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['sizes'] : array();
					$data = array(
						'width' 	=> $imagesize[0],
						'height' 	=> $imagesize[1],
						'file' 		=> $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['url'],
						'sizes' 	=> $sizes
					);
				}
			}
			return $data;
		}

		/**
		 * Change url for distant attachments
		 * @return $url   string
		 */
		function bws_wp_get_attachment_image_attributes( $attr, $attachment, $size = false ) {
			if ( ! empty( $attr['srcset'] ) && ! empty( $this->bws_demo_options['distant_attachments'] ) && in_array( $attachment->ID, $this->bws_demo_options['distant_attachments'] ) ) {
				$srcset = explode( ', ', $attr['srcset'] );
				foreach ( $srcset as $key => $value ) {
					$srcset[ $key ] = substr( $value, strpos( $value, 'https://' ) );
				}
				$attr['srcset'] = implode( ', ', $srcset );
			}
			return $attr;
		}

		/**
		 * Remove demo data
		 *
		 * @return array $message   message about the result of the query
		 */
		function bws_remove_demo_data() {
			$error        = 0;
			$message      = array(
				'error'   => null,
				'done'    => null,
				'options' => null
			);

			if ( empty( $this->bws_demo_options ) ) {
				$message['error'] = __( 'Demo data have already been removed.', $this->bws_plugin_text_domain );
			} else {

				/*
				 * Restore plugin options
				 */
				if ( ! empty( $this->bws_demo_options['options'] ) ) {
					$this->bws_demo_options['options']['display_demo_notice'] = 0;
					update_option( $this->bws_plugin_prefix . 'options', $this->bws_demo_options['options'] );
					if ( $this->remove_callback && function_exists( $this->remove_callback ) )
						call_user_func( $this->remove_callback );
				}
				$done = $this->bws_delete_demo_option();
				if ( ! $done )
					$error ++;

				/*
				 * Delete all posts
				 */
				if ( ! empty( $this->bws_demo_options['posts'] ) ) {
					foreach ( $this->bws_demo_options['posts'] as $post_id => $last_modified ) {
						/* delete only not modified posts */
						if ( get_post_modified_time( 'U', false, $post_id, false ) == $last_modified ) {
							$done = wp_delete_post( $post_id, true );
							if ( ! $done )
								$error ++;
						}
					}
				}

				/* Delete terms */
				if ( ! empty( $this->bws_demo_options['terms'] ) ) {
					foreach ( $this->bws_demo_options['terms'] as $taxonomy_name => $terms_values_array ) {
						foreach ( $terms_values_array as $term_id ) {
							wp_delete_term( $term_id, $taxonomy_name );
						}
					}
				}

				/*
				 * Delete all attachments
				 */
				if ( ! empty( $this->bws_demo_options['attachments'] ) ) {
					foreach ( $this->bws_demo_options['attachments'] as $post_id ) {
						$done = wp_delete_attachment( $post_id, true );
						if ( ! $done )
							$error ++;
					}
				}
				if ( ! empty( $this->bws_demo_options['distant_attachments'] ) ) {
					foreach ( $this->bws_demo_options['distant_attachments'] as $post_id ) {
						$done = wp_delete_attachment( $post_id, true );
						if ( ! $done )
							$error ++;
					}
				}
				if ( empty( $error ) ) {
					$message['done']    = __( 'Demo data successfully removed.', $this->bws_plugin_text_domain );
					$message['options'] = get_option( $this->bws_plugin_prefix . 'options' );
					$this->bws_demo_options = array();
				} else {
					$message['error'] = __( 'Removing demo data with some errors occurred.', $this->bws_plugin_text_domain );
				}
			}
			return $message;
		}

		/**
		 * Delete demo-options
		 * @return boolean
		 */
		function bws_delete_demo_option() {
			$done = delete_option( $this->bws_plugin_prefix . 'demo_options' );
			return $done;
		}

		function bws_handle_demo_notice( $show_demo_notice ) {

			if ( 1 == $show_demo_notice ) {
				global $wp_version;

				if ( isset( $_POST['bws_hide_demo_notice'] ) && check_admin_referer( $this->bws_plugin_basename, 'bws_demo_nonce_name' ) ) {
					$this->bws_plugin_options['display_demo_notice'] = 0;
					update_option( $this->bws_plugin_prefix . 'options', $this->bws_plugin_options );
					return;
				}
				if ( ! isset( $_POST['bws_handle_demo'] ) && ! isset( $_POST['bws_install_demo_confirm'] ) ) {
					if ( 4.2 > $wp_version ) {
						$plugin_dir_array = explode( '/', plugin_basename( __FILE__ ) ); ?>
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
								background: url("<?php echo plugins_url( $plugin_dir_array[0] . '/bws_menu/images/close_banner.png' ); ?>") no-repeat center center;
								box-shadow: none;
								<?php if ( 3.8 <= $wp_version ) { ?>
									position: relative;
									top: -4px;
								<?php } ?>
							}
							.bws_hide_demo_notice:hover {
								cursor: pointer;
							}
							.rtl #bws_handle_notice_form {
								float: left;
							}
						</style>
					<?php }
					if ( 4.2 <= $wp_version ) { ?>
						<style type="text/css">
							#bws_handle_notice_form {
								position: absolute;
								top: 2px;
								right: 0;
							}
							.rtl #bws_handle_notice_form {
								left: 0;
							}
						</style>
					<?php } ?>
					<div class="update-nag" style="position: relative;">
						<form id="bws_handle_notice_form" action="" method="post">
							<button class="notice-dismiss bws_hide_demo_notice" title="<?php _e( 'Close notice', $this->bws_plugin_text_domain ); ?>"></button>
							<input type="hidden" name="bws_hide_demo_notice" value="hide" />
							<?php wp_nonce_field( $this->bws_plugin_basename, 'bws_demo_nonce_name' ); ?>
						</form>
						<div style="margin: 0 20px;">
							<?php printf(
								__( 'Do you want to install demo content and settings for %s? (You can do this later using Import / Export settings)', $this->bws_plugin_text_domain ),
								$this->bws_plugin_name . ' by BestWebSoft'
							); ?>&nbsp;<a href="<?php echo admin_url( 'admin.php?page=' . $this->bws_plugin_page ); ?>"><?php _e( 'Yes, install demo now', $this->bws_plugin_text_domain ); ?></a>
						</div>
					</div>
				<?php }
			}
		}

		/**
		 * Generate Lorem Ipsum
		 * @return   string
		 */
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
}