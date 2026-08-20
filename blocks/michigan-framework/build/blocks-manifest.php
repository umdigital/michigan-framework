<?php
// This file is generated. Do not modify it manually.
return array(
	'accordion' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'michigan-framework/accordion',
		'version' => '1.0.0',
		'title' => 'U-M Accordion',
		'category' => 'layout',
		'icon' => 'sort',
		'description' => 'Accordion',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'selector' => 'span'
			),
			'id' => array(
				'type' => 'string'
			),
			'state' => array(
				'type' => 'string'
			)
		),
		'textdomain' => 'michigan-framework/accordion',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	)
);
