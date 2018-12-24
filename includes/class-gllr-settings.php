<?php
/**
 * Displays the content on the plugin settings page
 */

require_once( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );

if ( ! class_exists( 'Gllr_Settings_Tabs' ) ) {
	class Gllr_Settings_Tabs extends Bws_Settings_Tabs {
		public $wp_image_sizes = array();
		public $is_global_settings = true;
		public $cstmsrch_options;

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $_wp_additional_image_sizes, $gllr_options, $gllr_plugin_info, $gllr_BWS_demo_data;

			$this->is_global_settings = ( isset( $_GET['page'] ) && 'gallery-plugin.php' == $_GET['page'] );

			if ( $this->is_global_settings ) {
				$tabs = array(
					'settings' 		=> array( 'label' => __( 'Settings', 'gallery-plugin' ) ),
					'cover' 		=> array( 'label' => __( 'Cover', 'gallery-plugin' ) ),
					'lightbox' 		=> array( 'label' => __( 'Lightbox', 'gallery-plugin' ) ),
					'social' 		=> array( 'label' => __( 'Social', 'gallery-plugin' ), 'is_pro' => 1 ),
					'misc' 			=> array( 'label' => __( 'Misc', 'gallery-plugin' ) ),
					'custom_code' 	=> array( 'label' => __( 'Custom Code', 'gallery-plugin' ) ),
					'import-export' => array( 'label' => __( 'Import / Export', 'gallery-plugin' ) ),
					'license'		=> array( 'label' => __( 'License Key', 'gallery-plugin' ) ),
				);
			} else {
				$tabs = array(
					'images' 		=> array( 'label' => __( 'Images', 'gallery-plugin' ) ),
					'settings' 		=> array( 'label' => __( 'Settings', 'gallery-plugin' ), 'is_pro' => 1 ),
				);
			}

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $gllr_plugin_info,
				'prefix' 			 => 'gllr',
				'default_options' 	 => gllr_get_options_default(),
				'options' 			 => $gllr_options,
				'is_network_options' => is_network_admin(),
				'tabs' 				 => $tabs,
				'doc_link'			 => 'https://docs.google.com/document/d/1l4zMhovBgO7rsPIzJk_15v0sdhiCpnjuacoDEfmzGEw/',
				'wp_slug'			 => 'gallery-plugin',
				'demo_data'			 => $gllr_BWS_demo_data,
				'pro_page' 			 => 'admin.php?page=gallery-plugin-pro.php',
				'bws_license_plugin' => 'gallery-plugin-pro/gallery-plugin-pro.php',
				'link_key' 			 => '63a36f6bf5de0726ad6a43a165f38fe5',
				'link_pn' 			 => '79',
				'trial_days'		 => 7
			) );

			$wp_sizes = get_intermediate_image_sizes();

			foreach ( ( array ) $wp_sizes as $size ) {
				if ( ! array_key_exists( $size, $gllr_options['custom_size_px'] ) ) {
					if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
						$width  = absint( $_wp_additional_image_sizes[ $size ]['width'] );
						$height = absint( $_wp_additional_image_sizes[ $size ]['height'] );
					} else {
						$width  = absint( get_option( $size . '_size_w' ) );
						$height = absint( get_option( $size . '_size_h' ) );
					}

					if ( ! $width && ! $height ) {
						$this->wp_image_sizes[] = array(
							'value'  => $size,
							'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ),
						);
					} else {
						$this->wp_image_sizes[] = array(
							'value'  => $size,
							'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ) . ' ( ' . $width . ' &#215; ' . $height . ' ) ',
							'width'  => $width,
							'height' => $height
						);
					}
				}
			}

			$this->cstmsrch_options = get_option( 'cstmsrch_options' );

			add_action( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
			add_action( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
			add_action( get_parent_class( $this ) . '_additional_import_export_options', array( $this, 'additional_import_export_options' ) );
			add_filter( get_parent_class( $this ) . '_additional_restore_options', array( $this, 'additional_restore_options' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {

			/* Settings Tab */
			$this->options["custom_image_row_count"] 	= intval( $_POST['gllr_custom_image_row_count'] );
			if ( 1 > $this->options["custom_image_row_count"] )
				$this->options["custom_image_row_count"] = 1;

			$new_image_size_photo 		= esc_attr( $_POST['gllr_image_size_photo'] );
			$custom_image_size_w_photo 	= intval( $_POST['gllr_custom_image_size_w_photo'] );
			$custom_image_size_h_photo 	= intval( $_POST['gllr_custom_image_size_h_photo'] );
			$custom_size_px_photo 		= array( $custom_image_size_w_photo, $custom_image_size_h_photo );
			if ( 'photo-thumb' == $new_image_size_photo ) {
				if ( $new_image_size_photo != $this->options['image_size_photo'] ) {
					$need_image_update = true;
				} else {
					foreach ( $custom_size_px_photo as $key => $value ) {
						if ( $value != $this->options['custom_size_px']['photo-thumb'][ $key ] ) {
							$need_image_update = true;
							break;
						}
					}
				}
			}
			$this->options['custom_size_px']['photo-thumb'] = $custom_size_px_photo;
			$this->options['image_size_photo'] 				= $new_image_size_photo;

			$this->options["image_text"] 				= ( isset( $_REQUEST['gllr_image_text'] ) ) ? 1 : 0;
			$this->options["border_images"] 			= ( isset( $_POST['gllr_border_images'] ) ) ? 1 : 0;
			$this->options["border_images_width"]		= intval( $_POST['gllr_border_images_width'] );
			$this->options["border_images_color"]		= esc_attr( trim( $_POST['gllr_border_images_color'] ) );
			if ( ! preg_match( '/^#[A-Fa-f0-9]{6}$/', $this->options["border_images_color"] ) )
				$this->options["border_images_color"] = $this->default_options["border_images_color"];
			$this->options["order_by"]					= esc_attr( $_POST['gllr_order_by'] );
			$this->options["order"]						= esc_attr( $_POST['gllr_order'] );
			$this->options["return_link"]				= ( isset( $_POST['gllr_return_link'] ) ) ? 1 : 0;
			$this->options["return_link_url"]			= esc_url( $_POST['gllr_return_link_url'] );
			$this->options["return_link_text"] 			= stripslashes( esc_html( $_POST['gllr_return_link_text'] ) );
			$this->options["return_link_shortcode"]		= ( isset( $_POST['gllr_return_link_shortcode'] ) && isset( $_POST['gllr_return_link'] ) ) ? 1 : 0;

			/* Cover Tab */
			if ( $this->options['page_id_gallery_template'] != intval( $_POST['gllr_page_id_gallery_template'] ) ) {
				/* for rewrite */
				$this->options["flush_rewrite_rules"] = 1;
				$this->options['page_id_gallery_template'] = intval( $_POST['gllr_page_id_gallery_template'] );
			}

			$new_image_size_album 		= esc_attr( $_POST['gllr_image_size_album'] );
			$custom_image_size_w_album 	= intval( $_POST['gllr_custom_image_size_w_album'] );
			$custom_image_size_h_album 	= intval( $_POST['gllr_custom_image_size_h_album'] );
			$custom_size_px_album 		= array( $custom_image_size_w_album, $custom_image_size_h_album );
			if ( 'album-thumb' == $new_image_size_album ) {
				if ( $new_image_size_album != $this->options['image_size_album'] ) {
					$need_image_update = true;
				} else {
					foreach ( $custom_size_px_album as $key => $value ) {
						if ( $value != $this->options['custom_size_px']['album-thumb'][ $key ] ) {
							$need_image_update = true;
							break;
						}
					}
				}
			}

			$this->options['custom_size_px']['album-thumb'] = $custom_size_px_album;
			$this->options['image_size_album'] 				= $new_image_size_album;

			$this->options["cover_border_images"] 		= ( isset( $_POST['gllr_cover_border_images'] ) ) ? 1 : 0;
			$this->options["cover_border_images_width"]	= intval( $_POST['gllr_cover_border_images_width'] );
			$this->options["cover_border_images_color"]	= esc_attr( trim( $_POST['gllr_cover_border_images_color'] ) );
			if ( ! preg_match( '/^#[A-Fa-f0-9]{6}$/', $this->options["cover_border_images_color"] ) )
				$this->options["cover_border_images_color"] = $this->default_options["cover_border_images_color"];
			$this->options["album_order_by"]		= esc_attr( $_POST['gllr_album_order_by'] );
			$this->options["album_order"]			= esc_attr( $_POST['gllr_album_order'] );
			$this->options["galleries_layout"]			= esc_attr( $_POST['gllr_layout'] );
			$this->options["galleries_column_alignment"]	= esc_attr( $_POST['gllr_column_align'] );
			$this->options["read_more_link_text"]	= stripslashes( esc_html( $_POST['gllr_read_more_link_text'] ) );

			/* Lightbox Tab */
			$this->options["enable_lightbox"]		= ( isset( $_POST['gllr_enable_lightbox'] ) ) ? 1 : 0;
			$this->options["enable_image_opening"]	= ( isset( $_POST['gllr_enable_image_opening'] ) ) ? 1 : 0;
			$this->options["start_slideshow"]		= ( isset( $_POST['gllr_start_slideshow'] ) ) ? 1 : 0;
			$this->options["slideshow_interval"]	= empty( $_POST['gllr_slideshow_interval'] ) ? 2000 : intval( $_POST['gllr_slideshow_interval'] );
			$this->options["lightbox_download_link"] = ( isset( $_POST['gllr_lightbox_download_link'] ) ) ? 1 : 0;
			$this->options["single_lightbox_for_multiple_galleries"] = ( isset( $_POST['gllr_single_lightbox_for_multiple_galleries'] ) ) ? 1 : 0;

			/**
			 * rewriting post types name with unique one from default options
			 * @since 4.4.4
			 */
			if ( ! empty( $_POST['gllr_rename_post_type'] ) ) {
				global $wpdb;
				$wpdb->update(
					$wpdb->prefix . 'posts',
					array(
						'post_type'	=> $this->default_options['post_type_name']
					),
					array(
						'post_type'	=> 'gallery'
					)
				);
				$this->options['post_type_name'] = $this->default_options['post_type_name'];
			}

			if ( ! empty( $need_image_update ) )
				$this->options['need_image_update'] = __( 'Custom image size was changed. You need to update gallery images.', 'gallery-plugin' );

			if ( ! empty( $this->cstmsrch_options ) ) {
				if ( isset( $this->cstmsrch_options['output_order'] ) ) {
					$is_enabled = isset( $_POST['gllr_add_to_search'] ) ? 1 : 0;
					$post_type_exist = false;
					foreach ( $this->cstmsrch_options['output_order'] as $key => $item ) {
						if ( $item['name'] == $this->options['post_type_name'] && 'post_type' == $item['type'] ) {
							$post_type_exist = true;
							if ( $item['enabled'] != $is_enabled ) {
								$this->cstmsrch_options['output_order'][ $key ]['enabled'] = $is_enabled;
								$cstmsrch_options_update = true;
							}
							break;
						}
					}
					if ( ! $post_type_exist ) {
						$this->cstmsrch_options['output_order'][] = array(
							'name' 		=> $this->options['post_type_name'],
							'type' 		=> 'post_type',
							'enabled' 	=> $is_enabled );
						$cstmsrch_options_update = true;
					}
				} else if ( isset( $this->cstmsrch_options['post_types'] ) ) {
					if ( isset( $_POST['gllr_add_to_search'] ) && ! in_array( $this->options['post_type_name'], $this->cstmsrch_options['post_types'] ) ) {
						array_push( $this->cstmsrch_options['post_types'], $this->options['post_type_name'] );
						$cstmsrch_options_update = true;
					} else if ( ! isset( $_POST['gllr_add_to_search'] ) && in_array( $this->options['post_type_name'], $this->cstmsrch_options['post_types'] ) ) {
						unset( $this->cstmsrch_options['post_types'][ array_search( $this->options['post_type_name'], $this->cstmsrch_options['post_types'] ) ] );
						$cstmsrch_options_update = true;
					}
				}
				if ( isset( $cstmsrch_options_update ) )
					update_option( 'cstmsrch_options', $this->cstmsrch_options );
			}

			update_option( 'gllr_options', $this->options );
			$message = __( "Settings saved", 'gallery-plugin' );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display custom error\message\notice
		 * @access public
		 * @param  $save_results - array with error\message\notice
		 * @return void
		 */
		public function display_custom_messages( $save_results ) { ?>
			<noscript><div class="error below-h2"><p><strong><?php _e( "Please, enable JavaScript in Your browser.", 'gallery-plugin' ); ?></strong></p></div></noscript>
			<?php if ( ! empty( $this->options['need_image_update'] ) ) { ?>
				<div class="updated bws-notice inline gllr_image_update_message">
					<p>
						<?php echo $this->options['need_image_update']; ?>
						<input type="button" value="<?php _e( 'Update Images', 'gallery-plugin' ); ?>" id="gllr_ajax_update_images" name="ajax_update_images" class="button" />
					</p>
				</div>
			<?php }
		}

		/**
		 *
		 */
		public function tab_images() {
			global $post, $gllr_mode, $original_post;
			$original_post = $post;

			$wp_gallery_media_table = new Gllr_Media_Table();
			$wp_gallery_media_table->prepare_items(); ?>
			<h3 class="bws_tab_label"><?php _e( 'Gallery Images', 'gallery-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<div>
				<div class="error hide-if-js">
					<p><?php _e( 'Images adding requires JavaScript.', 'gallery-plugin' ); ?></p>
				</div>
				<div class="wp-media-buttons">
					<a href="#" id="gllr-media-insert" class="button insert-media add_media hide-if-no-js"><span class="wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'gallery-plugin' ); ?></a>
				</div>
				<?php $wp_gallery_media_table->views(); ?>
			</div>
			<div class="clear"></div>
			<?php if ( 'list' == $gllr_mode ) {
				$wp_gallery_media_table->display();
			} else { ?>
				<div class="error hide-if-js">
					<p><?php _e( 'The grid view for the Gallery images requires JavaScript.', 'gallery-plugin' ); ?> <a href="<?php echo esc_url( add_query_arg( 'mode', 'list', filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING ) ) ) ?>"><?php _e( 'Switch to the list view', 'gallery-plugin' ); ?></a></p>
				</div>
				<ul tabindex="-1" class="attachments ui-sortable ui-sortable-disabled hide-if-no-js" id="__attachments-view-39">
					<?php $wp_gallery_media_table->display_grid_rows(); ?>
				</ul>
			<?php } ?>
			<div class="clear"></div>
			<div id="hidden"></div>
		<?php }

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Gallery Settings', 'gallery-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<?php if ( ! $this->is_global_settings ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php _e( 'Single Gallery Settings', 'gallery-plugin' ); ?> </th>
								<td>
									<input disabled="disabled" type="checkbox" /> <span class="bws_info"><?php printf( __( 'Enable to configure single gallery settings and disable %s.', 'gallery-plugin' ), '<a style="z-index: 2;position: relative;" href="edit.php?post_type=' . $this->options['post_type_name'] . '&page=gallery-plugin.php" target="_blank">' . __( 'Global Settings', 'gallery-plugin' ) . '</a>' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } else {
				if ( ! $this->hide_pro_tabs ) { ?>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr valign="top">
									<th scope="row"><?php _e( 'Gallery Layout', 'gallery-plugin' ); ?> </th>
									<td>
										<fieldset>
											<label>
												<input disabled="disabled" type="radio" checked="checked" />
												<?php _e( 'Grid', 'gallery-plugin' ); ?>
												<?php echo bws_add_help_box( '<img src="' . plugins_url( 'images/view_grid.jpg', dirname( __FILE__ ) ) . '" />', 'bws-hide-for-mobile bws-auto-width' ); ?>
											</label>
											<br />
											<label>
												<input disabled="disabled" type="radio" />
												<?php _e( 'Masonry', 'gallery-plugin' ); ?>
												<?php echo bws_add_help_box( '<img src="' . plugins_url( 'images/view_masonry.jpg', dirname( __FILE__ ) ) . '" />', 'bws-hide-for-mobile bws-auto-width' ); ?>
											</label>
										</fieldset>
									</td>
								</tr>
							</table>
						</div>
						<?php $this->bws_pro_block_links(); ?>
					</div>
				<?php } ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Number of Columns', 'gallery-plugin' ); ?> </th>
						<td>
							<input type="number" name="gllr_custom_image_row_count" min="1" max="10000" value="<?php echo $this->options["custom_image_row_count"]; ?>" />
							 <div class="bws_info"><?php printf( __( 'Number of gallery columns (default is %s).', 'gallery-plugin' ), '3' ); ?></div>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Image Size', 'gallery-plugin' ); ?> </th>
						<td>
							<select name="gllr_image_size_photo">
								<?php foreach ( $this->wp_image_sizes as $data ) { ?>
									<option value="<?php echo $data['value']; ?>" <?php selected( $data['value'], $this->options['image_size_photo'] ); ?>><?php echo $data['name']; ?></option>
								<?php } ?>
								<option value="photo-thumb" <?php selected( 'photo-thumb', $this->options['image_size_photo'] ); ?> class="bws_option_affect" data-affect-show=".gllr_for_custom_image_size"><?php _e( 'Custom', 'gallery-plugin' ); ?></option>
							</select>
							<div class="bws_info"><?php _e( 'Maximum gallery image size. "Custom" uses the Image Dimensions values.', 'gallery-plugin' ); ?></div>
						</td>
					</tr>
					<tr valign="top" class="gllr_for_custom_image_size">
						<th scope="row"><?php _e( 'Custom Image Size', 'gallery-plugin' ); ?> </th>
						<td>
							<input type="number" name="gllr_custom_image_size_w_photo" min="1" max="10000" value="<?php echo $this->options['custom_size_px']['photo-thumb'][0]; ?>" /> x <input type="number" name="gllr_custom_image_size_h_photo" min="1" max="10000" value="<?php echo $this->options['custom_size_px']['photo-thumb'][1]; ?>" /> <?php _e( 'px', 'gallery-plugin' ); ?>
							<div class="bws_info"><?php _e( "Adjust these values based on the number of columns in your gallery. This won't effect the full size of your images in the lightbox.", 'gallery-plugin' ); ?></div>
						</td>
					</tr>
				</table>
				<?php if ( ! $this->hide_pro_tabs ) { ?>
					<div class="bws_pro_version_bloc gllr_for_custom_image_size">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr valign="top">
									<th scope="row"><?php _e( 'Crop Images', 'gallery-plugin' ); ?></th>
									<td>
										<input disabled checked type="checkbox" /> <span class="bws_info"><?php _e( 'Enable to crop images using the sizes defined for Custom Image Size. Disable to resize images automatically using their aspect ratio.', 'gallery-plugin' ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Crop Position', 'gallery-plugin' ); ?></th>
									<td>
										<div>
											<input disabled type="radio" />
											<input disabled type="radio" />
											<input disabled type="radio" />
											<br>
											<input disabled type="radio" />
											<input disabled checked type="radio" />
											<input disabled type="radio" />
											<br>
											<input disabled type="radio" />
											<input disabled type="radio" />
											<input disabled type="radio" />
										</div>
										<div class="bws_info"><?php _e( 'Select crop position base (by default: center).', 'gallery-plugin' ); ?></div>
									</td>
								</tr>
							</table>
						</div>
						<?php $this->bws_pro_block_links(); ?>
					</div>
				<?php } ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Image Title', 'gallery-plugin' ); ?></th>
						<td>
							<input type="checkbox" name="gllr_image_text" value="1" <?php checked( 1, $this->options["image_text"] ); ?> class="bws_option_affect" data-affect-show=".gllr_for_image_text" /> <span class="bws_info"><?php _e( 'Enable to display image title along with the gallery image.', 'gallery-plugin' ); ?></span>
						</td>
					</tr>
				</table>
				<?php if ( ! $this->hide_pro_tabs ) { ?>
					<div class="bws_pro_version_bloc gllr_for_image_text">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr valign="top">
									<th scope="row"><?php _e( 'Image Title Position', 'gallery-plugin' ); ?></th>
									<td>
										<fieldset>
											<label>
												<input disabled type="radio" value="under" checked="checked">
												<?php _e( 'Under image', 'gallery-plugin' ); ?>
												<?php echo bws_add_help_box( '<img src="' . plugins_url( 'images/display_text_under_image.jpg', dirname( __FILE__ ) ) . '" />', 'bws-hide-for-mobile bws-auto-width' ); ?>
											</label>
											<br/>
											<label>
												<input disabled type="radio" value="hover">
												<?php _e( 'On mouse hover', 'gallery-plugin' ); ?>
												<?php echo bws_add_help_box( '<img src="' . plugins_url( 'images/display_text_by_mouse_hover.jpg', dirname( __FILE__ ) ) . '" />', 'bws-hide-for-mobile bws-auto-width' ); ?>
											</label>
										</fieldset>
									</td>
								</tr>
							</table>
						</div>
						<?php $this->bws_pro_block_links(); ?>
					</div>
				<?php } ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Image Border', 'gallery-plugin' ); ?></th>
						<td>
							<input type="checkbox" name="gllr_border_images" value="1" <?php if ( 1 == $this->options["border_images"] ) echo 'checked="checked"'; ?> class="bws_option_affect" data-affect-show=".gllr_for_border_images" /> <span class="bws_info"><?php _e( 'Enable images border using the styles defined for Image Border Size and Color options.', 'gallery-plugin' ); ?></span>
						</td>
					</tr>
					<tr valign="top" class="gllr_for_border_images">
						<th scope="row"><?php _e( 'Image Border Size', 'gallery-plugin' ); ?></th>
						<td>
							<input type="number" min="0" max="10000" value="<?php echo $this->options["border_images_width"]; ?>" name="gllr_border_images_width" /> <?php _e( 'px', 'gallery-plugin' ); ?>
							<div class="bws_info"><?php printf( __( 'Gallery image border width (default is %s)', 'gallery-plugin' ), '10px' ); ?></div>
						</td>
					</tr>
					<tr valign="top" class="gllr_for_border_images">
						<th scope="row"><?php _e( 'Image Border Color', 'gallery-plugin' ); ?></th>
						<td>
							<input type="text" value="<?php echo $this->options["border_images_color"]; ?>" name="gllr_border_images_color" class="gllr_color_field" data-default-color="#F1F1F1" />
						</td>
					</tr>
				</table>
				<?php if ( ! $this->hide_pro_tabs ) { ?>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr valign="top">
									<th scope="row"><?php _e( 'Pagination', 'gallery-plugin' ); ?></th>
									<td>
										<input disabled type="checkbox" value="1" />
										<span class="bws_info"><?php _e( 'Enable pagination for images to limit number of images displayed on a single gallery page.', 'gallery-plugin' ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Number of Images', 'gallery-plugin' ); ?></th>
									<td>
										<input disabled type="number" value="10" />
										<div class="bws_info"><?php printf( __( 'Number of images displayed per page (default is %d).', 'gallery-plugin' ), '10' ); ?></div>
									</td>
								</tr>
							</table>
						</div>
						<?php $this->bws_pro_block_links(); ?>
					</div>
				<?php } ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Images by', 'gallery-plugin' ); ?></th>
						<td>
							<select name="gllr_order_by">
								<option value="meta_value_num" <?php selected( 'meta_value_num', $this->options["order_by"] ); ?>><?php _e( 'Manually (default)', 'gallery-plugin' ); ?></option>
								<option value="ID" <?php selected( 'ID', $this->options["order_by"] ); ?>><?php _e( 'Image ID', 'gallery-plugin' ); ?></option>
								<option value="title" <?php selected( 'title', $this->options["order_by"] ); ?>><?php _e( 'Name', 'gallery-plugin' ); ?></option>
								<option value="date" <?php selected( 'date', $this->options["order_by"] ); ?>><?php _e( 'Date', 'gallery-plugin' ); ?></option>
								<option value="rand" <?php selected( 'rand', $this->options["order_by"] ); ?> class="bws_option_affect" data-affect-hide=".gllr_image_order"><?php _e( 'Random', 'gallery-plugin' ); ?></option>
							</select>
							<div class="bws_info"><?php _e( 'Select images sorting order in your gallery. By default, you can sort images manually in the images tab.', 'gallery-plugin' ); ?></div>
						</td>
					</tr>
					<tr valign="top" class="gllr_image_order">
						<th scope="row"><?php _e( 'Arrange Images by', 'gallery-plugin' ); ?></th>
						<td>
							<fieldset>
								<label><input type="radio" name="gllr_order" value="ASC" <?php if ( 'ASC' == $this->options["order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Ascending (e.g. 1, 2, 3; a, b, c)', 'gallery-plugin' ); ?></label><br />
								<label><input type="radio" name="gllr_order" value="DESC" <?php if ( 'DESC' == $this->options["order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Descending (e.g. 3, 2, 1; c, b, a)', 'gallery-plugin' ); ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Back Link', 'gallery-plugin' ); ?></th>
						<td>
							<input type="checkbox" name="gllr_return_link" value="1" <?php if ( 1 == $this->options["return_link"] ) echo 'checked="checked"'; ?> class="bws_option_affect" data-affect-show=".gllr_for_return_link" /> <span class="bws_info"><?php _e( 'Enable to show a back link in a single gallery page which navigate to a previous page.' , 'gallery-plugin' ); ?></span>
						</td>
					</tr>
					<tr valign="top" class="gllr_for_return_link">
						<th scope="row"><?php _e( 'Back Link URL', 'gallery-plugin' ); ?></th>
						<td>
							<input type="text" value="<?php echo $this->options["return_link_url"]; ?>" name="gllr_return_link_url" maxlength="250" />
							<div class="bws_info"><?php _e( 'Back link custom page URL. Leave blank to use Gallery page template.' , 'gallery-plugin' ); ?></div>
						</td>
					</tr>
					<tr valign="top" class="gllr_for_return_link">
						<th scope="row"><?php _e( 'Back Link Label', 'gallery-plugin' ); ?> </th>
						<td>
							<input type="text" name="gllr_return_link_text" maxlength="250" value="<?php echo $this->options["return_link_text"]; ?>" />
						</td>
					</tr>
					<tr valign="top" class="gllr_for_return_link">
						<th scope="row"><?php _e( 'Back Link with Shortcode', 'gallery-plugin' ); ?> </th>
						<td>
							<input type="checkbox" name="gllr_return_link_shortcode" value="1" <?php if ( 1 == $this->options["return_link_shortcode"] ) echo 'checked="checked"'; ?> />
							<span class="bws_info"><?php _e( 'Enable to display a back link on a page where shortcode is used.' , 'gallery-plugin' ); ?></span>
						</td>
					</tr>
				</table>
			<?php }
		}

		/**
		 *
		 */
		public function tab_cover() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Cover Settings', 'gallery-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Galleries Page', 'gallery-plugin' ); ?></th>
					<td>
						<?php wp_dropdown_pages( array(
							'depth'                 => 0,
							'selected'              => isset( $this->options['page_id_gallery_template'] ) ? $this->options['page_id_gallery_template'] : false,
							'name'                  => 'gllr_page_id_gallery_template',
							'show_option_none'		=> '...'
						) ); ?>
						<div class="bws_info"><?php _e( 'Base page where all existing galleries will be displayed.' , 'gallery-plugin' ); ?></div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Albums Displaying', 'gallery-plugin' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="gllr_layout" value="column" id="gllr_column" <?php checked( 'column' == $this->options["galleries_layout"] ); ?> class="bws_option_affect" data-affect-show=".gllr_column_alignment" /> <?php _e( 'Column', 'gallery-plugin' ); ?></label><br/>
							<label><input type="radio" name="gllr_layout" value="rows" id="gllr_rows" <?php checked( 'rows' == $this->options["galleries_layout"] ); ?> class="bws_option_affect" data-affect-hide=".gllr_column_alignment" /> <?php _e( 'Rows', 'gallery-plugin' ); ?></label>
						</fieldset>
						<div class="bws_info"><?php _e( 'Select the way galleries will be displayed on the Galleries Page.' , 'gallery-plugin' ); ?></div>
					</td>
				</tr>
				<tr valign="top" class="gllr_column_alignment">
					<th scope="row"><?php _e( 'Column Alignment', 'gallery-plugin' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="gllr_column_align" value="left" <?php checked( 'left' == $this->options["galleries_column_alignment"] ); ?> /> <?php _e( 'Left', 'gallery-plugin' ); ?></label><br/>
							<label><input type="radio" name="gllr_column_align" value="right" <?php checked( 'right' == $this->options["galleries_column_alignment"] ); ?> /> <?php _e( 'Right', 'gallery-plugin' ); ?></label><br/>
							<label><input type="radio" name="gllr_column_align" value="center" <?php checked( 'center' == $this->options["galleries_column_alignment"] ); ?> /> <?php _e( 'Center', 'gallery-plugin' ); ?></label>
						</fieldset>
						<div class="bws_info"><?php _e( 'Select the column alignment.' , 'gallery-plugin' ); ?></div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Cover Image Size', 'gallery-plugin' ); ?> </th>
					<td>
						<select name="gllr_image_size_album">
							<?php foreach ( $this->wp_image_sizes as $data ) { ?>
								<option value="<?php echo $data['value']; ?>" <?php selected( $data['value'], $this->options['image_size_album'] ); ?>><?php echo $data['name']; ?></option>
							<?php } ?>
							<option value="album-thumb" <?php selected( 'album-thumb', $this->options['image_size_album'] ); ?> class="bws_option_affect" data-affect-show=".gllr_for_custom_image_size_album"><?php _e( 'Custom', 'gallery-plugin' ); ?></option>
						</select>
						<div class="bws_info"><?php _e( 'Maximum cover image size. Custom uses the Image Dimensions values.', 'gallery-plugin' ); ?></div>
					</td>
				</tr>
				<tr valign="top" class="gllr_for_custom_image_size_album">
					<th scope="row"><?php _e( 'Custom Cover Image Size', 'gallery-plugin' ); ?> </th>
					<td>
						<input type="number" name="gllr_custom_image_size_w_album" min="1" max="10000" value="<?php echo $this->options['custom_size_px']['album-thumb'][0]; ?>" /> x <input type="number" name="gllr_custom_image_size_h_album" min="1" max="10000" value="<?php echo $this->options['custom_size_px']['album-thumb'][1]; ?>" /> <?php _e( 'px', 'gallery-plugin' ); ?>
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc gllr_for_custom_image_size_album">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php _e( 'Crop Cover Images', 'gallery-plugin' ); ?></th>
								<td>
									<input disabled checked type="checkbox" name="" /> <span class="bws_info"><?php _e( 'Enable to crop images using the sizes defined for Custom Cover Image Size. Disable to resize images automatically using their aspect ratio.', 'gallery-plugin' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Crop Position', 'gallery-plugin' ); ?></th>
								<td>
									<div>
										<input disabled type="radio" name="" />
										<input disabled type="radio" name="" />
										<input disabled type="radio" name="" />
										<br>
										<input disabled type="radio" name="" />
										<input disabled checked type="radio" name="" />
										<input disabled type="radio" name="" />
										<br>
										<input disabled type="radio" name="" />
										<input disabled type="radio" name="" />
										<input disabled type="radio" name="" />
									</div>
									<div class="bws_info"><?php _e( 'Select crop position base (by default: center).', 'gallery-plugin' ); ?></div>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Cover Image Border', 'gallery-plugin' ); ?></th>
					<td>
						<input type="checkbox" name="gllr_cover_border_images" value="1" <?php if ( 1 == $this->options["cover_border_images"] ) echo 'checked="checked"'; ?> class="bws_option_affect" data-affect-show=".gllr_for_cover_border_images" /> <span class="bws_info"><?php _e( 'Enable cover images border using the styles defined for Image Border Size and Color.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
				<tr valign="top" class="gllr_for_cover_border_images">
					<th scope="row"><?php _e( 'Cover Image Border Size', 'gallery-plugin' ); ?></th>
					<td>
						<input type="number" min="0" max="10000" value="<?php echo $this->options["cover_border_images_width"]; ?>" name="gllr_cover_border_images_width" /> <?php _e( 'px', 'gallery-plugin' ); ?>
						<div class="bws_info"><?php printf( __( 'Cover image border width (default is %s)', 'gallery-plugin' ), '10px' ); ?></div>
					</td>
				</tr>
				<tr valign="top" class="gllr_for_cover_border_images">
					<th scope="row"><?php _e( 'Cover Image Border Color', 'gallery-plugin' ); ?></th>
					<td>
						<input type="text" value="<?php echo $this->options["cover_border_images_color"]; ?>" name="gllr_cover_border_images_color" class="gllr_color_field" data-default-color="#F1F1F1" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Sort Albums by', 'gallery-plugin' ); ?></th>
					<td>
						<select name="gllr_album_order_by">
							<option value="ID" <?php selected( 'ID', $this->options["album_order_by"] ); ?>><?php _e( 'Gallery ID', 'gallery-plugin' ); ?></option>
							<option value="title" <?php selected( 'title', $this->options["album_order_by"] ); ?>><?php _e( 'Title', 'gallery-plugin' ); ?></option>
							<option value="date" <?php selected( 'date', $this->options["album_order_by"] ); ?>><?php _e( 'Date', 'gallery-plugin' ); ?></option>
							<option value="modified" <?php selected( 'modified', $this->options["album_order_by"] ); ?>><?php _e( 'Last modified date', 'gallery-plugin' ); ?></option>
							<option value="comment_count" <?php selected( 'comment_count', $this->options["album_order_by"] ); ?>><?php _e( 'Comment count', 'gallery-plugin' ); ?></option>
							<option value="menu_order" <?php selected( 'menu_order', $this->options["album_order_by"] ); ?>><?php _e( '"Order" field on the gallery edit page', 'gallery-plugin' ); ?></option>
							<option value="author" <?php selected( 'author', $this->options["album_order_by"] ); ?>><?php _e( 'Author', 'gallery-plugin' ); ?></option>
							<option value="rand" <?php selected( 'rand', $this->options["album_order_by"] ); ?> class="bws_option_affect" data-affect-hide=".gllr_album_order"><?php _e( 'Random', 'gallery-plugin' ); ?></option>
						</select>
						<div class="bws_info"><?php _e( 'Select galleries sorting order in your galleries page.', 'gallery-plugin' ); ?></div>
					</td>
				</tr>
				<tr valign="top" class="gllr_album_order">
					<th scope="row"><?php _e( 'Arrange Albums by', 'gallery-plugin' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="gllr_album_order" value="ASC" <?php if ( 'ASC' == $this->options["album_order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Ascending (e.g. 1, 2, 3; a, b, c)', 'gallery-plugin' ); ?></label><br />
							<label><input type="radio" name="gllr_album_order" value="DESC" <?php if ( 'DESC' == $this->options["album_order"] ) echo 'checked="checked"'; ?> /> <?php _e( 'Descending (e.g. 3, 2, 1; c, b, a)', 'gallery-plugin' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Read More Link Label', 'gallery-plugin' ); ?></th>
					<td>
						<input type="text" name="gllr_read_more_link_text" maxlength="250" value="<?php echo $this->options["read_more_link_text"]; ?>" />
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc gllr_for_enable_lightbox">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php _e( 'Instant Lightbox', 'gallery-plugin' ); ?> </th>
								<td>
									<input type="checkbox" value="1" disabled />
									<span class="bws_info"><?php _e( 'Enable to display all images in the lightbox after clicking cover image or URL instead of going to a single gallery page.', 'gallery-plugin' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
		<?php }

		/**
		 *
		 */
		public function tab_lightbox() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Lightbox Settings', 'gallery-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Unclickable Thumbnail Images', 'gallery-plugin' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_enable_image_opening" <?php if ( 1 == $this->options["enable_image_opening"] ) echo 'checked="checked"'; ?> />
						<span class="bws_info"><?php _e( 'Enable to make the images in a single gallery unclickable and hide their URLs. This option also disables Lightbox.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
				<tr valign="top" class="gllr_for_enable_opening_images">
					<th scope="row"><?php _e( 'Enable Lightbox', 'gallery-plugin' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_enable_lightbox" <?php if ( 1 == $this->options["enable_lightbox"] ) echo 'checked="checked"'; ?> class="bws_option_affect" data-affect-show=".gllr_for_enable_lightbox" />
						<span class="bws_info"><?php _e( 'Enable to show the lightbox when clicking on gallery images.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc gllr_for_enable_lightbox">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php _e( 'Image Size', 'gallery-plugin' ); ?> </th>
								<td>
									<select disabled name="gllr_image_size_full">
										<?php foreach ( $this->wp_image_sizes as $data ) { ?>
											<option value="<?php echo $data['value']; ?>" <?php selected( $data['value'], 'large' ); ?>><?php echo $data['name']; ?></option>
										<?php } ?>
									</select>
									<div class="bws_info"><?php _e( 'Select the maximum gallery image size for the lightbox view. "Default" will display the original, full size image.', 'gallery-plugin' ); ?></div>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Overlay Color', 'gallery-plugin' ); ?> </th>
								<td>
									<input disabled="disabled" type="text" value="#777777" size="7" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Overlay Opacity', 'gallery-plugin' ); ?> </th>
								<td>
									<input disabled type="text" size="8" value="0.7" />
									<div class="bws_info"><?php printf( __( 'Lightbox overlay opacity. Leave blank to disable opacity (default is %s, max is %s).', 'gallery-plugin' ), '0.7', '1' ); ?></div>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table gllr_for_enable_lightbox">
				<tr valign="top">
					<th scope="row"><?php _e( 'Slideshow', 'gallery-plugin' ); ?> </th>
					<td>
						<input type="checkbox" name="gllr_start_slideshow" value="1" <?php if ( 1 == $this->options["start_slideshow"] ) echo 'checked="checked"'; ?> class="bws_option_affect" data-affect-show=".gllr_for_start_slideshow" /> <span class="bws_info"><?php _e( 'Enable to start the slideshow automatically when the lightbox is used.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
				<tr valign="top" class="gllr_for_start_slideshow">
					<th scope="row"><?php _e( 'Slideshow Duration', 'gallery-plugin' ); ?></th>
					<td>
						<input type="number" name="gllr_slideshow_interval" min="1" max="1000000" value="<?php echo $this->options["slideshow_interval"]; ?>" /> <?php _e( 'ms', 'gallery-plugin' ); ?>
						<div class="bws_info"><?php _e( 'Slideshow interval duration between two images.', 'gallery-plugin' ); ?></div>
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc gllr_for_enable_lightbox">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php _e( 'Lightbox Helpers', 'gallery-plugin' ); ?></th>
								<td>
									<input disabled type="checkbox" name="" /> <span class="bws_info"><?php _e( 'Enable to display the lightbox toolbar and arrows.', 'gallery-plugin' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Lightbox Thumbnails', 'gallery-plugin' ); ?></th>
								<td>
									<input disabled type="checkbox" name="" /> <span class="bws_info"><?php _e( 'Enable to use a lightbox helper navigation between images.', 'gallery-plugin' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Lightbox Thumbnails Position', 'gallery-plugin' ); ?></th>
								<td>
									<select disabled name="">
										<option><?php _e( 'Top', 'gallery-plugin' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Lightbox Button Label', 'gallery-plugin' ); ?></th>
								<td>
									<input type="text" disabled value="<?php _e( 'Read More', 'gallery-plugin' ); ?>" />
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table gllr_for_enable_lightbox">
				<tr valign="top">
					<th scope="row"><?php _e( 'Download Button', 'gallery-plugin' ); ?></th>
					<td>
						<input type="checkbox" name="gllr_lightbox_download_link" value="1" <?php if ( 1 == $this->options["lightbox_download_link"] ) echo 'checked="checked"'; ?> class="bws_option_affect" data-affect-show=".gllr_for_lightbox_download_link" /> <span class="bws_info"><?php _e( 'Enable to display download button.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Single Lightbox', 'gallery-plugin' ); ?></th>
					<td>
						<input type="checkbox" name="gllr_single_lightbox_for_multiple_galleries" value="1" <?php if ( 1 == $this->options["single_lightbox_for_multiple_galleries"] ) echo 'checked="checked"'; ?> /> <span class="bws_info"><?php _e( 'Enable to use a single lightbox for multiple galleries located on a single page.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
			</table>
		<?php }

		/**
		 *
		 */
		public function tab_social() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Social Sharing Buttons Settings', 'gallery-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<div class="bws_pro_version_bloc">
				<div class="bws_pro_version_table_bloc">
					<div class="bws_table_bg"></div>
					<table class="form-table bws_pro_version">
						<tr valign="top">
							<th scope="row"><?php _e( 'Social Buttons', 'gallery-plugin' ); ?></th>
							<td>
								<input type="checkbox" disabled="disabled" checked="checked" /> <span class="bws_info"><?php _e( 'Enable social sharing buttons in the lightbox.', 'gallery-plugin' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Social Networks', 'gallery-plugin' ); ?></th>
							<td>
								<fieldset>
									<label><input disabled="disabled" type="checkbox" /> Facebook</label><br>
									<label><input disabled="disabled" type="checkbox" /> Twitter</label><br>
									<label><input disabled="disabled" type="checkbox" /> Pinterest</label><br>
									<label><input disabled="disabled" type="checkbox" /> Google +1</label>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Counter', 'gallery-plugin' ); ?></th>
							<td>
								<input disabled type="checkbox" value="1" />
								<span class="bws_info"><?php _e( 'Enable to show likes counter for each social button (not available for Google +1).', 'gallery-plugin' ); ?></span>
							</td>
						</tr>
					</table>
				</div>
				<?php $this->bws_pro_block_links(); ?>
			</div>
		<?php }

		/**
		 *
		 */
		public function additional_import_export_options() { ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Demo Data', 'gallery-plugin' ); ?></th>
					<td>
						<?php $this->demo_data->bws_show_demo_button( __( 'Install demo data to create galleries with images, post with shortcodes and page with a list of all galleries.', 'gallery-plugin' ) ); ?>
					</td>
				</tr>
			</table>
		<?php }

		/**
		 * Display custom options on the 'misc' tab
		 * @access public
		 */
		public function additional_misc_options_affected() {
			global $wp_version, $wpdb;
			if ( ! $this->all_plugins ) {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->all_plugins = get_plugins();
			}
			$old_post_type_gallery = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'gallery'" );

			if ( ! empty( $old_post_type_gallery ) ) { ?>
				<tr valign="top">
					<th scope="row"><?php _e( 'Gallery Post Type', 'gallery-plugin' ); ?></th>
					<td>
						<input type="checkbox" name="gllr_rename_post_type" value="1" /> <span class="bws_info"><?php _e( 'Enable to avoid conflicts with other gallery plugins installed. All galleries created earlier will stay unchanged. However, after enabling we recommend to check settings of other plugins where "gallery" post type is used.', 'gallery-plugin' ); ?></span>
					</td>
				</tr>
			<?php }
			if ( ! $this->hide_pro_tabs ) { ?>
				</table>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'gallery-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php _e( 'Gallery Slug', 'gallery-plugin' ); ?></th>
								<td>
									<input type="text" value="gallery" disabled />
									<br>
									<span class="bws_info"><?php _e( 'Enter the unique gallery slug.', 'gallery-plugin' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
				<table class="form-table">
			<?php } ?>
			<tr valign="top">
				<th scope="row"><?php _e( 'Search Galleries', 'gallery-plugin' ); ?></th>
				<td>
					<?php $disabled = $checked = $link = '';
					if ( array_key_exists( 'custom-search-plugin/custom-search-plugin.php', $this->all_plugins ) || array_key_exists( 'custom-search-pro/custom-search-pro.php', $this->all_plugins ) ) {
						if ( ! is_plugin_active( 'custom-search-plugin/custom-search-plugin.php' ) && ! is_plugin_active( 'custom-search-pro/custom-search-pro.php' ) ) {
							$disabled = ' disabled="disabled"';
							$link = '<a href="' . admin_url( 'plugins.php' ) . '">' . __( 'Activate Now', 'gallery-plugin' ) . '</a>';
						}
						if ( isset( $this->cstmsrch_options['output_order'] ) ) {
							foreach ( $this->cstmsrch_options['output_order'] as $key => $item ) {
								if ( $item['name'] == $this->options['post_type_name'] && $item['type'] == 'post_type' ) {
									if ( $item['enabled'] )
										$checked = ' checked="checked"';
									break;
								}
							}
						} elseif ( ! empty( $this->cstmsrch_options['post_types'] ) && in_array( $this->options['post_type_name'], $this->cstmsrch_options['post_types'] ) ) {
							$checked = ' checked="checked"';
						}
					} else {
						$disabled = ' disabled="disabled"';
						$link = '<a href="https://bestwebsoft.com/products/wordpress/plugins/custom-search/?k=62eae81381e03dd9e843fc277c6e64c1&amp;pn=' . $this->link_pn . '&amp;v=' . $this->plugins_info["Version"] . '&amp;wp_v=' . $wp_version . '" target="_blank">' . __( 'Install Now', 'gallery-plugin' ) . '</a>';
					} ?>
					<input type="checkbox" name="gllr_add_to_search" value="1"<?php echo $disabled . $checked; ?> />
					 <span class="bws_info"><?php _e( 'Enable to include galleries to your website search.', 'gallery-plugin' ); ?> <?php printf( __( '%s is required.', 'gallery-plugin' ), 'Custom Search plugin' ); ?> <?php echo $link; ?></span>
				</td>
			</tr>
		<?php }

		/**
		 * Custom functions for "Restore plugin options to defaults"
		 * @access public
		 */
		public function additional_restore_options( $default_options ) {
			$default_options['post_type_name'] = $this->options['post_type_name'];
			return $default_options;
		}
	}
}