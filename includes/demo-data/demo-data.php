<?php
/**
 * Contents array with demo-data for
 * Gallery Plugin by Bestwebsoft
 */

if ( ! function_exists( 'bws_demo_data_array' ) ) {
	function bws_demo_data_array( $post_type ) {
		$posts = array(
			/* Page Template Gallery */
			array(
				'comment_status' 		=> 'closed',
				'ping_status'    		=> 'closed',
				'post_status'    		=> 'publish',
				'post_type'      		=> 'page',
				'post_title'     		=> 'DEMO Galleries',
				'post_content'   		=> '',
				'save_to_options'		=> 'page_id_gallery_template'
			),
			/* Galleries */
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_type'      => $post_type,
				'post_title'     => 'DEMO Gastronomy',
				'post_content'   => 'Nowadays food photographing is becoming more and more popular. Today thousands of blogs are devoted to such pictures. Using our Gallery plugin you can post such images easily! For more information visit <a href="https://bestwebsoft.com/products/wordpress/plugins/gallery/">Our Site</a>',
				'post_meta'      => array(
					'gllr_download_link' => '1'
				),
				'attachments_folder' => 'gastronomy'
			),
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_type'      => $post_type,
				'post_title'     => 'DEMO Music',
				'post_content'   => 'Music is an integral part of our lives. It surrounds us everywhere. Save memorable moments of the concerts and festivals and share them with your friends! Our Gallery plugin will help you with this. For more information visit <a href="https://bestwebsoft.com/products/wordpress/plugins/gallery/">Our Site</a>',
				'post_meta'      => array(
					'gllr_download_link' => '1'
				),
				'attachments_folder' => 'music'
			),
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_type'      => $post_type,
				'post_title'     => 'DEMO Travelling',
				'post_content'   => 'Millions of people all over the world spend their holidays travelling. Nowadays we can also share our impressions with friends! And our Gallery plugin will help you with this. Also you can use an exclusive add-on Gallery Categories which allows you to create different categories of galleries on your site. For more information visit <a href="https://bestwebsoft.com/">Our Site</a>',
				'post_meta'      => array(
					'gllr_download_link' => '1'
				),
				'attachments_folder' => 'travelling'
			),
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_type'      => $post_type,
				'post_title'     => 'DEMO Sport',
				'post_content'   => 'Sport is Life! And now you have a great opportunity to share your achievements with the whole world. And our Gallery plugin will help you with this. For more information visit <a href="https://bestwebsoft.com/products/wordpress/plugins/gallery/">Our Site</a>',
				'post_meta'      => array(
					'gllr_download_link' => '1'
				),
				'attachments_folder' => 'sport'
			),
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_type'      => $post_type,
				'post_title'     => 'DEMO Nature',
				'post_content'   => 'Look for inspiration in the nature! You can admire its beauty all day long. With our Gallery plugin you can share beautiful pictures with your friends. For more information visit <a href="https://bestwebsoft.com/products/wordpress/plugins/gallery/">Our Site</a>',
				'post_meta'      => array(
					'gllr_download_link' => '1'
				),
				'attachments_folder' => 'nature'
			),
			/* Post with Gallery shortcodes */
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_type'      => 'post',
				'post_title'     => 'Gallery DEMO',
				'post_content'   => '<p>This is a demonstration of a Gallery plugin for Wordpress websites.</p><h2>Create amazing galleries in few clicks</h2><p>Gallery plugin helps you to collect  images and display them on your website. Add unlimited galleries to your website - no programming knowledge required.</p><h2>Expand your possibilities with exclusive add-ons</h2><ul><li><a href="https://bestwebsoft.com/products/wordpress/plugins/gallery-categories/" target="_blank">Gallery categories</a>: Create different categories of your galleries.</li></ul><h2><span id="result_box" class="short_text" lang="en"><span class="hps">Help &amp; Support</span></span></h2><p>If you have any questions, our friendly Support Team is happy to help. <a href="https://support.bestwebsoft.com/" target="_blank">Visit our Help Center</a></p><h2>Shortcodes</h2><p>Use <code><strong>&#91;print_gllr id=<i>gallery_id</i> display=short]</strong></code> shortcode for displaying short description and link to the single gallery.</p><div>[print_gllr id={last_post_id} display=short]</div><div></div>Use <code><strong>&#91;print_gllr id=<i>gallery_id</i>]</strong></code> shortcode for displaying all images in gallery.<div>[print_gllr id={last_post_id}]</div>&nbsp;<div>{template_page} | <a href="https://drive.google.com/drive/u/0/folders/0B5l8lO-CaKt9QkJNaERwVEJnSVE" target="_blank">Instructions</a></div>',
			),
		);

		$attachments = array(
			/* gastronomy */
			'apple.jpg' => array(
				'gllr_image_alt_tag' => 'Apple',
				'gllr_image_text'    => 'Apple',
				'gllr_link_url'      => '',
			),
			'brunch.jpg' => array(
				'gllr_image_alt_tag' => 'Brunch',
				'gllr_image_text'    => 'Brunch',
				'gllr_link_url'      => '',
			),
			'cheese.jpg' => array(
				'gllr_image_alt_tag' => 'Cheese',
				'gllr_image_text'    => 'Cheese',
				'gllr_link_url'      => '',
			),
			'cinnamon.jpg' => array(
				'gllr_image_alt_tag' => 'Cinnamon',
				'gllr_image_text'    => 'Cinnamon',
				'gllr_link_url'      => '',
			),
			'cupcakes.jpg' => array(
				'gllr_image_alt_tag' => 'Cupcakes',
				'gllr_image_text'    => 'Cupcakes',
				'gllr_link_url'      => '',
			),
			'grapes.jpg' => array(
				'gllr_image_alt_tag' => 'Grapes',
				'gllr_image_text'    => 'Grapes',
				'gllr_link_url'      => '',
			),
			'hamburger.jpg' => array(
				'gllr_image_alt_tag' => 'Hamburger',
				'gllr_image_text'    => 'Hamburger',
				'gllr_link_url'      => '',
			),
			'pasta.jpg' => array(
				'gllr_image_alt_tag' => 'Pasta',
				'gllr_image_text'    => 'Pasta',
				'gllr_link_url'      => '',
			),
			'peanut_butter.jpg' => array(
				'gllr_image_alt_tag' => 'Peanut Butter',
				'gllr_image_text'    => 'Peanut Butter',
				'gllr_link_url'      => '',
			),
			'pizza.jpg' => array(
				'gllr_image_alt_tag' => 'Pizza',
				'gllr_image_text'    => 'Pizza',
				'gllr_link_url'      => '',
			),
			'restaurant.jpg' => array(
				'gllr_image_alt_tag' => 'Restaurant',
				'gllr_image_text'    => 'Restaurant',
				'gllr_link_url'      => '',
			),
			'spice.jpg' => array(
				'gllr_image_alt_tag' => 'Spice',
				'gllr_image_text'    => 'Spice',
				'gllr_link_url'      => '',
			),
			'cake.jpg' => array(
				'gllr_image_alt_tag' => 'Cake',
				'gllr_image_text'    => 'Cake',
				'gllr_link_url'      => '',
			),
			/* music */
			'acoustic_guitar.jpg' => array(
				'gllr_image_alt_tag' => 'Acoustic Guitar',
				'gllr_image_text'    => 'Acoustic Guitar',
				'gllr_link_url'      => '',
			),
			'disco.jpg' => array(
				'gllr_image_alt_tag' => 'Disco',
				'gllr_image_text'    => 'Disco',
				'gllr_link_url'      => '',
			),
			'dj.jpg' => array(
				'gllr_image_alt_tag' => 'DJ',
				'gllr_image_text'    => 'DJ',
				'gllr_link_url'      => '',
			),
			'drummer.jpg' => array(
				'gllr_image_alt_tag' => 'Drummer',
				'gllr_image_text'    => 'Drummer',
				'gllr_link_url'      => '',
			),
			'guitar_case.jpg' => array(
				'gllr_image_alt_tag' => 'Guitar Case',
				'gllr_image_text'    => 'Guitar Case',
				'gllr_link_url'      => '',
			),
			'headphones.jpg' => array(
				'gllr_image_alt_tag' => 'Headphones',
				'gllr_image_text'    => 'Headphones',
				'gllr_link_url'      => '',
			),
			'metronome.jpg' => array(
				'gllr_image_alt_tag' => 'Metronome',
				'gllr_image_text'    => 'Metronome',
				'gllr_link_url'      => '',
			),
			'music.jpg' => array(
				'gllr_image_alt_tag' => 'Music',
				'gllr_image_text'    => 'Music',
				'gllr_link_url'      => '',
			),
			'musicassette.jpg' => array(
				'gllr_image_alt_tag' => 'Musicassette',
				'gllr_image_text'    => 'Musicassette',
				'gllr_link_url'      => '',
			),
			'notes.jpg' => array(
				'gllr_image_alt_tag' => 'Notes',
				'gllr_image_text'    => 'Notes',
				'gllr_link_url'      => '',
			),
			'saxophone.jpg' => array(
				'gllr_image_alt_tag' => 'Saxophone',
				'gllr_image_text'    => 'Saxophone',
				'gllr_link_url'      => '',
			),
			'tickets.jpg' => array(
				'gllr_image_alt_tag' => 'Tickets',
				'gllr_image_text'    => 'Tickets',
				'gllr_link_url'      => '',
			),
			/* nature */
			'blueberry.jpg' => array(
				'gllr_image_alt_tag' => 'Blueberry',
				'gllr_image_text'    => 'Blueberry',
				'gllr_link_url'      => '',
			),
			'dandelion.jpg' => array(
				'gllr_image_alt_tag' => 'Dandelion',
				'gllr_image_text'    => 'Dandelion',
				'gllr_link_url'      => '',
			),
			'flower.jpg' => array(
				'gllr_image_alt_tag' => 'Flower',
				'gllr_image_text'    => 'Flower',
				'gllr_link_url'      => '',
			),
			'grass.jpg' => array(
				'gllr_image_alt_tag' => 'Grass',
				'gllr_image_text'    => 'Grass',
				'gllr_link_url'      => '',
			),
			'ladybug.jpg' => array(
				'gllr_image_alt_tag' => 'Ladybug',
				'gllr_image_text'    => 'Ladybug',
				'gllr_link_url'      => '',
			),
			'forest_edge.jpg' => array(
				'gllr_image_alt_tag' => 'Landscape',
				'gllr_image_text'    => 'Landscape',
				'gllr_link_url'      => '',
			),
			'roses.jpg' => array(
				'gllr_image_alt_tag' => 'Roses',
				'gllr_image_text'    => 'Roses',
				'gllr_link_url'      => '',
			),
			'tulips.jpg' => array(
				'gllr_image_alt_tag' => 'Tulips',
				'gllr_image_text'    => 'Tulips',
				'gllr_link_url'      => '',
			),
			'water.jpg' => array(
				'gllr_image_alt_tag' => 'Water',
				'gllr_image_text'    => 'Water',
				'gllr_link_url'      => '',
			),
			'winter.jpg' => array(
				'gllr_image_alt_tag' => 'Winter',
				'gllr_image_text'    => 'Winter',
				'gllr_link_url'      => '',
			),
			'wood.jpg' => array(
				'gllr_image_alt_tag' => 'Wood',
				'gllr_image_text'    => 'Wood',
				'gllr_link_url'      => '',
			),
			'grasshopper.jpg' => array(
				'gllr_image_alt_tag' => 'Grasshopper',
				'gllr_image_text'    => 'Grasshopper',
				'gllr_link_url'      => '',
			),
			/* sport */
			'air_gymnastics.jpg' => array(
				'gllr_image_alt_tag' => 'Air Gymnastics',
				'gllr_image_text'    => 'Air Gymnastics',
				'gllr_link_url'      => '',
			),
			'baseball.jpg' => array(
				'gllr_image_alt_tag' => 'Baseball',
				'gllr_image_text'    => 'Baseball',
				'gllr_link_url'      => '',
			),
			'basketball.jpg' => array(
				'gllr_image_alt_tag' => 'Basketball',
				'gllr_image_text'    => 'Basketball',
				'gllr_link_url'      => '',
			),
			'bike.jpg' => array(
				'gllr_image_alt_tag' => 'Bike',
				'gllr_image_text'    => 'Bike',
				'gllr_link_url'      => '',
			),
			'paraglider.jpg' => array(
				'gllr_image_alt_tag' => 'Paraglider',
				'gllr_image_text'    => 'Paraglider',
				'gllr_link_url'      => '',
			),
			'ski.jpg' => array(
				'gllr_image_alt_tag' => 'Ski',
				'gllr_image_text'    => 'Ski',
				'gllr_link_url'      => '',
			),
			'soccer.jpg' => array(
				'gllr_image_alt_tag' => 'Soccer',
				'gllr_image_text'    => 'Soccer',
				'gllr_link_url'      => '',
			),
			'swimming_pool.jpg' => array(
				'gllr_image_alt_tag' => 'Swimming Pool',
				'gllr_image_text'    => 'Swimming Pool',
				'gllr_link_url'      => '',
			),
			'tennis_rackets.jpg' => array(
				'gllr_image_alt_tag' => 'Tennis Rackets',
				'gllr_image_text'    => 'Tennis Rackets',
				'gllr_link_url'      => '',
			),
			'track.jpg' => array(
				'gllr_image_alt_tag' => 'Track',
				'gllr_image_text'    => 'Track',
				'gllr_link_url'      => '',
			),
			'motorcycle.jpg' => array(
				'gllr_image_alt_tag' => 'Motorcycle',
				'gllr_image_text'    => 'Motorcycle',
				'gllr_link_url'      => '',
			),
			'snowboard.jpg' => array(
				'gllr_image_alt_tag' => 'Snowboard',
				'gllr_image_text'    => 'Snowboard',
				'gllr_link_url'      => '',
			),
			/* travelling */
			'accessories.jpg' => array(
				'gllr_image_alt_tag' => 'Accessories',
				'gllr_image_text'    => 'Accessories',
				'gllr_link_url'      => '',
			),
			'beach.jpg' => array(
				'gllr_image_alt_tag' => 'Beach',
				'gllr_image_text'    => 'Beach',
				'gllr_link_url'      => '',
			),
			'coffee.jpg' => array(
				'gllr_image_alt_tag' => 'Coffee',
				'gllr_image_text'    => 'Coffee',
				'gllr_link_url'      => '',
			),
			'compass.jpg' => array(
				'gllr_image_alt_tag' => 'Compass',
				'gllr_image_text'    => 'Compass',
				'gllr_link_url'      => '',
			),
			'france.jpg' => array(
				'gllr_image_alt_tag' => 'France',
				'gllr_image_text'    => 'France',
				'gllr_link_url'      => '',
			),
			'globe.jpg' => array(
				'gllr_image_alt_tag' => 'Globe',
				'gllr_image_text'    => 'Globe',
				'gllr_link_url'      => '',
			),
			'landscape.jpg' => array(
				'gllr_image_alt_tag' => 'Landscape',
				'gllr_image_text'    => 'Landscape',
				'gllr_link_url'      => '',
			),
			'luggage.jpg' => array(
				'gllr_image_alt_tag' => 'Luggage',
				'gllr_image_text'    => 'Luggage',
				'gllr_link_url'      => '',
			),
			'luggage_cart.jpg' => array(
				'gllr_image_alt_tag' => 'Luggage Cart',
				'gllr_image_text'    => 'Luggage Cart',
				'gllr_link_url'      => '',
			),
			'maps.jpg' => array(
				'gllr_image_alt_tag' => 'Maps',
				'gllr_image_text'    => 'Maps',
				'gllr_link_url'      => '',
			),
			'motel.jpg' => array(
				'gllr_image_alt_tag' => 'Motel',
				'gllr_image_text'    => 'Motel',
				'gllr_link_url'      => '',
			),
			'railway.jpg' => array(
				'gllr_image_alt_tag' => 'Railway',
				'gllr_image_text'    => 'Railway',
				'gllr_link_url'      => '',
			),
			'teak_forests.jpg' => array(
				'gllr_image_alt_tag' => 'Teak Forests',
				'gllr_image_text'    => 'Teak Forests',
				'gllr_link_url'      => '',
			),
			'travel.jpg' => array(
				'gllr_image_alt_tag' => 'Travel',
				'gllr_image_text'    => 'Travel',
				'gllr_link_url'      => '',
			)
		);
		return array(
			'posts'       => $posts,
			'attachments' => $attachments
		);
	}
}