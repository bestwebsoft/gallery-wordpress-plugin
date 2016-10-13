<?php
/**
* Includes deprecated functions
 * @deprecated since 4.4.4
 * @todo remove after 01.04.2017
 */

/* Renaming old version option keys */
if ( ! function_exists( 'gllr_check_old_options' ) ) {
	function gllr_check_old_options() {
		if ( $old_options = get_option( 'gllr_options' ) ) {
			$update_option = false;
			if ( isset( $old_options['gllr_custom_size_name'] ) ) {
				$old_options['custom_size_name'] = $old_options['gllr_custom_size_name'];
				unset( $old_options['gllr_custom_size_name'] );
				$update_option = true;
			}
			if ( isset( $old_options['gllr_custom_size_px'] ) ) {
				$old_options['custom_size_px'] = $old_options['gllr_custom_size_px'];
				unset( $old_options['gllr_custom_size_px'] );
				$update_option = true;
			}
			if ( true === $update_option )
				update_option( 'gllr_options', $old_options );
		}
	}
}