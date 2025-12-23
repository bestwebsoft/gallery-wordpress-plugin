<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 * @package block-developer-examples
 */

$displayGalleryID = $attributes['galleryID'];
$displayGalleryMode = $attributes['displayMode'] ? ' display=short' : '';
?>
<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo do_shortcode( '[print_gllr id=' . $displayGalleryID . '' . $displayGalleryMode . ']' ); ?>
</p>
