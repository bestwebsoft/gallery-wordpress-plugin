<?php
/**
 * Include all plugin widgets
 */

/* Registing Widget */
if ( ! function_exists( 'gllr_register_widget' ) ) {
	function gllr_register_widget() {
		register_widget( 'GLLR_Categories_Widget' );
		register_widget( 'GLLR_Latest_Galleries_Widget' );
	}
}

/**
 * Class extends WP class WP_Widget, and create new widget
 * Gallery Categories widget
 */
if ( ! class_exists( 'GLLR_Categories_Widget' ) ) {
	class GLLR_Categories_Widget extends WP_Widget {
		/**
		 * constructor of class
		 */
		public function __construct() {
			$widget_ops = array( 'classname' => 'GLLR_Categories_Widget', 'description' => __( "A list or dropdown of Gallery categories.", 'gallery-plugin' ) );
			parent::__construct( 'gallery_categories_widget', __( 'Gallery Categories', 'gallery-plugin' ), $widget_ops );
		}
		/**
		 * Function to displaying widget in front end
		 *
		 */
		public function widget( $args, $instance ) {
			global $wp_version;
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Gallery Categories', 'gallery-plugin' ) : $instance['title'], $instance, $this->id_base );
			$c = ! empty( $instance['count'] ) ? '1' : '0';
			$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
			$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

			/* Get value of HTTP Request */
			if ( isset( $_REQUEST['gallery_categories'] ) ) {
				$term = get_term_by( 'slug', esc_attr( $_REQUEST['gallery_categories'] ), 'gallery_categories' );
			} else {
				global $wp;
				$http_request = parse_url( add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
				if ( isset( $http_request['query'] ) && preg_match( '/gallery_categories/' ,$http_request['query'] ) )
					$term = get_term_by( 'slug', substr( $http_request['query'], strpos( $http_request['query'], "=" ) + 1 ), 'gallery_categories' );
			}

			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			$cat_args = array(
				'orderby'      => 'name',
				'show_count'   => $c,
				'hierarchical' => $h
			);
			if ( $d ) {
				static $first_dropdown = true;
				$dropdown_id     = ( $first_dropdown ) ? 'gllr_cat' : 'gllr_cat_' . $this->number;
				$first_dropdown  = false;
				echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $title . '</label>';
				if ( 4.2 >= $wp_version ) {
					$cat_args['walker']       = new Gllr_CategoryDropdown();
					$cat_args['selected']     = isset( $term ) && ( ! empty( $term ) ) ? $term->slug : '-1';
				} else {
					$cat_args['value_field']  = 'slug';
					$cat_args['selected']     = isset( $term ) && ( ! empty( $term ) ) ? $term->term_id : -1;
				}
				$cat_args['show_option_none'] = __( 'Select Gallery Category', 'gallery-plugin' );
				$cat_args['taxonomy']         = 'gallery_categories';
				$cat_args['title_li']         = __( 'Gallery Categories', 'gallery-plugin' );
				$cat_args['name']             = 'gallery_categories';
				$cat_args['id']               = $dropdown_id; ?>
				<form action="<?php bloginfo( 'url' ); ?>/" method="get">
					<?php wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) );
					$script = '(function() {
                        var dropdown = document.getElementById( "' . esc_js( $dropdown_id ) . '" );
                        function onCatChange() {
                            if ( dropdown.options[ dropdown.selectedIndex ].value != -1 ) {
                                location.href = "' . home_url() . '/?gallery_categories=" + dropdown.options[ dropdown.selectedIndex ].value;
                            }
                        }
                        dropdown.onchange = onCatChange;
					})();';
					wp_register_script( 'gllr_widget_script', '' );
					wp_enqueue_script( 'gllr_widget_script' );
					wp_add_inline_script( 'gllr_widget_script', sprintf( $script ) ); ?>
					<noscript>
						<br />
						<input type="submit" value="<?php _e( 'View', 'gallery-plugin' ); ?>" />
					</noscript>
				</form>
			<?php } else { ?>
				<ul>
					<?php $cat_args['show_option_none'] = __( 'Gallery Categories', 'gallery-plugin' );
					$cat_args['taxonomy'] = 'gallery_categories';
					$cat_args['title_li'] = '';
					wp_list_categories( apply_filters( 'widget_categories_args', $cat_args ) ); ?>
				</ul>
			<?php }
			echo $args['after_widget'];
		}
		/**
		 * Function to save widget settings
		 * @param array()    $new_instance  array with new settings
		 * @param array()    $old_instance  array with old settings
		 * @return array()   $instance      array with updated settings
		 */
		public function update( $new_instance, $old_instance ) {
			$instance 					= $old_instance;
			$instance['title']			= strip_tags( $new_instance['title'] );
			$instance['count']			= ! empty( $new_instance['count'] ) ? 1 : 0;
			$instance['hierarchical']	= ! empty( $new_instance['hierarchical'] ) ? 1 : 0;
			$instance['dropdown']		= ! empty( $new_instance['dropdown'] ) ? 1 : 0;
			return $instance;
		}
		/**
		 * Function to displaying widget settings in back end
		 * @param  array()     $instance  array with widget settings
		 * @return void
		 */
		public function form( $instance ) {
			$instance     = wp_parse_args( ( array ) $instance, array( 'title' => '' ) );
			$title        = esc_attr( $instance['title'] );
			$count        = isset( $instance['count'] ) ? ( bool ) $instance['count'] : false;
			$hierarchical = isset( $instance['hierarchical'] ) ? ( bool ) $instance['hierarchical'] : false;
			$dropdown     = isset( $instance['dropdown'] ) ? ( bool ) $instance['dropdown'] : false; ?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'gallery-plugin' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'dropdown' ); ?>" name="<?php echo $this->get_field_name( 'dropdown' ); ?>"<?php checked( $dropdown ); ?> />
				<label for="<?php echo $this->get_field_id( 'dropdown' ); ?>"><?php _e( 'Display as dropdown', 'gallery-plugin' ); ?></label><br />
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
				<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show gallery counts', 'gallery-plugin' ); ?></label><br />
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"<?php checked( $hierarchical ); ?> />
				<label for="<?php echo $this->get_field_id( 'hierarchical' ); ?>"><?php _e( 'Show hierarchy', 'gallery-plugin' ); ?></label></p>
		<?php }
	}
}

