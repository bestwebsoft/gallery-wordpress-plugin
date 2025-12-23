<?php
// This file is generated. Do not modify it manually.
return array(
	'gallery-plugin' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'gallery-plugin/gallery-plugin',
		'version' => '0.1.0',
		'title' => 'Gallery by BestWebSoft',
		'category' => 'widgets',
		'description' => 'Add beautiful galleries, albums & images to your WordPress website in few clicks.',
		'example' => array(
			
		),
		'keywords' => array(
			'gallery'
		),
		'attributes' => array(
			'galleryID' => array(
				'type' => 'number'
			),
			'display' => array(
				'type' => 'string'
			),
			'displayMode' => array(
				'type' => 'boolean'
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'gallery-plugin',
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	)
);
