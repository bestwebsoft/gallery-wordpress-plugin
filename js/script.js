function gllr_update_images() {
	(function($) {
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
					$( "#ajax_update_images" ).removeAttr( "disabled" );
					return;
				}		
				$( '#gllr_img_loader' ).show();

				function updatenImageItem() {
					if ( curr >= list.length ) {
						$.ajax({
							url: '../wp-admin/admin-ajax.php?action=gllr_update_image',
							type: "POST",
							data: "action1=update_options&gllr_ajax_nonce_field=" + gllr_vars.gllr_nonce,
							success: function( result ) {}
						});
						$( "#ajax_update_images" ).removeAttr( "disabled" );
						gllr_setMessage( "<p>" + gllr_vars.img_success + "</p>" );
						$( '#gllr_img_loader' ).hide();
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
	})(jQuery);
}

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

/* add notice about changing in the settings page */
(function($) {
	$(document).ready( function() {		
		$( '#gllr_settings_form input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#gllr_settings_notice' ).css( 'display', 'block' );
			};
		});
	});
})(jQuery);