/**
 * Class extends WP class WP_Widget, and create new widget
 * Latest Galleries
 */
if ( ! class_exists( 'GLLR_Latest_Galleries_Widget' ) ) {
	class GLLR_Latest_Galleries_Widget extends WP_Widget {
		/**
		 * constructor of class
		 */
		public function __construct() {
			$widget_ops = array( 'classname'   => 'GLLR_Latest_Galleries_Widget',
			                     'description' => __( "Displays the latest galleries and a link to the Galleries page.", 'gallery-plugin' )
			);
			parent::__construct( 'gllr_latest_galleries_widget', __( 'Latest Galleries', 'gallery-plugin' ), $widget_ops );
			add_action( 'load-widgets.php', array( $this, 'gllr_widget_scripts' ) );
		}

		/**
		 * Function to connect color selection script
		 *
		 */
		public function gllr_widget_scripts() {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		/**
		 * Function to displaying widget in front end
		 *
		 */
		public function widget( $args, $instance ) {
			global $gllr_options;
			$widget_title                      = ( ! empty( $instance['widget_title'] ) ) ? apply_filters( 'widget_title', $instance['widget_title'], $instance, $this->id_base ) : '';
			$widget_galleries_button_text      = ( ! empty( $instance['widget_galleries_button_text'] ) ) ? apply_filters( 'widget_galleries_button_text', $instance['widget_galleries_button_text'], $instance, $this->id_base ) : '';
			$widget_galleries_button_link      = ( ! empty( $instance['widget_galleries_button_link'] ) ) ? apply_filters( 'widget_galleries_button_link', $instance['widget_galleries_button_link'], $instance, $this->id_base ) : '';
			$widget_galleries_count_display    = ( ! empty( $instance['widget_galleries_count_display'] ) ) ? apply_filters( 'widget_galleries_count_display', $instance['widget_galleries_count_display'], $instance, $this->id_base ) : '4';
			$widget_galleries_hover_color      = isset( $instance['widget_galleries_hover_color'] ) ? stripslashes( esc_html( $instance['widget_galleries_hover_color'] ) ) : '#F1F1F180';
			$widget_galleries_table_wrapper = '<div class="gllr-widget-table"><table><tr>';
			$widget_galleries_table_wrapper_end = '</tr></table></div>';

			if ( ! empty( $widget_title ) ) {
				echo $args['before_widget'] . $args['before_title'] . $widget_title . $args['after_title'];
			} else {
				echo $args['before_widget'] . $widget_title;
			}

			$gallery_posts = get_posts( array(
				'numberposts' => $widget_galleries_count_display,
				'post_type'   => $gllr_options['post_type_name']
			) );

			if ( 0 < count( $gallery_posts ) ) {
				if ( 6 > count( $gallery_posts ) ) {
					$count_column                  = 4;
					$count_row                     = 2;
				} else if( 10 > count( $gallery_posts ) ) {
					$count_column                  = 4;
					$count_row                     = 3;
				} else {
					$count_column                  = 6;
					$count_row                     = 3;
				}

				/* get properties of cell for each gallery and button */
				$gllr_cells_properties = gllr_generate_collage_template( count( $gallery_posts ) + 1, $count_column, $count_row );
				$gllr_image_number     = 0;
				$gllr_row_number       = 1;

				foreach ( $gallery_posts as $post ) {

				    /* check cell row */
					if(  $gllr_row_number != $gllr_cells_properties[ $gllr_image_number ]['row'] ){
						$widget_galleries_table_wrapper .= '</tr><tr>';
						$gllr_row_number = $gllr_cells_properties[ $gllr_image_number ]['row'];
                    }

					/* output galleries in cells  */
					$widget_galleries_table_wrapper .= $gllr_cells_properties[ $gllr_image_number ]['cell'] .
                        '<div class="gllr-widget-cell-item">' . get_the_post_thumbnail( $post->ID, 'full' ) . '
                            <div class="gllr-widget-cell-item-hover" style="background:' . $widget_galleries_hover_color . '80;">
                                <div class="gllr-widget-content-center">
                                   <a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a>
                                </div>
                            </div>
                        </div>
                    </td>';
					$gllr_image_number ++;
				}

				/* output button in cell */
				$widget_galleries_table_wrapper .= $gllr_cells_properties[ $gllr_image_number ]['cell'] . '<div class="gllr-widget-cell-item gllr-widget-content-center">
                            <a href="' . get_permalink( $widget_galleries_button_link ) . '"><span class="button">' . $widget_galleries_button_text . '</span></a>
                         </div>
				     </td></tr>';

				echo $widget_galleries_table_wrapper . $widget_galleries_table_wrapper_end;

			} else {
				_e( 'No galleries', 'gallery-plugin' );
			}
			echo $args['after_widget'];
		}

		/**
		 * Function to save widget settings
		 *
		 * @param array()    $new_instance  array with new settings
		 * @param array()    $old_instance  array with old settings
		 *
		 * @return array()   $instance      array with updated settings
		 */
		public function update( $new_instance, $old_instance ) {
			$instance                 = $old_instance;
			$instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ? strip_tags( $new_instance['widget_title'] ) : null;
			$instance['widget_galleries_button_text'] = isset( $new_instance['widget_galleries_button_text'] ) ? stripslashes( esc_html( $new_instance['widget_galleries_button_text'] ) ) : null;
			$instance['widget_galleries_button_link'] = isset( $new_instance['widget_galleries_button_link'] ) ? stripslashes( esc_html( $new_instance['widget_galleries_button_link'] ) ) : null;
			$instance['widget_galleries_count_display'] = isset( $new_instance['widget_galleries_count_display'] ) ? stripslashes( esc_html( $new_instance['widget_galleries_count_display'] ) ) : 4;
			$instance['widget_galleries_hover_color'] = isset( $new_instance['widget_galleries_hover_color'] ) ? stripslashes( esc_html( $new_instance['widget_galleries_hover_color'] ) ) : '#F1F1F1';

			return $instance;
		}

		/**
		 * Function to displaying widget settings in back end
		 *
		 * @param array()     $instance  array with widget settings
		 *
		 * @return void
		 */
		public function form( $instance ) {
			global $gllr_options;
			if ( empty( $gllr_options ) ) {
				gllr_settings();
			}
			$widget_title                   = isset( $instance['widget_title'] ) ? stripslashes( esc_html( $instance['widget_title'] ) ) : null;
			$widget_galleries_button_text   = isset( $instance['widget_galleries_button_text'] ) ? stripslashes( esc_html( $instance['widget_galleries_button_text'] ) ) : __( 'View more', 'gallery-plugin' );
			$widget_galleries_button_link   = isset( $instance['widget_galleries_button_link'] ) ? stripslashes( esc_html( $instance['widget_galleries_button_link'] ) ) : ( isset( $gllr_options['page_id_gallery_template'] ) ? $gllr_options['page_id_gallery_template'] : 0 );
			$widget_galleries_count_display = isset( $instance['widget_galleries_count_display'] ) ? stripslashes( esc_html( $instance['widget_galleries_count_display'] ) ) : 4;
			$widget_galleries_hover_color   = isset( $instance['widget_galleries_hover_color'] ) ? stripslashes( esc_html( $instance['widget_galleries_hover_color'] ) ) : '#F1F1F1';?>

			<p>
				<label for="<?php echo $this->get_field_id( 'widget_title' ); ?>">
					<?php _e( 'Title', 'gallery-plugin' ); ?>:
					<input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
					       name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
					       value="<?php echo esc_attr( $widget_title ); ?>"/>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'widget_galleries_button_text' ); ?>">
					<?php _e( 'Button Text', 'gallery-plugin' ); ?>:
					<input class="widefat" id="<?php echo $this->get_field_id( 'widget_galleries_button_text' ); ?>"
					       name="<?php echo $this->get_field_name( 'widget_galleries_button_text' ); ?>" type="text"
					       value="<?php echo esc_attr( $widget_galleries_button_text ); ?>"/>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'widget_galleries_button_link' ); ?>">
					<?php _e( 'Link to the Galleries Page', 'gallery-plugin' ); ?>:
					<?php $args = array(
						'depth'    => 0,
						'selected' => $widget_galleries_button_link,
						'echo'     => 1,
						'name'     => $this->get_field_name( 'widget_galleries_button_link' ),
						'class'    => 'widefat'
					);
					wp_dropdown_pages( $args ) ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'widget_galleries_count_display' ); ?>">
					<?php _e( 'Number of Galleries to Display', 'gallery-plugin' ); ?>:
					<select class="widefat" id="<?php echo $this->get_field_id( 'widget_galleries_count_display' ); ?>"
					        name="<?php echo $this->get_field_name( 'widget_galleries_count_display' ); ?>">
						<option <?php selected( $widget_galleries_count_display, 2 ); ?>>2</option>
						<option <?php selected( $widget_galleries_count_display, 4 ); ?>>4</option>
						<option <?php selected( $widget_galleries_count_display, 6 ); ?>>6</option>
						<option <?php selected( $widget_galleries_count_display, 8 ); ?>>8</option>
                        <option <?php selected( $widget_galleries_count_display, 10 ); ?>>10</option>
                        <option <?php selected( $widget_galleries_count_display, 12 ); ?>>12</option>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'widget_galleries_hover_color' ); ?>">
					<?php _e( 'Gallery Hover Color', 'gallery-plugin' ); ?>:</label>
				<input class="widefat gllr-hover-color" id="<?php echo $this->get_field_id( 'widget_galleries_hover_color' ); ?>"
				       name="<?php echo $this->get_field_name( 'widget_galleries_hover_color' ); ?>" type="text"
				       value="<?php echo esc_attr( $widget_galleries_hover_color ); ?>",
				       data-default-color="#F1F1F1"/>
			</p>
            <?php $script = "jQuery( document ).ready( function( $ ) {

                    $( '.widget-inside:has(.gllr-hover-color)' ).each( function () {
                        initColorPicker( $( this ) );
                    } );

                    $( document ).on( 'widget-added widget-updated', onFormUpdate );

                    function initColorPicker( widget ) {
                        widget.find( '.gllr-hover-color' ).not('[id*=\"__i__\"]').wpColorPicker( {
                            change: function( e, ui ) {
                                $( e.target ).val( ui.color.toString() );
                                $( e.target ).trigger( 'change' ); // enable widget \"Save\" button
                            },
                        } );
                    }

                    function onFormUpdate( event, widget ) {
                        initColorPicker( widget );
                    }

                } );";
			wp_register_script( 'gllr_form_script', '' );
			wp_enqueue_script( 'gllr_form_script' );
			wp_add_inline_script( 'gllr_form_script', sprintf( $script ) ); ?>
			<noscript>
				<p>
					<?php _e( 'Please, enable JavaScript in Your browser.', 'gallery-plugin' ); ?>
				</p>
			</noscript>
		<?php }
	}
}

