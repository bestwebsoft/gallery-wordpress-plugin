<?php
/**
 * Class extends WP class WP_Media_List_Table, 
 * and create new Media Table
 */

if ( ! class_exists( 'Gllr_Media_Table' ) ) {
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
			global $wpdb, $gllr_mode, $original_post, $wp_version;

			$columns = $this->get_columns();
			$hidden  = array( 'order' );
			$sortable = array();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$original_post ? $images_id = get_post_meta( $original_post->ID, '_gallery_images', true ) : false;
			if ( empty( $images_id ) ) {
				$total_items = 0;
			} else {
				$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE ID IN( " . $images_id . " )" );
			}

	 		$per_page = -1;

			$gllr_mode = get_user_option( 'gllr_media_library_mode', get_current_user_id() ) ? get_user_option( 'gllr_media_library_mode', get_current_user_id() ) : 'grid';
			$modes = array( 'grid', 'list' );

			if ( isset( $_GET['mode'] ) && in_array( $_GET['mode'], $modes ) ) {
				$gllr_mode = esc_attr( $_GET['mode'] );
				update_user_option( get_current_user_id(), 'gllr_media_library_mode', $gllr_mode );
			}

			$this->set_pagination_args( array(
				'total_items' 	=> $total_items,
				'total_pages' 	=> 1,
				'per_page' 		=> $per_page
			) );

			if ( $wp_version < '4.2' ) {
				$this->is_trash = isset( $_REQUEST['attachment-filter'] ) && 'trash' == $_REQUEST['attachment-filter'];
			}
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
			global $wpdb, $original_post;

			$images_id = get_post_meta( $original_post->ID, '_gallery_images', true );
			if ( empty( $images_id ) ) {
				$total_items = 0;
			} else {
				$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE ID IN( " . $images_id . " )" );
			}

			if ( $total_items > 0 ) {
				return true;
			} else {
				return false;
			}
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
		 * This is designated as optional for backwards-compatibility.
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

			if ( empty( $this->_actions ) ) {
				return;
			}

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
			global $gllr_mode; ?>
			<div class="gllr-wp-filter hide-if-no-js">
				<?php if ( 'grid' == $gllr_mode ) { ?>
					<a href="#" class="button media-button gllr-media-bulk-select-button hide-if-no-js"><?php _e( 'Bulk Select', 'gallery-plugin' ); ?></a>
					<a href="#" class="button media-button gllr-media-bulk-cansel-select-button hide-if-no-js"><?php _e( 'Cancel Selection', 'gallery-plugin' ); ?></a>
					<a href="#" class="button media-button button-primary gllr-media-bulk-delete-selected-button hide-if-no-js" disabled="disabled"><?php _e( 'Delete Selected', 'gallery-plugin' ); ?></a>
				<?php } else {
					$this->view_switcher( $gllr_mode );
				} ?>
			</div>
			<input type="hidden" name="gllr_mode" value="<?php echo $gllr_mode; ?>" />
		<?php }

		function get_columns() {
			$lists_columns = array(
				'cb'					=> '<input type="checkbox" />',
				'title'					=> __( 'File', 'gallery-plugin' ),
				'dimensions'			=> __( 'Dimensions', 'gallery-plugin' ),
				'gllr_image_text'		=> __( 'Title', 'gallery-plugin' ) . bws_add_help_box( '<img src="' . plugins_url( 'images/image-title-example.png', __FILE__ ) . '" />' ),
				'gllr_image_alt_tag'	=> __( 'Alt Text', 'gallery-plugin' ),
				'gllr_link_url'			=> __( 'URL', 'gallery-plugin' ) . bws_add_help_box( __( 'Enter your custom URL to link this image to other page or file. Leave blank to open a full size image.', 'gallery-plugin' ) ),
				'order' 				=> ''
			);
			return $lists_columns;
		}

		function display_rows( $lists = array(), $level = 0 ) {
			global $post, $gllr_mode, $original_post, $gllr_options;

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
				'order'				=> 'ASC'
			) );

			while ( have_posts() ) {
				the_post();
				$this->single_row( $gllr_mode );
			}
			wp_reset_postdata();
			wp_reset_query();
			$post = $old_post;
		}

		function display_grid_rows() {
			global $post, $gllr_mode, $original_post, $gllr_options;
			$old_post = $post;
			add_filter( 'the_title','esc_html' );

			$original_post ? $images_id = get_post_meta( $original_post->ID, '_gallery_images', true ) : false;
			query_posts( array(
				'post__in'			=> explode( ',', $images_id ),
				'post_type'			=> 'attachment',
				'posts_per_page'	=> -1,
				'post_status'		=> 'inherit',
				'meta_key'			=> '_gallery_order_' . $post->ID,
				'orderby'			=> $gllr_options['order_by'],
				'order'				=> 'ASC'
			) );
			while ( have_posts() ) {
				the_post();
				$this->single_row( $gllr_mode );
			}
			wp_reset_postdata();
			wp_reset_query();
			$post = $old_post;
		}

		function single_row( $gllr_mode ) {
			global $post, $original_post, $gllr_options, $gllr_plugin_info, $wp_version;

			if ( empty( $gllr_options ) ) {
				gllr_settings();
			}

			$attachment_metadata = wp_get_attachment_metadata( $post->ID );
			if ( 'grid' == $gllr_mode ) {
				$image_attributes = wp_get_attachment_image_src( $post->ID, 'medium' ); ?>
				<li tabindex="0" id="post-<?php echo $post->ID; ?>" class="gllr-media-attachment">
					<div class="gllr-media-attachment-preview">
						<div class="gllr-media-thumbnail">
							<div class="centered">
								<img src="<?php echo $image_attributes[0]; ?>" class="thumbnail" draggable="false" />
								<input type="hidden" name="_gallery_order_<?php echo $original_post->ID; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, '_gallery_order_'.$original_post->ID, true ); ?>" />
							</div>
						</div>
						<div class="gllr-media-attachment-details">
							<strong><?php echo get_post_meta( $post->ID, 'gllr_image_text', true ); ?></strong>
							<br />
							<?php the_title(); ?>
						</div>
					</div>
					<a href="#" class="gllr-media-actions-delete dashicons dashicons-trash" title="<?php _e( 'Remove Image from Gallery', 'gallery-plugin' ); ?>"></a>
					<input type="hidden" class="gllr_attachment_id" name="_gllr_attachment_id" value="<?php echo $post->ID; ?>" />
					<input type="hidden" class="gllr_post_id" name="_gllr_post_id" value="<?php echo $original_post->ID; ?>" />
					<a class="thickbox gllr-media-actions-edit dashicons dashicons-edit" href="<?php echo get_edit_post_link( $post->ID ); ?>#TB_inline?width=800&height=450&inlineId=gllr-media-attachment-details-box-<?php echo $post->ID; ?>" title="<?php _e( 'Edit Image Info', 'gallery-plugin' ); ?>"></a>
					<a class="gllr-media-check" tabindex="-1" title="<?php _e( 'Deselect', 'gallery-plugin' ); ?>" href="#"><div class="media-modal-icon"></div></a>
					<div id="gllr-media-attachment-details-box-<?php echo $post->ID; ?>" class="gllr-media-attachment-details-box">
						<?php $image_attributes = wp_get_attachment_image_src( $post->ID, 'large' ); ?>
						<div class="gllr-media-attachment-details-box-left">
							<div class="gllr_border_image">
								<img src="<?php echo $image_attributes[0]; ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" height="auto" width="<?php echo $image_attributes[1]; ?>" />
							</div>
						</div>
						<div class="gllr-media-attachment-details-box-right">
							<div class="attachment-details">
								<div class="attachment-info">
									<div class="details">
										<div><?php _e( 'File name', 'gallery-plugin' ); ?>: <?php the_title(); ?></div>
										<div><?php _e( 'File type', 'gallery-plugin' ); ?>: <?php echo get_post_mime_type( $post->ID ); ?></div>
										<div><?php _e( 'Dimensions', 'gallery-plugin' ); ?>: <?php echo $attachment_metadata['width']; ?> &times; <?php echo $attachment_metadata['height']; ?></div>
									</div>
								</div>
								<label class="setting" data-setting="title">
									<span class="name">
										<?php _e( 'Title', 'gallery-plugin' );
										echo bws_add_help_box( '<img src="' . plugins_url( 'images/image-title-example.png', __FILE__ ) . '" />', 'bws-auto-width' ); ?>
									</span>
									<input type="text" name="gllr_image_text[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, 'gllr_image_text', true ); ?>" />
								</label>
								<label class="setting" data-setting="alt">
									<span class="name"><?php _e( 'Alt Text', 'gallery-plugin' ); ?></span>
									<input type="text" name="gllr_image_alt_tag[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, 'gllr_image_alt_tag', true ); ?>" />
								</label>
								<label class="setting" data-setting="alt">
									<span class="name"><?php _e( 'URL', 'gallery-plugin' ); ?></span>
									<input type="text" name="gllr_link_url[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, 'gllr_link_url', true ); ?>" />
									<span class="bws_info"><?php _e( 'Enter your custom URL to link this image to other page or file. Leave blank to open a full size image.', 'gallery-plugin' ); ?></span>
								</label>
								<!-- pls -->
								<?php if ( ! bws_hide_premium_options_check( $gllr_options ) ) { ?>
									<div class="bws_pro_version_bloc gllr_like">
										<div class="bws_pro_version_table_bloc">
											<div class="bws_table_bg"></div>
											<label class="setting" data-setting="description">
												<span class="name"><?php _e( 'Description', 'gallery-plugin' );
												echo bws_add_help_box( '<img src="' . plugins_url( 'images/image-description-example.png', __FILE__ ) . '" />', 'bws-auto-width' ); ?></span>
												<textarea disabled name=""></textarea>
											</label>
											<label class="setting" data-setting="description">
												<span class="name">
													<?php _e( 'Lightbox Button URL', 'gallery-plugin' );
													echo bws_add_help_box( '<img src="' . plugins_url( 'images/image-button-example.png', __FILE__ ) . '" />', 'bws-auto-width' ); ?>
												</span>
												<input disabled type="text" name="" value="" />
											</label>
											<label class="setting" data-setting="description">
												<span class="name">
													<?php _e( 'New Tab', 'gallery-plugin' ); ?>
												</span>
												<input disabled type="checkbox" name="" value="" />	<span class="bws_info"><?php _e( 'Enable to open URLs above in a new tab.', 'gallery-plugin' ); ?></span>
											</label>
										</div>
										<div class="clear"></div>
										<div class="bws_pro_version_tooltip">
											<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/gallery/?k=63a36f6bf5de0726ad6a43a165f38fe5&pn=79&v=<?php echo $gllr_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="<?php _e( 'Go Pro', 'gallery-plugin' ); ?>"><?php _e( 'Upgrade to Pro', 'bestwebsoft' ); ?></a>
											<div class="clear"></div>
										</div>
									</div>
								<?php } ?>
								<!-- end pls -->
								<div class="gllr-media-attachment-actions">
									<a target="_blank" href="post.php?post=<?php echo $post->ID; ?>&amp;action=edit"><?php _e( 'Edit more details', 'gallery-plugin' ); ?></a>
									<span class="gllr-separator">|</span>
									<a href="#" class="gllr-media-actions-delete"><?php _e( 'Remove from Gallery', 'gallery-plugin' ); ?></a>
									<input type="hidden" class="gllr_attachment_id" name="_gllr_attachment_id" value="<?php echo $post->ID; ?>" />
									<input type="hidden" class="gllr_post_id" name="_gllr_post_id" value="<?php echo $original_post->ID; ?>" />
								</div>
							</div>
							<div class="gllr_clear"></div>
						</div>
					</div>
				</li>
			<?php } else {
				$user_can_edit = current_user_can( 'edit_post', $post->ID );
				$post_owner = ( get_current_user_id() == $post->post_author ) ? 'self' : 'other';
				$att_title = _draft_or_post_title(); ?>
				<tr id="post-<?php echo $post->ID; ?>" class="<?php echo trim( ' author-' . $post_owner . ' status-' . $post->post_status ); ?>">
					<?php list( $columns, $hidden ) = $this->get_column_info();
					foreach ( $columns as $column_name => $column_display_name ) {

						$classes = "$column_name column-$column_name";
						if ( in_array( $column_name, $hidden ) ) {
							$classes .= ' hidden';
						}

						if ( 'title' == $column_name ) {
							$classes .= ' column-primary has-row-actions';
						}

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
								<td <?php echo $attributes; ?> data-colname="<?php _e( 'Alt Text', 'gallery-plugin' ); ?>">
									<input type="text" name="<?php echo $column_name; ?>[<?php echo $post->ID; ?>]" value="<?php echo get_post_meta( $post->ID, $column_name, true ); ?>" />
								</td>
								<?php break;
							case 'gllr_link_url': ?>
								<td <?php echo $attributes; ?> data-colname="<?php _e( 'URL', 'gallery-plugin' ); ?>">
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
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'Edit', 'gallery-plugin' ) . '</a>';
				}
				if ( current_user_can( 'delete_post', $post->ID ) ) {
					if ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
						$actions['trash'] = "<a class='submitdelete' href='" . wp_nonce_url( "post.php?action=trash&amp;post=$post->ID", 'trash-post_' . $post->ID ) . "'>" . __( 'Trash', 'gallery-plugin' ) . "</a>";
					} else {
						$delete_ays = !MEDIA_TRASH ? " onclick='return showNotice.warn();'" : '';
						$actions['delete'] = "<a class='submitdelete'$delete_ays href='" . wp_nonce_url( "post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID ) . "'>" . __( 'Delete Permanently', 'gallery-plugin' ) . "</a>";
					}
				}
				$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'gallery-plugin' ), $att_title ) ) . '" rel="permalink">' . __( 'View', 'gallery-plugin' ) . '</a>';
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$actions['attach'] = '<a href="#the-list" onclick="findPosts.open( \'media[]\',\''.$post->ID.'\' );return false;" class="hide-if-no-js">' . __( 'Attach', 'gallery-plugin' ) . '</a>';
				}
			} else {
				if ( current_user_can( 'edit_post', $post->ID ) && !$this->is_trash ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'Edit', 'gallery-plugin' ) . '</a>';
				}
				if ( current_user_can( 'delete_post', $post->ID ) ) {
					if ( $this->is_trash ) {
						$actions['untrash'] = "<a class='submitdelete' href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$post->ID", 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore', 'gallery-plugin' ) . "</a>";
					} elseif ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
						$actions['trash'] = "<a class='submitdelete' href='" . wp_nonce_url( "post.php?action=trash&amp;post=$post->ID", 'trash-post_' . $post->ID ) . "'>" . __( 'Trash', 'gallery-plugin' ) . "</a>";
					}
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