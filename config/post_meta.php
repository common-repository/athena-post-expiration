<?php

return function ( $postName ) {
	$options = processOptions( $postName );

	return [
		'config' => [
			'title' => 'Athena Post Expirations',
			'id' => 'athena-post-settings',
			'pages' => $postName,
			'callback' => '',
			'context' => 'normal',
			'priority' => 'low'
		],
		'options' => [
			[
				'type' => 'post_meta',
				'id' => '_athena_options',
				'default' => ''
			],
			[
				'type' => 'select',
				'name' => __('Expiration Action', 'athena-post-expiration'),
				'desc' => '',
				'id' => '_athena_ex_type',
				'default' => 'draft',
				'options' => $options,
				'onChange' => true,
				'class' => 'athena-ex-type',
				'required' => 'false'
			],
			[
				'type' => 'text',
				'name' => __('Post Password', 'athena-post-expiration'),
				'desc' => '',
				'id' => '_athena_password',
				'default' => '',
				'activate' => 'athena-ex-type',
				'select' => 'password',
				'required' => true,
				'max' => 20
			],
			[
				'type' => 'category',
				'name' => __('Post Category', 'athena-post-expiration'),
				'desc' => '',
				'id' => '_athena_category',
				'default' => '',
				'activate' => 'athena-ex-type',
				'select' => 'category',
				'required' => true
			]
		]
	];
};