/**
 * Function generate position and sizes for cells in collage
 *
 * @param int $count_galleries count galleries with button
 * @param int $count_column    count columns in grid
 * @param int $count_row       count rows in grid
 *
 * @return array()   $cell_properties       array with classes and coordinates for cells
 */
if ( ! function_exists( 'gllr_generate_collage_template' ) ) {
	function gllr_generate_collage_template( $count_galleries, $count_column, $count_row ) {
		$count_empty_cell = $count_column * $count_row - $count_galleries;
		$count_large_cell = $count_medium_cell = 0;
		$image_number     = 1;

		if ( $count_empty_cell < 3 ) {
			$count_medium_cell = $count_empty_cell + $image_number;
			$count_large_cell  = $image_number;
		} else if ( 0 != $count_empty_cell % 3 ) {
			$count_large_cell  = floor( $count_empty_cell / 3 ) + $image_number;
			$count_medium_cell = $count_empty_cell % 3 + $count_large_cell;
		} else {
			$count_large_cell = $count_empty_cell / 3 + $image_number;
		}

		/* Create empty template. Zero is empty cell */
		$collage_template = array();
		for ( $i = 0; $i < $count_row; $i ++ ) {
			for ( $j = 0; $j < $count_column; $j ++ ) {
				$collage_template[ $i ][ $j ] = 0;
			}
		}

		/* Random Insert large cells */
		while ( $count_large_cell > $image_number ) {
			$i = rand( 0, $count_row - 2 );
			$j = rand( 0, $count_column - 2 );

			if ( 0 == $collage_template[ $i ][ $j ] && 0 == $collage_template[ $i + 1 ][ $j ] && 0 == $collage_template[ $i ][ $j + 1 ] && 0 == $collage_template[ $i + 1 ][ $j + 1 ] ) {
				$collage_template[ $i ][ $j ]              = $collage_template[ $i + 1 ][ $j ] = $collage_template[ $i ][ $j + 1 ] = $collage_template[ $i + 1 ][ $j + 1 ] = $image_number;
				$cell_properties[ $image_number ]['cell'] = '<td colspan="2" rowspan="2">';
				$cell_properties[ $image_number ]['col']     = $j + 1;
				$cell_properties[ $image_number ]['row']     = $i + 1;
				$image_number ++;
			}
		}

		/* Random Insert wide and tall cells */
		while ( $count_medium_cell > $image_number ) {
			$i = rand( 0, $count_row - 2 );
			$j = rand( 0, $count_column - 2 );

			if ( 0 == $collage_template[ $i ][ $j ] ) {
				switch ( rand( 0, 1 ) ) {
					case 0:
						if ( 0 == $collage_template[ $i + 1 ][ $j ] ) {
							$collage_template[ $i ][ $j ]              = $collage_template[ $i + 1 ][ $j ] = $image_number;
							$cell_properties[ $image_number ]['cell'] = '<td rowspan="2">';
							$cell_properties[ $image_number ]['col']     = $j + 1;
							$cell_properties[ $image_number ]['row']     = $i + 1;
							$image_number ++;
						}
						break;
					case 1:
						if ( 0 == $collage_template[ $i ][ $j + 1 ] ) {
							$collage_template[ $i ][ $j ]              = $collage_template[ $i ][ $j + 1 ] = $image_number;
							$cell_properties[ $image_number ]['cell'] = '<td colspan="2">';
							$cell_properties[ $image_number ]['col']     = $j + 1;
							$cell_properties[ $image_number ]['row']     = $i + 1;
							$image_number ++;
						}
						break;
				}
			}

			if ( $count_medium_cell == $image_number ) {
				break;
			}

			$i = rand( 1, $count_row - 1 );
			$j = rand( 1, $count_column - 1 );

			if ( 0 == $collage_template[ $i ][ $j ] ) {
				switch ( rand( 0, 1 ) ) {
					case 0:
						if ( 0 == $collage_template[ $i - 1 ][ $j ] ) {
							$collage_template[ $i ][ $j ]              = $collage_template[ $i - 1 ][ $j ] = $image_number;
							$cell_properties[ $image_number ]['cell'] = '<td rowspan="2">';
							$cell_properties[ $image_number ]['col']     = $j + 1;
							$cell_properties[ $image_number ]['row']     = $i;
							$image_number ++;
						}
						break;
					case 1:
						if ( 0 == $collage_template[ $i ][ $j - 1 ] ) {
							$collage_template[ $i ][ $j ]              = $collage_template[ $i ][ $j - 1 ] = $image_number;
							$cell_properties[ $image_number ]['cell'] = '<td colspan="2">';
							$cell_properties[ $image_number ]['col']     = $j;
							$cell_properties[ $image_number ]['row']     = $i + 1;
							$image_number ++;
						}
						break;
				}
			}
		}

		/* Insert small cells */
		for ( $i = 0; $i < $count_row; $i ++ ) {
			for ( $j = 0; $j < $count_column; $j ++ ) {
				if ( 0 == $collage_template[ $i ][ $j ] ) {
					$collage_template[ $i ][ $j ]              = $image_number;
					$cell_properties[ $image_number ]['cell'] = '<td>';
					$cell_properties[ $image_number ]['col']     = $j + 1;
					$cell_properties[ $image_number ]['row']     = $i + 1;
					$image_number ++;
				}
			}
		}

		usort( $cell_properties, 'gllr_cell_sort' );

		return $cell_properties;
	}
}

if ( ! function_exists( 'gllr_cell_sort' ) ) {
	function gllr_cell_sort( $a, $b ) {
		if ( $a['row'] == $b['row'] ) {
			return $a['col'] > $b['col'];
		} else {
			return $a['row'] > $b['row'];
		}
	}
}

add_action( 'widgets_init', 'gllr_register_widget' );