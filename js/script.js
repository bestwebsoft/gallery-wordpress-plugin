function gllr_setMessage( msg ) {
	(function($) {
		$( ".error" ).hide();
		$( "#gllr_settings_message.updated" ).html( msg );
		$( "#gllr_settings_message.updated" ).show();
	})(jQuery);
}

function gllr_setError( msg ) {
	(function($) {
		$( "#gllr_settings_message.updated" ).hide();
		$( ".error" ).html( msg );
		$( ".error" ).show();
	})(jQuery);
}

(function($) {
	$(document).ready( function() {	
		/* add notice about changing in the settings page */	
		$( '#gllr_settings_form input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#gllr_settings_notice' ).css( 'display', 'block' );
			};
		});

		$( '#gllr_ajax_update_images' ).click( function() {
			gllr_setMessage( "<p>" + gllr_vars.update_img_message + "</p>" );
			var curr = 0;
			$.ajax({
				/* update_img_url */
				url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
				type: "POST",
				data: "action1=get_all_attachment&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
				success: function( result ) {
					var list = eval( '(' + result + ')' );				
					if ( ! list ) {
						gllr_setError( "<p>" + gllr_vars.not_found_img_info + "</p>" );
						$( "#gllr_ajax_update_images" ).removeAttr( "disabled" );
						return;
					}		
					$( '#gllr_img_loader' ).css( 'display', 'inline-block' );

					function updatenImageItem() {
						if ( curr >= list.length ) {
							$.ajax({
								url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
								type: "POST",
								data: "action1=update_options&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
								success: function( result ) {}
							});
							$( "#gllr_ajax_update_images" ).removeAttr( "disabled" );
							gllr_setMessage( "<p>" + gllr_vars.img_success + "</p>" );
							$( '#gllr_img_loader' ).css( 'display', 'none' );
							return;
						}

						$.ajax({
							url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
							type: "POST",
							data: "action1=update_image&id=" + list[ curr ].ID + '&gllr_ajax_nonce_field=' + gllr_vars.gllr_nonce,
							success: function( result ) {
								curr = curr + 1;
								updatenImageItem();
							}
						});
					}
					updatenImageItem();
				},
				error: function( request, status, error ) {
					gllr_setError( "<p>" + gllr_vars.img_error + request.status + "</p>" );
				}
			});
		});
		
		$( '#gllr-media-insert' ).click( function open_media_window() {
			if ( this.window === undefined ) {
				this.window = wp.media({
					title: gllr_vars.wp_media_title,
					library: { type: 'image' },
					multiple: true,
					button: { text: gllr_vars.wp_media_button }
				});

				var self = this; /* Needed to retrieve our variable in the anonymous function below */
				this.window.on( 'select', function() {
					if ( $( '.view-grid' ).hasClass( 'current' ) ) {
						var all = self.window.state().get( 'selection' ).toJSON();
						all.forEach( function( item, i, arr ) {
							$.ajax({
								url: '../wp-admin/admin-ajax.php',
								type: "POST",
								data: "action=gllr_add_from_media&add_id="+item.id+"&post_id="+$( '#post_ID' ).val()+"&mode=grid&gllr_ajax_add_nonce=" + gllr_vars.gllr_add_nonce,
								success: function( result ) {
									$( '#post-body-content .attachments' ).prepend( result );
									$( '#post-body-content .attachments li:first-child' ).addClass( 'success' );
								}
							});
							$('<input type="hidden" name="gllr_new_image[]" id="gllr_new_image" value="' + item.id + '" />').appendTo( '#hidden' );
						});
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
								}
							});
							$('<input type="hidden" name="gllr_new_image[]" id="gllr_new_image" value="' + item.id + '" />').appendTo( '#hidden' );
						});
					} 
				});
			}

			this.window.open();
			return false;
		});

		if ( $.fn.sortable ) {
			$( '#the-list' ).sortable( {
				stop: function( event, ui ) { 
					var g = $( '#the-list' ).sortable( 'toArray' );
					var f = g.length;
					$.each(	g,
						function( k,l ) {							
							$( '#' + l + ' input[name^="_gallery_order"]' ).val( k + 1 );
						}
					)
				}
			});
			
			$( '.attachments' ).sortable({
				stop: function( event, ui ) { 
					var g = $( '.attachments' ).sortable( 'toArray' );
					var f = g.length;
					$.each(	g,
						function( k,l ) {		
							$( '#' + l + ' input[name^="_gallery_order"]' ).val( k + 1 );
						}
					)
				}
			});
		}

		$( '.gllr-media-bulk-select-button' ).on( 'click', function() {
			$( '.attachments' ).sortable( 'disable' ).addClass( 'bulk-selected' );
			$( '.wp-filter' ).addClass( 'selected' );
			$( '.gllr-media-attachment-details' ).hide();
			$( '.gllr-media-attachment' ).on( 'click', function(){
				if ( $( this ).hasClass( 'details' ) )
					$( this ).removeClass( 'details' ).removeClass( 'selected' );
				else
					$( this ).addClass( 'details' ).addClass( 'selected' );
				if ( $( '.gllr-media-attachment.selected' ).length > 0 )
					$( '.gllr-media-bulk-delete-selected-button' ).removeAttr( 'disabled' );
				else
					$( '.gllr-media-bulk-delete-selected-button' ).attr( 'disabled', 'disabled' );
			});
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
			});
			return false;
		});

		$( '.gllr-media-bulk-cansel-select-button' ).on( 'click', function() {
			$( '.attachments' ).sortable().removeClass( 'bulk-selected' );
			$( '.attachments' ).sortable( 'option', 'disabled', false );
			$( '.attachments li' ).removeClass( 'details selected' );
			$( '.wp-filter' ).removeClass( 'selected' );
			$( '.gllr-media-attachment' ).off( 'click' );
			$( '.gllr-media-check' ).off( 'click' );
			if ( $( '.view-grid' ).hasClass( 'current' ) /*&& window.mobilecheck()*/ ) {
				$('.attachments').addClass( 'touch' );
				$('.touch .gllr-media-attachment').on( 'click', function mediaAttachmentDetails() {
					$( '.gllr-media-attachment-details' ).hide();
					$( this ).find( '.gllr-media-attachment-details' ).show();
				});
			}
			return false;
		});

		$( '.gllr-media-delete-attachment' ).on( 'click', function() {
			var gllr_attachment_id = $( this ).parent().find( '.gllr_attachment_id' ).val();
			var gllr_post_id = $( this ).parent().find( '.gllr_post_id' ).val();
			
			$.ajax({
				url: '../wp-admin/admin-ajax.php?action=gllr_delete_image',
				type: "POST",
				data: "action=gllr_delete_image&delete_id_array=" + gllr_attachment_id + "&post_id=" + gllr_post_id + "&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
				success: function( result ) {
					if ( result == 'updated' ) {
						$( '#post-'+gllr_attachment_id ).remove();
						tb_remove();
					}
				}
			});
		});

		$( '.gllr-media-bulk-delete-selected-button' ).on( 'click', function() {
			if ( 'disabled' != $( this ).attr( 'disabled' ) ) {
				if ( window.confirm( gllr_vars.warnBulkDelete ) ) {
					var delete_id_array = '';
					$( '.attachments li.selected' ).each( function() {
						delete_id_array += $( this ).attr( 'id' ).replace( 'post-', '' ) + ',';
					});
					$( '.gllr-media-spinner' ).css( 'display', 'inline-block' );
					$( '.attachments' ).attr( 'disabled', 'disabled' );
					$.ajax({
						url: '../wp-admin/admin-ajax.php?action=gllr_delete_image',
						type: "POST",
						data: "action=gllr_delete_image&delete_id_array="+delete_id_array+"&post_id="+$( '#post_ID' ).val()+"&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
						success: function( result ) {
							if ( result == 'updated' ) {
								$( '.gllr-media-attachment.selected' ).remove();
								$( '.gllr-media-bulk-delete-selected-button' ).attr( 'disabled', 'disabled' );
							}
							$( '.gllr-media-spinner' ).css( 'display', 'none' );
							$( '.attachments' ).removeAttr( 'disabled' );
							$('.touch .gllr-media-attachment').on( 'click', function mediaAttachmentDetails() {
								$( '.gllr-media-attachment-details' ).hide();
								$( this ).find( '.gllr-media-attachment-details' ).show();
							});
						}
					});
				}
			}
			return false;
		});

		window.mobilecheck = function() {
		   var check = false;
		   (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
		   return check; 
		};

		if ( $( '.view-grid' ).hasClass( 'current' ) && window.mobilecheck() ) {
			$('.attachments').addClass( 'touch' );
			$('.touch .gllr-media-attachment').on( 'click', function mediaAttachmentDetails() {
				$( '.gllr-media-attachment-details' ).hide();
				$( this ).find( '.gllr-media-attachment-details' ).show();
			});
		}
	});
})(jQuery);

/* Create notice on a gallery page */
function gllr_notice_wiev( data_id ) {
	(function( $ ) {
		/*	function to send Ajax request to gallery notice */
		gllr_notice_media_attach = function( post_id, thumb_id, typenow ) {
			$.ajax({
				url: "/wp-admin/admin-ajax.php",
				type: "POST",
				data: "action=gllr_media_check&&thumbnail_id=" + thumb_id + "&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce + "&post_type=" + typenow,
				success: function( html ) {				
					if ( undefined != html.data ) {
						$( ".media-frame-content" ).find( "#gllr_media_notice" ).html( html.data );
						$( '.button.media-button-select' ).attr( 'disabled', 'disabled' );
					} else {
						$( '.button.media-button-select' ).removeAttr( 'disabled' );
					}
				} 
			});	
		}
		gllr_notice_media_attach( wp.media.view.settings.post.id, data_id, typenow ); 
	})( jQuery );
}