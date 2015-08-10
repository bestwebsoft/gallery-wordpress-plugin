function gllr_replace_image_blocks( max_block_count, resize ) {
	(function($) {

		var row_width = $( ".gllr_image_row" ).width(),
			first_block_count = $( ".gllr_image_row:first .gllr_image_block" ).length,
			block_width = $( ".gllr_image_block:first" ).outerWidth( true );	

		if ( block_width * first_block_count == row_width ) {
			var last_block_css_width = parseInt( $( ".gllr_image_block:last-child" ).find( 'p' ).css( 'width' ) ) - 1;
			$( ".gllr_image_block:last-child" ).find( 'p' ).css( 'width', last_block_css_width );
		}

		/* check width */
		if ( block_width * first_block_count > row_width ) {

			var count_blocks_in_row = row_width / block_width;

			if ( parseInt( count_blocks_in_row ) != count_blocks_in_row ) {
				count_blocks_in_row = Math.floor( count_blocks_in_row );
			} else {
				count_blocks_in_row--;
			}
			if ( count_blocks_in_row != 0 ) {
				var total_count = 0,
					count_for_replace = first_block_count - count_blocks_in_row;

				$( '.gllr_image_row' ).each( function() {
					total_count = total_count +	count_for_replace;
					if ( $( this ).is( ':last-child' ) ) {
						var last_blocks_count = $( this ).find( ".gllr_image_block" ).length;
						
						if ( block_width * last_blocks_count >= row_width ) {
							var count_row_for_create = Math.ceil( last_blocks_count / count_blocks_in_row );
							for ( var i = 0; i < count_row_for_create; i++ ) {
								$( '<div class="gllr_image_row"></div>' ).insertAfter( '.gllr_image_row:last-child' );
							}												
						}
					} else {
						var next = $( this ).next().find( '.gllr_image_block:first' );
						$( this ).find( '.gllr_image_block' ).slice( - total_count ).insertBefore( next );
					}
				});	
				$( '.gllr_image_row' ).each( function() {	
					var blocks_count = $( this ).find( ".gllr_image_block" ).length;
					if ( blocks_count > count_blocks_in_row ) {		
						var count_for_replace = blocks_count - count_blocks_in_row;
						$( $( this ).next() ).append( $( this ).find( '.gllr_image_block' ).slice( - count_for_replace ) );
					}
				});
			}
		} else {
			if ( true == resize && first_block_count < max_block_count && ( ( block_width * first_block_count ) < $( '.gallery_box_single' ).width() ) ) {
				var count_blocks_in_row = Math.floor( ( $( '.gallery_box_single' ).width() ) / block_width );

				if ( count_blocks_in_row > max_block_count ) {
					count_blocks_in_row = max_block_count;
				}

				if ( first_block_count < count_blocks_in_row ) {

					var all_rows_count = $( ".gllr_image_row" ).length;

					for ( var i = 0; i <= all_rows_count; i++ ) {
						var current = $( ".gllr_image_row" ).eq( i );

						if ( $( current ).length > 0 && $( current ).next().length > 0 ) {

							if ( $( current ).find( '.gllr_image_block' ).length < count_blocks_in_row ) {

								var count_for_replace = count_blocks_in_row - $( current ).find( '.gllr_image_block' ).length;

								if ( $( current ).next().find( '.gllr_image_block' ).length <= count_for_replace ) {
									$( current ).append( $( current ).next().find( '.gllr_image_block' ) );
									$( current ).next().remove();
								} else {
									if ( count_for_replace == 1 )
										$( current ).append( $( current ).next().find( '.gllr_image_block' ).slice( 0, 1 ) );
									else
										$( current ).append( $( current ).next().find( '.gllr_image_block' ).slice( 0, count_for_replace ) );
								} 				

								if ( $( current ).find( '.gllr_image_block' ).length < count_blocks_in_row ) {
									i--;
								}
							}
						}
					};
				}
			}
		}
	})(jQuery);
}

(function($) {
	$(document).ready( function() {			
		var max_block_count = $( ".gllr_image_row:first .gllr_image_block" ).length;
		if ( $( ".gllr_image_row" ).length > 0 ) {
			gllr_replace_image_blocks( max_block_count, false );		
		};

		$(window).resize( function() {
			gllr_replace_image_blocks( max_block_count, true );

			$( '.gllr_image_row' ).each( function() {
				if ( $( this ).find( '.gllr_image_block' ).length < 1 ) {
					$( this ).remove();
				}
			});
		});
	});	
})(jQuery);