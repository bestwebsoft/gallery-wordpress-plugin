(function($) {
	$(document).ready( function() {
		$( window ).resize( function() {
			$( ".gllr_grid" ).each( function () {
				var $gallery = $( this ),
					gallery_wrap_width = $gallery.parent( '.gallery_box_single' ).width(),
					$gallery_rows = $gallery.find( '.gllr_image_row' ),
					$gallery_first_row = $gallery_rows.filter( ':first' ),
					$images = $gallery.find( '.gllr_image_block' ),
					images_in_first_row = $gallery_first_row.find( '.gllr_image_block' ),
					count_images_in_first_row = images_in_first_row.length,
					width_image_block_ = images_in_first_row.filter( ':first' ).width(),
					columns = $gallery.data( 'columns' ),
					count_images = $images.length,
					pre_count_images_in_row = Math.floor( gallery_wrap_width / width_image_block_ ),
					count_images_in_row = ( columns < pre_count_images_in_row ) ? columns : pre_count_images_in_row,
					count_rows = Math.ceil( count_images / count_images_in_row );

				if( count_images_in_first_row != count_images_in_row && count_images_in_row != 0 ) {

					$gallery.html( '' );

					for( var i = 1; i <= count_rows; i++ ) {
						var $new_row = $( '<div/>', {
							'class' : 'gllr_image_row'
						}),
						$new_images_in_row = $images.splice( 0, count_images_in_row );
						$new_row.append( $new_images_in_row );
						$gallery.append( $new_row );
					}
				}
			});
		}).trigger( 'resize' );
	});
})(jQuery);