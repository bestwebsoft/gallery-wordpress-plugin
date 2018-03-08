( function( $ ) {
	$( document ).ready( function() {
		$( window ).resize( function() {

			$( ".gllr_grid:visible" ).each( function() {
				var $gallery = $( this ),
					gallery_wrap_width = $gallery.parent( '.gallery_box_single' ).width(),
					$gallery_rows = $gallery.find( '.gllr_image_row' ),
					$gallery_first_row = $gallery_rows.filter( ':first' ),
					$images = $gallery.find( '.gllr_image_block' ),
					images_in_first_row = $gallery_first_row.find( '.gllr_image_block' ),
					count_images_in_first_row = images_in_first_row.length,
					width_image_block_ = images_in_first_row.filter( ':first' ).width(),
					columns = $gallery.data( 'gllr-columns' ),
					count_images = $images.length,
					pre_count_images_in_row = Math.floor( gallery_wrap_width / width_image_block_ ),
					count_images_in_row = ( columns < pre_count_images_in_row ) ? columns : pre_count_images_in_row,
					count_rows = Math.ceil( count_images / count_images_in_row );

				if ( count_images_in_first_row != count_images_in_row && count_images_in_row != 0 ) {

					$gallery.empty();

					for( var i = 1; i <= count_rows; i++ ) {
						var $new_row = $( '<div/>', {
							'class' : 'gllr_image_row'
						} ),
						$new_images_in_row = $images.splice( 0, count_images_in_row );
						$new_row.append( $new_images_in_row );
						$gallery.append( $new_row );
					}
				}
			} );

			/* Set equal sizes for every list item in the row when galleries are set to be displayed inline */
			var $inline_list_items = $( '.gllr-display-inline li' );
			if ( $inline_list_items.length ) {
				$inline_list_items.css( { 'width' : '', 'height' : '' } );
				var	$chunk = $( [] ),
					item_count = 0,
					col_margins = $inline_list_items.outerWidth( true ) - $inline_list_items.outerWidth(),
					/* initial item width */
					init_width = $inline_list_items.data( 'gllr-width' ),
					parent_width = $( '.gllr-display-inline' ).innerWidth(),
					cols = Math.floor( parent_width/( init_width + col_margins ) ),
					/* recalculate column width so the columns take all the available width */
					calc_width =  Math.floor( parent_width / cols ) - col_margins;

				/* set items width to calculated value */
				$inline_list_items.css( { 'width' : calc_width } );

				/* divide items collection into chunks by the calculated columns number */
				$inline_list_items.each( function() {
					/* add each list item to chunk */
					$chunk = $chunk.add( $( this ) );

					item_count++;

					/* when the last element in the row is reached */
					if ( item_count == cols ) {
						/* calculate max item height in the row */
						var chunk_max_height = Math.max.apply( null, $chunk.map( function () {
							return $( this ).outerHeight();
						} ) );

						/* set equal height for all items in the row */
						$chunk.css( { 'height' : chunk_max_height } );
						/* clear chunk, start new */
						item_count = 0;
						$chunk = $( [] );
					}
				} );
			}
		} ).trigger( 'resize' );
	} );
} )( jQuery );