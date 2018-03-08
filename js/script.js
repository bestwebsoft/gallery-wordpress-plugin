function gllr_setMessage( msg ) {
	( function( $ ) {
		$( ".error" ).hide();
		$( ".gllr_image_update_message" ).html( msg ).show();
	} )( jQuery );
}

function gllr_setError( msg ) {
	( function( $ ) {
		$( ".gllr_image_update_message" ).hide();
		$( ".error" ).html( msg ).show();
	} )( jQuery );
}

( function( $ ) {
	$( document ).ready( function() {
		/* include color-picker */
		if ( $.fn.wpColorPicker ) {
			$( '.gllr_color_field' ).wpColorPicker();
		}
		
		$( 'input[name="gllr_enable_image_opening"]' ).on( 'change', function() {
			if( $( 'input[name="gllr_enable_image_opening"]' ).prop( 'checked' ) ) {
				$( 'input[name="gllr_enable_lightbox"]' ).prop( 'checked', false );
				$( 'input[name="gllr_enable_lightbox"]' ).attr( 'disabled', true );
				$( 'input[name="gllr_enable_lightbox"]' ).trigger( 'change' );
			} else {
				$('input[name="gllr_enable_lightbox"]').removeAttr("disabled");
			}
		} ).trigger( 'change' );

		$( '#gllr_ajax_update_images' ).click( function() {
			gllr_setMessage( "<p>" + gllr_vars.update_img_message + "</p>" );
			var curr = 0;
			$.ajax( {
				/* update_img_url */
				url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
				type: "POST",
				data: "action1=get_all_attachment&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
				success: function( result ) {
					var list = $.parseJSON( result );
					if ( ! list ) {
						gllr_setError( "<p>" + gllr_vars.not_found_img_info + "</p>" );
						return;
					}
					$( '.gllr_loader' ).css( 'display', 'inline-block' );

					var curr = 0,
						all_count = Object.keys( list ).length;
					$.each( list, function( index, value ) {
						$.ajax( {
							url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
							type: "POST",
							data: "action1=update_image&id=" + value + '&gllr_ajax_nonce_field=' + gllr_vars.gllr_nonce,
							success: function( result ) {
								curr = curr + 1;
								if ( curr >= all_count ) {
									$.ajax( {
										url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
										type: "POST",
										data: "action1=update_options&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
									} );
									gllr_setMessage( "<p>" + gllr_vars.img_success + "</p>" );
									$( '.gllr_loader' ).hide();
								}
							}
						} );	
					} );
				},
				error: function( request, status, error ) {
					gllr_setError( "<p>" + gllr_vars.img_error + request.status + "</p>" );
				}
			} );
		} );

		if ( $( window ).width() < 800 ) {
			$.each(	$( '.gllr_add_responsive_column' ), function() {
				var content = '<div class="gllr_info hidden">';
				$.each(	$( this ).find( 'td:hidden' ).not( '.column-order' ), function() {
					content = content + '<label>' + $( this ).attr( 'data-colname' ) + '</label><br/>' + $( this ).html() + '<br/>';
					$( this ).html( '' );
				} );
				content = content + '</div>';
				$( this ).find( '.column-title' ).append( content );
				$( this ).find( '.gllr_info_show' ).show();
			} );
			$( '.gllr_info_show' ).on( 'click', function( event ) {
				event.preventDefault();
				if ( $( this ).next( '.gllr_info' ).is( ':hidden' ) ) {
					$( this ).next( '.gllr_info' ).show();
				} else {
					$( this ).next( '.gllr_info' ).hide();
				}
			} );
		}

		if ( ! $( '#post-body-content .attachments li' ).length )
			$( '.gllr-media-bulk-select-button' ).hide();

		$( '#gllr-media-insert' ).click( function open_media_window() {
			if ( this.window === undefined ) {
				this.window = wp.media( {
					title: gllr_vars.wp_media_title,
					library: { type: 'image' },
					multiple: true,
					button: { text: gllr_vars.wp_media_button }
				} );

				var self = this; /* Needed to retrieve our variable in the anonymous function below */
				this.window.on( 'select', function() {
					if ( 'grid' == $( 'input[name="gllr_mode"]' ).val() ) {
						var all = self.window.state().get( 'selection' ).toJSON();
						all.forEach( function( item, i, arr ) {
							$.ajax({
								url: '../wp-admin/admin-ajax.php',
								type: "POST",
								data: "action=gllr_add_from_media&add_id="+item.id+"&post_id="+$( '#post_ID' ).val()+"&mode=grid&gllr_ajax_add_nonce=" + gllr_vars.gllr_add_nonce,
								success: function( result ) {
									$( '#post-body-content .attachments' ).prepend( result );
									$( '#post-body-content .attachments li:first-child' ).addClass( 'success' );
									$( '.gllr-media-bulk-select-button' ).show();

									if ( ! $( '.attachments' ).data( 'ui-sortable' ) ) {										
										gllr_add_sortable();
									}
								}
							} );
							$( '<input type="hidden" name="gllr_new_image[]" id="gllr_new_image" value="' + item.id + '" />' ).appendTo( '#hidden' );
						} );
					} else {
						var all = self.window.state().get( 'selection' ).toJSON();
						all.forEach( function( item, i, arr ) {
							$.ajax({
								url: '../wp-admin/admin-ajax.php',
								type: "POST",
								data: "action=gllr_add_from_media&add_id="+item.id+"&post_id="+$( '#post_ID' ).val()+"&mode=list&gllr_ajax_add_nonce=" + gllr_vars.gllr_add_nonce,
								success: function( result ) {
									$( '#the-list' ).prepend( result );
									$( '#the-list tr:first-child' ).addClass( 'success' );
									$( '#the-list tr.no-items' ).remove();
									if ( ! $( '#the-list' ).data( 'ui-sortable' ) ) {
										gllr_add_sortable();
									}
								}
							} );
							$( '<input type="hidden" name="gllr_new_image[]" id="gllr_new_image" value="' + item.id + '" />' ).appendTo( '#hidden' );
						} );
					}
				} );
			}

			this.window.open();
			return false;
		} );

		function gllr_add_sortable() {			
			if ( $.fn.sortable ) {
				if ( $( "#the-list tr" ).length > 1 ) {
					$( '#the-list' ).sortable( {
						stop: function( event, ui ) {
							var g = $( '#the-list' ).sortable( 'toArray', { handle: ":not(input)" } );
							var f = g.length;
							$.each(	g,
								function( k,l ) {
									$( '#' + l + ' input[name^="_gallery_order"]' ).val( k + 1 );
								}
							)
						}
					} );
					$( "#the-list input" ).on( 'click', function() { $( this ).focus(); } );
				} else if ( $( ".attachments li" ).length > 1 ) {
					$( '.attachments' ).sortable( {
						stop: function( event, ui ) {
							var g = $( '.attachments' ).sortable( 'toArray' );
							var f = g.length;
							$.each(	g,
								function( k,l ) {
									$( '#' + l + ' input[name^="_gallery_order"]' ).val( k + 1 );
								}
							)
						}
					} );
				}
			}
		}
		gllr_add_sortable();

		$( '.gllr-media-bulk-select-button' ).on( 'click', function() {
			$( '.attachments' ).sortable( 'disable' ).addClass( 'bulk-selected' );
			$( '.gllr-wp-filter' ).addClass( 'selected' );
			$( '.gllr-media-attachment' ).on( 'click', function(){
				if ( $( this ).hasClass( 'details' ) )
					$( this ).removeClass( 'details' ).removeClass( 'selected' );
				else
					$( this ).addClass( 'details' ).addClass( 'selected' );
				if ( $( '.gllr-media-attachment.selected' ).length > 0 )
					$( '.gllr-media-bulk-delete-selected-button' ).removeAttr( 'disabled' );
				else
					$( '.gllr-media-bulk-delete-selected-button' ).attr( 'disabled', 'disabled' );
			} );
			$( '.gllr-media-check' ).on( 'click', function(){
				if ( $( this ).parent().hasClass( 'details' ) )
					$( this ).parent().removeClass( 'details' ).removeClass( 'selected' );
				else
					$( this ).parent().addClass( 'details' ).addClass( 'selected' );
				if ( $( '.gllr-media-attachment.selected' ).length > 0 )
					$( '.gllr-media-bulk-delete-selected-button' ).removeAttr( 'disabled' );
				else
					$( '.gllr-media-bulk-delete-selected-button' ).attr( 'disabled', 'disabled' );
				return false;
			} );
			return false;
		} );

		$( '.gllr-media-bulk-cansel-select-button' ).on( 'click', function() {
			$( '.attachments' ).sortable().removeClass( 'bulk-selected' );
			$( '.attachments' ).sortable( 'option', 'disabled', false );
			$( '.attachments li' ).removeClass( 'details selected' );
			$( '.gllr-wp-filter' ).removeClass( 'selected' );
			$( '.gllr-media-attachment' ).off( 'click' );
			$( '.gllr-media-check' ).off( 'click' );
			return false;
		} );

		$( document ).on( 'click', '.gllr-media-actions-delete', function() {
			if ( window.confirm( gllr_vars.warnSingleDelete ) ) {			
				var gllr_attachment_id = $( this ).parent().find( '.gllr_attachment_id' ).val();
				var gllr_post_id = $( this ).parent().find( '.gllr_post_id' ).val();

				$.ajax( {
					url: '../wp-admin/admin-ajax.php',
					type: "POST",
					data: "action=gllr_delete_image&delete_id_array=" + gllr_attachment_id + "&post_id=" + gllr_post_id + "&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
					success: function( result ) {
						if ( 'updated' == result ) {
							$( '#post-' + gllr_attachment_id ).remove();
							tb_remove();
							if ( ! $( '#post-body-content .attachments li' ).length ) {
								$( '.gllr-media-bulk-select-button' ).hide();
							}
						}
					}
				} );
			}
		} );

		$( '.gllr-media-bulk-delete-selected-button' ).on( 'click', function() {
			if ( 'disabled' != $( this ).attr( 'disabled' ) ) {
				if ( window.confirm( gllr_vars.warnBulkDelete ) ) {
					var delete_id_array = '';
					$( '.attachments li.selected' ).each( function() {
						delete_id_array += $( this ).attr( 'id' ).replace( 'post-', '' ) + ',';
					} );
					$( '.gllr-media-spinner' ).css( 'display', 'inline-block' );
					$( '.attachments' ).attr( 'disabled', 'disabled' );
					$.ajax( {
						url: '../wp-admin/admin-ajax.php',
						type: "POST",
						data: "action=gllr_delete_image&delete_id_array=" + delete_id_array + "&post_id=" + $( '#post_ID' ).val() + "&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
						success: function( result ) {
							if ( 'updated' == result ) {
								$( '.gllr-media-attachment.selected' ).remove();
								$( '.gllr-media-bulk-delete-selected-button' ).attr( 'disabled', 'disabled' );
								if ( ! $( '#post-body-content .attachments li' ).length ) {
									$( '.gllr-media-bulk-cansel-select-button' ).trigger( 'click' );
									$( '.gllr-media-bulk-select-button' ).hide();
								}
							}
							$( '.gllr-media-spinner' ).css( 'display', 'none' );
							$( '.attachments' ).removeAttr( 'disabled' );							
						}
					} );
				}
			}
			return false;
		} );

		$( '.post-type-gallery .view-switch a' ).on( 'click', function( event ) {
			if ( window.confirm( gllr_vars.confirm_update_gallery ) ) {
				event.preventDefault();
				var mode = $( 'input[name="gllr_mode"]' ).val();
				/* change view mode */
				$.ajax( {
					url: "../wp-admin/admin-ajax.php",
					type: "POST",
					data: "action=gllr_change_view_mode&mode=" + mode + "&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
					success: function( result ) {
						$( '#publishing-action .button-primary' ).click();
					}
				} );
			}
		} );
	} );
} )( jQuery );

/* Create notice on a gallery page */
function gllr_notice_wiev( data_id ) {
	( function( $ ) {
		/*	function to send Ajax request to gallery notice */
		gllr_notice_media_attach = function( post_id, thumb_id, typenow ) {
			$.ajax( {
				url: "../wp-admin/admin-ajax.php",
				type: "POST",
				data: "action=gllr_media_check&thumbnail_id=" + thumb_id + "&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce + "&post_type=" + typenow,
				success: function( html ) {
					if ( undefined != html.data ) {
						$( ".media-frame-content" ).find( "#gllr_media_notice" ).html( html.data );
						$( '.button.media-button-select' ).attr( 'disabled', 'disabled' );
					} else {
						$( '.button.media-button-select' ).removeAttr( 'disabled' );
					}
				}
			} );
		}
		gllr_notice_media_attach( wp.media.view.settings.post.id, data_id, typenow );
	} )( jQuery );
}